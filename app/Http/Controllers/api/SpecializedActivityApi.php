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

    public function getSpeechActivities(Request $request, string $subjectId, string $activityType, string $difficulty){

        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try{
            $activities = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/{$activityType}/{$difficulty}")
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
                'success'     => true,
                'activities' => $activities['flashcards']
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

            foreach ($validated['flashcard_text'] ?? [] as $text) {
                $id = (string) Str::uuid()->toString();
                $flashcardData[$id] = [
                    'value'=> $text,
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
}
