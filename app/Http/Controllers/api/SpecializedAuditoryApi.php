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

            'answer' => 'required|array|min:1',
            'answer.*.audio_file' => 'required|file'
        ]);

        try{

            $bucket = $this->storage->getBucket();
            $items  = [];

            foreach($validated['activity'] as $index => $bingo){
                $image_file = $bingo['image'];
                $uuid = (string) Str::uuid()->toString();
                $remoteImagePath = 'images/auditory/' . $uuid . $image_file->getClientOriginalName();

                $bucket->upload(
                    fopen($image_file->getPathName(), 'r'),
                    ['name' => $remoteImagePath]
                );

                $items[] = [
                    'image_no' => $index,
                    'image_path' => $remoteImagePath,
                    'is_answer'=> $bingo['is_answer'],
                ];
            }

            $audio_files = [];

            foreach ($validated['answer'] as $audio) {
                $uuid = (string) Str::uuid();
                $filename = "{$uuid}.mp3";
                $remoteAudioPath = "audio/auditory/" . $filename;

                $bucket->upload(
                    fopen($audio->getPathName(), 'r'),
                    ['name' => $remoteAudioPath]
                );

                $audio_files[] = ['audio_path' => $remoteAudioPath];
            }

            $date = now()->toDateTimeString();

            $activityData = [
                'items' => $items,
                'audio_files' => $audio_files,
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
                'data'    => $activityData,
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
            'activity.*.is_answer'=> 'required|string',

            'answer' => 'required|array|min:1',
            'answer.*.audio_file' => 'required|file'
        ]);

        try{

            $bucket = $this->storage->getBucket();
            $items  = [];

            foreach($validated['activity'] as $index => $bingo){
                $image_file = $bingo['image'];
                $uuid = (string) Str::uuid()->toString();
                $remoteImagePath = 'images/auditory/' . $uuid . $image_file->getClientOriginalName();

                $bucket->upload(
                    fopen($image_file->getPathName(), 'r'),
                    ['name' => $remoteImagePath]
                );

                $items[] = [
                    'image_no' => $index,
                    'image_path' => $remoteImagePath,
                    'is_answer'=> $bingo['is_answer'],
                ];
            }

            $audio_files = [];

            foreach ($validated['answer'] as $audio) {
                $uuid = (string) Str::uuid();
                $filename = "{$uuid}.wav";
                $remoteAudioPath = "audio/auditory/{$filename}";

                $bucket->upload(
                    fopen($audio->getPathName(), 'r'),
                    ['name' => $remoteAudioPath]
                );

                $audio_files[] = ['audio_path' => $remoteAudioPath];
            }

            $date = now()->toDateTimeString();

            $activityData = [
                'items' => $items,
                'audio_files' => $audio_files,
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
                'data'    => $activityData,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function startAuditoryActivity(
        Request $request,
        string $subjectId,
        string $activityType,
        string $difficulty,
        string $activityId
    ){
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id'); 

        try{
            $activityData = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/{$activityType}/{$difficulty}/{$activityId}")
                ->getSnapshot()
                ->getValue();

            if (! $activityData || ! isset($activityData['items'])) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Activity not found.'
                ], 404);
            }

            $answers = [];
            foreach ($activityData['items'] as $index => $item) {
                $answers[] = [
                    'image_no'   => $index,
                    'image_path' => $item['image_path'],
                    'answer'     => null,
                ];
            }

            $audio_files = [];
            foreach ($activityData['audio_files'] as $index => $item) {
                $audio_files[] = [
                    'audio_files' => $item['audio_path'],
                ];
            }

            $attemptId   = (string) Str::uuid();
            $startedAt   = now()->toDateTimeString();

            $initialInfo = [
                'answers' => $answers,
                'audio_files' => $audio_files,
                'started_at' => $startedAt,
                'status' => 'in-progress',
            ];

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attempts/{$activityType}/{$activityId}/{$userId}/{$attemptId}")
                ->set($initialInfo);

            return response()->json([
                'success'    => true,
                'attemptId'  => $attemptId,
                'activity' => $answers,
                'audio_files' => $audio_files
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function finalizeAuditoryAttempt(
        Request $request,
        string $subjectId,
        string $activityType,
        string $difficulty,
        string $activityId,
        string $attemptId
    ){
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        $validated = $request->validate([
            'activity' => 'required|array',
            'activity.*.image_no' => 'required|integer',
            'activity.*.answer'=> 'required|string',
        ]);

        try{
            $ref = $this->database->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attempts/{$activityType}/{$activityId}/{$userId}/{$attemptId}");
            
            $correctData = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/{$activityType}/{$difficulty}/{$activityId}")
                ->getSnapshot()
                ->getValue() ?? [];

            if (!isset($correctData['items'])) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Activity data not found.'
                ], 404);
            }

            $studentAnswers = $validated['activity'];
            $attemptResult = [];

            $correctMap = [];
            foreach ($correctData['items'] as $correct) {
                $key = $correct['image_no'];
                $correctMap[$key] = $correct['is_answer'];
            }

            $total_score = 0;

            foreach ($studentAnswers as $ans) {
                $key = $ans['image_no'];
                $is_correct = isset($correctMap[$key])
                            && $correctMap[$key] === $ans['answer'];

                if($is_correct) $total_score += 1;

                $attemptResult[] = [
                    'image_no'   => $ans['image_no'],
                    'is_correct' => $is_correct,
                ];
            }

            $now = now()->toDateTimeString();

            $ref->getChild('answers')->set($attemptResult);
            $ref->update([
                'score' => $total_score,
                'completed_at' => $now,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Submitted Successfully",
                'score' => $total_score,
                'results' => $attemptResult,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
