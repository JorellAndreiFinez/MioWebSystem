<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;


class StudentApiController extends Controller
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

    public function viewSubjectsApi(Request $request)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $role = $request->get('firebase_user_role');

        $subjects = $this->database
            ->getReference("subjects/GR" . $gradeLevel)
            ->getSnapshot()
            ->getValue() ?? [];

        $filteredSubjects = [];
        foreach ($subjects as $subjectId => $subjectData) {
            $filteredSubjects[] = [
                'subject_id' => $subjectId,
                'section' => $subjectData['code'] ?? null,
                'title' => $subjectData['title'] ?? null,
                'description' => $subjectData['modules']['MOD00']['description'] ?? null,
                'subjectType' => $subjectData['subjectType'] ?? null,
                'specialized_type' => $subjectData['specialized_type'] ?? null,
            ];
        }

        return response()->json([
            'success' => true,
            'subjects' => $filteredSubjects,
            'role' => $role,
        ], 200);
    }


    public function getSubjectModulesApi(Request $request, string $subjectId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        $modules = $this->database
            ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/modules")
            ->getSnapshot()
            ->getValue()
            ?? [];

        $filteredmodules = [];

        if (!empty($modules) && is_array($modules)) {
            foreach ($modules as $key => $item) {
                $filteredmodules[] = [
                    'module_id' => $key,
                    'title' => $item['title'] ?? null,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'modules' => $filteredmodules,
        ], 200);
    }

    public function getSubjectAnnouncementsApi(Request $request, string $subjectId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        $announcements = $this->database
            ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/announcements")
            ->getSnapshot()
            ->getValue();

        $filteredAnnouncements = [];
        if (!empty($announcements) && is_array($announcements)) {
            foreach ($announcements as $key => $item) {
                $filteredAnnouncements[] = [
                    'announcement_id' => $key,
                    'date_posted'     => $item['date_posted']    ?? null,
                    'description'     => $item['description']    ?? null,
                    'subject_id'      => $item['subject_id']     ?? null,
                    'title'           => $item['title']          ?? null,
                ];
            }
        }

        return response()->json([
            'success'       => true,
            'announcements' => $filteredAnnouncements,
        ], 200);
    }

    public function getSubjectAnnouncementByIdApi(Request $request, string $subjectId, string $announcementId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try {
            $refPath = "subjects/GR{$gradeLevel}/{$subjectId}/announcements/{$announcementId}";
            $snapshot = $this->database
                ->getReference($refPath)
                ->getSnapshot()
                ->getValue();

            if (empty($snapshot)) {
                return response()->json([
                    'success'     => false,
                    'announcement'=> null,
                    'error'       => 'Announcement not found.',
                ], 404);
            }

            $files = [];
            if (!empty($snapshot['files']) && is_array($snapshot['files'])) {
                foreach ($snapshot['files'] as $file) {
                    if (isset($file['url'])) {
                        $files[] = $file['url'];
                    }
                }
            }

            $links = !empty($snapshot['links']) && is_array($snapshot['links'])
                ? $snapshot['links']
                : [];

            $announcement = [
                'title'       => $snapshot['title'],
                'description' => $snapshot['description'],
                'date_posted' => $snapshot['date_posted'],
                'links'       => $links,
                'files'       => $files,
            ];

            return response()->json([
                'success'      => true,
                'announcement' => $announcement,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success'      => false,
                'announcement' => null,
                'error'        => 'Failed to fetch announcement: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getSubjectAssignmentsApi(Request $request, string $subjectId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        $assignments = $this->database
            ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/assignments")
            ->getSnapshot()
            ->getValue();

        $filteredAssignments = [];
        if (!empty($assignments) && is_array($assignments)) {
            foreach ($assignments as $key => $item) {
                $filteredAssignments[] = [
                    'assignment_id' => $key,
                    'attempts' => $item['attempts'] ?? null,
                    'availability' => $item['availability'] ?? null,
                    'createdAt' => $item['created_at'] ?? null,
                    'description' => $item['description'] ?? null,
                    'publishedAt' => $item['published_at'] ?? null,
                    'title' => $item['title'] ?? null,
                    'total' => $item['total'] ?? null,
                    'submission_type' => $item['submission_type'] ?? null,
                ];
            }
        }

        return response()->json([
            'success'       => true,
            'assignments' => $filteredAssignments,
        ], 200);
    }

    public function getSubjectAssignmentByIdApi(Request $request, string $subjectId, string $assignmentId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try{
            $assignmentData = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/assignments/{$assignmentId}")
                ->getSnapshot()
                ->getValue();

            if (empty($assignmentData)) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Assignment not found.',
                ], 404);
            }

            if (isset($assignmentData['people'])) {
                unset($assignmentData['people']);
            }

            return response()->json([
                'success'       => true,
                'assignment' => $assignmentData,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Failed to create assignment: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getSubjectScoresApi(Request $request, string $subjectId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        $scores = $this->database
            ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/scores")
            ->getSnapshot()
            ->getValue();

        return response()->json([
            'success' => true,
            'scores' => $scores,
        ], 200);
    }

    public function getSubjectQuizzesApi(Request $request, string $subjectId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try {
            $snapshot = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/quizzes")
                ->getSnapshot();

            $allQuizzes = $snapshot->getValue() ?? [];
            $filteredQuizzes = [];

            foreach ($allQuizzes as $quizId => $quiz) {
                if (!is_array($quiz)) {
                    continue;
                }

                $questions = [];
                if (!empty($quiz['questions']) && is_array($quiz['questions'])) {
                    foreach ($quiz['questions'] as $questionId => $qData) {
                        $questions[] = [
                            'question_id' => $questionId,
                            'question'    => $qData['question']    ?? null,
                            'type'        => $qData['type']        ?? null,
                            'options'     => $qData['options']     ?? [],
                        ];
                    }
                }

                $filteredQuizzes[] = [
                'quiz_id' => $quizId,
                'title'   => $quiz['title'] ?? null,
                'total'   => $quiz['total'] ?? null,
            ];
            }

            return response()->json([
                'success' => true,
                'quizzes' => $filteredQuizzes,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getSubjectQuizByIdApi(Request $request, string $subjectId, string $quizId){
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try{
            $quizzes = $this->database
            ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/quizzes/{$quizId}")
            ->getSnapshot()
            ->getValue() ?? [];

            if (isset($quizzes['people'])) {
                unset($quizzes['people']);
            }

            return response()->json([
                'success' => true,
                'quiz' => $quizzes,
                
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    
}
