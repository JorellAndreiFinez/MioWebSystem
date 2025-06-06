<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Kreait\Firebase\Factory;
use Carbon\Carbon;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Exception\Auth\EmailExists;
use Kreait\Firebase\Exception\AuthException;
use Illuminate\Support\Facades\Hash;
use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\InvalidPassword;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Exception\Auth\InvalidArgument;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage as LocalStorage;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageObject;
use Barryvdh\DomPDF\Facade\Pdf;
use function Illuminate\Log\log;

class SpeechaceController extends Controller
{

    protected $database;
    protected $auth;

    protected $bucketName;
    protected $storageClient;
    


    public function __construct()
    {
        $path = base_path('storage/firebase/firebase.json');

        $factory = (new Factory)
            ->withServiceAccount(base_path('storage/firebase/firebase.json'))
            ->withDatabaseUri('https://miolms-default-rtdb.firebaseio.com');

        $this->database = $factory->createDatabase();
        $this->auth = $factory->createAuth(); // Firebase Auth instance

          // Create Google Cloud Storage client
        $this->storageClient = new StorageClient([
            'keyFilePath' => $path,
        ]);

        // Your Firebase Storage bucket name
        $this->bucketName = 'miolms.firebasestorage.app';
    }

     protected function uploadToFirebaseStorage($file, $storagePath)
        {
            $bucket = $this->storageClient->bucket($this->bucketName);
            $fileName = $file->getClientOriginalName();
            $firebasePath = "{$storagePath}/" . uniqid() . '_' . $fileName;

            $bucket->upload(
                fopen($file->getRealPath(), 'r'),
                ['name' => $firebasePath]
            );

            return [
                'name' => $fileName,
                'path' => $firebasePath,
                'url' => "https://firebasestorage.googleapis.com/v0/b/{$this->bucketName}/o/" . urlencode($firebasePath) . "?alt=media",
            ];
        }

     public function submit(Request $request)
    {
         Log::info('Incoming request data:', $request->all());
         Log::info('Auditory replay counts:', $request->input('auditory_replay_counts', []));
        Log::info('Auditory response times:', $request->input('auditory_response_times', []));


        // or just to test auditory inputs:
        Log::info('Auditory inputs:', $request->input('auditory_inputs', []));

        $request->validate([
            'texts' => 'required|array',
            'texts.*' => 'required|string',
            'user_audio_files' => 'required|array',
            'user_audio_files.*' => 'required|file|mimes:wav,mp3,webm',
            'auditory_inputs' => 'required|array', // expects user input for auditory test
            'auditory_inputs.*' => 'required|string',
            'auditory_volume_levels' => 'required|array',
            'auditory_volume_levels.*' => 'required|numeric|min:0.2|max:1.0',
            'auditory_replay_counts' => 'required|array',
            'auditory_replay_counts.*' => 'required|integer|min:1',

            'auditory_response_times' => 'required|array',
            'auditory_response_times.*' => 'required|integer|min:0',
        ]);

        $apiKey = env('SPEECHACE_API_KEY');
        $texts = $request->input('texts');
        $auditoryInputs = $request->input('auditory_inputs');
        $files = $request->file('user_audio_files');
        $client = new \GuzzleHttp\Client();
        $uid = Session::get('firebase_uid');
        $results = [];
        $auditoryResults = [];
        $auditoryItems = $this->database->getReference("enrollment/assessment_settings/physical/auditory")->getValue() ?? [];
        // Extract text only, indexed numerically
        $auditoryAnswers = [];
        foreach ($auditoryItems as $item) {
            $auditoryAnswers[] = $item['text'] ?? '';
        }

        $volumeLevels = $request->input('auditory_volume_levels');
        $replayCounts = $request->input('auditory_replay_counts', []);
        $responseTimes = $request->input('auditory_response_times', []);



        if ($uid) {
            $this->database->getReference("enrollment/enrollees/{$uid}/Assessment/Speech_Auditory/status")
                ->set('done');
        }
        // === SPEECH TEST ===
        foreach ($texts as $index => $text) {
            $file = $files[$index] ?? null;
            if (!$file) {
                $results[] = ['error' => "Missing audio file for phrase #".($index+1)];
                continue;
            }

            try {
            // Upload audio file to Firebase Storage
            $uploadResult = $this->uploadToFirebaseStorage(
                $file,
                "enrollment/{$uid}/assessment/speech"
            );
                $response = $client->request('POST', 'https://api2.speechace.com/api/scoring/text/v9/json', [
                    'query' => ['key' => $apiKey, 'dialect' => 'en-us'],
                    'multipart' => [
                        ['name' => 'text', 'contents' => $text],
                        ['name' => 'user_audio_file', 'contents' => fopen($file->getRealPath(), 'r'), 'filename' => $file->getClientOriginalName()],
                    ],
                ]);

                $resultRaw = $response->getBody()->getContents();
                Log::info('SpeechAce API response: ' . $resultRaw);

                $decoded = json_decode($resultRaw, true);
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
                    'uploaded_audio' => $uploadResult
                ];

                if (!empty($decoded['text_score']['word_score_list'])) {
                    foreach ($decoded['text_score']['word_score_list'] as $word) {
                        $cleaned['words'][] = [
                            'word' => $word['word'] ?? '',
                            'quality_score' => $word['quality_score'] ?? null,
                            'phones' => $word['phone_score_list'] ?? [],
                            'syllables' => $word['syllable_score_list'] ?? [],
                        ];
                    }
                }

                if ($uid) {
                    $this->database->getReference("enrollment/enrollees/{$uid}/Assessment/Speech_Auditory/Speech")
                        ->push($cleaned);
                }

                $results[] = $cleaned;

            } catch (\Exception $e) {
                Log::error('SpeechAce API error: ' . $e->getMessage());
                $results[] = ['error' => 'SpeechAce API error: ' . $e->getMessage()];
            }
        }

        // === AUDITORY TEST ===
        foreach ($auditoryAnswers as $index => $expectedVariants) {
            // Ensure $expectedVariants is an array
            if (!is_array($expectedVariants)) {
                $expectedVariants = [$expectedVariants];
            }

            $userInput = trim($auditoryInputs[$index] ?? '');
            $normalize = function ($text) {
                return strtolower(trim(preg_replace('/[^\w\s]/u', '', $text)));
            };

            $normalizedInput = $normalize($userInput);
            $normalizedExpected = array_map($normalize, $expectedVariants);

            $match = false;
            foreach ($normalizedExpected as $expected) {
                if (strcasecmp($normalizedInput, $expected) === 0) {
                    $match = true;
                    break;
                }
            }

            $expectedPhrase = $normalizedExpected[0];
            $expectedWords = explode(' ', $expectedPhrase);
            $userWords = explode(' ', $normalizedInput);

            $missingWords = array_diff($expectedWords, $userWords);
            $extraWords = array_diff($userWords, $expectedWords);
            $errorsCount = count($missingWords) + count($extraWords);

            if ($match) {
                $score = 100;
                $assessment = 'pass';
            } elseif ($errorsCount === 1) {
                $score = 75;
                $assessment = 'partial';
            } elseif ($errorsCount > 1 && $errorsCount <= 3) {
                $score = 50;
                $assessment = 'fail';
            } else {
                $score = 0;
                $assessment = 'fail';
            }

            $auditoryResults[] = [
                'index' => $index,
                'expected' => $expectedPhrase,
                'user_input' => $userInput,
                'match' => $match,
                'missing_words' => array_values($missingWords),
                'extra_words' => array_values($extraWords),
                'score' => $score,
                'assessment' => $assessment,
                'volume_level' => floatval($volumeLevels[$index] ?? 1.0), 
                'replay_count' => (int)($replayCounts[$index] ?? 0),
                'reaction_time_seconds' => (int)($responseTimes[$index] ?? 0),
                'timestamp' => now()->toDateTimeString(),
            ];
        }

        $totalScore = array_sum(array_column($auditoryResults, 'score'));
        $maxScore = count($auditoryResults) * 100;
        $receptionScore = count($auditoryResults) > 0 ? round($totalScore / count($auditoryResults), 2) : 0;

        $speechReception = [
            'overall_score' => $receptionScore,
            'max_possible' => $maxScore,
            'timestamp' => now()->toDateTimeString(),
        ];

         // === WORD RECOGNITION SCORE (WRS) ===

        $normalizeText = function ($text) {
            return strtolower(trim(preg_replace('/[^\w\s]/u', '', $text)));
        };

        $correctWordCount = 0;
        $totalWordsCount = 0;

        foreach ($auditoryAnswers as $index => $expectedPhrase) {
            $expectedPhrase = is_array($expectedPhrase) ? $expectedPhrase[0] : $expectedPhrase; // handle if array
            $userInput = trim($auditoryInputs[$index] ?? '');

            $normalizedExpected = $normalizeText($expectedPhrase);
            $normalizedUserInput = $normalizeText($userInput);

            $expectedWords = explode(' ', $normalizedExpected);
            $userWords = explode(' ', $normalizedUserInput);

            $totalWordsCount += count($expectedWords);

            foreach ($expectedWords as $word) {
                if (in_array($word, $userWords)) {
                    $correctWordCount++;
                }
            }
        }

        $percentCorrect = $totalWordsCount > 0 ? round(($correctWordCount / $totalWordsCount) * 100, 2) : 0;

        $wordRecognitionScore = [
            'correct_words' => $correctWordCount,
            'total_words' => $totalWordsCount,
            'percent_correct' => $percentCorrect,
            'timestamp' => now()->toDateTimeString(),
        ];


        if ($uid) {
            $auditoryData = [
                'results' => $auditoryResults,
                'speech_reception' => $speechReception,
                'word_recognition_score' => $wordRecognitionScore,

            ];

            Log::info('Auditory data saved for user: ' . $uid, ['data' => $auditoryData]);

            $this->database->getReference("enrollment/enrollees/{$uid}/Assessment/Speech_Auditory/Auditory")
                ->set($auditoryData);
        }

        return view('enrollment-panel.enrollment-panel', [
            'page' => 'main-assessment2',
            'speech_results' => $results,
            'auditory_results' => $auditoryResults,
            ]);

    }

     public function submit2(Request $request)
    {
        $request->validate([
            'texts' => 'required|array',
            'texts.*' => 'required|string',
            'user_audio_files' => 'required|array',
            'user_audio_files.*' => 'required|file|mimes:wav,mp3,webm',
        ]);

        $apiKey = env('SPEECHACE_API_KEY');
        $texts = $request->input('texts');
        $files = $request->file('user_audio_files');
        $client = new \GuzzleHttp\Client();
        $uid = Session::get('firebase_uid');
        $results = [];

        if ($uid) {
            $this->database->getReference("enrollment/enrollees/{$uid}/Assessment/Reading/status")
                ->set('done');
        }

        foreach ($texts as $index => $text) {
            $file = $files[$index] ?? null;
            if (!$file) {
                $results[] = ['error' => "Missing audio file for sentence #" . ($index + 1)];
                continue;
            }

            try {
                // ✅ Upload audio to Firebase Storage
                $uploadResult = $this->uploadToFirebaseStorage(
                    $file,
                    "enrollment/{$uid}/assessment/reading"
                );

                Log::info("Audio uploaded for sentence #".($index+1).": " . json_encode($uploadResult));

                // ✅ Send to SpeechAce
                $response = $client->request('POST', 'https://api2.speechace.com/api/scoring/text/v9/json', [
                    'query' => ['key' => $apiKey, 'dialect' => 'en-us'],
                    'multipart' => [
                        ['name' => 'text', 'contents' => $text],
                        ['name' => 'user_audio_file', 'contents' => fopen($file->getRealPath(), 'r'), 'filename' => $file->getClientOriginalName()],
                        ['name' => 'markup_language', 'contents' => 'arpa_mark'],
                    ],
                ]);

                $resultRaw = $response->getBody()->getContents();
                Log::info("SpeechAce raw response for sentence #".($index+1).": " . $resultRaw);

                $decoded = json_decode($resultRaw, true);

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
                    'uploaded_audio' => $uploadResult, // 🔗 Firebase Storage metadata
                ];

                if (!empty($decoded['text_score']['word_score_list'])) {
                    foreach ($decoded['text_score']['word_score_list'] as $word) {
                        $cleaned['words'][] = [
                            'word' => $word['word'] ?? '',
                            'quality_score' => $word['quality_score'] ?? null,
                            'phones' => $word['phone_score_list'] ?? [],
                            'syllables' => $word['syllable_score_list'] ?? [],
                        ];
                    }
                }

                if ($uid) {
                    $this->database->getReference("enrollment/enrollees/{$uid}/Assessment/Reading")
                        ->push($cleaned);
                }

                $results[] = $cleaned;

            } catch (\Exception $e) {
                Log::error("Error processing sentence #".($index+1).": " . $e->getMessage());
                $results[] = ['error' => 'Processing error: ' . $e->getMessage()];
            }
        }

        return view('enrollment-panel.enrollment-panel', [
            'page' => 'main-assessment3',
            'speech_results' => $results,
        ]);
    }


    public function submit3(Request $request)
    {
        $request->validate([
            'texts' => 'required|array',
            'texts.*' => 'required|string',
            'user_audio_files' => 'required|array',
            'user_audio_files.*' => 'required|file|mimes:wav,mp3,webm',
        ]);

        $apiKey = env('SPEECHACE_API_KEY');
        $texts = $request->input('texts');
        $files = $request->file('user_audio_files');
        $client = new \GuzzleHttp\Client();
        $uid = Session::get('firebase_uid');
        $results = [];

        if ($uid) {
            // Mark sentence test status
            $this->database->getReference("enrollment/enrollees/{$uid}/Assessment/fillblanks/status")
                ->set('done');
        }

        foreach ($texts as $index => $text) {
            $file = $files[$index] ?? null;
            if (!$file) {
                $results[] = ['error' => "Missing audio file for sentence #".($index+1)];
                continue;
            }

            try {
                // Upload audio to Firebase Storage
                $audioUpload = $this->uploadToFirebaseStorage($file, "enrollment/{$uid}/assessment/fillblanks");

                // Call SpeechAce API
                $response = $client->request('POST', 'https://api2.speechace.com/api/scoring/text/v9/json', [
                    'query' => ['key' => $apiKey, 'dialect' => 'en-us'],
                    'multipart' => [
                        ['name' => 'text', 'contents' => $text],
                        ['name' => 'user_audio_file', 'contents' => fopen($file->getRealPath(), 'r'), 'filename' => $file->getClientOriginalName()],
                        ['name' => 'markup_language', 'contents' => 'arpa_mark'],
                    ],
                ]);

                $resultRaw = $response->getBody()->getContents();
                Log::info('SpeechAce API response: ' . $resultRaw);
                $decoded = json_decode($resultRaw, true);
                $overallQuality = $decoded['text_score']['overall_quality_score'] ?? null;

                $missingWords = [];
                foreach ($decoded['text_score']['word_score_list'] ?? [] as $word) {
                    if (($word['quality_score'] ?? 100) < 20) {
                        $missingWords[] = $word['word'] ?? '';
                    }
                }

                $threshold = 80;
                $isMatch = ($overallQuality !== null && $overallQuality >= $threshold);

                $cleaned = [
                    'text' => $decoded['text_score']['text'] ?? '',
                    'overall_quality_score' => $overallQuality,
                    'is_match' => $isMatch,
                    'missing_words' => $missingWords,
                    'ending_punctuation' => $decoded['text_score']['ending_punctuation'] ?? null,
                    'ielts_pronunciation_score' => $decoded['text_score']['ielts_score']['pronunciation'] ?? null,
                    'pte_pronunciation_score' => $decoded['text_score']['pte_score']['pronunciation'] ?? null,
                    'toeic_pronunciation_score' => $decoded['text_score']['toeic_score']['pronunciation'] ?? null,
                    'cefr_pronunciation_score' => $decoded['text_score']['cefr_score']['pronunciation'] ?? null,
                    'speechace_pronunciation_score' => $decoded['text_score']['speechace_score']['pronunciation'] ?? null,
                    'version' => $decoded['version'] ?? null,
                    'request_id' => $decoded['request_id'] ?? null,
                    'audio_url' => $audioUpload['url'], // 🔽 add audio URL
                    'timestamp' => now()->toDateTimeString(),
                    'words' => [],
                ];

                foreach ($decoded['text_score']['word_score_list'] ?? [] as $word) {
                    $cleaned['words'][] = [
                        'word' => $word['word'] ?? '',
                        'quality_score' => $word['quality_score'] ?? null,
                        'phones' => $word['phone_score_list'] ?? [],
                        'syllables' => $word['syllable_score_list'] ?? [],
                    ];
                }

                if ($uid) {
                    // 🔽 Save result with audio in fillblanks path
                    $this->database->getReference("enrollment/enrollees/{$uid}/Assessment/fillblanks")
                        ->push($cleaned);
                }

                $results[] = $cleaned;

            } catch (\Exception $e) {
                Log::error('SpeechAce API error: ' . $e->getMessage());
                $results[] = ['error' => 'SpeechAce API error: ' . $e->getMessage()];
            }
        }

        return view('enrollment-panel.enrollment-panel', [
            'page' => 'main-assessment4',
            'speech_results' => $results,
        ]);
    }


    public function submit4(Request $request)
    {
        $request->validate([
            'texts' => 'required|array',
            'texts.*' => 'required|string',
            'user_audio_files' => 'required|array',
            'user_audio_files.*' => 'required|file|mimes:wav,mp3,webm',
        ]);

        $apiKey = env('SPEECHACE_API_KEY');
        $texts = $request->input('texts');
        $files = $request->file('user_audio_files');
        $client = new \GuzzleHttp\Client();
        $uid = Session::get('firebase_uid');
        $results = [];

        if ($uid) {
            $this->database->getReference("enrollment/enrollees/{$uid}/Assessment/Sentence/status")
                ->set('done');
        }

        foreach ($texts as $index => $text) {
            $file = $files[$index] ?? null;
            if (!$file) {
                $results[] = ['error' => "Missing audio file for sentence #".($index+1)];
                continue;
            }

            try {
                $response = $client->request('POST', 'https://api2.speechace.com/api/scoring/text/v9/json', [
                    'query' => ['key' => $apiKey, 'dialect' => 'en-us'],
                    'multipart' => [
                        ['name' => 'text', 'contents' => $text],
                        ['name' => 'user_audio_file', 'contents' => fopen($file->getRealPath(), 'r'), 'filename' => $file->getClientOriginalName()],
                        ['name' => 'markup_language', 'contents' => 'arpa_mark'],
                    ],
                ]);

                $resultRaw = $response->getBody()->getContents();
                Log::info('SpeechAce API response: ' . $resultRaw);

                $decoded = json_decode($resultRaw, true);
                $overallQuality = $decoded['text_score']['overall_quality_score'] ?? null;

                // Detect missing words (quality_score very low or zero)
                $missingWords = [];
                if (!empty($decoded['text_score']['word_score_list'])) {
                    foreach ($decoded['text_score']['word_score_list'] as $word) {
                        // You can tune this threshold depending on what you consider "missing"
                        $qualityScore = $word['quality_score'] ?? 100;
                        if ($qualityScore < 20) { // consider words below 20 quality_score as missing/not spoken well
                            $missingWords[] = $word['word'] ?? '';
                        }
                    }
                }

                $threshold = 80;
                $isMatch = ($overallQuality !== null && $overallQuality >= $threshold);

                $cleaned = [
                    'text' => $decoded['text_score']['text'] ?? '',
                    'overall_quality_score' => $overallQuality,
                    'is_match' => $isMatch,
                    'missing_words' => $missingWords, // store missing words here
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

                foreach ($decoded['text_score']['word_score_list'] ?? [] as $word) {
                    $cleaned['words'][] = [
                        'word' => $word['word'] ?? '',
                        'quality_score' => $word['quality_score'] ?? null,
                        'phones' => $word['phone_score_list'] ?? [],
                        'syllables' => $word['syllable_score_list'] ?? [],
                    ];
                }

                if ($uid) {
                    $this->database->getReference("enrollment/enrollees/{$uid}/Assessment/Sentence")
                        ->push($cleaned);
                }

                $results[] = $cleaned;

            } catch (\Exception $e) {
                Log::error('SpeechAce API error: ' . $e->getMessage());
                $results[] = ['error' => 'SpeechAce API error: ' . $e->getMessage()];
            }
        }

        return view('enrollment-panel.enrollment-panel', [
            'page' => 'main-assessment4',
            'speech_results' => $results,
        ]);
    }

    public function submitWrittenTest(Request $request)
{
    // Validate input: selected_choices must be an array and required for each question
    $request->validate([
        'selected_choices' => 'required|array',
        'selected_choices.*' => 'required|string',
    ]);

    $selectedChoices = $request->input('selected_choices');
    $uid = Session::get('firebase_uid');

    if (!$uid) {
        return redirect()->back()->withErrors(['error' => 'User not authenticated']);
    }

    // Prepare the data to save
    $answersData = [
        'answers' => $selectedChoices,
        'submitted_at' => now()->toDateTimeString(),
        'status' => 'done',
    ];

    try {
        // Save the written test answers under the user’s Assessment node
        $this->database->getReference("enrollment/enrollees/{$uid}/Assessment/WrittenTest")
            ->set($answersData);

        // Optionally set status flag
        $this->database->getReference("enrollment/enrollees/{$uid}/Assessment/WrittenTest/status")
            ->set('done');

        return view('enrollment-panel.enrollment-panel', [
            'page' => 'main-assessment-written',
            'message' => 'Written test answers submitted successfully.',
            'submitted_answers' => $selectedChoices,
        ]);
    } catch (\Exception $e) {
        Log::error('Error saving written test answers: ' . $e->getMessage());
        return redirect()->back()->withErrors(['error' => 'Failed to submit written answers. Please try again.']);
    }
}










    }
