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

class SpecializedActivityApi extends Controller
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

    private function checkAttempts(string $gradeLevel, string $userId, string $activityId, string $activityType, string $subjectId){

        $checkAttempts =  $this->database
            ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attempts/{$activityType}/{$activityId}/{$userId}")
            ->getSnapshot()
            ->getValue() ?? [];

        return $checkAttempts;

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

                'attempts' => 'required|integer|min:1',

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

            foreach ($validated['flashcard_file'] ?? [] as $idx => $file) {
                $id   = Str::uuid()->toString();
                $path = $file->store('flashcards', 'public');
                $url = asset(Storage::disk('public')->url($path));

                $flashcardData[$id] = [
                    'image_path' => $path,
                ];

                if (isset($answers[$idx])) {
                    $flashcardData[$id]['answer'] = $answers[$idx];
                }
            }

            $activityType = $validated['activity_type'];
            $difficulty = $validated['difficulty'];
            $activity_id = $this->generateUniqueId('ACT');
            $date = now()->toDateTimeString();

            $activityData = [
                'flashcards'=> $flashcardData,
                'attempts' => $validated['attempts'],
                'total' => count($flashcardData),
                'created_at' => $date,
                'updated_at' => $date
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

        try{
            $subjectData = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/{$activityType}/{$difficulty}/{$activityId}")
                ->getSnapshot()
                ->getValue() ?? [];

            $flashcards = $subjectData['flashcards'];
            
            $attemptId   = (string) Str::uuid();
            $startedAt   = now()->toDateTimeString();

            $studentAnswers = [];
            foreach ($flashcards as $idx => $card) {
                $studentAnswers[$card['flashcard_id']] = [
                    'card_no' => $idx,
                    'word' => $card['word'],
                    'audio_path' => null,
                ];
            }
            
            $initialInfo = [
                'answers' => $studentAnswers,
                'started_at' => $startedAt,
                'status'     => 'in-progress',
            ];

            $check = $this->database
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
                'audio_file'   => 'required|file',
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
            $filename = "{$uuid}.wav";
            $path = $file->storeAs('audio_submissions', $filename, 'public');
            $now = now()->toDateTimeString();

            $word = $answers[$flashcardId]['word'];
            // for pronunciation api
            // $pronunciation_details = $this->pronunciationScoreApi($path, $word);

            $updatedAnswer = [
                'word'         => $word,
                'audio_path'   => $path,
                'answered_at'  => $now,
                // 'pronunciation_details' => $pronunciation_details,
            ];

            $answersRef
                ->getChild($flashcardId)
                ->update($updatedAnswer);

            return response()->json([
                'success' => true,
                'message' => 'Answer submitted successfully',
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
        $userId     = $request->get('firebase_user_id');
        $now        = now()->toDateTimeString();

        $refPath = "subjects/GR{$gradeLevel}/{$subjectId}/attempts/{$activityType}/{$activityId}/{$userId}/{$attemptId}";
        $ref = $this->database->getReference($refPath);

        $updatedActivity = [
            'status'       => 'submitted',
            'submitted_at' => $now,
        ];

        try {
            $ref->update($updatedActivity);

            $answers = $ref
                ->getChild('answers')
                ->getSnapshot()
                ->getValue() ?? [];

            foreach ($answers as $cardId => $answer) {
                $details = $answer['pronunciation_details'] ?? [];
                $overallScore = $details['overall_quality_score'] ?? null;
                $wordScoreList = $details['word_score_list'] ?? [];

                $phonemeDetails = [];
                $phonemeSum = 0;
                $phonemeCount = 0;

                foreach ($wordScoreList as $wordEntry) {
                    $phones = $wordEntry['phone_score_list'] ?? [];
                    foreach ($phones as $phoneEntry) {
                        $phone = $phoneEntry['phone'] ?? null;
                        $qualityScore = $phoneEntry['quality_score'] ?? null;
                        $soundMostLike = $phoneEntry['sound_most_like'] ?? null;

                        $phonemeDetails[] = [
                            'phone' => $phone,
                            'quality_score' => $qualityScore,
                            'sound_most_like' => $soundMostLike,
                        ];

                        if ($qualityScore !== null) {
                            $phonemeSum += $qualityScore;
                            $phonemeCount ++;
                        }
                    }
                }

                $averagePhoneme = $phonemeCount
                    ? round($phonemeSum / $phonemeCount, 2)
                    : null;

                $scores[$cardId] = [
                    'overall_quality_score' => $overallScore,
                    'average_phoneme_score' => $averagePhoneme,
                    'phoneme_details' => $phonemeDetails,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Activity submitted successfully.',
                'scores'  => $scores,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Could not update activity status.',
            ], 500);
        }
    }

    public function getActivityScore(
        Request $request,
        string $subjectId,
        string $activityId,
        string $attemptId
    ) {
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId     = $request->get('firebase_user_id');

        $scores = $this->database
            ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/scores/{$userId}/{$activityId}/{$attemptId}")
            ->getSnapshot()
            ->getValue() ?? [];

        if (empty($scores)) {
            return response()->json([
                'success' => false,
                'error'   => 'No scores found for this attempt.'
            ], 404);
        }

        $detailedScores = [];
        foreach ($scores['details'] as $flashcardId => $detail) {
            $firstWord = "";
            $firstWordScore = 0;

            if (!empty($detail['word_score_list'][0]['word'])) {
                $firstWord = $detail['word_score_list'][0]['word'];
            }
            
            if (!empty($detail['word_score_list'][0]['quality_score'])) {
                $firstWordScore = $detail['word_score_list'][0]['quality_score'];
            }

            $detailedScores[$flashcardId] = [
                'word' => $firstWord,
                'pronunciation_score' => $firstWordScore
            ];
        }
        
        return response()->json([
            'success' => true,
            'average' => $scores['average']   ?? 0,
            'totalScore' => $scores['totalScore'] ?? 0,
            'totalItems' => $scores['count']     ?? 0,
            'detailedScores' => $detailedScores,
        ], 200);
    }
}