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
        try{
            $gradeLevel = $request->get('firebase_user_gradeLevel');
            $userId = $request->get('firebase_user_id');

            $validated = $request->validate([
                'audio_file' => ['required','file']
            ]);

            $file = $request->file('audio_file');

            $uuid     = (string) Str::uuid();
            $extension = 'wav';
            $filename  = "{$uuid}.{$extension}";
            $path      = $file->storeAs('audio_submissions', $filename, 'public');
            $date  = now()->toDateTimeString();

            $ref = $this->database->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attempts/{$activityType}/{$activityId}/{$userId}/{$attemptId}");
            $attempt = $ref->getSnapshot()->getValue() ?? [];

            $answers = $attempt['answers'] ?? [];

            foreach ($answers as $idx => $entry) {
                if (isset($entry['flashcard_id']) && $entry['flashcard_id'] === $flashcardId) {
                    $answers[$idx]['audio_path']  = $path;
                    $answers[$idx]['updated_at']  = $date;
                    break;
                }
            }

            $ref->update([
                'answers'     => $answers,
                'updated_at'  => $date,
            ]);

            return response()->json([
                'success'      => true,
                'message' => "Successfully saved answer",
                'file' => $validated['audio_file']
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function submitActivity(
        Request $request, 
        string $subjectId, 
        string $activityType, 
        string $difficulty, 
        string $activityId, 
        string $attemptId, 
    ){
    
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        $date = now()->toDateTimeString();

        $updateData = [
            'status' => 'submitted',
            'submitted_at' => $date
        ];

        $submitAttempt =  $this->database
        ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attempts/{$activityType}/{$activityId}/{$userId}/{$attemptId}")
        ->update($updateData);

        return response()->json([
            'success' => true,
            'message' => "successfully submitted",
        ],200);
    }

    public function scoring(Request $request){
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        $request->validate([
        'text' => 'required|string',
        'user_audio_file' => 'required|file|mimes:wav,mp3,webm,wav',
        ]);
    }
}