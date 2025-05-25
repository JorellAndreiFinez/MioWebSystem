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
            1 => ['dog'],
            2 => ['helicopter'],
            3 => ['unbelievable'],
            4 => ['It is raining', 'It is raining.'],
            5 => ['She is my friend', 'She is my friend.'],
        ];

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
        foreach ($auditoryAnswers as $index => $expectedVariants) {
            $userInput = trim($auditoryInputs[$index] ?? '');

            // Normalize user input
            $normalizedInput = strtolower(trim(rtrim($userInput, '.')));
            $normalizedExpected = array_map(function ($ans) {
                return strtolower(trim(rtrim($ans, '.')));
            }, $expectedVariants);

            // Determine if the normalized user input exactly matches any expected answer
            $match = in_array($normalizedInput, $normalizedExpected);

            // Get the first expected phrase as the basis for comparison
            $expectedPhrase = $normalizedExpected[0];
            $expectedWords = explode(' ', $expectedPhrase);
            $userWords = explode(' ', $normalizedInput);

            // Identify missing words
            $missingWords = array_diff($expectedWords, $userWords);

            $auditoryResults[] = [
                'index' => $index,
                'expected' => $expectedPhrase,
                'user_input' => $userInput,
                'match' => $match,
                'missing_words' => array_values($missingWords),
                'timestamp' => now()->toDateTimeString(),
            ];
        }

        if ($uid) {
            $ref = $this->database->getReference("enrollment/enrollees/{$uid}/Assessment/Speech_Auditory/Auditory");
            foreach ($auditoryResults as $auditoryResult) {
                $ref->push($auditoryResult);
            }
        }

        return response()->json([
            'speech_results' => $results,
            'auditory_results' => $auditoryResults,
            'success' => true,
            'redirect_url' => route('enroll.assessment.reading')
        ]);


    }




    }
