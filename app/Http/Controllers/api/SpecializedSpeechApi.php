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

    private function generateStudentFeedback(array $phones, float $overallScore = 0): ?string
    {
        $excellentScoreTemplates = [
            "🌟 Excellent pronunciation! You're clearly mastering the sounds!",
            "🏆 Outstanding! Your pronunciation was very clear — keep it up!",
            "🎉 Great job! Your speaking score shows strong articulation and confidence.",
            "👏 Impressive! You pronounced the word with clarity and control.",
            "💬 That was a sharp and clear performance — keep building on that momentum!",
            "🚀 You're sounding amazing — your pronunciation is spot-on!",
            "🌈 Brilliant work! Your overall speech score is showing real progress.",
            "📣 Loud and clear! You're doing a fantastic job with pronunciation.",
            "🧠 Great control! Your voice was well-recognized — nicely done!",
            "🎯 Almost perfect! Your pronunciation is reaching native-like clarity.",
            "🔥 Fantastic pronunciation! You're developing excellent speech habits.",
            "🎓 You're speaking like a pro — amazing pronunciation!",
            "🌟 Wow! That was crystal clear — your voice is getting stronger every time.",
            "🏅 You nailed it! Your pronunciation is confident and accurate.",
            "📈 You're leveling up! That pronunciation was top-tier.",
            "🥇 Gold star performance — your pronunciation skills are shining through!",
            "🌼 Smooth and natural — your pronunciation flows beautifully.",
            "🔊 Everything sounded just right — great job with that word!",
            "🎵 Your voice had rhythm and clarity — very impressive!",
            "🔑 That was a key step forward — flawless pronunciation!",
            "📚 You’re sounding fluent — all that practice is really showing!",
            "💎 Clear and polished! This was one of your best pronunciations yet.",
            "🎤 Your speech was strong, clear, and easy to understand — keep it up!",
            "🌠 Your pronunciation sparkled — great clarity and expression!",
            "💪 Confident delivery and strong articulation — amazing work!",
            "🌟 Consistent clarity — you’re mastering the sounds so well.",
            "📣 Your voice rang through perfectly — keep that energy going!",
            "🧩 Every sound was in place — excellent control and effort!",
        ];

        if ($overallScore >= 90) {
            return $excellentScoreTemplates[array_rand($excellentScoreTemplates)];
        }

        $templates = [
            "🌟 Nice try! Your '{phone}' sounded like '{sound_most_like}' — you're on the right track!",
            "👍 You're close! The '{phone}' came out like '{sound_most_like}', but you're improving.",
            "🗣️ Almost there! Make the '{phone}' sound clearer next time — it was heard as '{sound_most_like}'.",
            "💪 Good effort! The '{phone}' sounded like '{sound_most_like}' — just a little more focus needed.",
            "🎯 Getting better! That '{phone}' was heard as '{sound_most_like}', but you're making great progress!",
            "👏 You're improving! The '{phone}' slipped into '{sound_most_like}', but you're getting there.",
            "🔊 Oops! The '{phone}' was interpreted as '{sound_most_like}', but it’s okay — that happens.",
            "🧠 Learning moment: '{sound_most_like}' was heard instead of '{phone}' — great effort overall.",
            "🎵 The '{phone}' sound needs a bit of tuning — it registered more like '{sound_most_like}'.",
            "😊 Almost there! Just watch out — your '{phone}' sounded a bit like '{sound_most_like}'.",
            "🎤 Just a little off — the '{phone}' became '{sound_most_like}'. Keep up the practice!",
            "🚀 Great progress! Your '{phone}' turned into '{sound_most_like}', but that’s totally normal.",
            "🔁 That '{phone}' came out like '{sound_most_like}' — you’re definitely improving though.",
            "😄 Keep it up! Your '{phone}' was almost right, just softened into '{sound_most_like}'.",
            "📢 Boosting clarity helps! The '{phone}' came out as '{sound_most_like}'. You're learning fast!",
            "💡 Tip: The '{phone}' sounded like '{sound_most_like}' — check how your mouth moves for that sound.",
            "🔍 The '{phone}' could be sharper — it leaned toward '{sound_most_like}' this time.",
            "👐 The '{phone}' was shaped more like '{sound_most_like}' — a great chance to refine it.",
            "🏁 You're nearly there! The '{phone}' sounded like '{sound_most_like}', but it's all part of progress.",
            "📣 Your '{phone}' came across as '{sound_most_like}' — still, this is strong progress!",
            "🥳 So close! The '{phone}' had a hint of '{sound_most_like}', but you're doing great.",
            "🎉 You're doing great! That '{phone}' just sounded a bit like '{sound_most_like}'.",
            "📣 The system picked up '{sound_most_like}' instead of '{phone}' — learning happens one sound at a time.",
            "🚧 That '{phone}' leaned into '{sound_most_like}' — don’t worry, you're building real skills.",
            "🧱 Building blocks: the '{phone}' sound turned into '{sound_most_like}' — still a great step forward.",
            "🎬 The '{phone}' didn’t land quite right — it came across as '{sound_most_like}', but this helps us grow.",
            "🧏 The system picked up '{sound_most_like}' instead of '{phone}', but your progress is showing!",
            "🎨 That '{phone}' sound blended into '{sound_most_like}' — learning pronunciation is like shaping clay!",
            "📦 Your '{phone}' was packed a little too close to '{sound_most_like}' — good chance to refine it.",
            "🔬 Almost clear! Your '{phone}' slipped into '{sound_most_like}' — great focus so far.",
            "💫 Great energy! The '{phone}' slid into '{sound_most_like}' — small shifts like this help you grow.",
            "🚀 One small step! That '{phone}' echoed like '{sound_most_like}' — speech is a journey!",
            "🌿 The '{phone}' sounded like '{sound_most_like}', but small sounds shape big learning!",
            "🔈 The '{phone}' was heard more like '{sound_most_like}' — a small difference, but you're improving fast.",
            "🎯 That '{phone}' got mixed with '{sound_most_like}' — and that’s totally okay in learning.",
            "🎧 According to the system, your '{phone}' was heard more like '{sound_most_like}' — progress detected!",
            "🌟 Pronunciation update: '{phone}' was detected as '{sound_most_like}' — you’re tuning your sounds well.",
        ];

        $wrongPhones = array_filter($phones, function ($phone) {
            return isset($phone['sound_most_like'], $phone['phone']) &&
                $phone['sound_most_like'] !== $phone['phone'];
        });

        if (empty($wrongPhones)) {
            return null;
        }

        usort($wrongPhones, function ($a, $b) {
            return ($a['quality_score'] ?? 100) <=> ($b['quality_score'] ?? 100);
        });

        $worst = $wrongPhones[0];
        $template = $templates[array_rand($templates)];

        return str_replace(
            ['{phone}', '{sound_most_like}'],
            [$worst['phone'], $worst['sound_most_like']],
            $template
        );
    }

    private function generateParentFeedback(array $phones, float $overallScore = 0): string
    {
        $praiseTemplates = [
            "🌟 Your child is making wonderful progress in pronunciation!",
            "🎉 Great news! Your child’s pronunciation is getting clearer and more confident.",
            "💬 Your child showed great effort and improvement in speaking today.",
            "🏆 Your child is showing strong development in speech clarity!",
            "😊 You should be proud — your child is really growing in pronunciation skills!",
            "📈 Your child is becoming more confident with every word spoken.",
            "👏 Excellent effort! Your child’s voice is sounding clearer each time.",
            "🗣️ Your child’s speaking skills are improving steadily — well done!",
            "🎯 Strong performance! They're on track with their pronunciation goals.",
            "📣 Your child is becoming a more confident speaker!",
            "✨ Your child is learning fast — their pronunciation is improving day by day!",
            "💡 We noticed clearer sounds and better confidence in your child’s speech.",
            "🌈 Your child is making exciting progress in their speaking journey!",
            "🎓 Great improvement — your child is sounding more fluent with every session.",
            "🌟 Steady and strong! Your child’s effort in pronunciation really stands out.",
            "🧠 Their hard work is paying off — we’re hearing more clarity each time!",
            "🗨️ Your child spoke today with more confidence and precision — keep encouraging them!",
            "📚 Practice is working — your child’s pronunciation is getting smoother.",
            "📢 The improvement is clear — your child’s voice is getting stronger and more accurate.",
            "💬 Clearer, louder, more confident — that’s how your child sounded today!",
            "🌟 A big step forward — your child is mastering new sounds with ease.",
            "💖 Your child’s voice is becoming clearer — and their confidence is blooming.",
            "🎵 Their speech is starting to flow more naturally — great job!",
            "📈 We’re seeing consistent improvement — your child is putting in great effort.",
            "🚀 Your child is reaching new milestones in pronunciation. Amazing progress!",
            "🌼 Soft but confident — your child’s pronunciation has noticeably improved.",
            "🎯 Each attempt gets stronger — your child is working hard and it shows.",
            "🥇 We’re proud to see how well your child is expressing their words now.",
            "📋 Your child’s speech clarity was better than ever today!",
            "🧩 Bit by bit, your child is building excellent speaking skills.",
            "👂 We heard the progress today — your child is doing wonderfully!",
            "📣 Louder and clearer — your child’s pronunciation is blossoming.",
            "📦 That was a solid effort — your child’s growth in speaking is very noticeable.",
            "💪 Your child is gaining the confidence to pronounce even tricky words.",
            "🧒 Their improvement is inspiring — thank you for supporting their learning journey!",
        ];

        $gentlyImproveTemplates = [
            "🧠 With a bit more practice, your child will pronounce some tricky sounds even better.",
            "🔍 There are just a few sounds to work on, but your child is clearly improving.",
            "🧱 A few sounds were a little unclear, but that’s part of the learning journey!",
            "🌱 A couple of words were challenging, but practice is helping your child grow!",
            "🎵 There’s a small opportunity to make some sounds clearer — and your child is on the right path.",
            "📘 Some words could use a little more clarity, but your child is making great strides.",
            "🗣️ A few sounds still need polishing, but your child is clearly progressing.",
            "👂 With continued practice, your child’s pronunciation will become even clearer.",
            "📖 Some tricky sounds popped up today, but your child is learning to handle them well.",
            "🌤️ A little more focus on certain sounds will go a long way — progress is visible.",
            "💬 Just a few pronunciation spots to fine-tune — nothing your child can’t handle!",
            "🧭 Your child is headed in the right direction, with only a few minor pronunciation slips.",
            "🎯 A few target sounds can still be improved — your child is doing the right work.",
            "🏗️ Those challenging sounds are just building blocks — your child is laying a great foundation.",
            "🚧 There were small bumps in pronunciation today, but growth is absolutely happening.",
            "📈 Some improvements can still be made — but your child is already on track.",
            "💡 A couple of sounds were tricky, but each session helps refine them.",
            "🎓 Mastery takes time — your child is working through the hard parts with determination.",
            "🧩 One or two sounds didn’t come through clearly, but that’s normal in learning.",
            "🌻 Steady effort is making a difference — even the tough words are improving.",
            "🎶 Some pronunciations need a little more practice, but the improvement is easy to hear.",
            "🪴 Growth is happening! A few words need practice, but the progress is real.",
            "📝 Just a few sounds to revisit — your child is picking things up well.",
            "🛤️ Slight pronunciation detours today, but the journey is moving forward.",
            "🔧 A little extra attention on pronunciation will make your child even more confident.",
            "📢 We noticed a few unclear sounds — but we also noticed a lot of effort.",
            "🪜 Your child is one step away from mastering those tricky sounds!",
            "🧠 With focus and practice, those few rough sounds will soon be crystal clear.",
            "📎 Just a bit more sharpening needed — your child is almost there!",
            "🫶 These minor sound errors are part of learning — and your child is doing great overall!",
            "🌈 Just a little more practice and your child will master even the tricky sounds.",
            "🎯 One or two sounds needed refining today — but your child is clearly on the right path.",
            "🧠 Your child is absorbing well — just a few sounds left to strengthen.",
            "🗣️ Their speech is growing stronger, even if a few sounds still need attention.",
            "📚 A couple of words didn’t come out as clearly, but that’s totally normal at this stage.",
            "🔁 Consistent practice will smooth out the last few pronunciation hurdles.",
            "🪶 Some sounds were a little soft — your child is learning to express them with more clarity.",
            "🔭 There’s room to sharpen just a few sounds — your child is almost there!",
            "🌟 The effort is there! A small boost in focus will perfect those sounds.",
            "🧒 Your child may stumble over a sound or two — but every learner does!",
            "🚸 It’s okay if a few sounds were unclear — they’re still doing an excellent job.",
            "🛎️ Just a few gentle reminders needed for clearer speech in certain words.",
            "🗺️ A few mispronunciations popped up, but they’re part of every speaker’s journey.",
            "🎤 There’s progress in every attempt — even if a few words needed extra effort.",
            "🎨 Speech is a work of art — and your child is refining the details beautifully.",
        ];

        if ($overallScore >= 90) {
            return $praiseTemplates[array_rand($praiseTemplates)];
        }
        $wrongPhones = array_filter($phones, function ($phone) {
            return isset($phone['sound_most_like'], $phone['phone']) &&
                $phone['sound_most_like'] !== $phone['phone'];
        });

        if (empty($wrongPhones)) {
            return $praiseTemplates[array_rand($praiseTemplates)];
        }

        $praise = $praiseTemplates[array_rand($praiseTemplates)];
        $tip = $gentlyImproveTemplates[array_rand($gentlyImproveTemplates)];

        return "$praise $tip";
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
            foreach($attempts as $attemptId => $attempt){
                $attempts_data[$attemptId] = [
                    'score' => $attempt['overall_score'] ?? $attempt['score'] ?? null,
                    'submitted_at' => $attempt['submitted_at'] ?? null,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Successfully get activity',
                'attempts' => $attempts_data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
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
            $phones = $pronunciation_details['words'][0]['phones'] ?? [];
            $overallScore = $pronunciation_details['speechace_pronunciation_score'] ?? 0;

            $bucket = $this->storage->getBucket();
            $bucket->upload(
                fopen($file->getPathName(), 'r'),
                ['name' => $remotePath]
            );

            $student_feedback = $this->generateStudentFeedback($phones, $overallScore);
            $parent_feedback = $this->generateParentFeedback($phones, $overallScore);
            $teacher_feedback = $this->generateTeacherPronunciationReport($pronunciation_details);

            $feedbacks = [
                'student' => $student_feedback,
                'parent' => $parent_feedback,
                'teacher' => $teacher_feedback,
            ];

            $now = now()->toDateTimeString();
            $updatedAnswer = [
                'audio_path' => $remotePath,
                'answered_at' => $now,
                'pronunciation_details' => $pronunciation_details,
                'feedback' => $feedbacks,
            ];

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attempts/{$activityType}/{$activityId}/{$userId}/{$attemptId}/answers/{$flashcardId}")
                ->update($updatedAnswer);

            Storage::disk('public')->delete($path);

            return response()->json([
                'success' => true,
                'message' => 'Answer submitted successfully',
                'feedback' => $student_feedback
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