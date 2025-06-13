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

    public function getBingoById(Request $request, string $subjectId, string $difficulty, string $activityId){
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id'); 

        try{

            $activity = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/bingo/{$difficulty}/{$activityId}")
                ->getSnapshot()
                ->getValue() ?? [];

            if (empty($activity)){
                return response()->json([
                    'success' => false,
                    'message' => "Activity not found"
                ]);
            }

            $bucket = $this->storage->getBucket();

            $items = [];
            foreach($activity['items'] as $index => $item){
                $image = $bucket->object($item['image_path'])->signedUrl(now()->addMinutes(30));

                $items[] = [
                    "id" => $index,
                    "image_id" => $index,
                    "image_path" => $image,
                    "filename" => $item['filename']
                ];
            }

            $audioList = [];
            foreach($activity['audio_paths'] as $index => $item){
                $audio = $bucket->object($item['audio_path'])->signedUrl(now()->addMinutes(30));

                $audioList[] = [
                    "audio_id" => $index,
                    "audio_path" => $audio,
                    "filename" => $item['filename']
                ];
            }

            return response()->json([
                'success' => true,
                'items' => $items,
                'audio' => $audioList,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getMatchingById(Request $request, string $subjectId, string $difficulty, string $activityId){
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id'); 

        try{

            $activity = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/matching/{$difficulty}/{$activityId}")
                ->getSnapshot()
                ->getValue() ?? [];

            if (empty($activity)){
                return response()->json([
                    'success' => false,
                    'message' => "Activity not found"
                ]);
            }

            $bucket = $this->storage->getBucket();

            $items = [];
            $audiolist = [];
            $answers = [];

            foreach($activity['items'] as $index => $item){
                $image = $bucket->object($item['image_path'])->signedUrl(now()->addMinutes(30));
                $audio = $bucket->object($item['audio_path'])->signedUrl(now()->addMinutes(30));

                $items[] = [
                    "id" => $index,
                    "image_id" => $item['image_id'],
                    "image_path" => $image,
                    "filename" => $item['image_filename'] ?? "unnamed_image",
                ];

                $audiolist[] =[
                    "audio_id" => $item['audio_id'],
                    "audio_path" => $audio,
                    "filename" => $item['audio_filename'] ?? "unnamed_audio",
                ];

                $answers[] = [
                    "audio_id" => $item['audio_id'],
                    "image_id" => $item['image_id'],
                ];
            }

            return response()->json([
                'success' => true,
                'items' => $items,
                'audio' => $audiolist,
                'answers' => $answers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function createBingoActivity(Request $request, string $subjectId){
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id'); 

        $validated = $request->validate([
            'activity_type' => 'required|in:bingo',
            'difficulty' => 'required|in:easy,average,difficult,challenge',

            'activity' => 'required|array|min:9|max:12',
            'activity.*.image' => 'required|file|image|mimes:jpg,png|max:5120',
            'activity.*.is_answer'=> 'required|string|min:1',

            'audio' => 'required|array|min:1',
            'audio.*.audio_file' => 'required|file|mimetypes:audio/mpeg,audio/mp3,video/mp4'
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

                $items[$image_id] = [
                    'image_path' => $remoteImagePath,
                    'filename' => $image_file->getClientOriginalName()
                ];

                if($bingo['is_answer'] === "true" || $bingo['is_answer'] === true){
                    $correct_answers[] = ['image_id' => $image_id];
                }
            }

            $audio_paths = [];

            foreach ($validated['audio'] as $audio) {
                $audio_file = $audio['audio_file'];
                $uuid = (string) Str::uuid();
                $filename = $uuid . $audio_file->getClientOriginalName();
                $remoteAudioPath = "audio/auditory/" . $filename;

                $bucket->upload(
                    fopen($audio_file->getPathName(), 'r'),
                    ['name' => $remoteAudioPath]
                );

                $audio_id = (string) Str::uuid();

                $audio_paths[$audio_id] = [
                    'audio_path' => $remoteAudioPath,
                    'filename' => $audio_file->getClientOriginalName()
                ];
            }

            $date = now()->toDateTimeString();

            $activityData = [
                'items' => $items,
                'audio_paths' => $audio_paths,
                'correct_answers' => $correct_answers,
                'total' => count($items),
                'created_at' => $date,
                'created_by' => $userId
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
        $userId = $request->get('firebase_user_id'); 

        $validated = $request->validate([
            'activity_type' => 'required|in:matching',
            'difficulty' => 'required|in:easy,average,difficult,challenge',

            'activity' => 'required|array|min:3|max:5',
            'activity.*.image' => 'required|file|image|mimes:jpg,png|max:5120',
            'activity.*.audio' => 'required|file|mimetypes:audio/mpeg,audio/mp3,video/mp4'
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
                $audioFilename = $audioUuid . $audio_file->getClientOriginalName();
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
                    'image_id' => $image_id,
                    'image_path' => $remoteImagePath,
                    'audio_id' => $audio_id,
                    'audio_path' => $remoteAudioPath,
                    'image_filename' => $image_file->getClientOriginalName(),
                    'audio_filename' => $audio_file->getClientOriginalName()
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

    public function editBingoActivity(
        Request $request,
        string $subjectId,
        string $difficulty,
        string $activityId
    ) {
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        $validated = $request->validate([
            'activity' => 'required|array|min:9|max:12',
            'activity.*.image' => 'nullable|file|mimes:jpg,png|max:5120',
            'activity.*.image_id' => 'nullable|string|min:1',
            'activity.*.is_answer' => 'required|string|min:1',

            'audio' => 'required|array|min:1',
            'audio.*.audio_id' => 'nullable|string|min:1',
            'audio.*.audio_file' => 'nullable|file|mimetypes:audio/mpeg,audio/mp3,video/mp4'
        ]);

        try {
            $existingActivity = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/bingo/{$difficulty}/{$activityId}")
                ->getSnapshot()
                ->getValue();

            if (empty($existingActivity)) {
                return response()->json([
                    'success' => false,
                    'message' => "Activity not found!"
                ]);
            }

            $mappedCorrectAnswers = [];
            foreach ($existingActivity['correct_answers'] ?? [] as $image_id) {
                $mappedCorrectAnswers[$image_id] = true;
            }

            $updatedActivity = [];
            $bucket = $this->storage->getBucket();

            foreach ($validated['activity'] as $item) {
                $image_id = isset($item['image_id']) ? (string) $item['image_id'] : (string) Str::uuid();
                $isAnswer = $item['is_answer'];

                if (isset($item['image']) && $item['image']) {
                    $image = $item['image'];
                    $remoteImagePath = 'images/auditory/' . $image_id . '_' . $image->getClientOriginalName();

                    $existing_image = $existingActivity['items'][$image_id]['image_path'] ?? null;
                    if ($existing_image) {
                        $bucket->object($existing_image)->delete();
                    }

                    $bucket->upload(
                        fopen($image->getPathName(), 'r'),
                        ['name' => $remoteImagePath]
                    );

                    $updatedActivity[$image_id] = [
                        'image_path' => $remoteImagePath,
                        'filename' => $image->getClientOriginalName(),
                        'is_answer' => $isAnswer
                    ];
                }else {
                    $updatedActivity[$image_id] = [
                        'image_path' => $existingActivity['items'][$image_id]['image_path'] ?? null,
                        'filename' => $existingActivity['items'][$image_id]['filename'] ?? null,
                        'is_answer' => $isAnswer
                    ];
                }

                if ($isAnswer === "true") {
                    $mappedCorrectAnswers[$image_id] = true;
                } else {
                    unset($mappedCorrectAnswers[$image_id]);
                }
            }

            $uploadedAudio = [];
            foreach ($validated['audio'] as $audioItem) {
                $audioId = isset($audioItem['audio_id']) ? (string) $audioItem['audio_id'] : (string) Str::uuid();

                if(isset($item['audio_file']) && $item['audio_file']){
                    $audioFile = $audioItem['audio_file'];
                    $existing_audio = $existingActivity['audio'][$audioId]['audio_path'] ?? null;

                    if($existing_audio){
                        $bucket->object($existing_audio)->delete();
                    }

                    $remoteAudioPath = 'audio/auditory/' . $audioId . '_' . $audioFile->getClientOriginalName();
                    $bucket->upload(
                        fopen($audioFile->getPathName(), 'r'),
                        ['name' => $remoteAudioPath]
                    );

                    $uploadedAudio[$audioId] = [
                        'audio_path' => $remoteAudioPath,
                        'filename' => $audioFile->getClientOriginalName(),
                    ];
                }
            }

            $date = now()->toDateTimeString();

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/bingo/{$difficulty}/{$activityId}")
                ->update([
                    'items' => $updatedActivity,
                    'correct_answers' => array_keys($mappedCorrectAnswers),
                    'audio' => $uploadedAudio,
                    'updated_at' => $date,
                    'updated_by' => $userId,
                ]);

            return response()->json([
                'success' => true,
                'message' => "Updated successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function editMatchingActivity(
        Request $request,
        string $subjectId,
        string $difficulty,
        string $activityId
    ) {
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        $validated = $request->validate([
            'activity' => 'required|array|min:3|max:5',
            'activity.*.image' => 'nullable|file|image|mimes:jpg,png|max:5120',
            'activity.*.image_id' => 'required|string|min:1',
            'activity.*.audio' => 'nullable|file|mimetypes:audio/mpeg,audio/mp3,video/mp4',
            'activity.*.audio_id' => 'required|string|min:1',
        ]);

        try {
            $existingActivity = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/matching/{$difficulty}/{$activityId}")
                ->getSnapshot()
                ->getValue();

            if (!$existingActivity) {
                return response()->json([
                    'success' => false,
                    'error' => 'Activity not found.'
                ], 404);
            }

            $mapped_image_paths = [];
            $mapped_audio_paths = [];
            foreach ($existingActivity['items'] as $item) {
                $mapped_image_paths[$item['image_id']] = $item['image_path'];
                $mapped_audio_paths[$item['audio_id']] = $item['audio_path'];
            }

            $bucket = $this->storage->getBucket();
            $newItems = [];

            foreach ($validated['activity'] as $activityItem) {
                $image_id = $activityItem['image_id'] ?? (string) Str::uuid();
                $audio_id = $activityItem['audio_id'] ?? (string) Str::uuid();
                $remoteImagePath = $mapped_image_paths[$image_id] ?? '';
                $remoteAudioPath = $mapped_audio_paths[$audio_id] ?? '';
                $image_filename = $mapped_image_paths[$image_id] ?? "unnamed_image";
                $audio_filename = $mapped_audio_paths[$audio_id] ?? "unnamed_audio";

                if (!empty($activityItem['image'])) {
                    $image = $activityItem['image'];
                    $existing_image = $mapped_image_paths[$image_id] ?? null;

                    if ($existing_image) {
                        $bucket->object($existing_image)->delete();
                    }

                    $remoteImagePath = 'images/auditory/' . $image_id . '_' . $image->getClientOriginalName();
                    $image_filename = $image->getClientOriginalName();

                    $bucket->upload(
                        fopen($image->getPathName(), 'r'),
                        ['name' => $remoteImagePath]
                    );
                }

                if (!empty($activityItem['audio'])) {
                    $audio = $activityItem['audio'];
                    $existing_audio = $mapped_audio_paths[$audio_id] ?? null;

                    if ($existing_audio) {
                        $bucket->object($existing_audio)->delete();
                    }

                    $remoteAudioPath = 'audio/auditory/' . $audio_id . '_' . $audio->getClientOriginalName();
                    $audio_filename = $audio->getClientOriginalName();

                    $bucket->upload(
                        fopen($audio->getPathName(), 'r'),
                        ['name' => $remoteAudioPath]
                    );
                }

                $newItems[] = [
                    'image_id' => $image_id,
                    'image_path' => $remoteImagePath,
                    'image_filename' => $image_filename,
                    'audio_id' => $audio_id,
                    'audio_path' => $remoteAudioPath,
                    'audio_filename' => $audio_filename
                ];
            }

            $date = now()->toDateTimeString();

            if (count($newItems) === 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'No valid activity items to update.'
                ], 422);
            }

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/matching/{$difficulty}/{$activityId}")
                ->update([
                    'items' => $newItems,
                    'updated_at' => $date,
                    'updated_by' => $userId,
                    'items_count' => count($newItems),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Matching activity updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function startBingoActivity(
        Request $request,
        string $subjectId,
        string $difficulty,
        string $activityId
    ) {
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id'); 

        try {
            $activityData = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/bingo/{$difficulty}/{$activityId}")
                ->getSnapshot()
                ->getValue();

            if (!$activityData) {
                return response()->json([
                    'success' => false,
                    'error' => 'Activity not found.'
                ], 404);
            }

            $bucket = $this->storage->getBucket();

            $items = [];
            foreach ($activityData['items'] as $index => $item) {
                $imageUrl = $bucket->object($item['image_path'])->signedUrl(now()->addMinutes(15));
                $items[] = [
                    'image_url' => $imageUrl,
                    'image_id' => $index,
                    'is_answer' => "false"
                ];
            }

            shuffle($items);

            $audio = [];
            foreach ($activityData['audio_paths'] as $index => $audioItem) {
                $audioUrl = $bucket->object($audioItem['audio_path'])->signedUrl(now()->addMinutes(15));
                $audio[] = [
                    'audio_url' => $audioUrl,
                    'audio_id' => $index,
                ];
            }

            shuffle($audio);

            $attemptId = $this->generateUniqueId("ATTM");
            $startedAt = now()->toDateTimeString();

            $initialInfo = [
                'items' => $items,
                'audios' => $audio,
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
                'audio_paths' => $audio,
                'total' => count($items)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
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
            $audios = [];

            foreach ($activityData['items'] as $index => $item) {
                $image = $bucket->object($item['image_path'])->signedUrl(now()->addMinutes(15));
                $audio = $bucket->object($item['audio_path'])->signedUrl(now()->addMinutes(15));

                $items[] = [
                    'image_url' => $image,
                    'image_id' => $item['image_id'],
                ];

                $audios[] = [
                    'audio_url' => $audio,
                    'audio_id' => $item['audio_id'],
                ];
            }

            shuffle($items);
            shuffle($audios);

            $attemptId = $this->generateUniqueId("ATTM");
            $startedAt = now()->toDateTimeString();

            $initialInfo = [
                'items' => $items,
                'audio' => $audios,
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
                'audio' => $audios,
                'total' => count($items),
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
            'answers' => 'required|array|min:1',
            'answers.*.image_id' => 'required|string|min:1',
            'answers.*.selected_at' => 'required|string|min:1',

            'audio_played' => 'required|array',
            'audio_played.*.audio_id' => 'required|string|min:1',
            'audio_played.*.played_at' => 'required|array',
            'audio_played.*.played_at.*' => 'required|string',
        ]);

        try {
            $now = now()->toDateTimeString();

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
                $correctMap[$correct] = true;
            }

            $submittedMap = [];
            foreach ($validated['answers'] as $answer) {
                $submittedMap[$answer['image_id']] = $answer['selected_at'];
            }

            $attemptResult = [];
            $intex = [];

            $rawScore = 0;
            $totalCorrect = count($activity['correct_answers']);

            foreach ($activity['items'] as $index => $item) {
                $indexKey = (string) $index;
                if (isset($submittedMap[$indexKey])) {
                    $isCorrect = isset($correctMap[$indexKey]);
                    if ($isCorrect) {
                        $rawScore++;
                    }

                    $attemptResult[] = [
                        'image_id' => $indexKey,
                        'is_correct' => $isCorrect,
                        'selected_at' => $submittedMap[$indexKey],
                    ];

                    if (!$isCorrect) {
                        $intex[] = ['index' => $submittedMap[$indexKey]];
                    }
                }
            }

            $score = $totalCorrect > 0 ? round(($rawScore / $totalCorrect) * 100) : 0;

            $ref->update([
                'items' => $attemptResult,
                'score' => $score,
                'audio_played'=> $validated['audio_played'],
                'submitted_at' => $now,
                'status' => "submitted",
            ]);

            return response()->json([
                'success' => true,
                'message' => "Submitted Successfully",
                'score' => $score,
                'intex' => $intex
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
            'answers.*.image_id' => 'required|string|min:1',
            'answers.*.audio_id' => 'required|string|min:1',

            'answerLogs' => 'required|array|min:1',
            'answerLogs.*.audio_id' => 'required|string',
            'answerLogs.*.audio_played' => 'required|array|min:1',
            'answerLogs.*.audio_played.*' => 'required|string|min:1',

            'answerLogs.*.selected' => 'required|array|min:1',
            'answerLogs.*.selected.*' => 'required|string',

            'answerLogs.*.image_selected_at' => 'required|array',
            'answerLogs.*.image_selected_at.*' => 'nullable|string|min:1',
        ]);

        if (count($validated['answers']) === 0) {
            return response()->json([
                'message' => 'No answers submitted.'
            ], 422);
        }

        try {
            $date = now()->toDateTimeString();

            $activityData = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/matching/{$difficulty}/{$activityId}/items")
                ->getSnapshot()
                ->getValue() ?? [];

            $answerKeyMap = [];
            foreach ($activityData as $item) {
                $key = $item['audio_id'] . '|' . $item['image_id'];
                $answerKeyMap[$key] = true;
            }

            $rawScore = 0;
            $total = count($validated['answers']);
            $items = [];

            foreach ($validated['answers'] as $answer) {
                $key = $answer['audio_id'] . '|' . $answer['image_id'];
                $isCorrect = isset($answerKeyMap[$key]);

                if ($isCorrect) {
                    $rawScore++;
                }

                $items[] = [
                    'audio_id' => $answer['audio_id'],
                    'image_id' => $answer['image_id'],
                    'correct' => $isCorrect,
                ];
            }

            $score = $total > 0 ? round(($rawScore / $total) * 100) : 0;

            $processedLogs = [];
            foreach ($validated['answerLogs'] as $log) {
                $processedLogs[] = [
                    'audio_id' => $log['audio_id'],
                    'play_count' => count($log['audio_played']),
                    'first_played_at' => $log['audio_played'][0] ?? null,
                    'last_played_at' => end($log['audio_played']) ?: null,
                    'selected_images' => $log['selected'],
                    'image_selected_at' => $log['image_selected_at'],
                ];
            }

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attempts/matching/{$activityId}/{$userId}/{$attemptId}")
                ->update([
                    'status' => "submitted",
                    'submitted_at' => $date,
                    'total_score' => $score,
                    'items' => $items,
                    'answer_logs' => $processedLogs,
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

    public function continueBingoActivity(
        Request $request,
        string $subjectId,
        string $activityId,
        string $attemptId,
        string $activityType,
    ) {
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        try {
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

            return response()->json([
                'success' => true,
                'attemptId' => $attemptId,
                'items' => shuffle($attempt['items']) ?? [],
                'audio_paths' => shuffle($attempt['audios']) ?? [],
                'total' => isset($attempt['items']) ? count($attempt['items']) : 0,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
