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
use Illuminate\Support\Facades\Storage;
use Kreait\Firebase\Messaging\CloudMessage;

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

    private function createNotification(string $gradeLevel, string $subjectId, string $body, string $title, string $announcement_id, string $type){
        $students = $this->database
            ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/people")
            ->getSnapshot()
            ->getValue();

        $users = $this->database->getReference("users")->getSnapshot()->getValue();

        $tokens = [];

        if (is_array($students)) {
            foreach ($students as $student_id => $student) {
                if (!empty($users[$student_id]['fcm_token'])) {
                    $tokens[$student_id] = $users[$student_id]['fcm_token'];
                }
            }
        }

        $notified_students = [];
        foreach ($tokens as $student_id => $token) {
            try {
                $message = CloudMessage::withTarget('token', $token)
                    ->withNotification([
                        'title' => $title,
                        'body' => $body,
                    ])
                    ->withData([
                        'type' => 'notification',
                        'subjectId' => $subjectId,
                        'announcement_id' => $announcement_id
                    ]);

                $this->messaging->send($message);

            } catch (\Exception $e) {
                \Log::error("FCM send failed for {$student_id}: " . $e->getMessage());
            }

            $notified_students[$student_id] = true;
        }

        $notification_id = $this->generateUniqueId("NOTI");
        $date = now()->toDateTimeString();

        $notification_logs = [];
        $notification_logs[$notification_id] = [
            'title' => $title,
            'body' => $body,
            'date' => $date,
            'subject_id' => $subjectId,
            'announcement_id' => $announcement_id,
            'student_ids' => $notified_students,
            'type' => $type
        ];

        $this->database->getReference('notifications')->update($notification_logs);
    }

    public function editSubjectAnnouncementApi(Request $request, string $subjectId, string $announcement_id){
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        $validated = $request->validate([
            'title'       => 'required|string|max:50',
            'description' => 'required|string|max:300',
            'files'       => 'nullable|array',
            'files.*.file'=> 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,odt,rtf|max:5120',
            'image_urls'   => 'nullable|array',
            'image_urls.*' => 'nullable|string|min:1',
            'urls'        => 'nullable|array',
            'urls.*.url'  => 'nullable|string|min:1',
            'date_posted' => 'required|string|min:1'
        ]);

        try {
            $existing = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/announcements/{$announcement_id}")
                ->getSnapshot()
                ->getValue() ?? [];

            if (empty($existing)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Announcement not found.',
                ], 404);
            }

            $existingFiles = [];
            foreach ($existing['files'] as $file) {
                $existingFiles[$file['url']] = $file;
            }

            $files = [];
            if (!empty($validated['image_urls'])){
                foreach($validated['image_urls'] as $url){
                    if(isset($existingFiles[$url])){
                        $files[] = $existingFiles[$url];
                    }
                }
            }

            if (!empty($validated['files'])) {
                foreach ($validated['files'] ?? [] as $fileData) {
                    if (!isset($fileData['file'])) continue;
                    $file = $fileData['file'];
                    $file_id = (string) Str::uuid();
                    $remotePath = "subjects/{$subjectId}/announcements/{$file_id}";

                    $uploadResult = $this->uploadToFirebaseStorage($file, $remotePath);
                    $files[] = $uploadResult;
                }
            }

            $urls = collect($validated['urls'] ?? [])
                ->pluck('url')
                ->filter()
                ->values()
                ->all();

            $date = now()->toDateTimeString();
            $date_posted = Carbon::createFromFormat('m/d/Y H:i:s', str_replace(',', '', $validated['date_posted']));
            $formatted = $date_posted->format('Y-m-d');

            $announcementData = [
                'title'       => $validated['title'],
                'description' => $validated['description'],
                'links'       => $urls,
                'date_posted' => $formatted,
                'files'       => $files,
                'updated_at'  => now()->toDateTimeString(),
                'updated_by'  => $userId
            ];

            $postedDate = Carbon::parse($validated['date_posted'])->startOfDay();
            $today = now()->startOfDay();

            if ($postedDate->lte($today)) {
                $this->createNotification($gradeLevel, $subjectId, $validated['description'], $validated['title'] , $announcement_id, "announcement");
            }

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/announcements/{$announcement_id}")
                ->update($announcementData);

            return response()->json([
                'success' => true,
                'message' => 'Announcement updated successfully.',
                'files' => $files,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Failed to create announcement: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function createSubjectAnnouncementApi(Request $request, string $subjectId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        $validated = $request->validate([
            'title'       => 'required|string|max:50',
            'description' => 'required|string|max:300',
            'files'       => 'nullable|array',
            'files.*.file'=> 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,odt,rtf|max:5120',
            'urls'        => 'nullable|array',
            'urls.*.url'  => 'nullable|string|min:1',
            'date_posted' => 'required|string|min:1'
        ]);

        try {
            $files = [];
            if (!empty($validated['files'])) {
                foreach ($validated['files'] ?? [] as $fileData) {
                    if (!isset($fileData['file'])) continue;
                    $file = $fileData['file'];
                    $file_id = (string) Str::uuid();
                    $remotePath = "subjects/{$subjectId}/announcements/{$file_id}";

                    $uploadResult = $this->uploadToFirebaseStorage($file, $remotePath);
                    $files[] = $uploadResult;
                }
            }

            $urls = collect($validated['urls'] ?? [])
                ->pluck('url')
                ->filter()
                ->values()
                ->all();

            $date = now()->toDateTimeString();
            $date_posted = Carbon::createFromFormat('m/d/Y H:i:s', str_replace(',', '', $validated['date_posted']));
            $formatted = $date_posted->format('Y-m-d');

            $announcementData = [
                'title'       => $validated['title'],
                'description' => $validated['description'],
                'links'       => $urls,
                'date_posted' => $formatted,
                'files'       => $files,
                'create_at'   => $date,
                'created_by'  => $userId
            ];

            $announcement_id = $this->generateUniqueId('SUB-ANN');

            $students = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/people")
                ->getSnapshot()
                ->getValue();

            $users = $this->database->getReference("users")->getSnapshot()->getValue();

            $postedDate = Carbon::parse($validated['date_posted'])->startOfDay();
            $today = now()->startOfDay();

            if ($postedDate->lte($today)) {
                $this->createNotification($gradeLevel, $subjectId, $validated['description'], $validated['title'] , $announcement_id, "announcement");
            }

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/announcements/{$announcement_id}")
                ->set($announcementData);

            return response()->json([
                'success' => true,
                'message' => 'Announcement created successfully.',
            ], 201);

        } catch (\Exception $e) {
            \Log::error("asd " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error'   => 'Failed to create announcement: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteSubjectAnnouncementApi(Request $request, string $subjectId, string $announcementId){
        
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try {
            $ref = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/announcements/{$announcementId}");

            $snapshot = $ref->getSnapshot();
            if (! $snapshot->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Announcement not found.',
                ], 404);
            }
            $ref->remove();

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
            'published_at' => 'required|date',
            'deadline' => 'required|date',
            'availabilityFrom' => 'required|string|min:1',
            'availabilityTo' => 'required|string|min:1',
            'attempts' => 'required|integer|min:1|max:100',
            'title' => 'required|string|max:250',
            'description' => 'required|string|max:1000',
            'total' => 'required|integer|min:1',
            'submission_type' => 'required|string|in:text,file',
            'max_file_size' => 'nullable|integer',
            'visibility' => 'required|boolean',
            'file_types_types' => 'nullable|array',
            'file_types_types.*' => 'nullable|string|in:pdf,docx,pptx,mp3,mp4,jpg,png,txt,zip,xlsx',
        ]);

        $assignmentId = $this->generateUniqueId('ASS');
        $date = now()->toDateTimeString();

        try{
            $peoples = $this->database->getReference("subjects/GR{$gradeLevel}/{$subjectId}/people")->getSnapshot()->getValue();

            $availability = [
                'end' => $validated['availabilityTo'],
                'start' => $validated['availabilityFrom']
            ];

            $assignmentData = [
                'created_at' => $date,
                'people' => $peoples,
                'availability' => $availability,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'attempts' => $validated['attempts'],
                'submission_type' => $validated['submission_type'],
                'max_file_size' => $validated['max_file_size'] ?? null,
                'visibility' => $validated['visibility'],
                'file_types_types' => $validated['file_types_types'] ?? [],
                'published_at' => $validated['published_at'],
                'deadline' => $validated['deadline'],
                'total' => $validated['total'],
            ];

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

    public function getScores(Request $request, string $subjectId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try {
            $activities = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized")
                ->getSnapshot()
                ->getValue() ?? [];

            if (empty($activities)) {
                return response()->json([
                    'success' => false,
                    'message' => 'not found'
                ]);
            }

            $attempts = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attempts")
                ->getSnapshot()
                ->getValue() ?? [];

            $peoples = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/people")
                ->getSnapshot()
                ->getValue() ?? [];

            $activity_list = [];

            foreach ($activities as $activityType => $difficultyGroups) {
                $activity_list[$activityType] = [];

                foreach ($difficultyGroups as $difficulty => $activitySet) {
                    $activityIds = array_keys($activitySet);

                    $studentsAnsweredCount = 0;
                    if (isset($attempts[$activityType])) {
                        foreach ($attempts[$activityType] as $studentId => $studentAttempts) {
                            if (str_starts_with($studentId, 'SPE')) {
                                $studentsAnsweredCount++;
                            }
                        }
                    }

                    $activity_list[$activityType][$difficulty] = [
                        'activity_ids' => $activityIds,
                        'students_answered' => $studentsAnsweredCount,
                        'total_students' => count($peoples)
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'activities' => $activity_list
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function getStudents(Request $request, string $subjectId) {
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try {
            $peoples = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/people")
                ->orderByChild("last_name")
                ->getSnapshot()
                ->getValue() ?? [];

            return response()->json([
                'success' => true,
                'peoples' => $peoples
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getStudentAttempts(Request $request, string $subjectId, string $activityType, string $activityId, string $userId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try {
            $attempts = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attempts/{$activityType}/{$activityId}/{$userId}")
                ->orderByChild("submitted_at")
                ->getSnapshot()
                ->getValue() ?? [];

            $filtered = array_filter($attempts, function ($attempt) {
                return isset($attempt['submitted_at']) && $attempt['submitted_at'] !== null && $attempt['submitted_at'] !== '';
            });

            $attempts = [];

            foreach($filtered as $attempt_id => $filter){
                $attempts[$attempt_id] = [
                    'score'        => $filter['overall_score'] ?? $attempt['score'] ?? null,
                    'submitted_at' => $filter['submitted_at'] ?? null,
                ];
            }

            return response()->json([
                'success' => true,
                'attempts' => $attempts
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getStudentActivity(Request $request, string $subjectId, string $activityType, string $activityId, string $userId, string $attemptId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try {
            $attempt = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attempts/{$activityType}/{$activityId}/{$userId}/{$attemptId}")
                ->getSnapshot()
                ->getValue() ?? [];
            
            $feedbacks = [];
            foreach($attempt['answers'] ?? [] as $itemId => $item){
                $feedbacks[$itemId] = [
                    'feedback' => $item['feedback']['teacher'] ?? 'No feedback provided'
                ];
            }

            return response()->json([
                'success' => true,
                'feedbacks' => $feedbacks ?? [],
                'overall_score' => $attempt['overall_score'] ?? $attempt['score'] ?? 0,
                'attempt' => $attempt
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getAttendance(Request $request, string $subjectId){
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try{
            $attendance = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attendance")
                ->getSnapshot()
                ->getValue();

            return response()->json([
                'success' => true,
                'attendance' => $attendance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getAttendanceById(Request $request, string $subjectId, string $attendance_id){
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try{
            $attendance = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attendance/{$attendance_id}")
                ->getSnapshot()
                ->getValue();

            return response()->json([
                'success' => true,
                'students' => $attendance['people'],
                'date' => $attendance['date'],
                'attendance_id' => $attendance_id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getAttendanceStudents(Request $request, string $subjectId) {
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try {
            $peoples = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/people")
                ->getSnapshot()
                ->getValue() ?? [];

            $students = [];

            foreach ($peoples as $index => $people) {
                if (isset($people['role']) && $people['role'] !== "teacher") {
                    $students[$index] = $people;
                }
            }

            $now = now();
            $currentYear = $now->year;
            $currentMonth = str_pad($now->month, 2, '0', STR_PAD_LEFT);
            $currentDay = str_pad($now->day, 2, '0', STR_PAD_LEFT);

            $attendance_id = $currentYear . $currentMonth . $currentDay . "_" . strtoupper($now->format('D'));

            return response()->json([
                'success' => true,
                'students' => $students,
                'date' => today()->toDateString(),
                'attendance_id' => $attendance_id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function AddAttendance(Request $request, string $subjectId, string $attendance_id)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        $validated = $request->validate([
            'students' => 'required|array|min:1',
            'students.*.student_id' => 'required|string|min:1',
            'students.*.status' => 'required|string|in:present,late,absent',
        ]);

        try {
            $existing = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attendance/{$attendance_id}")
                ->getSnapshot()
                ->exists();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'error' => 'Attendance already exists for this date.',
                ], 409);
            }

            $peoples = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/people")
                ->getSnapshot()
                ->getValue() ?? [];

            $students = [];
            $date = now()->toDateTimeString();

            foreach ($validated['students'] as $student) {
                $studentId = $student['student_id'];
                if (isset($peoples[$studentId])) {
                    $name = $peoples[$studentId]['first_name'] . " " . $peoples[$studentId]['last_name'];

                    $students[$studentId] = [
                        'name' => $name,
                        'status' => $student['status'],
                        'student_id' => $studentId,
                        'timestamp' => $date,
                    ];
                }
            }
            
            $attendance = [
                'date' => today()->toDateString(),
                'date_created' => $date,
                'people' => $students,
            ];

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attendance/{$attendance_id}")
                ->set($attendance);

            return response()->json([
                'success' => true,
                'message' => 'Attendance successfully recorded.',
                'attendance_id' => $attendance_id,
                'peoples' => $attendance,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateAttendance(Request $request, string $subjectId, string $attendance_id){
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        $validated = $request->validate([
            'students' => 'required|array|min:1',
            'students.*.student_id' => 'required|string|min:1',
            'students.*.status' => 'required|string|in:present,late,absent',
        ]);

        try{
            $attendance = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attendance/{$attendance_id}")
                ->getSnapshot()
                ->getValue();

            if (!$attendance) {
                return response()->json([
                    'success' => false,
                    'error' => 'Attendance record not found.',
                ], 404);
            }

            $updatedStudents = [];
            foreach ($validated['students'] as $student) {
                $studentId = $student['student_id'];

                if (isset($attendance[$studentId])) {
                    $attendance[$studentId]['status'] = $student['status'];
                    $attendance[$studentId]['timestamp'] = now()->toDateTimeString();

                    $updatedStudents[$studentId] = $attendance[$studentId];
                }
            }

            $date = now()->toDateTimeString();

            $newAttendance = [
                'date_created' => $attendance['date_created'],
                'date' => $attendance['date'],
                'date_updated' => $date
            ];

            $attendance = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attendance/{$attendance_id}")
                ->set($newAttendance);

            return response()->json([
                'success' => true,
                'message' => 'Attendance successfully updated.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
