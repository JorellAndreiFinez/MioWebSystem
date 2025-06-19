<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Kreait\Firebase\Messaging\CloudMessage;

class QuizzesController extends Controller
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

        $this->messaging = (new Factory())
            ->withServiceAccount($path)
            ->createMessaging();
    }

    protected function uploadToFirebaseStorage($file, $storagePath)
    {
        $bucket = $this->storage->getBucket();
        $fileName = $file->getClientOriginalName();
        $firebasePath = "{$storagePath}" . '_' . $fileName;

        $bucket->upload(
            fopen($file->getRealPath(), 'r'),
            ['name' => $firebasePath]
        );

        $object = $bucket->object($firebasePath);
        $object->update([], ['predefinedAcl' => 'publicRead']);

        return [
            'name' => $fileName,
            'path' => $firebasePath,
            'url'  => "https://storage.googleapis.com/{$bucket->name()}/" . $firebasePath,
        ];
    }

    private function generateUniqueId(string $prefix): string{
        $now = now();
        $currentYear = $now->year;
        $currentMonth = str_pad($now->month, 2, '0', STR_PAD_LEFT);
        $currentDay = str_pad($now->day, 2, '0', STR_PAD_LEFT);
        $randomDigits = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        $announcementId = "{$prefix}{$currentYear}{$currentMonth}{$currentDay}{$randomDigits}";

        return $announcementId;
    }

    public function createQuiz(Request $request, string $subjectId){
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        $validated = $request->validate([
            'title' => 'required|string|min:1|max:80',
            'description' => 'required|string|min:1|max:300',
            'attempts' => 'required|numeric|min:1',
            'deadline_date' => 'nullable|string|min:1',
            'end_time' => 'nullable|string|min:1',
            'start_time' => 'required|string|min:1',
            'time_limit' => 'required|string|min:1',
            // 'show_correct_answers' // default false
            'access_code' => 'nullable|string|min:1',

            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string|min:1',
            'questions.*.answer' => 'required|string|min:1',
            'questions.*.options' => 'nullable|array',
            'questions.*.options.*' => 'required|string|min:1',
            'questions.*.points' => 'required|numeric|min:1',
            'questions.*.questionType' => 'required|string|in:multiple_choice,essay,file_upload,fill,dropdown',
            'questions.*.multiple_type' => 'nullable|string|in:radio,checkbox',
        ]);

        try{
            $students = $this->database->getReference("subjects/GR{$gradeLevel}/{$subjectId}/people")->getSnapshot()->getValue() ?? [];

            $people_data = [];
            foreach ($students as $studentId => $student) {
                if (isset($student['role']) && $student['role'] !== 'teacher') {
                    $firstName = $student['first_name'] ?? '';
                    $lastName = $student['last_name'] ?? '';
                    $name = trim("$firstName $lastName");

                    $people_data[$studentId] = [
                        'comments' => '',
                        'name' => $name,
                        'status' => 'not_started',
                        'total_student_attempts' => 0,
                    ];
                }
            }

            $questions = [];
            $totalPoints = 0;
            foreach ($validated['questions'] ?? [] as $question) {
                $questionId = (string) Str::uuid();
                $questionData = [
                    'question' => $question['question'],
                    'answer' => $question['answer'],
                    'points' => (float) $question['points'],
                    'type' => $question['questionType'],
                    'multiple_type' => $question['multiple_type'] ?? null
                ];

                if (isset($question['options'])) {
                    $optionData = [];
                    foreach ($question['options'] as $option) {
                        $optionId = substr(md5(uniqid()), 0, 7);
                        $optionData[$optionId] = $option;
                    }
                    $questionData['options'] = $optionData;
                }

                $questions[$questionId] = $questionData;
                $totalPoints += $questionData['points'];
            }

            $quizId = $this->generateUniqueId("QU");

            $date = now()->toDateTimeString();
            $quizData = [
                'quiz_id' => $quizId,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'attempts' => (int) $validated['attempts'],
                'deadline_date' => $validated['deadline_date'] ?? '',
                'end_time' => $validated['end_time'] ?? '',
                'start_time' => $validated['start_time'],
                'time_limit' => (int) $validated['time_limit'],
                'access_code' => $validated['access_code'] ?? '',
                'created_at' => $date,
                'publish_date' => $date,
                'no_time_limit' => false,
                'show_correct_answers' => false,
                'people' => $people_data,
                'questions' => $questions,
                'total_points' => $totalPoints
            ];

            // check for the start and end time
            // check the deadline date

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/quizzes/{$quizId}")
                ->set($quizData);

            return response()->json([
                'success' => true,
                'quiz_id' => $quizId,
                'quiz_data' => $quizData,
                'message' => 'Quiz successfully created.',
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getQuizzes(Request $request, string $subjectId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try {
            $quizzes = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/quizzes")
                ->getSnapshot()
                ->getValue() ?? [];

            $filteredQuizzes = [];

            foreach ($quizzes as $quizId => $quizData) {
                $filteredQuizzes[] = [
                    'quiz_id' => $quizId,
                    'title' => $quizData['title'] ?? '',
                    'total_points' => $quizData['total_points'] ?? 0,
                    'deadline_date' => $quizData['deadline_date'] ?? null,
                ];
            }

            usort($filteredQuizzes, function ($a, $b) {
                $aDate = strtotime($a['deadline_date'] ?? '9999-12-31');
                $bDate = strtotime($b['deadline_date'] ?? '9999-12-31');
                return $aDate <=> $bDate;
            });

            return response()->json([
                'success' => true,
                'quizzes' => $filteredQuizzes,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getAttempts(Request $request, string $subjectId, string $quizId){
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id'); 

        try {
            $attempts = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/quizzes/{$quizId}/people/{$userId}/attempts")
                ->getSnapshot()
                ->getValue() ?? [];

            $quiz = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/quizzes/{$quizId}")
                ->getSnapshot()
                ->getValue() ?? [];

            $scores = [];
            $quizInfo = [
                'title' => $quiz['title'] ?? null,
                'description' => $quiz['description'] ?? null,
                'deadline' => $quiz['deadline_date'] ?? null,
                'time_limit' => $quiz['time_limit'] ?? null,
                'total_points' => $quiz['total_points'] ?? null,
                'end_time' => $quiz['end_time'] ?? null,
                'start_time' => $quiz['start_time'] ?? null,
                'attempts' => $quiz['attempts'] ?? null,
            ];

            $active_attempt = null; 
            foreach ($attempts as $attemptId => $attempt) {
                if (isset($attempt['status']) && $attempt['status'] === "in_progress" && empty($attempt['submitted_at'])) {
                    $active_attempt = $attemptId;
                }

                $scores[] = [
                    'attempt_id' => $attemptId,
                    'submitted_at' => $attempt['submitted_at'] ?? null,
                    'score' => $attempt['score'] ?? null,
                    'status' => $attempt['status'] ?? null,
                ];
            }

            usort($scores, function ($a, $b) {
                return strtotime($b['submitted_at'] ?? '1970-01-01') <=> strtotime($a['submitted_at'] ?? '1970-01-01');
            });

            return response()->json([
                'success' => true,
                'quiz_info' => $quizInfo,
                'scores' => $scores,
                'active_attempt' => $active_attempt
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function startQuiz(Request $request, string $subjectId, string $quizId){
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        try{
            $quiz_data = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/quizzes/{$quizId}")
                ->getSnapshot()
                ->getValue() ?? [];

            if(empty($quiz_data)){
                return response()->json([
                    'success' => false,
                    'message' => "Quiz not found"
                ]);
            }

            if (!isset($quiz_data['people'][$userId])) {
                return response()->json([
                    'success' => false,
                    'message' => "You are not allowed to access this quiz.",
                ], 403);
            }

            // if (
            //     isset($quiz_data['people'][$userId]['total_student_attempts']) &&
            //     $quiz_data['people'][$userId]['total_student_attempts'] >= $quiz_data['attempts']
            // ) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => "You have reached the maximum number of attempts.",
            //     ], 403);
            // }

            $attemptId = $this->generateUniqueId("ATTM");
            $date = now()->toDateTimeString();

            $items = [];
            foreach ($quiz_data['questions'] ?? [] as $questionId => $questionData) {
                $items[$questionId] = [
                    'answer' => '',
                    'answered_at' => '',
                    'type' => $questionData['type'],
                    'question' => $questionData['question'],
                    'choices' => $questionData['options'] ?? [],
                    'points' => $questionData['points'],
                    'multiple_type' => $questionData['multiple_type'] ?? null,
                ];
            }

            $previousAttempts = $quiz_data['people'][$userId]['attempts'] ?? [];

            $previousAttempts[$attemptId] = [
                'started_at' => $date,
                'items' => $items,
                'status' => 'in_progress',
            ];

            $quizLog = [
                'total_student_attempts' => ($quiz_data['people'][$userId]['total_student_attempts'] ?? 0) + 1,
                'attempts' => $previousAttempts,
            ];

            $this->database->getReference("subjects/GR{$gradeLevel}/{$subjectId}/quizzes/{$quizId}/people/{$userId}")
                ->update($quizLog);

            return response()->json([
                'success' => true,
                'message' => "Quiz data loaded successfully.",
                'items' => $items,
                'attemptId' => $attemptId
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function continueQuiz(Request $request, string $subjectId, string $quizId, string $attemptId){
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        try{
            $attempt = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/quizzes/{$quizId}/people/{$userId}/attempts/{$attemptId}")
                ->getSnapshot()
                ->getValue() ?? [];

            if(empty($attempt)){
                return response()->json([
                    'success' => false,
                    'message' => "Attempt not found"
                ]);
            }

            $answers = [];
            foreach($attempt['items'] as $item_id => $item){
                if(isset($item['answer']) && !empty($item['answer'])){
                    $answers[] = [
                        'itemId' => $item_id,
                        'answer' => $item['answer']
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'items' => $attempt['items'],
                'attemptId' => $attemptId,
                'answers' => $answers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function submitAnswer(Request $request, string $subjectId, string $quizId, string $attemptId, string $itemId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        $validated = $request->validate([
            'answer_text' => 'nullable|string',
            'answer_array' => 'nullable|array',
            'answer_array.*' => 'required|string',
            'answer_file' => 'nullable|file|mimes:pdf,png,jpg,jpeg|max:5120',
        ]);

        if (empty($validated['answer_text']) && !$request->hasFile('answer_file') && empty($validated['answer_array'])) {
            return response()->json([
                'success' => false,
                'message' => 'Either answer_text, answer_array, or answer_file must be provided.',
            ], 422);
        }

        try {
            $date = now()->toDateTimeString();
            $answerValue = null;

            if (!empty($validated['answer_array'])) {
                $answerValue = $validated['answer_array'];
            } elseif (!empty($validated['answer_text'])) {
                $answerValue = $validated['answer_text'];
            } elseif (isset($validated['answer_file']) && $validated['answer_file']->isValid()) {
                $file = $validated['answer_file'];
                $file_id = (string) Str::uuid();
                $firebasePath = "quiz_answers/{$userId}/{$file_id}";
                $uploadedFileInfo = $this->uploadToFirebaseStorage($file, $firebasePath);

                $answerValue = [
                    'name' => $uploadedFileInfo['name'],
                    'path' => $uploadedFileInfo['path'],
                    'url'  => $uploadedFileInfo['url'],
                ];
            }

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/quizzes/{$quizId}/people/{$userId}/attempts/{$attemptId}/items/{$itemId}")
                ->update([
                    'answer' => $answerValue,
                    'answered_at' => $date,
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Answer submitted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function finalizeQuiz(Request $request, string $subjectId, string $quizId, string $attemptId){
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        try {
            $basePath = "subjects/GR{$gradeLevel}/{$subjectId}/quizzes/{$quizId}/people/{$userId}/attempts/{$attemptId}";

            $attemptSnapshot = $this->database
                ->getReference($basePath)
                ->getSnapshot();

            if (!$attemptSnapshot->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attempt not found.',
                ], 404);
            }

            $answers = $attemptSnapshot->getValue()['items'] ?? [];

            $questionsPath = "subjects/GR{$gradeLevel}/{$subjectId}/quizzes/{$quizId}/questions";
            $questions = $this->database
                ->getReference($questionsPath)
                ->getSnapshot()
                ->getValue();

            $score = 0;
            $totalPoints = 0;
            $totalScoredPoints = 0;
            $pending = [];

            foreach ($questions as $questionId => $questionData) {
                $type = $questionData['type'] ?? 'text';
                $points = (int) ($questionData['points'] ?? 1);
                $correctAnswer = strtolower(trim($questionData['answer'] ?? ''));
                $studentAnswer = strtolower(trim($answers[$questionId]['answer'] ?? ''));

                $totalPoints += $points;

                if (in_array($type, ['essay', 'file_upload'])) {
                    $pending[] = $questionId;
                    continue;
                }

                if ($studentAnswer === $correctAnswer) {
                    $score += $points;
                }

                $totalScoredPoints += $points;
            }

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/quizzes/{$quizId}/people/{$userId}/attempts/$attemptId")
                ->update([
                    'status' => 'finished',
                    'finalized_at' => now()->toDateTimeString(),
                    'score' => $score,
                    'total_quiz_points' => $totalPoints,
                    'total_questions' => count($questions),
                    'pending_manual_review' => $pending,
                    'status' => count($pending) ? "pending" : "completed"
                ]);

            if (!empty($pending)) {
                return response()->json([
                    'success' => true,
                    'message' => "Quiz submitted. Please wait for your teacher to review.",
                    'score' => 0,
                    'total_quiz_points' => $totalPoints,
                    'pending' => $pending
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Quiz finalized successfully.",
                'score' => $score,
                'total_possible_score' => $totalScoredPoints,
                'total_quiz_points' => $totalPoints,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

}
