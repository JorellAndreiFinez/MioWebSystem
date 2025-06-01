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

class SpecializedAuditoryApi extends Controller
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

    public function createBingoActivity(Request $request, string $subjectId){
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        $validated = $request->validate([
            'activity_type' => 'required|in:bingo',
            'difficulty' => 'required|in:easy,average,difficult,challenge',

            'activity' => 'required|array|min:9|max:12',
            'activity.*.image' => 'required|file|image|mimes:jpg,png|max:5120',
            'activity.*.is_answer'=> 'required|string',

            'audio' => 'required|array|min:1',
            'audio.*.audio_file' => 'required|file'
        ]);

        try{
            $bucket = $this->storage->getBucket();
            $items  = [];
            $correct_answers = [];

            foreach($validated['activity'] as $index => $bingo){
                $image_file = $bingo['image'];
                $uuid = (string) Str::uuid()->toString();
                $remoteImagePath = 'images/auditory/' . $uuid . $image_file->getClientOriginalName();

                $bucket->upload(
                    fopen($image_file->getPathName(), 'r'),
                    ['name' => $remoteImagePath]
                );

                $image_id = (string) Str::uuid()->toString();

                $items[] = [
                    'image_id' => $image_id,
                    'image_path' => $remoteImagePath,
                    'is_answer'=> $bingo['is_answer'],
                ];

                if($bingo['is_answer'] === "true"){
                    $correct_answers[] = ['image_id' => $image_id];
                }
            }

            $audio_paths = [];

            foreach ($validated['audio'] as $audio) {
                $audio_file = $audio['audio_file'];
                $uuid = (string) Str::uuid();
                $filename = $uuid . $file->getClientOriginalName();
                $remoteAudioPath = "audio/auditory/" . $filename;

                $bucket->upload(
                    fopen($audio_file->getPathName(), 'r'),
                    ['name' => $remoteAudioPath]
                );

                $audio_id = (string) Str::uuid();

                $audio_paths[] = [
                    'audio_id' => $audio_id,
                    'audio_path' => $remoteAudioPath
                ];
            }

            $date = now()->toDateTimeString();

            $activityData = [
                'items' => $items,
                'audio_paths' => $audio_paths,
                'correct_answers' => $correct_answers,
                'total' => count($items),
                'created_at' => $date,
            ];

            $activity_id = $this->generateUniqueId('SPE');
            $activityType = $validated['activity_type'];
            $difficulty = $validated['difficulty'];

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/{$activityType}/{$difficulty}/{$activity_id}")
                ->set($activityData);

            return response()->json([
                'success' => true,
                'data' => $activityData,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function createMatchingCardsActivity(Request $request, string $subjectId){
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        $validated = $request->validate([
            'activity_type' => 'required|in:matching',
            'difficulty' => 'required|in:easy,average,difficult,challenge',

            'activity' => 'required|array|min:3|max:5',
            'activity.*.image' => 'required|file|image|mimes:jpg,png|max:5120',
            'activity.*.audio' => 'required|file|mimetypes:video/mp4,audio/mp3'
        ]);

        try{

            $bucket = $this->storage->getBucket();
            $items  = [];

            foreach($validated['activity'] as $index => $activity){
                $image_file = $activity['image'];
                $audio_file = $activity['audio'];


                $imageUuid = (string) Str::uuid();
                $remoteImagePath = 'images/auditory/' . $imageUuid . $image_file->getClientOriginalName();

                $audioUuid = (string) Str::uuid();
                $audioFilename = $audioUuid . $file->getClientOriginalName();
                $remoteAudioPath = "audio/auditory/" . $audioFilename;

                $bucket->upload(
                    fopen($audio_file->getPathName(), 'r'),
                    ['name' => $remoteAudioPath]
                );

                $bucket->upload(
                    fopen($image_file->getPathName(), 'r'),
                    ['name' => $remoteImagePath]
                );

                $image_id = (string) Str::uuid();
                $audio_id = (string) Str::uuid();

                $items[] = [
                    'image_no' => $index,
                    'image_id' => $image_id,
                    'image_path' => $remoteImagePath,
                    'audio_id' => $audio_id,
                    'audio_path' => $remoteAudioPath
                ];
            }

            $date = now()->toDateTimeString();

            $activityData = [
                'items' => $items,
                'total' => count($validated['activity']),
                'created_at' => now()->toDateTimeString(),
            ];

            $activity_id = $this->generateUniqueId('SPE');
            $activityType = $validated['activity_type'];
            $difficulty = $validated['difficulty'];

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/{$activityType}/{$difficulty}/{$activity_id}")
                ->set($activityData);

            return response()->json([
                'success' => true,
                'data'    => $activityData,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function startBingoActivity(
        Request $request,
        string $subjectId,
        string $difficulty,
        string $activityId
    ){
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id'); 

        try{
            $activityData = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/bingo/{$difficulty}/{$activityId}")
                ->getSnapshot()
                ->getValue();

            if (! $activityData) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Activity not found.'
                ], 404);
            }

            $bucket = $this->storage->getBucket();

            $items = [];
            foreach ($activityData['items'] as $index => $item) {
                $image = $bucket->object($item['image_path'])->signedUrl(now()->addMinutes(15));
                
                $items[] = [
                    'image_url' => $image,
                    'image_id' => $item['image_id'],
                ];
            }

            $attemptId   = $this->generateUniqueId("ATTM");
            $startedAt   = now()->toDateTimeString();

            $initialInfo = [
                'items' => $items,
                'started_at' => $startedAt,
                'status' => 'in-progress',
            ];

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attempts/bingo/{$activityId}/{$userId}/{$attemptId}")
                ->set($initialInfo);

            return response()->json([
                'success' => true,
                'attemptId' => $attemptId,
                'items' => $items,
                'audio_paths' => $activityData['audio_paths'],
                'total' => count($items)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function startMatchingActivity(
        Request $request,
        string $subjectId,
        string $difficulty,
        string $activityId
    ){
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id'); 

        try{
            $activityData = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/matching/{$difficulty}/{$activityId}")
                ->getSnapshot()
                ->getValue();

            if (! $activityData) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Activity not found.'
                ], 404);
            }

            $bucket = $this->storage->getBucket();

            $items = [];
            foreach ($activityData['items'] as $index => $item) {
                $image = $bucket->object($item['image_path'])->signedUrl(now()->addMinutes(15));
                
                $items[] = [
                    'image_url' => $image,
                    'audio_path' => $item['audio_path'],
                    'audio_ids' => $item['audio_id'],
                    'image_ids' => $item['image_id'],
                ];
            }

            $attemptId   = $this->generateUniqueId("ATTM");
            $startedAt   = now()->toDateTimeString();

            $initialInfo = [
                'items' => $items,
                'started_at' => $startedAt,
                'status' => 'in-progress',
            ];

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attempts/matching/{$activityId}/{$userId}/{$attemptId}")
                ->set($initialInfo);

            return response()->json([
                'success' => true,
                'attemptId' => $attemptId,
                'items' => $items,
                'total' => count($items)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function finalizeBingoAttempt(
        Request $request,
        string $subjectId,
        string $difficulty,
        string $activityId,
        string $attemptId
    ) {
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.image_id' => 'required|string',
        ]);

        try {
            $ref = $this->database->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attempts/bingo/{$activityId}/{$userId}/{$attemptId}");

            $activity = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/bingo/{$difficulty}/{$activityId}")
                ->getSnapshot()
                ->getValue() ?? [];

            if (!isset($activity['items']) || !isset($activity['correct_answers'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Activity data not found.',
                ], 404);
            }

            $correctMap = [];
            foreach ($activity['correct_answers'] as $correct) {
                $correctMap[$correct['image_id']] = true;
            }

            $submittedMap = [];
            foreach ($validated['answers'] as $answer) {
                $submittedMap[$answer['image_id']] = true;
            }

            $score = 0;
            $attemptResult = [];

            foreach ($activity['items'] as $item) {
                $imageId = $item['image_id'];

                if (isset($submittedMap[$imageId]) && !isset($correctMap[$imageId])) {
                    $attemptResult[] = [
                        'image_id' => $imageId,
                        'is_correct' => false,
                    ];
                } else {
                    $score++;
                    $attemptResult[] = [
                        'image_id' => $imageId,
                        'is_correct' => true,
                    ];
                }
            }

            $now = now()->toDateTimeString();

            $ref->update([
                'items' => $attemptResult,
                'score' => $score,
                'completed_at' => $now,
                'status' => "submitted",
            ]);

            return response()->json([
                'success' => true,
                'message' => "Submitted Successfully",
                'score' => $score,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function finalizeMatchingAttempt(
        Request $request,
        string $subjectId,
        string $difficulty,
        string $activityId,
        string $attemptId
    ) {
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.image_id' => 'required|string',
            'answers.*.audio_id' => 'required|string'
        ]);

        if (count($validated['answers']) === 0) {
            return response()->json([
                'message' => 'No answers submitted.'
            ], 422);
        }

        try{
            $activityData = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/matching/{$difficulty}/{$activityId}/items")
                ->getSnapshot()
                ->getValue() ?? [];

            $score = 0;
            $total = count($validated['answers']);

            $answerKeyMap = [];
            foreach ($activityData as $item) {
                $key = $item['audio_id'] . '|' . $item['image_id'];
                $answerKeyMap[$key] = true;
            }

            $score = 0;
            $items = [];

            foreach ($validated['answers'] as $answer) {
                $key = $answer['audio_id'] . '|' . $answer['image_id'];
                $isCorrect = isset($answerKeyMap[$key]);

                if ($isCorrect) {
                    $score++;
                }

                $items[] = [
                    'audio_id' => $answer['audio_id'],
                    'image_id' => $answer['image_id'],
                    'correct' => $isCorrect,
                ];
            }

            $date = now()->toDateTimeString();


            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attempts/matching/{$activityId}/{$userId}/{$attemptId}")
                ->update([
                    'submitted_at' => $date,
                    'score' => $score,
                    'items' => $items,
                ]);

            return response()->json([
                'success' => true,
                'score' => $score,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
