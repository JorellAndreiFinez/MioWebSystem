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

class SpeechaceController extends Controller
{

    protected $database;
    protected $auth;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(base_path('storage/firebase/firebase.json'))
            ->withDatabaseUri('https://miolms-default-rtdb.firebaseio.com');

        $this->database = $factory->createDatabase();
        $this->auth = $factory->createAuth(); // Firebase Auth instance
    }

     public function submit(Request $request)
    {
        $request->validate([
            'texts' => 'required|array',
            'texts.*' => 'required|string',
            'user_audio_files' => 'required|array',
            'user_audio_files.*' => 'required|file|mimes:wav,mp3,webm',
            'auditory_inputs' => 'required|array', // expects user input for auditory test
            'auditory_inputs.*' => 'required|string',
        ]);

        $apiKey = env('SPEECHACE_API_KEY');
        $texts = $request->input('texts');
        $auditoryInputs = $request->input('auditory_inputs');
        $files = $request->file('user_audio_files');
        $client = new \GuzzleHttp\Client();
        $uid = Session::get('firebase_uid');
        $results = [];
        $auditoryResults = [];

        // Predefined answers (ideally should be dynamic or stored in DB)
        $auditoryAnswers = [
            0 => ['dog'],
            1 => ['helicopter'],
            2 => ['unbelievable'],
            3 => ['It is raining', 'It is raining.'],
            4 => ['She is my friend', 'She is my friend.'],
        ];

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
       // === AUDITORY TEST (with assessment and scoring) ===
        foreach ($auditoryAnswers as $index => $expectedVariants) {
            $userInput = trim($auditoryInputs[$index] ?? '');
            $normalizedInput = strtolower(trim(rtrim($userInput, '.')));
            $normalizedExpected = array_map(function ($ans) {
                return strtolower(trim(rtrim($ans, '.')));
            }, $expectedVariants);

            $match = in_array($normalizedInput, $normalizedExpected);
            $expectedPhrase = $normalizedExpected[0];
            $expectedWords = explode(' ', $expectedPhrase);
            $userWords = explode(' ', $normalizedInput);

            // Compare words
            $missingWords = array_diff($expectedWords, $userWords);
            $extraWords = array_diff($userWords, $expectedWords);
            $errorsCount = count($missingWords) + count($extraWords);

            // === SCORING LOGIC ===
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
                'timestamp' => now()->toDateTimeString(),
            ];
        }


        if ($uid) {
            $ref = $this->database->getReference("enrollment/enrollees/{$uid}/Assessment/Speech_Auditory/Auditory");
            foreach ($auditoryResults as $auditoryResult) {
                $ref->push($auditoryResult);
            }
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

        // === SPEECH TEST ===
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
                Log::error('SpeechAce API error: ' . $e->getMessage());
                $results[] = ['error' => 'SpeechAce API error: ' . $e->getMessage()];
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
