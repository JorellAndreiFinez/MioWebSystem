<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class TeacherApiController extends Controller
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

    public function editSubjectAnnouncementApi(Request $request, string $subjectId, string $announcementId){
        
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        $validated = $request->validate([
            'title'       => 'required|string|max:250',
            'description' => 'required|string|max:1000',
        ]);

        try {
            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/announcements/{$announcementId}")
                ->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Announcement updated successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Failed to update announcement: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function createSubjectAnnouncementApi(Request $request, string $subjectId)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string|max:1000',
        ]);

        $gradeLevel = $request->get('firebase_user_gradeLevel');

        $announcementId = $this->generateUniqueId('ANN');

        $announcementData = [
            'title'       => $validated['title'],
            'description' => $validated['description'],
            'date_posted' => now()->toDateTimeString(),
        ];

        try {
            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/announcements/{$announcementId}")
                ->set($announcementData);

            return response()->json([
                'success' => true,
                'message' => 'Announcement created successfully.',
                'announcement_id' => $announcementId,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Failed to create announcement: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteSubjectAnnouncementApi(Request $request, string $subjectId, string $announcementId){
        
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try {
            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/announcements/{$announcementId}")
                ->remove();

            return response()->json([
                'success' => true,
                'message' => "Announcement deleted successfully.",
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Failed to delete announcement: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function createAssignmentApi(Request $request, string $subjectId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        $validated = $request->validate([
            'availability'          => 'required|array|size:2',
            'availability.start'    => 'required|string',
            'availability.end'      => 'required|string|',
            'attempts'              => 'required|integer|min:1|max:100',
            'title'                 => 'required|string|max:250',
            'description'           => 'required|string|max:1000',
            'total'                 => 'required|integer|min:1',
            'submission_type'       => 'required|string',
            'published_at'          => 'nullable|string',
            'deadline'              => 'nullable|string',
        ]);

        $assignmentId = $this->generateUniqueId('ASS');
        $date = now()->toDateTimeString();

        $assignmentData = array_merge($validated, [
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        try{
            $assignment = $this->database
            ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/assignments/{$assignmentId}")
            ->set($assignmentData);

            return response()->json([
                'success' => true,
                'message' => "Assignment created successfully.",
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function editSubjectAssignmentApi(Request $request, string $subjectId, string $assignmentId){
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        $validated = $request->validate([
            'availability'          => 'required|array|size:2',
            'availability.start'    => 'required|string',
            'availability.end'      => 'required|string|after:availability.start',
            'attempts'              => 'required|integer|min:1|max:100',
            'title'                 => 'required|string|max:250',
            'description'           => 'required|string|max:1000',
            'total'                 => 'required|integer|min:1',
            'submission_type'       => 'required|string',
            'published_at'          => 'nullable|string',
            'deadline'              => 'nullable|string',
        ]);

        try{

            $this->database->getReference("subjects/GR{$gradeLevel}/{$subjectId}/assignments/{$assignmentId}")
            ->update($validated);

            return response()->json([
                'success' => true,
                'message' => "Assignment {$assignmentId} updated successfully."
            ], 200);

        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Failed to delete assignment: ' . $e->getMessage(),
            ], 500);
        }

    }

    public function deleteSubjectAssignmentApi(Request $request, string $subjectId, string $assignmentId){
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try{
            $this->database->getReference("subjects/GR{$gradeLevel}/{$subjectId}/assignments/{$assignmentId}")
            ->remove();

            return response()->json([
                'success' => true,
                'message' => "Assignment {$assignmentId} deleted successfully.",
            ], 200);
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Failed to delete assignment: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function createSubjectQuizzesApi(Request $request, string $subjectId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try {
            $validated = $request->validate([
                'deadline'                 => 'required|string',
                'attempts'                 => 'required|integer|min:1',
                'description'              => 'required|string|max:1000',
                'title'                    => 'required|string|max:255',
                'total'                    => 'required|integer|min:1',
                'time_limit'               => 'required|integer|min:1',
                'publish_date'             => 'required|string',
                'questions'                => 'required|array|min:1',
                'questions.*.question'     => 'required|string',
                'questions.*.answer'       => 'required|string',
                'questions.*.type'         => 'required|string',
                'questions.*.options'      => 'required|array|min:1',
                'questions.*.options.*'    => 'required|string',
            ]);

            $questionsWithIds = [];
            foreach ($validated['questions'] as $q) {
                $uuid = (string) Str::uuid();
                $questionsWithIds[$uuid] = [
                    'question' => $q['question'],
                    'answer'   => $q['answer'],
                    'type'     => $q['type'],
                    'options'  => $q['options'],
                ];
            }

            $quizId = $this->generateUniqueId('QU');
            $date   = now()->toDateTimeString();

            $quizData = array_merge($validated, [
                'questions'  => $questionsWithIds,
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/quizzes/{$quizId}")
                ->set($quizData);

            return response()->json([
                'success' => true,
                'message' => "Quiz created successfully.",
            ], 201);

        } catch (\Exception $e) {
            Log::error('Quiz creation failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateSubjectQuizzesApi(Request $request, string $subjectId, string $quizId){
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try{
            $validated = $request->validate([
                'deadline' => 'required|string',
                'attempts' => 'required|number|min:1',
                'description' => 'required|string|max:1000',
                'title' => 'required|string|max:255',
                'total' => 'required|number|min:1',
                'time_limit' => 'required|number|min:1',
                'questions' => 'required',
            ]);


            $quizzes = $this->database
            ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/quizzes/{$quizId}")
            ->update();

            return response()->json([
                'success' => true,
                'message' => "Successfully update quiz",
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function createSubjectSpecializedActivity(Request $request, string $subjectId){
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try{
            $validated = $request->validate([
                'activity_type'  => ['required','in:picture,question,phrase,pronunciation'],
                'difficulty'     => ['required','in:easy,medium,hard'],
                'task_text'      => 'nullable|string|max:250',
                'task_file'      => [
                    'nullable',
                    'file',
                    Rule::requiredIf($request->input('activity_type') === 'picture'),
                    'mimes:jpg,png,gif',
                    'max:5120',
                ],
                'answer_text'    => 'nullable|string|max:250',
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

}
