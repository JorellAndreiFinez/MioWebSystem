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

        if (! Storage::disk('public')->exists($audioPath)) {
            return [];
        }
        $filePath = Storage::disk('public')->path($audioPath);

        $client = new Client([
            'base_uri' => 'https://api2.speechace.com',
            'timeout'  => 30,
        ]);

        try {
            $response = $client->request('POST', '/api/scoring/text/v9/json', [
                'query'     => [
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

            $data = json_decode($response->getBody(), true);
            // return the nested “score” object or an empty array
            return $data['text_score'] ?? [];
        } catch (RequestException $e) {
            // log and swallow
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

    public function getSpeechActivityById(Request $request, string $subjectId, string $activityType, string $difficulty, string $activityId){

        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try{
            $activities = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/{$activityType}/{$difficulty}/{$activityId}")
                ->getSnapshot()
                ->getValue() ?? []; 

            return response()->json([
                'success' => true,
                'activities' => $activities['flashcards'],
                'attempts' => $activities['attempts']
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

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Invalid input.',
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function takeActivity(Request $request, string $subjectId, string $activityType, string $difficulty, string $activityId){
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        $attempts = $this->checkAttempts($gradeLevel, $userId, $activityId, $activityType, $subjectId);
        $activity = $this->database
            ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/{$activityType}/{$difficulty}/{$activityId}")
            ->getSnapshot()
            ->getValue() ?? [];

        $allowedAttempts = $activity['attempts'];

        if (count($attempts) < $allowedAttempts){
            $attemptId = (string) Str::uuid()->toString();
            $date = now()->toDateTimeString();

            $answers = [];
            foreach ($activity['flashcards'] ?? [] as $index=> $fc) {
                $answers[$index] = [
                    'flashcard_id' => $fc['flashcard_id'],
                    'audio_path' => null,
                ];
            }

            $attemptInfo = [
                'started_at' => $date,
                'status' => "in-progress",
                'answers' => $answers,
                'score' => 0,
            ];

            $check = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attempts/{$activityType}/{$activityId}/{$userId}/{$attemptId}")
                ->set($attemptInfo);

            return response()->json([
                'success' => true,
                'attemptId' => $attemptId
            ],201);
        }

        return response()->json([
            'success' => false,
            'error'   => 'Attempt limit reached.',
        ], 403);
    }

    public function updateActivity(
        Request $request,
        string $subjectId,
        string $activityType,
        string $difficulty,
        string $activityId,
        string $attemptId,
        string $flashcardId
    ) {
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId     = $request->get('firebase_user_id');

        $request->validate([
            'audio_file' => ['required', 'file'],
        ]);

        $file = $request->file('audio_file');
        $uuid = (string) Str::uuid();
        $filename = "{$uuid}.wav";
        $path = $file->storeAs('audio_submissions', $filename, 'public');
        $now = now()->toDateTimeString();

        $basePath = "subjects/GR{$gradeLevel}/{$subjectId}/attempts/{$activityType}/{$activityId}/{$userId}/{$attemptId}";
        $ref = $this->database->getReference($basePath);

        $attempt = $ref->getSnapshot()->getValue() ?? [];
        $answers = $attempt['answers'] ?? [];

        $found = false;
        foreach ($answers as $idx => $entry) {
            if ($entry['flashcard_id'] === $flashcardId) {
                $answers[$idx]['audio_path'] = $path;
                $answers[$idx]['updated_at'] = $now;
                $found = true;
                break;
            }
        }

        if (!$found) {
            return response()->json([
                'success' => false,
                'error'   => 'Flashcard not found for this attempt'
            ], 404);
        }

        $ref->getChild('answers')->set($answers);

        return response()->json([
            'success'     => true,
            'message'     => 'Answer submitted successfully',
            'flashcardId' => $flashcardId,
            'audio_path'  => $path,
        ], 200);
    }

    public function submitActivity(
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

        $attemptPath = "subjects/GR{$gradeLevel}/{$subjectId}/attempts/{$activityType}/{$activityId}/{$userId}/{$attemptId}";
        $attemptRef = $this->database->getReference($attemptPath);
        $attemptRef->update([
            'status'       => 'submitted',
            'submitted_at' => $now,
        ]);

        $flashcards = $this->database
            ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/{$activityType}/{$difficulty}/{$activityId}/flashcards")
            ->getSnapshot()
            ->getValue() ?? [];

        $answers = $attemptRef
            ->getChild('answers')
            ->getSnapshot()
            ->getValue() ?? [];

        $totalScore  = 0;
        $count       = count($flashcards);
        $scoreData   = [];
        $audio_paths = [];
        $words       = [];

        foreach ($flashcards as $idx => $card) {
            $flashcardId = $card['flashcard_id'] ?? (string)$idx;
            $word        = $card['word'] ?? '';
            $audioPath   = $answers[$idx]['audio_path'] ?? null;

            $words[] = $word;
            $audio_paths[] = $audioPath;

            $cardScore = 0;
            if ($audioPath) {
                $response = $this->pronunciationScoreApi($audioPath, $word);

                if (!empty($response['word_score_list'])) {
                    $cardScore = $response['word_score_list'][0]['quality_score'] ?? 0;
                }

                $scoreData[$flashcardId] = $response;
            }


            $totalScore += $cardScore;

            $answers[$idx]['score'] = $cardScore;
            $answers[$idx]['scored_at'] = $now;
        }

        $average = $count > 0 ? round($totalScore / $count, 2) : 0;

        $attemptRef->getChild('answers')->set($answers);

        $grades = [
            $attemptId => [
                'totalScore' => $totalScore,
                'average'    => $average,
                'count'      => $count,
                'details'    => $scoreData,
                'scored_at'  => $now,
            ]
        ];

        $scoresRef = $this->database
            ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/scores/${userId}/{$activityId}");
        $scoresRef->set($grades);

        return response()->json([
            'success' => true,
            'message' => 'Activity scored and saved successfully.',
            'score' => $totalScore,
            'average' => $average,
            'totalItems' => $count,
            'audio_paths' => $audio_paths,
            'words' => $words,
        ], 200);
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