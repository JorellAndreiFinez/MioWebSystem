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

    public function createSpeechActivity(Request $request, string $subjectId){
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try{
            $validated = $request->validate([
                'activity_type' => 'required|in:picture,question,phrase,pronunciation',
                'difficulty' => 'required|in:easy,average,difficult,challenge',

                'flashcard_text' => 'nullable|array',
                'flashcard_text.*' => 'string|max:250',

                'flashcard_file' => 'nullable|array|required_if:activity_type,picture',
                'flashcard_file.*' => 'file|mimes:jpg,png|max:5120',

                'flashcard_answer' => 'nullable|array',
                'flashcard_answer.*' => 'string|max:250|required_if:activity_type,picture',
            ]);

            $flashcardData = [];
            $answers = $validated['flashcard_answer'] ?? [];

            foreach ($validated['flashcard_text'] ?? [] as $index => $text) {
                $id = (string) Str::uuid()->toString();
                $flashcardData[$index] = [
                    'flashcard_id' => $id,
                    'word'=> $text,
                ];
            }

            $bucket = $this->storage->getBucket();

            foreach ($validated['flashcard_file'] ?? [] as $idx => $file) {
                $uuid = Str::uuid()->toString();
                $remotePath = 'images/speech/' . $uuid . $file->getClientOriginalName();

                $bucket->upload(
                    fopen($file->getPathName(), 'r'),
                    ['name' => $remotePath]
                );

                $flashcardData[$uuid] = array_merge(
                    $flashcardData[$uuid] ?? ['flashcard_id' => $uuid],
                    [
                        'image_path' => $remotePath,
                        'answer' => $answers[$idx] ?? null,
                    ]
                );
            }

            $activityType = $validated['activity_type'];
            $difficulty = $validated['difficulty'];
            $activity_id = $this->generateUniqueId('SPE');
            $date = now()->toDateTimeString();

            $activityData = [
                'flashcards'=> $flashcardData,
                'total' => count($flashcardData),
                'created_at' => $date,
            ];

            $this->database
             ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/{$activityType}/{$difficulty}/{$activity_id}")
             ->set($activityData);

            return response()->json([
                'success' => true,
                'message'=> "Activity created successfully",
                'activity'=> $activityData,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function startFlashcardActivity(Request $request, string $subjectId, string $activityType, string $difficulty, string $activityId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        /**
         * 
         * 
         * 
         * 
         * CHECK FOR AVAILABLE PREVIOUD ATTEMPT STATUS INPROGRESS
         * 
         * 
         * 
         */
        try{
            $activityData = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/{$activityType}/{$difficulty}/{$activityId}")
                ->getSnapshot()
                ->getValue() ?? [];

            if (! $activityData || ! isset($activityData['flashcards'])) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Activity not found.'
                ], 404);
            }

            $flashcards = $activityData['flashcards'];
            
            $attemptId = $this->generateUniqueId("ATTM");
            $startedAt   = now()->toDateTimeString();

            $studentAnswers = [];
            foreach ($flashcards as $idx => $card) {
                $studentAnswers[$card['flashcard_id']] = [
                    'card_no' => $idx,
                    'word' => $card['word'] ?? $card['answer'] ?? "",
                    'audio_path' => null,
                    'image_path' => $card['image_path'] ?? ""
                ];
            }
            
            $initialInfo = [
                'answers' => $studentAnswers,
                'started_at' => $startedAt,
                'status'     => 'in-progress',
            ];

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attempts/{$activityType}/{$activityId}/{$userId}/{$attemptId}")
                ->set($initialInfo);

            return response()->json([
                'success' => true,
                'attemptId' => $attemptId,
                'flashcards' => $flashcards,
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
        try{
            $gradeLevel = $request->get('firebase_user_gradeLevel');
            $userId = $request->get('firebase_user_id');

            $data = $request->validate([
                'audio_file' => 'required|file|mimetypes:video/mp4,audio/mp3',
            ]);

            $answersRef = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attempts/{$activityType}/{$activityId}/{$userId}/{$attemptId}/answers");

            $answers = $answersRef->getSnapshot()->getValue() ?? [];

            if (! isset($answers[$flashcardId])) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Invalid flashcard ID for this attempt',
                ], 400);
            }

            $file = $request->file('audio_file');
            $uuid = (string) Str::uuid();
            $filename = $uuid . $file->getClientOriginalName();
            $path = $file->storeAs('audio_submissions', $file->getClientOriginalName() , 'public');
            $remotePath = "audio/speech/{$activityType}/{$activityId}/{$userId}/{$attemptId}" . $filename;

            $bucket = $this->storage->getBucket();
            $bucket->upload(
                fopen($file->getPathName(), 'r'),
                ['name' => $remotePath]
            );

            $now = now()->toDateTimeString();
            $word = $answers[$flashcardId]['word'];
            $pronunciation_details = $this->pronunciationScoreApi($path, $word);

            $updatedAnswer = [
                'word' => $word,
                'audio_path' => $remotePath,
                'answered_at' => $now,
                'pronunciation_details' => $pronunciation_details,
            ];

            $answersRef
                ->getChild($flashcardId)
                ->update($updatedAnswer);

            Storage::disk('public')->delete($path);

            return response()->json([
                'success' => true,
                'message' => 'Answer submitted successfully',
                'pronunciation_details' => $pronunciation_details,
                'type' => $request->file('audio_file')->getMimeType()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
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
                $wordsList = $details['words']               ?? [];

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

            // return response()->json([
            //     'success' => true,
            //     'message' => 'Activity submitted successfully.',
                // 'scores'  => [
                //     '664aef4a-ccb0-419a-9d6d-565e19321c9e' => [
                //         'word'            => 'banana',
                //         'quality_score'   => 98,
                //         'phones'          => [
                //             ['phone' => 'b',  'quality_score' => 98.7,  'sound_most_like' => 'bae',  'extent' => [59, 68]],
                //             ['phone' => 'ah', 'quality_score' => 97.5,  'sound_most_like' => 'ah', 'extent' => [68, 74]],
                //             ['phone' => 'n',  'quality_score' => 98.0,  'sound_most_like' => 'n',  'extent' => [74, 83]],
                //             ['phone' => 'ae', 'quality_score' => 100.0, 'sound_most_like' => 'ae', 'extent' => [83, 95]],
                //             ['phone' => 'n',  'quality_score' => 98.75, 'sound_most_like' => 'ae',  'extent' => [95, 107]],
                //             ['phone' => 'ah', 'quality_score' => 97.0,  'sound_most_like' => 'ah', 'extent' => [107, 119]],
                //         ],
                //         'syllables'       => [
                //             ['letters' => 'ba',  'quality_score' => 98, 'extent' => [59, 74]],
                //             ['letters' => 'nan', 'quality_score' => 99, 'extent' => [74, 107]],
                //             ['letters' => 'a',   'quality_score' => 97, 'extent' => [107, 119]],
                //         ],
                //     ],
                //     '95c11a5c-6941-49e4-9038-b53334175cdd' => [
                //         'word'            => 'apple',
                //         'quality_score'   => 90,
                //         'phones'          => [
                //             ['phone' => 'ae', 'quality_score' => 91.0, 'sound_most_like' => 'ae', 'extent' => [10, 20]],
                //             ['phone' => 'p',  'quality_score' => 89.5, 'sound_most_like' => 'p',  'extent' => [20, 30]],
                //             ['phone' => 'l',  'quality_score' => 90.2, 'sound_most_like' => 'll',  'extent' => [30, 40]],
                //         ],
                //         'syllables'       => [
                //             ['letters' => 'ap',  'quality_score' => 90, 'extent' => [10, 30]],
                //             ['letters' => 'ple', 'quality_score' => 90, 'extent' => [30, 40]],
                //         ],
                //         'timestamp'       => '2025-05-27 14:35:00',
                //     ],
                //     'c4cd1987-6449-4115-8f63-8f790c679319' => [
                //         'word'            => 'orange',
                //         'quality_score'   => 95,
                //         'phones'          => [
                //             ['phone' => 'ao', 'quality_score' => 96.0, 'sound_most_like' => 'ao', 'extent' => [5, 15]],
                //             ['phone' => 'r',  'quality_score' => 94.5, 'sound_most_like' => 'r',  'extent' => [15, 25]],
                //             ['phone' => 'n',  'quality_score' => 95.2, 'sound_most_like' => 'nn',  'extent' => [25, 35]],
                //             ['phone' => 'j',  'quality_score' => 95.0, 'sound_most_like' => 'j',  'extent' => [35, 45]],
                //         ],
                //         'syllables'       => [
                //             ['letters' => 'or',    'quality_score' => 95, 'extent' => [5, 25]],
                //             ['letters' => 'ange',  'quality_score' => 95, 'extent' => [25, 45]],
                //         ],
                //     ],
                // ],
            //     'overall_score' => 200
            // ], 200);

        } catch (\Exception $e) {
            \Log::error('finalizeFlashcardAttempt failed', [
                'path'  => $refPath,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error'   => 'Could not update activity status.',
            ], 500);
        }
    }
}