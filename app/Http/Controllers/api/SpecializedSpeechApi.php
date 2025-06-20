<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Storage as FirebaseStorage;

class SpecializedSpeechApi extends Controller
{
    public function __construct()
    {
        $path = base_path('storage/firebase/firebase.json');

        if (!file_exists($path)) {
            die("This File Path .{$path}. does not exist.");
        }

        $this->database = (new Factory)
            ->withServiceAccount($path)
            ->withDatabaseUri('https://miolms-default-rtdb.firebaseio.com')
            ->createDatabase();

        $this->storage = (new Factory())
            ->withServiceAccount($path)
            ->withDefaultStorageBucket('miolms.firebasestorage.app')
            ->createStorage();
    }

    private function generateUniqueId(string $prefix): string
    {
        $now = now();
        $currentYear = $now->year;
        $currentMonth = str_pad($now->month, 2, '0', STR_PAD_LEFT);
        $currentDay = str_pad($now->day, 2, '0', STR_PAD_LEFT);
        $randomDigits = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        $announcementId = "{$prefix}{$currentYear}{$currentMonth}{$currentDay}{$randomDigits}";

        return $announcementId;
    }

    private function generateFeedback(array $words)
    {
        $phoneme_ipa = [
            "aa" => "ɑ",
            "ae" => "æ",
            "ah0" => "ə",
            "ah1" => "ʌ",
            "ao" => "ɔ",
            "aw" => "aʊ",
            "ay" => "aɪ",
            "b" => "b",
            "ch" => "tʃ",
            "d" => "d",
            "dh" => "ð",
            "eh" => "ɛ",
            "er" => "ɚ",
            "ey" => "eɪ",
            "f" => "f",
            "g" => "g",
            "hh" => "h",
            "ih" => "ɪ",
            "iy" => "i",
            "jh" => "dʒ",
            "k" => "k",
            "l" => "l",
            "m" => "m",
            "n" => "n",
            "ng" => "ŋ",
            "ow" => "oʊ",
            "oy" => "ɔɪ",
            "p" => "p",
            "r" => "r",
            "s" => "s",
            "sh" => "ʃ",
            "t" => "t",
            "th" => "θ",
            "uh" => "ʊ",
            "uw" => "u",
            "v" => "v",
            "w" => "w",
            "y" => "j",
            "z" => "z",
            "zh" => "ʒ",
        ];

        $tips = [
            "/p/" => [
                "description" => "voiceless bilabial plosive",
                "student" => "Press your lips together and release a small puff of air — like a quick “puh.” Feel the air on your hand.",
                "teacher" => "Focus on lip closure and aspiration. Use minimal pairs like pat vs bat. Try tissue-blowing exercises to visualize airflow.",
                "parent" => "Help your child press their lips and blow gently. Hold a tissue in front of their mouth to see it flutter when they say “puh.”"
            ],
            "/b/" => [
                "description"=> "voiced bilabial plosive",
                "student"=> "Press your lips together and say “buh” using your voice. Feel the buzz in your throat.",
                "teacher"=> "Contrast with /b/ to teach voicing. Use tactile feedback — place fingers on the throat to feel vibration.",
                "parent"=> "Say “buh” and let your child touch your throat to feel the buzz. Then have them try it themselves."
            ],
            "/t/" => [
                "description"=> "voiceless alveolar plosive",
                "student"=> "Tap your tongue behind your upper teeth and say “tuh.” It’s a quick, light sound.",
                "teacher"=> "Reinforce tongue-tip contact at the alveolar ridge. Use mirror practice and CVC words like top, tap.",
                "parent"=> "Help your child tap their tongue near their teeth. Use a mirror to show where the tongue goes."
            ],
            "/d/" => [
                "description" => "voiced alveolar plosive",
                "student" => "Touch your tongue behind your teeth and say “duh” with your voice. Feel the vibration.",
                "teacher" => "Contrast with /t/. Use voiced plosive drills and mirror feedback. Try minimal pairs like dog vs tog.",
                "parent" => "Say “duh” and let your child feel the throat buzz. Use a mirror to show tongue placement."
            ],
            "/g/" => [
                "description" => "voiced velar plosive",
                "student" => "Say “guh” by raising the back of your tongue and using your voice. Feel the buzz.",
                "teacher" => "Focus on voiced velars. Use contrasts like go vs coat. Use throat-touch feedback.",
                "parent" => "Say “guh” and let your child feel the vibration in their throat. Practice with words like go, gum."
            ],
            "/tʃ/" => [
                "description" => "voiceless postalveolar affricate",
                "student" => "Make a “ch” sound by stopping the air with your tongue then releasing it — like “chuh.”",
                "teacher" => "Teach affricate as stop + fricative. Use words like chew, chalk. Use train sound “ch-ch-ch” for fun.",
                "parent" => "Practice “ch” by pretending to be a train — “ch-ch-ch!” Make it playful and rhythmic."
            ],
            "/dʒ/" => [
                "description" => "voiced postalveolar affricate",
                "student" => "Say “juh” by stopping the air and releasing it with your voice. Feel the buzz.",
                "teacher" => "Reinforce voiced affricate production. Contrast with /tʃ/: jar vs char. Use mirror and tactile cues.",
                "parent" => "Say “juh” slowly and let your child feel the vibration. Use words like jam, jump."
            ],
            "/f/" => [
                "description"=> "voiceless labiodental fricative",
                "student" => "Gently bite your bottom lip and blow air out — say “ffff.”",
                "teacher"=> "Emphasize lip-teeth contact and airflow. Use mirrors and initial sound drills.",
                "parent"=> "Show your child how to bite their lip and blow. Use a feather or tissue to show the air movement."
            ],
            "/v/" => [
                "description" => "voiced labiodental fricative",
                "student" => "Bite your bottom lip and use your voice — say “vuh.” Feel the buzz.",
                "teacher" => "Contrast with /f/. Use tactile vibration exercises and minimal pairs like fan vs van.",
                "parent" => "Say “vuh” and let your child feel the buzzing in their lips and throat. Try “v-v-v” like a buzzing bee."
            ],
            "/θ/" => [
                "description" => "voiceless dental fricative",
                "student" => "Put your tongue between your teeth and blow — like “th” in think.",
                "teacher" => "Use mirror for interdental placement. Contrast with /s/ or /t/. Practice with slow airflow.",
                "parent" => "Help your child stick their tongue out a little and blow gently. Use a mirror to guide placement."
            ],
            "/ð/" => [
                "description" => "voiced dental fricative",
                "student" => "Put your tongue between your teeth and use your voice — say “th” like in this.",
                "teacher" => "Practice voiced dental fricatives with sustained phonation drills. Contrast with /θ/.",
                "parent" => "Say “th” with your child and feel the voice buzz through the tongue. Try words like that, them."
            ],
            "/s/" => [
                "description" => "voiceless alveolar fricative",
                "student" => "Make a long \"sss\" sound by letting air pass between your tongue and teeth without using your voice.",
                "teacher" => "Focus on tongue positioning near the alveolar ridge and airflow. Use minimal pairs like \"sip\" vs \"zip\".",
                "parent" => "Help your child say “sss” like a snake, with no voice, just air through the teeth."
            ],
            "/z/" => [
                "description" => "voiced alveolar fricative",
                "student" => "Make a buzzing \"zzz\" sound with your tongue near your teeth while using your voice.",
                "teacher" => "Contrast with /s/. Use tactile techniques to highlight vocal cord vibration.",
                "parent" => "Say “zzz” together and feel the buzzing in your throat while holding the sound."
            ],
            "/ʃ/" => [
                "description" => "voiceless postalveolar fricative",
                "student" => "Round your lips slightly and make a soft “shhh” sound like you’re asking someone to be quiet.",
                "teacher" => "Use whisper drills and contrast with /s/. Visual prompts and audio modeling help.",
                "parent" => "Make a fun “shhh” sound together like quieting a room. Use hand gestures for fun reinforcement."
            ],
            "/ʒ/" => [
                "description" => "voiced postalveolar fricative",
                "student" => "Say a smooth buzzing sound like the middle of “measure” — lips rounded, air flows with voice.",
                "teacher" => "Emphasize voiced frication behind alveolar ridge. Use repetition and isolation.",
                "parent" => "This one’s tricky — say a gentle “zhhh” sound together and feel the buzz in your throat."
            ],
            "/h/" => [
                "description" => "voiceless glottal fricative",
                "student" => "Gently breathe out as if you’re fogging up a mirror — that’s the “h” sound.",
                "teacher" => "Reinforce gentle glottal airflow. Combine with vowel onsets like “ha, he, hi.”",
                "parent" => "Have your child pretend to blow warm air on their hand — “hhh” like hot."
            ],
            "/m/" => [
                "description" => "bilabial nasal",
                "student" => "Close your lips and hum gently through your nose to make the “mmm” sound.",
                "teacher" => "Use nasal vibration awareness and bilabial drills. Practice in CVC words.",
                "parent" => "Say “mmm” like something tasty. Your child should feel a tickle in their nose."
            ],
            "/n/" => [
                "description" => "alveolar nasal",
                "student" => "Touch your tongue behind your teeth and hum through your nose — like “nnn.”",
                "teacher" => "Focus on alveolar nasal articulation. Use contrastive drills with /d/ or /t/.",
                "parent" => "Say “nnn” slowly and encourage your child to feel the buzz in their nose."
            ],
            "/ŋ/" => [
                "description" => "velar nasal",
                "student" => "Push the back of your tongue up to make the “ng” sound — like the end of “sing.”",
                "teacher" => "Practice final velar nasal articulation. Use phoneme segmentation drills.",
                "parent" => "Say “nggg” with your child — remind them not to let the air out their mouth."
            ],
            "/l/" => [
                "description" => "alveolar lateral approximant",
                "student" => "Touch your tongue to the spot behind your top teeth and let air flow around the sides — say “lll.”",
                "teacher" => "Emphasize lateral airflow and tongue-tip contact. Use mirror cues and repetition.",
                "parent" => "Practice “llll” together. Use tongue-tip contact behind teeth to guide them."
            ],
            "/r/" => [
                "description" => "alveolar approximant",
                "student" => "Curl the tip of your tongue slightly up without touching the roof of your mouth — say “rrr.”",
                "teacher" => "Focus on retroflex vs bunched articulations. Avoid lateralization.",
                "parent" => "This can be tricky. Say “rrr” like a growl together and feel the vibration."
            ],
            "/ɹ/" => [
                "description" => "alveolar approximant",
                "student" => "Curl the tip of your tongue slightly up without touching the roof of your mouth — say “rrr.”",
                "teacher" => "Focus on retroflex vs bunched articulations. Avoid lateralization.",
                "parent" => "This can be tricky. Say “rrr” like a growl together and feel the vibration."
            ],
            "/j/" => [
                "description" => "palatal approximant",
                "student" => "Say “yuh” by gliding your tongue up toward the roof of your mouth near the front.",
                "teacher" => "Use glide onset practice with vowels (e.g., yes, you). Emphasize tongue elevation.",
                "parent" => "Say “yuh” like in “yes” together, moving from quiet to louder for practice."
            ],
            "/w/" => [
                "description" => "labio-velar approximant",
                "student" => "Round your lips and say “wuh” while pushing air out with your voice.",
                "teacher" => "Emphasize rounded lips and gliding motion. Use vocalic onset drills.",
                "parent" => "Show your child how to round their lips and say “wuh” like blowing a kiss with sound."
            ],
            "/k/" => [
                "description" => "voiceless velar plosive",
                "student" => "Lift the back of your tongue to the roof of your mouth and release — say “kuh.”",
                "teacher" => "Practice velar articulation with visual and kinesthetic cues. Use words like kick, cat.",
                "parent" => "Help your child say “kuh” by lifting the back of their tongue. Try saying it while pretending to cough gently."
            ],
            "/ʔ/" => [
                "description" => "glottal plosive",
                "student" => "This is a catch in your throat — like the break in “uh-oh.” Gently stop and start the air.",
                "teacher" => "Practice glottal stops with awareness of throat closure. Use contrast with vowel onsets.",
                "parent" => "Say “uh-oh” with your child. Point out the tiny pause in the middle."
            ],
            "/iː/" => [
                "description" => "close front unrounded vowel",
                "student" => "Smile wide and stretch the sound — say a long “eeee” like you're smiling in a photo.",
                "teacher" => "Reinforce tense vowel positioning and sustained phonation. Contrast with /ɪ/ using minimal pairs like seat vs sit.",
                "parent" => "Encourage your child to smile and say a long “eeee” sound. Use a mirror to show the smile shape."
            ],
            "/ɪ/" => [
                "description" => "near-close near-front unrounded vowel",
                "student" => "Say a short “ih” sound with a relaxed mouth — quick and soft.",
                "teacher" => "Contrast with /iː/. Use vowel length discrimination and minimal pairs like bit vs beet.",
                "parent" => "Help your child say a short “ih” sound. Keep it light and relaxed — no smiling needed."
            ],
            "/e/" => [
                "description" => "close-mid front unrounded vowel",
                "student" => "Say “eh” with your mouth slightly open and your tongue forward — like in bed.",
                "teacher" => "Practice mid front vowel with vowel maps and /e/-/æ/ contrasts. Use visual cues.",
                "parent" => "Show your child how to say a flat “eh” sound — not too wide or narrow."
            ],
            "/æ/" => [
                "description" => "near-open front unrounded vowel",
                "student" => "Open your mouth wide and say “aah” like you're surprised — short and clear.",
                "teacher" => "Focus on low front tongue placement and open jaw. Use contrasts with /e/ and words like cat vs ket.",
                "parent" => "Say a wide “aah” together and exaggerate the mouth shape. Use playful expressions."
            ],
            "/ɑː/" => [
                "description" => "open back unrounded vowel",
                "student" => "Drop your jaw low and say “ahh” deep in your throat — hold it long and steady.",
                "teacher" => "Teach open back tongue placement. Use long vowel contrast drills like car vs cut.",
                "parent" => "Help your child say a low, long “ahh” — like at the doctor’s office. Use a mirror to show jaw drop."
            ],
            "/ʌ/" => [
                "description" => "open-mid back unrounded vowel",
                "student" => "Say “uh” with a relaxed mouth and neutral tongue — short and low.",
                "teacher" => "Focus on central stressed vowel production. Contrast with /ə/ and /ɑː/.",
                "parent" => "Practice a short, relaxed “uh” with your child. Use words like cup, mud."
            ],
            "/ɒ/" => [
                "description" => "open back rounded vowel",
                "student" => "Round your lips and drop your jaw slightly to say “aw” — short and deep.",
                "teacher" => "Reinforce rounded lip posture and open back tongue. Contrast with /ɔː/.",
                "parent" => "Say a quick, soft “aw” together while making round lips. Use words like hot, cot."
            ],
            "/ɔː/" => [
                "description" => "open-mid back rounded vowel",
                "student" => "Round your lips and make a long “awww” sound — like you're admiring something.",
                "teacher" => "Emphasize duration and lip rounding. Use minimal pairs like caught vs cot.",
                "parent" => "Practice a long “awww” sound while rounding lips — like blowing a kiss with sound."
            ],
            "/ʊ/" => [
                "description" => "near-close near-back rounded vowel",
                "student" => "Say a short “uh” sound with rounded lips — quick and soft.",
                "teacher" => "Focus on tongue height and lip rounding. Contrast with /uː/ using pairs like book vs boot.",
                "parent" => "Help your child say a short “uh” sound with slightly rounded lips. Use words like foot, look."
            ],
            "/uː/" => [
                "description" => "close back rounded vowel",
                "student" => "Say a long “ooo” by rounding your lips tightly and holding the sound.",
                "teacher" => "Practice prolonged back rounded vowels. Use /ʊ/ vs /uː/ contrasts like pool vs pull.",
                "parent" => "Say a long “ooo” together — like blowing a sound through a straw. Use words like moon, spoon."
            ],
            "/ə/" => [
                "description" => "mid central (schwa)",
                "student" => "Make a soft “uh” sound — very quick and gentle, like a whisper.",
                "teacher" => "Practice schwa in unstressed syllables. Use clapping or stress marking in multisyllabic words.",
                "parent" => "Practice quick and soft “uh” sounds in short words like banana, sofa. Keep it light and relaxed."
            ],
            "/ɜː/" => [
                "description" => "mid central unrounded vowel",
                "student" => "Say a long “err” sound in the middle of your mouth — steady and smooth.",
                "teacher" => "Teach mid central vowel with prolonged phonation. Contrast with /ə/ in stressed vs unstressed syllables.",
                "parent" => "Say “errr” slowly with your child and help them keep the sound even. Use words like bird, turn."
            ],
            "/eɪ/" => [
                "description" => "fronting diphthong",
                "student" => "Start with a short “eh” and glide into a smiling “ee” — one smooth, connected sound.",
                "teacher" => "Practice gliding from /e/ to /ɪ/. Use minimal pairs like bed vs bade to highlight the shift.",
                "parent" => "Say “eh–ee” slowly with your child. Use a mirror to show the smile forming at the end."
            ],
            "/aɪ/" => [
                "description" => "rising diphthong",
                "student" => "Begin with a wide “ah” and glide up into a sharp “ee” — like climbing a hill with your voice.",
                "teacher" => "Emphasize open mouth at the start and rising tongue movement. Contrast with /eɪ/ and /ɔɪ/.",
                "parent" => "Say “ah-ee” together slowly. Use hand motions to show the rising pitch and movement."
            ],
            "/ɔɪ/" => [
                "description" => "closing diphthong",
                "student" => "Round your lips for “aw” and slide into a quick “ee” — like turning a corner with your voice.",
                "teacher" => "Highlight the shift from rounded back to front close. Use contrasts like boy vs buy.",
                "parent" => "Practice “aw-ee” slowly. Show how the lips start round and end in a smile."
            ],
            "/aʊ/" => [
                "description" => "rising diphthong",
                "student" => "Say “ah” and glide into a rounded “oo” — feel your mouth move from open to round.",
                "teacher" => "Teach front open to back rounded transition. Use visual diagrams and tactile cues.",
                "parent" => "Try “ah-oo” slowly with your child. Use a mirror to show the lip rounding at the end."
            ],
            "/əʊ/" => [
                "description" => "closing diphthong",
                "student" => "Start with a soft “uh” and glide into a rounded “oo” — keep it smooth and flowing.",
                "teacher" => "Contrast with /ə/ and /uː/. Use mirror work to show lip rounding and tongue movement.",
                "parent" => "Say “uh–oo” slowly. Help your child round their lips at the end like blowing a bubble."
            ],
            "/ɪə/" => [
                "description" => "centering diphthong",
                "student" => "Start with “ih” and glide gently to “uh” — like sliding down a soft slope.",
                "teacher" => "Teach front-close to mid-central transition. Use vowel contour maps and slow repetition.",
                "parent" => "Say “ih–uh” slowly with your child. Keep the sound gentle and connected."
            ],
            "/eə/" => [
                "description" => "centering diphthong",
                "student" => "Say “eh” and slide into a soft “uh” — one smooth breathy sound.",
                "teacher" => "Emphasize mid-front to mid-central glide. Use elongation and stress placement in practice.",
                "parent" => "Say “eh–uh” slowly. Help your child keep the sound soft and flowing."
            ],
            "/ʊə/" => [
                "description" => "centering diphthong",
                "student" => "Start with a rounded “oo” and glide into “uh” — relax your lips at the end.",
                "teacher" =>"Practice rounded-to-central glides. Use mirror feedback and minimal pairs like poor vs paw.",
                "parent" => "Say “oo–uh” slowly. Show how the lips start rounded and relax at the end."
            ],
        ];

        $praise = [
            'student' => [
                "🎉 Fantastic! You pronounced “{word}” perfectly!",
                "✅ Well done! Your pronunciation of “{word}” was clear and confident!",
                "👏 Great job saying “{word}” – that was excellent!",
                "🌟 You nailed “{word}” flawlessly!",
                "🔥 Awesome work on “{word}” – keep it up!",
                "🥳 Wow! You said “{word}” like a pro!",
                "✨ Brilliant pronunciation of “{word}”! Keep rocking!",
                "🏆 You earned an A+ on “{word}”!",
                "🎈 Your production of “{word}” was outstanding!",
                "💯 Perfect! That was textbook “{word}”!",
                "🎶 Listen to that perfect “{word}” you just pronounced!",
                "🌈 Your pronunciation of “{word}” was as bright as a rainbow!",
                "🥇 That was gold-medal worthy “{word}”!",
                "🎵 Your “{word}” sounds music to my ears!",
                "👏 You crushed the “{word}” sound—so clear and precise!",
                "🎊 Brilliant work on your “{word}” sound!",
                "🌟 Stellar job! “{word}” has never sounded better.",
                "🏅 You’re acing that “{word}”—keep shining!",
                "✨ Your “{word}” is on point—fantastic effort!",
                "💯 Perfect production of “{word}”—well done!",
                "🎈 That “{word}” was immaculate—great going!",
                "🚀 Your “{word}” launch was spot-on!",
                "🥇 First place for “{word}”—excellent!",
                "🎯 Superb articulation of “{word}”!",
                "🥂 Cheers to your amazing “{word}”!",
                "🏅 Medal-worthy “{word}”—you’re doing fantastic!",
                "🌟 Your “{word}” shone like a star!",
                "🎉 What an incredible “{word}”! ",
                "✨ Your “{word}” sounded crystal clear!",
                "🎊 “{word}” has never sounded better!",
                "🎥 Cinematic “{word}” performance!",
                "💡 Illuminating “{word}”!",
                "📈 Upward trajectory on “{word}”!",
                "🌞 Sunny “{word}” shout-out!"
            ],
            'teacher' => [
                "Student pronounced “{word}” accurately. Integrate it into a full sentence next.",
                "Excellent work on “{word}”. Now practice using it in conversation or reading passages.",
                "“{word}” is stable. Monitor carry-over into longer utterances.",
                "No issues with “{word}”. Encourage use in varied contexts (questions, statements).",
                "Production of “{word}” is strong—progress to sentence-level drills.",
                "Great accuracy on “{word}”! Encourage using it in questions next.",
                "Reliable performance with “{word}”. Next, try in mixed practice sets.",
                "Solid mastery of “{word}”. Observe generalization across activities.",
                "Dependable “{word}” production—challenge with minimal-pair contrasts.",
                "Consistent “{word}” usage—introduce complex sentences including it.",
                "Excellent “{word}” articulation—track fluency under timed tasks.",
                "Strong production of “{word}”—consider incorporating into dialogue practice.",
                "“{word}” is well-produced; encourage spontaneous mentions in conversation.",
                "Exemplary “{word}”—now work on natural intonation patterns.",
                "Outstanding “{word}” production—set goals for contextual use.",
                "Flawless “{word}”—challenge with varying stress and intonation.",
                "Premium “{word}”—practice embedding in longer discourse.",
                "Reliable “{word}”—model in peer-to-peer activities.",
                "Monitor generalization of “{word}” in storytelling.",
                "Track maintenance of “{word}” over multiple sessions.",
                "Plan to combine “{word}” in next reading comprehension.",
                "Challenge student with synonyms containing “{word}.”",
                "Observe student's stress pattern on “{word}.”",
                "Incorporate “{word}” into group activities.",
                "Use digital flashcards for “{word}.”",
                "Encourage peer evaluation of “{word}” usage.",
                "Set up a mini-quiz around “{word}.”",
                "Record sessions to monitor “{word}” retention.",
                "Use video modeling for “{word}.”",
                "Provide prompts requiring “{word}.”",
                "Link “{word}” to a story context.",
                "Encourage writing sentences with “{word}.”",
                "Test spontaneous recall of “{word}.”",
                "Use transition phrases ending with “{word}.”"
            ],
            'parent' => [
                "👏 Your child said “{word}” perfectly! Try having them use it in a short story.",
                "🎉 Celebrate that clear “{word}” sound! You can practice it in fun sentences at home.",
                "🥳 They did a great job with “{word}”! Point it out while reading bedtime stories.",
                "🌟 Amazing pronunciation of “{word}”! Use it in daily routines (e.g., “I see a {word}”).",
                "✅ They’ve mastered “{word}”! Reinforce by having them teach it to someone else.",
                "🏡 Home practice idea: find {word} around the house and say it together.",
                "📚 Try reading a book and clap when you hear “{word}.”",
                "🎲 Play a word game using “{word}” and keep score!",
                "💡 Use daily routines—name objects like “{word}” aloud.",
                "🎨 Draw a picture of “{word}” and label it together.",
                "🎶 Sing a song that features “{word}” in the lyrics.",
                "📣 Cheer “{word}” like a champion when they say it!",
                "🛍️ On errands, point out items and say “{word}.”",
                "📅 Make “{word}” the word of the day and use it often.",
                "📱 Record your child saying “{word}” and celebrate playback!",
                "👪 Play “guess the word” with “{word}” as the hero!",
                "🍿 Pop popcorn—say “{word}” with each kernel popped!",
                "🧩 Spell “{word}” with magnets and pronounce together.",
                "🎉 Throw a mini “{word}” party when it's pronounced correctly!",
                "📝 Practice “{word}” by writing it in chalk on the sidewalk.",
                "📖 Look for “{word}” in street signs during walks.",
                "🎉 Have a surprise treat when “{word}” is pronounced correctly.",
                "📚 Create a mini-book featuring “{word}.”",
                "🥣 Say “{word}” each time you take a bite at dinner.",
                "🚶‍♂️ Take a nature walk and say “{word}” when you see it.",
                "🌟 Give a sticker every time “{word}” is said correctly.",
                "🔔 Ring a bell and say “{word}” when it's heard correctly.",
                "💬 Use “{word}” as a 'secret handshake' word with family.",
                "🎻 Play music and say “{word}” to the beat.",
                "🖐️ Give a high-five after each correct “{word}.”",
                "☎️ Call a relative and share “{word}” with them.",
                "🎨 Paint the “{word}” and label it for art time.",
                "🚴‍♀️ Say “{word}” each time you pedal on a bike.",
                "⭐ Post a 'word of the day' poster featuring “{word}.”"
            ]
        ];

        $lowestPhoneme = null;
        $lowestScore   = null;

        foreach ($words as $word) {
            if (empty($word['phones']) || !is_array($word['phones'])) {
                continue;
            }

            foreach ($word['phones'] as $phone) {
                if (!isset($phone['phone'], $phone['quality_score'])) {
                    continue;
                }

                $phoneme = $phone['phone'];
                $score   = $phone['quality_score'];

                if ($lowestScore === null || $score < $lowestScore) {
                    $lowestScore   = $score;
                    $lowestPhoneme = $phoneme;
                }
            }
        }

        if ($lowestPhoneme === null) {
            return [];
        }

        $ipa    = $phoneme_ipa[$lowestPhoneme] ?? null;
        $tipKey = $ipa ? "/{$ipa}/" : null;

        $feedback = [
            'phoneme' => $lowestPhoneme,
            'score'   => $lowestScore,
            'ipa'     => $ipa,
            'student' => null,
            'teacher' => null,
            'parent'  => null,
        ];

        if ($lowestScore >= 90) {
            if (!empty($praise['student'])) {
                $templ = $praise['student'][array_rand($praise['student'])];
                $feedback['student'] = str_replace('{word}', $lowestPhoneme, $templ);
            }
            if (!empty($praise['teacher'])) {
                $templ = $praise['teacher'][array_rand($praise['teacher'])];
                $feedback['teacher'] = str_replace('{word}', $lowestPhoneme, $templ);
            }
            if (!empty($praise['parent'])) {
                $templ = $praise['parent'][array_rand($praise['parent'])];
                $feedback['parent'] = str_replace('{word}', $lowestPhoneme, $templ);
            }
        } else {
            if ($tipKey && isset($tips[$tipKey]['student'])) {
                $feedback['student'] = $tips[$tipKey]['student'];
                $feedback['teacher'] = $tips[$tipKey]['teacher'];
                $feedback['parent'] = $tips[$tipKey]['parent'];
            }
        }

        return $feedback;
    }

    public function generateTeacherPronunciationReport(array $data): string
    {
        $report = [];

        $wordData = $data['words'][0] ?? null;
        if (!$wordData) return "No word data found.";

        $word = $wordData['word'] ?? 'N/A';
        $report[] = "🗂 Pronunciation Report for '{$word}'";

        $cefr = $data['cefr_pronunciation_score'] ?? 'N/A';
        $ielts = $data['ielts_pronunciation_score'] ?? 'N/A';
        $toeic = $data['toeic_pronunciation_score'] ?? 'N/A';
        $pte = $data['pte_pronunciation_score'] ?? 'N/A';
        $speechace = $data['speechace_pronunciation_score'] ?? 'N/A';

        $report[] = "\n📊 Overall Scores:";
        $report[] = "• CEFR: {$cefr}";
        $report[] = "• IELTS: {$ielts}";
        $report[] = "• TOEIC: {$toeic}";
        $report[] = "• PTE: {$pte}";
        $report[] = "• MIÓ: {$speechace}";

        $phonemeIssues = [];
        foreach ($wordData['phones'] as $phone) {
            $actual = $phone['phone'] ?? '';
            $heard = $phone['sound_most_like'] ?? '';
            $score = $phone['quality_score'] ?? 0;

            if ($actual !== $heard || $score < 95) {
                $phonemeIssues[] = "• /{$actual}/ ➜ Heard as /{$heard}/ (Score: " . round($score, 1) . ")";
            }

            if (isset($phone['child_phones'])) {
                foreach ($phone['child_phones'] as $child) {
                    $childScore = $child['quality_score'] ?? 0;
                    $childSound = $child['sound_most_like'] ?? '';
                    if ($childScore < 95) {
                        $phonemeIssues[] = "  ↳ Sub-sound heard as /{$childSound}/ (Score: " . round($childScore, 1) . ")";
                    }
                }
            }
        }

        if (!empty($phonemeIssues)) {
            $report[] = "\n🎯 Phoneme Accuracy:";
            $report = array_merge($report, $phonemeIssues);
        } else {
            $report[] = "\n🎯 Phoneme Accuracy: All sounds were accurate and clear.";
        }

        $stressIssues = [];
        if (isset($wordData['syllables'])) {
            foreach ($wordData['syllables'] as $syllable) {
                $letters = $syllable['letters'] ?? '';
                $actualStress = $syllable['stress_level'] ?? null;
                $expectedStress = $syllable['predicted_stress_level'] ?? null;

                if ($actualStress !== $expectedStress) {
                    $stressIssues[] = "• Syllable '{$letters}' stress mismatch (Expected: {$expectedStress}, Got: {$actualStress})";
                }
            }
        }

        if (!empty($stressIssues)) {
            $report[] = "\n🧭 Stress Accuracy:";
            $report = array_merge($report, $stressIssues);
        } else {
            $report[] = "\n🧭 Stress Accuracy: All syllables had correct stress.";
        }

        $report[] = "\n📌 Notes:";
        if (empty($phonemeIssues) && empty($stressIssues)) {
            $report[] = "No issues detected. Pronunciation is clear and proficient.";
        } else {
            $report[] = "Minor issues found. Review flagged sounds and stress patterns for targeted support.";
        }

        return implode("\n", $report);
    }


    private function pronunciationScoreApi(string $audioPath, string $word): array
    {
        $audioPath = Str::after($audioPath, 'public/');

        if (!Storage::disk('public')->exists($audioPath)) {
            return [];
        }

        $filePath = Storage::disk('public')->path($audioPath);

        $client = new Client([
            'base_uri' => 'https://api2.speechace.com',
            'timeout'  => 30,
        ]);

        try {
            $response = $client->request('POST', '/api/scoring/text/v9/json', [
                'query' => [
                    'key'     => env('SPEECHACE_API_KEY'),
                    'dialect' => 'en-us',
                    'user_id' => 'XYZ-ABC-99001',
                ],
                'multipart' => [
                    [
                        'name'     => 'text',
                        'contents' => $word,
                    ],
                    [
                        'name'     => 'user_audio_file',
                        'contents' => fopen($filePath, 'r'),
                        'filename' => basename($filePath),
                    ],
                ],
            ]);

            $decoded = json_decode($response->getBody(), true);

            $cleaned = [
                'text' => $decoded['text_score']['text'] ?? '',
                'overall_quality_score' => $decoded['text_score']['overall_quality_score'] ?? null,
                'ending_punctuation' => $decoded['text_score']['ending_punctuation'] ?? null,
                'ielts_pronunciation_score' => $decoded['text_score']['ielts_score']['pronunciation'] ?? null,
                'pte_pronunciation_score' => $decoded['text_score']['pte_score']['pronunciation'] ?? null,
                'toeic_pronunciation_score' => $decoded['text_score']['toeic_score']['pronunciation'] ?? null,
                'cefr_pronunciation_score' => $decoded['text_score']['cefr_score']['pronunciation'] ?? null,
                'speechace_pronunciation_score' => $decoded['text_score']['speechace_score']['pronunciation'] ?? null,
                'version' => $decoded['version'] ?? null,
                'request_id' => $decoded['request_id'] ?? null,
                'words' => [],
                'timestamp' => now()->toDateTimeString(),
            ];

            if (!empty($decoded['text_score']['word_score_list'])) {
                foreach ($decoded['text_score']['word_score_list'] as $wordData) {
                    $cleaned['words'][] = [
                        'word' => $wordData['word'] ?? '',
                        'quality_score' => $wordData['quality_score'] ?? null,
                        'phones' => $wordData['phone_score_list'] ?? [],
                        'syllables' => $wordData['syllable_score_list'] ?? [],
                    ];
                }
            }

            return $cleaned;

        } catch (RequestException $e) {
            Log::error('Speechace API failure', ['err' => $e->getMessage()]);
            return [];
        }
    }

    public function getSpeechActivities(Request $request, string $subjectId, string $category, string $difficulty){

        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try{
            $activities = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/{$category}/{$difficulty}")
                ->getSnapshot()
                ->getValue() ?? []; 

            $ids = array_keys($activities);

            return response()->json([
                'success'     => true,
                'activities' => $ids
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function checkActiveActivity(Request $request, string $subjectId, string $activityType, string $activityId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        try {
            $attempts = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attempts/{$activityType}/{$activityId}/{$userId}")
                ->getSnapshot()
                ->getValue() ?? [];

            $attempts_data = [];
            foreach ($attempts as $attemptId => $attempt) {
                $attempts_data[$attemptId] = [
                    'score'        => $attempt['overall_score'] ?? $attempt['score'] ?? null,
                    'submitted_at' => $attempt['submitted_at'] ?? null,
                ];
            }

            uasort($attempts_data, function ($a, $b) {
                if ($a['submitted_at'] === $b['submitted_at']) {
                    return 0;
                }
                return ($a['submitted_at'] < $b['submitted_at']) ? 1 : -1;
            });

            return response()->json([
                'success'  => true,
                'message'  => 'Successfully retrieved activity attempts',
                'attempts' => $attempts_data,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function continueActivity(Request $request, string $subjectId, string $activityType, string $activityId, string $attemptId){
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        try{
            $attempt = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attempts/{$activityType}/{$activityId}/{$userId}/{$attemptId}")
                ->getSnapshot()
                ->getValue() ?? [];

            if (empty($attempt)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attempt not found',
                ], 404);
            }

            $bucket = $this->storage->getBucket();

            $items = [];
            $latestTimestamp = null;
            $lastAnsweredIndex = 0;
            foreach ($attempt['answers'] as $index => $answer) {
                if (!empty($answer['answered_at'])) {
                    if (!$latestTimestamp || $answer['answered_at'] > $latestTimestamp) {
                        $latestTimestamp = $answer['answered_at'];
                        $lastAnsweredIndex = (int) $index;
                    }
                }

                if(!empty($answer['image_path'])){
                    $image_url = $bucket->object($answer['image_path'])->signedUrl(now()->addMinutes(15));
                    $items[$index] = [
                        'text' => $answer['text'],
                        'image_url' => $image_url,
                    ];
                }else{
                    $items[$index] = [
                        'text' => $answer['text'],
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'flashcards' => $items,
                'attemptId' => $attemptId,
                'last_answered' => $lastAnsweredIndex,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getActivityPictureById(Request $request, string $subjectId, string $activityType, string $difficulty, string $activityId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try {
            $activity = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/{$activityType}/{$difficulty}/{$activityId}")
                ->getSnapshot()
                ->getValue() ?? [];

            if (empty($activity)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Activity not found',
                ], 404);
            }

            $flashcards = [];

            $bucket = $this->storage->getBucket();

            foreach($activity['items'] as $index => $item){
                if($item['image_path'] ?? false){
                    $image = $bucket->object($item['image_path'])->signedUrl(now()->addMinutes(15));

                    $flashcards[] = [
                        'flashcard_id' => $index,
                        'image_url' => $image,
                        'text' => $item['text']
                    ];
                }else{
                    $flashcards[] = [
                        'flashcard_id' => $index,
                        'text' => $item['text']
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Activity retrieved successfully',
                'items' => $flashcards
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function createSpeechPictureActivity(Request $request, string $subjectId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        $validated = $request->validate([
            'activity_type' => 'required|in:picture',
            'difficulty' => 'required|in:easy,average,difficult,challenge',

            'flashcards' => 'required|array|min:1',
            'flashcards.*.text' => 'required|string|min:1|max:250',
            'flashcards.*.image' => 'required|file|mimes:jpg,png',
        ]);

        try {
            $activity_data = [];
            $bucket = $this->storage->getBucket();

            foreach ($validated['flashcards'] as $index => $flashcard) {
                $flashcard_id = (string) Str::uuid();
                $file = $flashcard['image'];
                $text = $flashcard['text'];
                $filename = $file->getClientOriginalName();
                $remotePath = 'images/speech/' . $flashcard_id . $filename ;

                $bucket->upload(
                    fopen($file->getPathname(), 'r'),
                    ['name' => $remotePath]
                );

                $activity_data[$flashcard_id] = [
                    'text' => $text,
                    'filename' => $filename,
                    'image_path' => $remotePath,
                ];
            }

            $activityType = $validated['activity_type'];
            $difficulty = $validated['difficulty'];
            $activity_id = $this->generateUniqueId('SPE');
            $date = now()->toDateTimeString();

            $activityData = [
                'items' => $activity_data,
                'total' => count($activity_data),
                'created_at' => $date,
                'created_by' => $userId
            ];

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/{$activityType}/{$difficulty}/{$activity_id}")
                ->set($activityData);

            return response()->json([
                'success' => true,
                'message' => "Activity created successfully",
                'activity' => $activityData,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function createSpeechActivity(Request $request, string $subjectId){
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        $validated = $request->validate([
            'activity_type' => 'required|in:question,phrase,pronunciation',
            'difficulty' => 'required|in:easy,average,difficult,challenge',

            'flashcards' => 'required|array',
            'flashcards.*.text' => 'required|string|min:1|max:250',
        ]);

        try{
            $activity_data = [];

            foreach ($validated['flashcards'] as $index => $flashcard) {
                $id = (string) Str::uuid();
                $activity_data[$id] = [
                    'text' => $flashcard['text'],
                ];
            }

            $activityType = $validated['activity_type'];
            $difficulty = $validated['difficulty'];
            $activity_id = $this->generateUniqueId('SPE');
            $date = now()->toDateTimeString();

            $activityData = [
                'items'=> $activity_data,
                'total' => count($activity_data),
                'created_at' => $date,
                'created_by' => $userId,
            ];

            $this->database
             ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/{$activityType}/{$difficulty}/{$activity_id}")
             ->set($activityData);

            return response()->json([
                'success' => true,
                'message'=> "Activity created successfully",
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function editSpeechPictureActivity(Request $request, string $subjectId, string $difficulty, string $activityId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        $validated = $request->validate([
            'flashcards' => 'required|array|min:1',
            'flashcards.*.text' => 'required|string|min:1|max:250',
            'flashcards.*.flashcard_id' => 'nullable|string|min:1',
            'flashcards.*.image' => 'nullable|file|mimes:jpg,png|max:5120',
        ]);

        try {
            $existing_activity = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/picture/{$difficulty}/{$activityId}")
                ->getSnapshot()
                ->getValue();

            if (empty($existing_activity)) {
                return response()->json([
                    'success' => false,
                    'message' => "Activity not found"
                ], 404);
            }

            $mapped_paths = [];
            $mapped_filenames = [];
            foreach ($existing_activity['items'] as $item_id => $item) {
                $mapped_paths[$item_id] = $item['image_path'];
                $mapped_filenames[$item_id] = $item['filename'];
            }

            $updated_items = [];
            $bucket = $this->storage->getBucket();

            foreach ($validated['flashcards'] as $flashcard) {
                $flashcard_id = $flashcard['flashcard_id'] ?? (String) Str::uuid();
                $remotePath = $mapped_paths[$flashcard_id] ?? null;
                $filename = $mapped_filenames[$flashcard_id] ?? null;

                if (isset($flashcard['image']) && $flashcard['image']) {
                    $image = $flashcard['image'];
                    $image_id = (string) Str::uuid();
                    $filename = $image->getClientOriginalName();
                    $remotePath = 'images/speech/' . $image_id . $filename; 

                    if(isset($mapped_paths[$flashcard_id])){
                        $bucket->object($mapped_paths[$flashcard_id])->delete();
                    }

                    $bucket->upload(
                        fopen($image->getPathname(), 'r'),
                        ['name' => $remotePath]
                    );
                }

                $updated_items[$flashcard_id] = [
                    'filename' => $filename,
                    'text' => $flashcard['text'],
                    'image_path' => $remotePath,
                ];
                
            }

            $date = now()->toDateTimeString();
            $userId = $request->get('firebase_user_id');

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/picture/{$difficulty}/{$activityId}")
                ->set([
                    'items' => $updated_items,
                    'total' => count($updated_items),
                    'updated_at' => $date,
                    'updated_by' => $userId,
                    'created_by' => $existing_activity['created_by'] ?? "",
                    'created_at' => $existing_activity['created_at'] ?? "",
                    'activity_difficulty' => $existing_activity['activity_difficulty'] ?? null,
                    'activity_title' => $existing_activity['activity_title'] ?? null,
                    'assessment_id' => $existing_activity['assessment_id'] ?? null,
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Activity updated successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function editSpeechActivity(Request $request, string $subjectId, string $activityType, string $difficulty, string $activityId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        $validated = $request->validate([
            'flashcards' => 'required|array|min:1',
            'flashcards.*.flashcard_id' => 'nullable|uuid',
            'flashcards.*.text' => 'required|string|min:1|max:250',
        ]);

        try {
            $existing_activity = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/{$activityType}/{$difficulty}/{$activityId}")
                ->getSnapshot()
                ->getValue() ?? [];

            if (empty($existing_activity)) {
                return response()->json([
                    'success' => false,
                    'message' => "Activity not found"
                ], 404);
            }

            $updated_items = [];
            foreach ($validated['flashcards'] as $flashcard) {
                $flashcard_id = $flashcard['flashcard_id'] ?? (string) Str::uuid();

                $updated_items[] = [
                    'flashcard_id' => $flashcard_id,
                    'text' => $flashcard['text'],
                ];
            }

            $date = now()->toDateTimeString();

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/{$activityType}/{$difficulty}/{$activityId}")
                ->set([
                    'items' => $updated_items,
                    'total' => count($updated_items),
                    'updated_at' => $date,
                    'updated_by' => $userId,
                    'created_by' => $existing_activity['created_by'] ?? "",
                    'created_at' => $existing_activity['created_at'] ?? "",
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Activity updated successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function startFlashcardActivity(Request $request, string $subjectId, string $activityType, string $difficulty, string $activityId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        try{
            $activityData = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/{$activityType}/{$difficulty}/{$activityId}")
                ->getSnapshot()
                ->getValue() ?? [];

            if (!$activityData) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Activity not found.'
                ], 404);
            }

            $flashcards = $activityData['items'];
            
            $attemptId = $this->generateUniqueId("ATTM");
            $startedAt = now()->toDateTimeString();

            $bucket = $this->storage->getBucket();

            $studentAnswers = [];
            $attemp = [];
            foreach ($flashcards as $flashcardId => $item) {
                $imagePath = $item['image_path'] ?? null;
                $imageUrl = null;

                if ($imagePath) {
                    $imageUrl = $bucket->object($imagePath)->signedUrl(now()->addMinutes(15));
                }

                $studentAnswers[$flashcardId] = [
                    'text' => $item['text'],
                    'image_url' => $imageUrl,
                ];

                $attemp[$flashcardId] = [
                    'text' => $item['text'],
                    'image_path' => $imagePath,
                ];
            }
            
            $initialInfo = [
                'answers' => $attemp,
                'started_at' => $startedAt,
                'status'     => 'in-progress',
            ];

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attempts/{$activityType}/{$activityId}/{$userId}/{$attemptId}")
                ->set($initialInfo);

            return response()->json([
                'success' => true,
                'attemptId' => $attemptId,
                'flashcards' => $studentAnswers,
            ],201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function submitFlashcardAnswer(
        Request $request,
        string $subjectId,
        string $activityType,
        string $activityId,
        string $attemptId,
        string $flashcardId
    ) {
        try {
            $gradeLevel = $request->get('firebase_user_gradeLevel');
            $userId = $request->get('firebase_user_id');

            $data = $request->validate([
                'audio_file' => 'required|file|mimetypes:video/mp4,audio/mp3',
            ]);

            $answersRef = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attempts/{$activityType}/{$activityId}/{$userId}/{$attemptId}/answers")
                ->getSnapshot()
                ->getValue() ?? [];

            $answer = $answersRef[$flashcardId];

            if (!isset($answersRef[$flashcardId])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid flashcard ID for this attempt',
                ], 400);
            }

            $answer = $answersRef[$flashcardId];
            $file = $request->file('audio_file');
            $uuid = (string) Str::uuid();
            $filename = $uuid . $file->getClientOriginalName();
            $path = $file->storeAs('audio_submissions', $filename, 'public');
            $remotePath = "audio/speech/{$activityType}/{$activityId}/{$userId}/{$attemptId}/{$filename}";
            $word = $answer['text'];

            $pronunciation_details = $this->pronunciationScoreApi($path, $word);
            $overallScore = $pronunciation_details['speechace_pronunciation_score'] ?? 0;

            $bucket = $this->storage->getBucket();
            $bucket->upload(
                fopen($file->getPathName(), 'r'),
                ['name' => $remotePath]
            );

            $feedbacks = $this->generateFeedback(
                $pronunciation_details['words'] ?? []
            );

            $now = now()->toDateTimeString();
            $updatedAnswer = [
                'audio_path' => $remotePath,
                'answered_at' => $now,
                'pronunciation_details' => $pronunciation_details,
                'feedback' => $feedbacks
            ];

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attempts/{$activityType}/{$activityId}/{$userId}/{$attemptId}/answers/{$flashcardId}")
                ->update($updatedAnswer);

            Storage::disk('public')->delete($path);

            return response()->json([
                'success' => true,
                'message' => 'Answer submitted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function finalizeFlashcardAttempt(
        Request $request,
        string $subjectId,
        string $activityType,
        string $difficulty,
        string $activityId,
        string $attemptId
    ) {
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');
        $now = now()->toDateTimeString();

        $ref = $this->database->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attempts/{$activityType}/{$activityId}/{$userId}/{$attemptId}");

        try {
            $answers = $ref->getChild('answers')->getSnapshot()->getValue() ?? [];

            $scores = [];
            $totalQuality = 0;
            $numCards = count($answers);

            foreach ($answers as $cardId => $answer) {
                $details = $answer['pronunciation_details'] ?? [];
                $wordsList = $details['words'] ?? [];

                if (! empty($wordsList) && is_array($wordsList[0])) {
                    $w = $wordsList[0];

                    $quality = $w['quality_score'] ?? 0;
                    $totalQuality += $quality;

                    $scores[$cardId] = [
                        'word' => $w['word'] ?? '',
                        'quality_score' => $quality,
                        'phones' => $w['phones'] ?? [],
                        'syllables' => $w['syllables'] ?? [],
                        'timestamp' => $details['timestamp'] ?? $now,
                    ];
                } else {
                    $scores[$cardId] = [
                        'word' => '',
                        'quality_score' => 0,
                        'phones' => [],
                        'syllables' => [],
                        'timestamp' => $now,
                    ];
                }
            }

            $overallAverage = $numCards > 0
                ? round($totalQuality / $numCards, 2)
                : 0;

            $ref->update([
                'status' => 'submitted',
                'overall_score' => $overallAverage,
                'submitted_at' => $now,
            ]);

            return response()->json([
                'success'       => true,
                'message'       => 'Activity submitted successfully.',
                'scores'        => $scores, // remove from the frontend // for teahcer only
                'overall_score' => $overallAverage,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Could not update activity status.',
            ], 500);
        }
    }
}