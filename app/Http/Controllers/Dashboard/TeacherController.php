<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Database;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageObject;
use Barryvdh\DomPDF\Facade\Pdf;


class TeacherController extends Controller
{

    protected $database;
    protected $storageClient;
    protected $bucketName;



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

        // Create Google Cloud Storage client
        $this->storageClient = new StorageClient([
            'keyFilePath' => $path,
        ]);

        // Your Firebase Storage bucket name
        $this->bucketName = 'miolms.firebasestorage.app';
    }

    public function showScores($subjectId)
    {
        // 1. Get active school year
        $schoolYearsRef = $this->database->getReference('schoolyears');
        $schoolYears = $schoolYearsRef->getValue() ?? [];
        $activeSchoolYear = null;

        foreach ($schoolYears as $schoolYear) {
            if ($schoolYear['status'] === 'active') {
                $activeSchoolYear = $schoolYear['schoolyearid'];
                break;
            }
        }

        if (!$activeSchoolYear) {
            return redirect()->route('mio.teacher-panel')->with('error', 'No active school year found.');
        }

        // 2. Get subject and grade level
        $subjectsRef = $this->database->getReference('subjects');
        $subjectsData = $subjectsRef->getValue() ?? [];

        $subject = null;
        $gradeLevelFound = null;

        foreach ($subjectsData as $gradeLevel => $subjectList) {
            foreach ($subjectList as $subj) {
                if ($subj['subject_id'] === $subjectId) {
                    $subject = $subj;
                    $gradeLevelFound = $gradeLevel;
                    break 2;
                }
            }
        }

        if (!$subject || !$gradeLevelFound) {
            return redirect()->route('mio.teacher-panel')->with('error', 'Subject not found.');
        }

        // 3. Get student names
        $peopleRef = $this->database->getReference("subjects/{$gradeLevelFound}/{$subjectId}/people");
        $people = $peopleRef->getValue() ?? [];

        // 4. Define activity types
        $activityTypes = [
            'pronunciation', 'picture', 'question', 'phrase',
            'bingo', 'matching', 'fill', 'talk2me', 'homonyms'
        ];

        $groupedAttempts = [];

        foreach ($activityTypes as $activityType) {
            $activityRef = $this->database->getReference("subjects/{$gradeLevelFound}/{$subjectId}/attempts/{$activityType}");
            $students = $activityRef->getValue() ?? [];

            foreach ($students as $activitySetId => $studentSet) {
                if (!is_array($studentSet)) continue;

                foreach ($studentSet as $studentId => $studentAttempts) {
                    if (!is_array($studentAttempts)) continue;

                    // Sort attempts by submitted_at descending
                    uasort($studentAttempts, function ($a, $b) {
                        return strtotime($b['submitted_at'] ?? '') <=> strtotime($a['submitted_at'] ?? '');
                    });

                    // Get the most recent attempt
                    $recentAttempt = reset($studentAttempts);
                    $attemptId = key($studentAttempts);
                    if (!isset($recentAttempt['answers'])) continue;

                    foreach ($recentAttempt['answers'] as $answerId => $answerData) {
                        $attempt = [
                            'student_id' => $studentId,
                            'attempt_id' => $attemptId,
                            'answer_id' => $answerId,
                            'answered_at' => $answerData['answered_at'] ?? null,
                            'started_at' => $recentAttempt['started_at'] ?? null,
                            'submitted_at' => $recentAttempt['submitted_at'] ?? null,
                            'audio_path' => $answerData['audio_path'] ?? null,
                            'card_no' => $answerData['card_no'] ?? null,
                            'student_first_name' => $people[$studentId]['first_name'] ?? '',
                            'student_last_name' => $people[$studentId]['last_name'] ?? '',
                        ];

                        // Generate signed audio URL
                        if (!empty($attempt['audio_path'])) {
                            $signedUrl = $this->getAudioDownloadUrl($attempt['audio_path']);
                            $attempt['audio_url'] = $signedUrl ?? null;
                            Log::info("Signed URL: {$signedUrl}");
                        } else {
                            $attempt['audio_url'] = null;
                        }

                        // Pronunciation specific logic
                        if (($subject['specialized_type'] ?? null) === 'speech' && isset($answerData['pronunciation_details'])) {
                            $attempt['pronunciation_details'] = $answerData['pronunciation_details'];
                            $speechaceScore = $answerData['pronunciation_details']['speechace_pronunciation_score'] ?? null;

                            if ($speechaceScore !== null) {
                                $feedback = $this->getPronunciationFeedback((int)$speechaceScore);
                                $attempt['pronunciation_details']['ielts_pronunciation_score'] = $feedback['ielts'];
                                $attempt['pronunciation_details']['cefr_pronunciation_score'] = $feedback['cefr'];
                                $attempt['pronunciation_details']['pte_pronunciation_score'] = $feedback['pte'];
                                $attempt['pronunciation_details']['feedback'] = $feedback['feedback'];
                            }

                            // Calculate MIÓ Score
                            $words = $answerData['pronunciation_details']['words'] ?? [];
                            $totalScore = 0;
                            $wordCount = 0;

                            foreach ($words as $word) {
                                if (isset($word['quality_score'])) {
                                    $totalScore += $word['quality_score'];
                                    $wordCount++;
                                }
                            }

                            $attempt['mio_score'] = $wordCount > 0 ? round($totalScore / $wordCount, 2) : null;
                        }

                        $groupedAttempts[$activityType][] = $attempt;
                    }
                }
            }
        }

        $subjectTypeRef = $this->database->getReference("subjects/{$gradeLevelFound}/{$subjectId}/subjectType");
        $subjectType = $subjectTypeRef->getValue() ?? [];

        $specializedTypeRef = $this->database->getReference("subjects/{$gradeLevelFound}/{$subjectId}/specialized_type");
        $specializedType = $specializedTypeRef->getValue() ?? [];

        if($subjectType === 'specialized') {
            if($specializedType === 'speech') {
                return view('mio.head.teacher-panel', [
                'page' => 'scores',
                'subject' => $subject,
                'groupedAttempts' => $groupedAttempts,
            ]);
            }
        } else {
            return view('mio.head.teacher-panel', [
                'page' => 'scores-academics',
                'subject' => $subject,
                'groupedAttempts' => $groupedAttempts,
            ]);
        }
    }





    protected function getAudioDownloadUrl($objectPath)
        {
            try {
                $bucket = $this->storageClient->bucket($this->bucketName);
                $object = $bucket->object($objectPath);

                // Signed URL valid for 1 hour (3600 seconds)
                $url = $object->signedUrl(
                    new \DateTime('1 hour'),
                    [
                        'version' => 'v4',
                    ]
                );

                return $url;

            } catch (\Exception $e) {
                // Log error or handle exception
                Log::error("Failed to generate signed URL for: {$objectPath}, error: " . $e->getMessage());
                return null;
            }
    }

    public static function getPronunciationFeedback($score)
    {
        // Speechace -> IELTS, CEFR, PTE based on the table
        $bands = [
            ['range' => [97, 100], 'ielts' => '9.0', 'cefr' => 'C2', 'pte' => '90'],
            ['range' => [92, 96], 'ielts' => '8.5', 'cefr' => 'C2', 'pte' => '90'],
            ['range' => [86, 91], 'ielts' => '8.0', 'cefr' => 'C1+', 'pte' => '85'],
            ['range' => [81, 85], 'ielts' => '7.5', 'cefr' => 'C1', 'pte' => '76'],
            ['range' => [75, 80], 'ielts' => '7.0', 'cefr' => 'B2', 'pte' => '68'],
            ['range' => [69, 74], 'ielts' => '6.5', 'cefr' => 'B1+', 'pte' => '59'],
            ['range' => [64, 68], 'ielts' => '6.0', 'cefr' => 'B1', 'pte' => '51'],
            ['range' => [58, 63], 'ielts' => '5.5', 'cefr' => 'A2+', 'pte' => '42'],
            ['range' => [53, 57], 'ielts' => '5.0', 'cefr' => 'A2', 'pte' => '34'],
            ['range' => [47, 52], 'ielts' => '4.5', 'cefr' => 'A1+', 'pte' => '25'],
            ['range' => [42, 46], 'ielts' => '4.0', 'cefr' => 'A1', 'pte' => '20'],
            ['range' => [0, 41], 'ielts' => '0-3.5', 'cefr' => 'A0', 'pte' => '10'],
        ];

        foreach ($bands as $band) {
            [$min, $max] = $band['range'];
            if ($score >= $min && $score <= $max) {
                return [
                    'ielts' => $band['ielts'],
                    'cefr' => $band['cefr'],
                    'pte' => $band['pte'],
                    'feedback' => self::getFeedbackComment($band['cefr'])
                ];
            }
        }

        return [
            'ielts' => '-',
            'cefr' => '-',
            'pte' => '-',
            'feedback' => 'Score not available.'
        ];
    }

    protected static function getFeedbackComment($cefr)
    {
        $feedback = [
            'C2' => 'Excellent pronunciation. Near-native fluency.',
            'C1+' => 'Very good. You sound clear and confident.',
            'C1' => 'Good job! Slight improvements needed.',
            'B2' => 'Fairly good, but work on consistency.',
            'B1+' => 'Understandable, some pronunciation errors.',
            'B1' => 'Needs improvement in intonation and clarity.',
            'A2+' => 'Basic level. Keep practicing common words.',
            'A2' => 'Work on articulation and rhythm.',
            'A1+' => 'Beginner level. Start with simple phrases.',
            'A1' => 'Struggling with pronunciation. Practice daily.',
            'A0' => 'Needs foundational work in speech sounds.',
        ];

        return $feedback[$cefr] ?? 'No feedback available.';
    }




   public function showDashboard()
    {
        // Fetch the current logged-in user's section_id
       $loggedInTeacherId = session('firebase_user')['uid'] ?? null;

        // Fetch the active school year from Firebase
        $activeSchoolYearRef = $this->database->getReference('schoolyears');
        $schoolYears = $activeSchoolYearRef->getValue() ?? [];
        $activeSchoolYear = null;

        foreach ($schoolYears as $schoolYear) {
            if ($schoolYear['status'] === 'active') {
                $activeSchoolYear = $schoolYear['schoolyearid'];
                break;
            }
        }

        // Fetch grade levels from Firebase
        $gradeLevelsRef = $this->database->getReference('gradelevel');
        $gradeLevels = $gradeLevelsRef->getValue() ?? [];

        // Initialize an array to hold the subjects for each grade level
        $allSubjects = [];

        foreach ($gradeLevels as $gradeLevelKey => $gradeLevel) {
            // Fetch subjects for each grade level under the active school year
            $subjectsRef = $this->database->getReference('subjects/' . $gradeLevelKey);
            $subjects = $subjectsRef->getValue() ?? [];

            // Only add subjects for the active school year AND the user's section
            $gradeSubjects = array_filter($subjects, function($subject) use ($activeSchoolYear, $loggedInTeacherId) {
            return isset($subject['schoolyear_id'], $subject['teacher_id']) &&
                $subject['schoolyear_id'] === $activeSchoolYear &&
                $subject['teacher_id'] === $loggedInTeacherId;
        });


            $allSubjects[$gradeLevelKey] = $gradeSubjects;
        }


        // Fetch sections under the active school year
        $sectionsRef = $this->database->getReference('sections');
        $sections = $sectionsRef->getValue();

        $activeSections = [];
        foreach ($sections as $sectionId => $section) {
            if ($section['schoolyear_id'] === $activeSchoolYear && $section['status'] === 'active') {
                $activeSections[] = $section;
            }
        }

        // Filter active sections based on the logged-in user's teacher_id
       $filteredSections = array_filter($activeSections, function($section) use ($loggedInTeacherId) {
        return isset($section['teacher_id']) && $section['teacher_id'] === $loggedInTeacherId;
    });


        // Fetch users (students and teachers) for the active sections
        $usersRef = $this->database->getReference('users');
        $users = $usersRef->getValue();

        // Organize users by section
        $usersRef = $this->database->getReference('users');
        $users = $usersRef->getValue() ?? [];

        // Organize teachers by section
        $sectionTeachers = [];

        foreach ($activeSections as $section) {
            $sectionId = $section['sectionid'] ?? null;
            if (!$sectionId) continue;

            $sectionTeachers[$sectionId] = [];

            // Look into each section's modules for teacher assignments
            if (!empty($section['modules'])) {
                foreach ($section['modules'] as $module) {
                    if (!empty($module['people'])) {
                        foreach ($module['people'] as $person) {
                            if (($person['role'] ?? '') === 'teacher') {
                                $teacherId = $person['teacher_id'] ?? null;
                                if ($teacherId && isset($users[$teacherId])) {
                                    $sectionTeachers[$sectionId][] = $users[$teacherId];
                                }
                            }
                        }
                    }
                }
            }
        }


        // Fetch modules for the logged-in user's teacher id and ensure they are assigned correctly
        $modulesForTeacher = [];
        foreach ($allSubjects as $gradeLevelKey => $subjects) {
        foreach ($subjects as $subject) {
            // Ensure the student’s section matches the subject's section
            if (
            isset($subject['teacher_id'], $subject['schoolyear_id']) &&
            $subject['teacher_id'] === $loggedInTeacherId &&
            $subject['schoolyear_id'] === $activeSchoolYear
            ) {
                $modulesForTeacher[] = $subject;
            }
        }
    }

        $adminAnnouncementsRef = $this->database->getReference('admin-announcements');
        $adminAnnouncements = $adminAnnouncementsRef->getValue() ?? [];

        $subjectAnnouncements = [];

        foreach ($allSubjects as $gradeLevelKey => $subjects) {
            foreach ($subjects as $subject) {
                if ($subject['teacher_id'] === $loggedInTeacherId && $subject['schoolyear_id'] === $activeSchoolYear) {
                    $subjectId = $subject['subject_id'];
                    $announcementRef = $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectId}/announcements");
                    $announcements = $announcementRef->getValue() ?? [];

                    foreach ($announcements as $announcementId => $announcement) {
                        $announcement['subject'] = $subject['title'] ?? 'Subject';
                        $announcement['date'] = $announcement['date_posted'] ?? 'Unknown Date';
                        $announcement['subject_id'] = $subject['subject_id'];
                        $announcement['grade_level_key'] = $gradeLevelKey;
                        $announcement['type'] = 'subject';
                        $announcement['id'] = $announcementId;
                        $subjectAnnouncements[] = $announcement;
                    }
                }
            }
        }

        $allAnnouncements = [];

        // Tag and merge admin announcements
        foreach ($adminAnnouncements as $announcementId => $announcement) {
        $announcement['subject'] = 'General';
        $announcement['date'] = $announcement['date'] ?? 'Unknown Date';
        $announcement['type'] = 'general';
        $announcement['id'] = $announcementId;
        $allAnnouncements[] = $announcement;
        }


        // Merge subject-specific announcements
        $allAnnouncements = array_merge($allAnnouncements, $subjectAnnouncements);

        // Sort by date (latest first)
        usort($allAnnouncements, function ($a, $b) {
            return strtotime($b['date']) <=> strtotime($a['date']);
        });

        // Pass filtered data to the view
        return view('mio.head.teacher-panel', [
            'page' => 'teacher-dashboard',
            'subjects' => $modulesForTeacher, // Display only the modules related to the user's section
            'allSubjects' => $allSubjects,
            'activeSchoolYear' => $activeSchoolYear,
            'activeSections' => $filteredSections, // Display only the filtered sections for the logged-in user
            'announcements' => $allAnnouncements
        ]);
    }


    public function showSubject($subjectId)
    {
        // Fetch the active school year from Firebase
        $activeSchoolYearRef = $this->database->getReference('schoolyears');
        $schoolYears = $activeSchoolYearRef->getValue() ?? [];
        $activeSchoolYear = null;

        foreach ($schoolYears as $schoolYear) {
            if ($schoolYear['status'] === 'active') {
                $activeSchoolYear = $schoolYear['schoolyearid'];
                break;
            }
        }

        // Fetch all subjects
        $subjectsRef = $this->database->getReference('subjects');
        $allSubjects = $subjectsRef->getValue() ?? [];

        // Find the subject by subject_id
        $subject = null;
        foreach ($allSubjects as $gradeLevel => $subjects) {
            foreach ($subjects as $subjectItem) {
                if ($subjectItem['subject_id'] === $subjectId) {
                    $subject = $subjectItem;
                    break 2;
                }
            }
        }

        if (!$subject) {
            return redirect()->route('mio.teacher-panel')->with('error', 'Subject not found.');
        }

        // Fetch the modules for the subject
        $modulesRef = $this->database->getReference('modules');
        $modules = $modulesRef->getValue() ?? [];
        $subjectModules = [];

        foreach ($modules as $module) {
            if ($module['subject_id'] === $subjectId) {
                $subjectModules[] = $module;
            }
        }

        // Pass subject and module data to the view
        return view('mio.head.teacher-panel', [
            'page' => 'teacher-subject',
            'subject' => $subject,
            'modules' => $subjectModules
        ]);
    }

    // TEACHER SCORES



    // TEACHER ATTENDANCE
   public function showAttendance(Request $request, $subjectId)
    {
        $attendanceDate = $request->input('attendance_date', now()->format('Y-m-d'));

        $subjectsRef = $this->database->getReference('subjects');
        $allSubjects = $subjectsRef->getValue() ?? [];

        $subjectData = null;
        $gradeLevelKey = null;

        foreach ($allSubjects as $grade => $subjects) {
            if (isset($subjects[$subjectId])) {
                $subjectData = $subjects[$subjectId];
                $gradeLevelKey = $grade;
                break;
            }
        }

        if (!$subjectData) {
            return response()->json(['error' => 'Subject not found'], 404);
        }

        // Find attendance ID for the given date if exists
        $attendanceId = null;
        $attendanceData = [];

        if (!empty($subjectData['attendance'])) {
            foreach ($subjectData['attendance'] as $id => $record) {
                if (($record['date'] ?? '') === $attendanceDate) {
                    $attendanceId = $id;
                    $attendanceData = $record;
                    break;
                }
            }
        }

        // Filter out teachers from the people list
        $students = [];
        if (!empty($subjectData['people'])) {
            foreach ($subjectData['people'] as $personId => $person) {
                if (
                    (isset($person['role']) && strtolower($person['role']) === 'student') ||
                    (!isset($person['role']) && !isset($person['teacher_id'])) // maybe treat as student if no role and no teacher_id
                ) {
                    $students[$personId] = $person;
                }
            }
        }




        foreach ($subjectData['people'] as $personId => $person) {
            Log::info("Person role: " . ($person['role'] ?? 'no role'));
        }


        return view('mio.head.teacher-panel', [
            'page' => 'attendance',
            'subjectId' => $subjectId,
            'attendanceId' => $attendanceId,
            'attendance' => $attendanceData,
            'subject' => $subjectData,
            'people' => $students,
            'attendanceDate' => $attendanceDate,
            'gradeLevelKey' => $gradeLevelKey,
        ]);
    }

    public function updateAttendance(Request $request, $subjectId)
{
    $attendanceDate = $request->input('attendance_date');
    $peopleInput = $request->input('people', []);

    $subjectsRef = $this->database->getReference('subjects');
    $allSubjects = $subjectsRef->getValue() ?? [];

    $gradeLevelKey = null;
    $subjectData = null;

    // Find grade level and subject data
    foreach ($allSubjects as $grade => $subjects) {
        if (isset($subjects[$subjectId])) {
            $gradeLevelKey = $grade;
            $subjectData = $subjects[$subjectId];
            break;
        }
    }

    if (!$subjectData) {
        return abort(404, 'Subject not found.');
    }

    // Format attendance ID, e.g., ATT20250520_TUE
    $date = \Carbon\Carbon::parse($attendanceDate);
    $attendanceId = 'ATT' . $date->format('Ymd') . '_' . strtoupper($date->format('D'));

    // Build student names lookup from subject people
    $studentNames = [];
    if (!empty($subjectData['people'])) {
        foreach ($subjectData['people'] as $personId => $person) {
            // Check if this person is a student by role
            if (isset($person['role']) && $person['role'] === 'student') {
                $first = trim($person['first_name'] ?? '');
                $last = trim($person['last_name'] ?? '');
                $fullName = trim($first . ' ' . $last);
                if ($fullName === '') {
                    $fullName = '(No Name)';
                }
                $studentNames[$personId] = $fullName;
            }
        }

    }

    // Prepare people attendance array with status, timestamp, and name
    $attendancePeople = [];
        foreach ($peopleInput as $personId => $person) {
            if (isset($studentNames[$personId])) {
                $attendancePeople[$personId] = [
                    'status' => $person['status'] ?? 'absent',
                    'timestamp' => now()->format('Y-m-d H:i:s'),
                    'name' => $studentNames[$personId],
                    'student_id' => $personId,
                ];
            }
        }





    // Get existing attendance if any
    $existingAttendance = $subjectData['attendance'][$attendanceId] ?? null;

    $attendanceRef = $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectId}/attendance/{$attendanceId}");

    if ($existingAttendance) {
        // Update existing attendance record
        $attendanceRef->update([
            'date' => $attendanceDate,
            'people' => $attendancePeople,
            'date_updated' => now()->format('Y-m-d H:i:s'),
        ]);
    } else {
        // Create new attendance record
        $attendanceRef->set([
            'date' => $attendanceDate,
            'people' => $attendancePeople,
            'date_created' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    return redirect()->route('mio.subject-teacher.attendance', [
        'subjectId' => $subjectId,
        'attendance_date' => $attendanceDate,
    ])->with('success', 'Attendance saved.');
}


    public function storeAttendance(Request $request, $subjectId)
    {

        return $this->updateAttendance($request, $subjectId);
    }


    // TEACHER QUIZZES
    public function showQuizzes($subjectId)
    {
        // Find grade level key
        $subjectsRef = $this->database->getReference('subjects');
        $allSubjects = $subjectsRef->getValue() ?? [];
        $gradeLevelKey = null;
        $matchedSubject = null;

        foreach ($allSubjects as $key => $subjects) {
            foreach ($subjects as $subject) {
                if ($subject['subject_id'] === $subjectId) {
                    $gradeLevelKey = $key;
                    $matchedSubject = $subject;
                    break 2;
                }
            }
        }

        if (!$gradeLevelKey || !$matchedSubject) {
            return abort(404, 'Subject not found.');
        }

        // Fetch quizzes
        $quizzesRef = $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectId}/quizzes");
        $rawQuizzes = $quizzesRef->getValue() ?? [];

        $quizzes = [];
        foreach ($rawQuizzes as $key => $quiz) {
            $quiz['id'] = $key;
            $quizzes[] = $quiz;
        }

        return view('mio.head.teacher-panel', [
            'page' => 'quiz',
            'quizzes' => $quizzes,
            'subjectId' => $subjectId,
            'subject' => $matchedSubject,
        ]);
    }


    public function addAcadsQuiz($subjectId)
    {
        // Step 1: Locate the subject
        $subjectsRef = $this->database->getReference('subjects');
        $allSubjects = $subjectsRef->getValue() ?? [];
        $gradeLevelKey = null;
        $matchedSubject = null;

        foreach ($allSubjects as $grade => $subjects) {
            if (isset($subjects[$subjectId])) {
                $gradeLevelKey = $grade;
                $matchedSubject = $subjects[$subjectId];
                break;
            }
        }

        if (!$gradeLevelKey || !$matchedSubject || ($matchedSubject['subjectType'] ?? '') !== 'academics') {
            return abort(404, 'Academic subject not found.');
        }

        // Step 2: Return form view
        return view('mio.head.teacher-panel', [
            'page' => 'add-acads-quiz',
            'subjectId' => $subjectId,
            'subject' => $matchedSubject,
            'gradeLevelKey' => $gradeLevelKey,
        ]);
    }

   public function storeQuiz(Request $request, $subjectId)
{
    // Step 1: Find gradeLevelKey and subjectKey
    $subjectsRef = $this->database->getReference('subjects');
    $allSubjects = $subjectsRef->getValue() ?? [];

    $gradeLevelKey = null;
    $subjectKey = null;

    foreach ($allSubjects as $gradeKey => $subjects) {
        foreach ($subjects as $key => $subjectData) {
            if (isset($subjectData['subject_id']) && $subjectData['subject_id'] === $subjectId) {
                $gradeLevelKey = $gradeKey;
                $subjectKey = $key;
                break 2;
            }
        }
    }

    if (!$gradeLevelKey || !$subjectKey) {
        return back()->with('error', 'Subject not found.');
    }

    // Step 2: Validate request input
    $request->validate([
        'quiz.title' => 'required|string|max:255',
        'quiz.description' => 'nullable|string',
        'quiz.publish_date' => 'required|date',
        'quiz.start_time' => 'required',
        'quiz.no_deadline' => 'nullable|boolean',
        'quiz.deadline_date' => 'nullable|required_without:quiz.no_deadline|date',
        'quiz.end_time' => 'nullable|required_without:quiz.no_deadline',
        'quiz.time_limit' => 'nullable|integer|min:0',
        'quiz.no_time_limit' => 'nullable|boolean',
        'quiz.total_points' => 'required|integer|min:0',
        'quiz.attempts' => 'required|integer|min:1',
        'quiz.one_question_at_a_time' => 'nullable|boolean',
        'quiz.can_go_back' => 'nullable|boolean',
        'quiz.show_correct_answers' => 'nullable|boolean',
        'questions' => 'required|array',
        'questions.*.question' => 'required|string',
        'questions.*.type' => 'required|string|in:multiple_choice,essay,file_upload,fill_blank,dropdown',
        'questions.*.options' => 'sometimes|array',
        'questions.*.options.*' => 'string',
        'questions.*.points' => 'required|numeric|min:0.01',
    ]);


    $quizData = $request->input('quiz');
    $questions = $request->input('questions');

    $noDeadline = isset($quizData['no_deadline']) && $quizData['no_deadline'];

        if ($noDeadline) {
            $quizData['deadline_date'] = '';
            $quizData['end_time'] = '';
        }


    // Additional validation: if one of deadline_date or end_time is filled, the other must be required
    $deadlineDate = $request->input('quiz.deadline_date');
    $endTime = $request->input('quiz.end_time');

    if (($deadlineDate && !$endTime) || (!$deadlineDate && $endTime)) {
        return back()
            ->withInput()
            ->withErrors(['deadline' => 'Both Deadline Date and End Time are required if either is provided.']);
    }

    // Step 3: Generate unique quiz ID
    $today = Carbon::now()->format('Ymd');
    $prefix = 'QU' . $today;

    $quizzesRef = $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectKey}/quizzes");
    $existingQuizzes = $quizzesRef->getValue() ?? [];

    $maxNumber = 0;
    foreach (array_keys($existingQuizzes) as $existingId) {
        if (Str::startsWith($existingId, $prefix)) {
            $numberPart = substr($existingId, strlen($prefix), 3);
            if (ctype_digit($numberPart)) {
                $num = (int)$numberPart;
                if ($num > $maxNumber) {
                    $maxNumber = $num;
                }
            }
        }
    }

    $newNumber = $maxNumber + 1;
    $quizId = $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

    // Step 4: Build people (students)
    $studentsRef = $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectKey}/people");
    $students = $studentsRef->getValue() ?? [];

    $people = [];
    foreach ($students as $studentId => $studentInfo) {
        $people[$studentId] = [
            'name' => trim(($studentInfo['first_name'] ?? '') . ' ' . ($studentInfo['last_name'] ?? '')),
            'attempts' => [],
            'latest_score' => null,
            'latest_submitted_at' => null,
            'comments' => '',
            'status' => 'not_started',
            'total_student_attempts' => 0,
        ];
    }

    // Step 5: Handle optional file upload
    $fileUrl = null;
    if ($request->hasFile('quiz_file') && $request->file('quiz_file')->isValid()) {
        $file = $request->file('quiz_file');
        $path = $file->store("public/quiz_files");
        $fileUrl = asset(str_replace('public', 'storage', $path));
    }

    // Step 6: Determine time limit
    $timeLimit = isset($quizData['time_limit']) ? (int)$quizData['time_limit'] : 0;
    $noTimeLimit = $timeLimit === 0;

    // ✅ Step 7: Build questions array with UUIDs, handling all types
    $questionMap = [];
    foreach ($questions as $q) {
        $questionId = (string) Str::uuid();

        $data = [
            'question' => $q['question'],
            'type' => $q['type'],
            'points' => (float) $q['points'],
        ];

        // Add options only for types that support it
        if (in_array($q['type'], ['multiple_choice', 'dropdown', 'fill_blank'])) {
            $data['options'] = $q['options'] ?? [];
        }

        // Add answer only if provided (optional for file_upload and essay)
        if (isset($q['answer'])) {
            $data['answer'] = $q['answer'];
        } else {
            // Optional: initialize answer as empty string for consistency
            $data['answer'] = '';
        }

        $questionMap[$questionId] = $data;
    }

    // Step 8: Build final quiz payload
    $quizPayload = [
        'quiz_id' => $quizId,
        'title' => $quizData['title'],
        'description' => $quizData['description'] ?? '',
        'publish_date' => $quizData['publish_date'],
        'start_time' => $quizData['start_time'],
        'deadline_date' => $quizData['deadline_date'] ?? null,
        'end_time' => $quizData['end_time'] ?? null,
        'time_limit' => $timeLimit,
        'no_time_limit' => $noTimeLimit,
        'total_points' => (int) $quizData['total_points'],
        'attempts' => (int) $quizData['attempts'],
        'access_code' => $quizData['access_code'] ?? '',
        'one_question_at_a_time' => isset($quizData['one_question_at_a_time']),
        'can_go_back' => isset($quizData['can_go_back']),
        'show_correct_answers' => isset($quizData['show_correct_answers']),
        'created_at' => now()->toDateTimeString(),
        'questions' => $questionMap,
        'people' => $people,
    ];

    if ($fileUrl) {
        $quizPayload['file_url'] = $fileUrl;
    }

    // Step 9: Save to Firebase
    $quizRef = $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectKey}/quizzes/{$quizId}");
    $quizRef->set($quizPayload);

    // Step 10: Redirect
    return redirect()->route('mio.subject-teacher.quiz', $subjectId)
        ->with('success', 'Quiz successfully created.');
    }


    public function deleteQuiz($subjectId, $quizId)
        {
            // Get the grade level key where the subject exists
            $subjectsRef = $this->database->getReference('subjects');
            $allSubjects = $subjectsRef->getValue() ?? [];

            $gradeLevelKey = null;
            foreach ($allSubjects as $gradeKey => $subjects) {
                if (array_key_exists($subjectId, $subjects)) {
                    $gradeLevelKey = $gradeKey;
                    break;
                }
            }

            if (!$gradeLevelKey) {
                return back()->with('error', 'Grade level not found for this subject.');
            }

            // Delete the assignment from the Firebase database
            $quizRef = $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectId}/quizzes/{$quizId}");
            $quizRef->remove();

            return redirect()->route('mio.subject-teacher.quiz', ['subjectId' => $subjectId])
                            ->with('success', 'Quiz deleted successfully.');
        }


        // QUIZ DETAILS
    public function showQuizDetails($subjectId, $quizId)
    {
        // Step 1: Find grade level and subjectKey
        $subjectsRef = $this->database->getReference('subjects');
        $allSubjects = $subjectsRef->getValue() ?? [];

        $gradeLevelKey = null;
        $subjectKey = null;

        foreach ($allSubjects as $gradeKey => $subjects) {
            foreach ($subjects as $key => $subjectData) {
                if (isset($subjectData['subject_id']) && $subjectData['subject_id'] === $subjectId) {
                    $gradeLevelKey = $gradeKey;
                    $subjectKey = $key;
                    $subject = $subjectData; // ✅ Assign subject details
                    break 2;
                }
            }
        }


        if (!$gradeLevelKey || !$subjectKey) {
            return abort(404, 'Subject not found.');
        }

        // Step 2: Get the specific assignment
        $quizRef = $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectKey}/quizzes/{$quizId}");
        $quiz = $quizRef->getValue();

        if (!$quiz) {
            return abort(404, 'Quiz not found.');
        }

        $quiz['id'] = $quizId;

        // Step 3: Get students/people
        $peopleRef = $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectKey}/quizzes/{$quizId}/people");
        $quiz['people'] = $peopleRef->getValue() ?? [];

        // Step 4: Load submission for selected student
        $selectedStudentId = request()->input('student_id');
        $submission = null;

        $studentAttempts = [];

    if ($selectedStudentId) {
        // Path: submissions > student_id
        $submissionRef = $this->database
            ->getReference("subjects/{$gradeLevelKey}/{$subjectKey}/quizzes/{$quizId}/submissions/{$selectedStudentId}");

        $submissionData = $submissionRef->getValue();

        // Check if multiple attempts exist (ATTM keys)
        if (is_array($submissionData)) {
            foreach ($submissionData as $attemptKey => $attemptData) {
                if (str_starts_with($attemptKey, 'ATTM')) {
                    $studentAttempts[$attemptKey] = $attemptData;
                }
            }

            // Default: pick the latest attempt (by key) or allow frontend to choose
            end($studentAttempts);
            $latestAttemptKey = key($studentAttempts);
            $submission = $studentAttempts[$latestAttemptKey] ?? null;
        }
    }


        $questions = isset($quiz['questions']) ? $quiz['questions'] : [];


        return view('mio.head.teacher-panel', [
            'page' => 'quiz-body',
            'quiz' => $quiz,
            'questions' => $quiz['questions'] ?? [],
            'subjectId' => $subjectId,
            'quizId' => $quizId,
            'gradeLevelKey' => $gradeLevelKey,
            'submission' => $submission,
            'selectedStudentId' => $selectedStudentId,
            'subject' => $subject,
            'studentAttempts' => $studentAttempts,
        ]);
    }

    public function showEditAcadsQuiz($subjectId, $quizId)
    {
        // Step 1: Find grade level and subjectKey
        $subjectsRef = $this->database->getReference('subjects');
        $allSubjects = $subjectsRef->getValue() ?? [];

        $gradeLevelKey = null;
        $subjectKey = null;
        $subject = null;

        foreach ($allSubjects as $gradeKey => $subjects) {
            foreach ($subjects as $key => $subjectData) {
                if (isset($subjectData['subject_id']) && $subjectData['subject_id'] === $subjectId) {
                    $gradeLevelKey = $gradeKey;
                    $subjectKey = $key;
                    $subject = $subjectData; // Subject details
                    break 2;
                }
            }
        }

        if (!$gradeLevelKey || !$subjectKey) {
            return abort(404, 'Subject not found.');
        }

        // Step 2: Get the specific quiz
        $quizRef = $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectKey}/quizzes/{$quizId}");
        $quiz = $quizRef->getValue();

        if (!$quiz) {
            return abort(404, 'Quiz not found.');
        }

        $quiz['id'] = $quizId;

        // Step 3: Get quiz questions
        $questions = $quiz['questions'] ?? [];

        // Step 4: (Optional) If you want to pass other related data, add here

        // Return view with quiz data for editing
        return view('mio.head.teacher-panel', [
            'page' => 'edit-acads-quiz', // assuming your blade uses this to render edit quiz form
            'quiz' => $quiz,
            'questions' => $questions,
            'subjectId' => $subjectId,
            'quizId' => $quizId,
            'gradeLevelKey' => $gradeLevelKey,
            'subject' => $subject,
        ]);
    }

    public function updateAcadsQuiz(Request $request, $subjectId, $quizId)
    {
        // Step 1: Find gradeLevelKey and subjectKey
        $subjectsRef = $this->database->getReference('subjects');
        $allSubjects = $subjectsRef->getValue() ?? [];

        $gradeLevelKey = null;
        $subjectKey = null;

        foreach ($allSubjects as $gradeKey => $subjects) {
            foreach ($subjects as $key => $subjectData) {
                if (isset($subjectData['subject_id']) && $subjectData['subject_id'] === $subjectId) {
                    $gradeLevelKey = $gradeKey;
                    $subjectKey = $key;
                    break 2;
                }
            }
        }

        if (!$gradeLevelKey || !$subjectKey) {
            return abort(404, 'Subject not found.');
        }

        // Step 2: Check if quiz exists
        $quizRef = $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectKey}/quizzes/{$quizId}");
        $existingQuiz = $quizRef->getValue();

        if (!$existingQuiz) {
            return abort(404, 'Quiz not found.');
        }

        // Step 3: Validate input
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'publish_date' => 'required|date',
            'start_time' => 'required',
            'deadline' => 'nullable|date',
            'end_time' => 'nullable',
            'time_limit' => 'required|integer|min:0',
            'total' => 'required|integer|min:0',
            'attempts' => 'required|integer|min:1',
            'questions' => 'required|array',
            'questions.*.question' => 'required|string',
            'questions.*.type' => 'required|string',
            'questions.*.answer' => 'required|string',
            'questions.*.options' => 'sometimes|array',
            'questions.*.options.*' => 'string',
        ]);

        // Step 4: Prepare updated quiz data
        $updatedQuiz = [
            'title' => $request->input('title'),
            'description' => $request->input('description', ''),
            'publish_date' => $request->input('publish_date'),
            'start_time' => $request->input('start_time'),
            'deadline' => $request->input('deadline'),
            'end_time' => $request->input('end_time'),
            'time_limit' => (int) $request->input('time_limit'),
            'total' => (int) $request->input('total'),
            'attempts' => (int) $request->input('attempts'),
            'created_at' => $existingQuiz['created_at'] ?? now()->toDateTimeString(),
            'people' => $existingQuiz['people'] ?? [],
            'questions' => [],

            // Additional settings from form
            'one_question_at_a_time' => $request->input('quiz.one_question_at_a_time') ? true : false,
            'can_go_back' => $request->input('quiz.can_go_back') ? true : false,
            'show_correct_answers' => $request->input('quiz.show_correct_answers') ? true : false,
        ];

        // Step 5: Update questions
        $questions = $request->input('questions');
        foreach ($questions as $key => $q) {
            $updatedQuiz['questions'][$key] = [
                'question' => $q['question'],
                'type' => $q['type'],
                'answer' => $q['answer'],
                'options' => $q['options'] ?? [],
            ];
        }

        // Step 6: Save to Firebase
        $quizRef->set($updatedQuiz);

        // Step 7: Redirect with success
        return redirect()->route('mio.subject-teacher.quiz-body', ['subjectId' => $subjectId, 'quizId' => $quizId])
                        ->with('success', 'Quiz updated successfully.');
    }

    public function updateAttempt(Request $request, $subjectId, $quizId)
{
    $studentId = $request->input('student_id');
    $attemptId = $request->input('attempt_id');
    $score = $request->input('score');
    $answers = $request->input('answers');

    // Validate data here as needed

    // Build updated data structure
    $updatedAttempt = [
        'score' => $score,
        'answers' => [],
        // Other fields like submitted_at, total_points should be preserved or recalculated
    ];

    foreach ($answers as $qid => $answerData) {
        $updatedAttempt['answers'][$qid] = [
            'student_answer' => $answerData['student_answer'],
            'points' => floatval($answerData['points']),
            // You might want to keep correct_answer and question from old data
        ];
    }

    // Fetch existing attempt data from Firebase (optional)

    // Update the Firebase document:
    // Use your Firebase PHP SDK or REST API to update the student's attempt at:
    // path like: quizzes/{quizId}/people/{studentId}/ATTM{attemptId}

    // Example (pseudocode):
    // $firebase = app('firebase.database');
    // $ref = $firebase->getReference("quizzes/{$quizId}/people/{$studentId}/{$attemptId}");
    // $ref->update($updatedAttempt);

    // Redirect back with success message
    return redirect()->back()->with('message', 'Attempt updated successfully.');
}



// TEACHER ASSIGNMENTS

    public function showAssignment($subjectId)
    {
        // Find grade level key
        $subjectsRef = $this->database->getReference('subjects');
        $allSubjects = $subjectsRef->getValue() ?? [];
        $gradeLevelKey = null;

        foreach ($allSubjects as $key => $subjects) {
            foreach ($subjects as $subject) {
                if ($subject['subject_id'] === $subjectId) {
                    $gradeLevelKey = $key;
                    break 2;
                }
            }
        }

        // Fetch assignments
        $assignmentsRef = $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectId}/assignments");
        $rawAssignments = $assignmentsRef->getValue() ?? [];

        $assignments = [];

        if (is_array($rawAssignments)) {
            foreach ($rawAssignments as $key => $assignment) {
                $assignment['id'] = $key;
                $assignments[] = $assignment;
            }
        }


        return view('mio.head.teacher-panel', [
            'page' => 'assignment',
            'assignments' => $assignments,
            'subjectId' => $subjectId,

        ]);
    }

   public function addAssignment(Request $request, $subjectId)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date',
            'availability_start' => 'required',
            'availability_end' => 'nullable',
            'points_total' => 'required|integer',
            'attempts' => 'required|integer',
            'publish_date' => 'required|date',
            'attachments' => 'nullable|array',
            'attachments.*.link' => 'nullable|url',
            'attachments.*.file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
        ]);

        // Find grade level key that contains the subject ID
        $subjectsRef = $this->database->getReference('subjects');
        $allSubjects = $subjectsRef->getValue() ?? [];

        $gradeLevelKey = null;
        foreach ($allSubjects as $gradeKey => $subjects) {
            if (array_key_exists($subjectId, $subjects)) {
                $gradeLevelKey = $gradeKey;
                break;
            }
        }

        if (!$gradeLevelKey) {
            return back()->with('error', 'Grade level not found for this subject.');
        }

        // Fetch students under this subject and grade level
        $studentsRef = $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectId}/people");
        $students = $studentsRef->getValue() ?? [];

        // Prepare student information for the people field
        $people = [];
        foreach ($students as $studentId => $studentInfo) {
            $people[$studentId] = [
                'work' => '',  // Default work value, can be updated later
                'name' => ($studentInfo['first_name'] ?? '') . ' ' . ($studentInfo['last_name'] ?? ''),
                'submitted_at' => null,  // Initially, no submission
                'attempts' => 0,  // Initial attempts count
                'comments' => '',
                'feedback' => '',  // Default no comments
                'score' => null,  // Initially no score
                'timestamp' => now()->toDateTimeString(),  // Timestamp of assignment creation
            ];
        }

        // Handle attachments
        $attachments = [];
        if ($request->has('attachments')) {
            foreach ($request->attachments as $index => $attachment) {
                $fileUrl = null;

                if (isset($attachment['file']) && $request->file("attachments.$index.file")) {
                    $file = $request->file("attachments.$index.file");
                    $path = $file->store("assignments", 'public');
                    $fileUrl = asset("storage/" . $path);
                }

                $attachments[] = [
                    'link' => $attachment['link'] ?? '',
                    'file' => $fileUrl ?? '',
                ];
            }
        }

        // Generate a unique ID for the assignment based on a custom format
        $dateKey = now()->format('Ymd'); // Year-Month-Day format
        $timeKey = now()->format('His'); // Hour-Minute-Second format
        $assignmentKey = "ASS{$dateKey}{$timeKey}"; // Example: 20230514193000

        $deadline = $validated['deadline'] ?? '';
        $endtime = $validated['availability_end'] ?? '';

        $newAssignment = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? '',
            'deadline' => $deadline,
            'availability' => [
                'start' => $validated['availability_start'],
                'end' => $endtime,
            ],
            'total' => $validated['points_total'],
            'attempts' => $validated['attempts'],
            'attachments' => $attachments,
            'people' => $people, // Add the student information to the people field
            'created_at' => now()->toDateTimeString(),
            'published_at' => $validated['publish_date'] . ' ' . $validated['availability_start'],

        ];

        // Save the new assignment with the unique assignment ID
        $assignmentsRef = $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectId}/assignments/{$assignmentKey}");
        $assignmentsRef->set($newAssignment);

        return redirect()->route('mio.subject-teacher.assignment', ['subjectId' => $subjectId])->with('success', 'Assignment added successfully.');
    }

    public function deleteAssignment($subjectId, $assignmentId)
    {
        // Get the grade level key where the subject exists
        $subjectsRef = $this->database->getReference('subjects');
        $allSubjects = $subjectsRef->getValue() ?? [];

        $gradeLevelKey = null;
        foreach ($allSubjects as $gradeKey => $subjects) {
            if (array_key_exists($subjectId, $subjects)) {
                $gradeLevelKey = $gradeKey;
                break;
            }
        }

        if (!$gradeLevelKey) {
            return back()->with('error', 'Grade level not found for this subject.');
        }

        // Delete the assignment from the Firebase database
        $assignmentsRef = $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectId}/assignments/{$assignmentId}");
        $assignmentsRef->remove();

        return redirect()->route('mio.subject-teacher.assignment', ['subjectId' => $subjectId])
                        ->with('success', 'Assignment deleted successfully.');
    }

// ASSIGNMENT DETAILS
    public function showAssignmentDetails($subjectId, $assignmentId)
    {
        // Step 1: Find grade level and subjectKey
        $subjectsRef = $this->database->getReference('subjects');
        $allSubjects = $subjectsRef->getValue() ?? [];

        $gradeLevelKey = null;
        $subjectKey = null;

        foreach ($allSubjects as $gradeKey => $subjects) {
            foreach ($subjects as $key => $subject) {
                if (isset($subject['subject_id']) && $subject['subject_id'] === $subjectId) {
                    $gradeLevelKey = $gradeKey;
                    $subjectKey = $key;
                    break 2;
                }
            }
        }

        if (!$gradeLevelKey || !$subjectKey) {
            return abort(404, 'Subject not found.');
        }

        // Step 2: Get the specific assignment
        $assignmentRef = $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectKey}/assignments/{$assignmentId}");
        $assignment = $assignmentRef->getValue();

        if (!$assignment) {
            return abort(404, 'Assignment not found.');
        }

        $assignment['id'] = $assignmentId;

        // Step 3: Get students/people
        $peopleRef = $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectKey}/assignments/{$assignmentId}/people");
        $assignment['people'] = $peopleRef->getValue() ?? [];

        // Step 4: Load submission for selected student
        $selectedStudentId = request()->input('student_id');
        $submission = null;

        if ($selectedStudentId) {
            $submissionRef = $this->database
                ->getReference("subjects/{$gradeLevelKey}/{$subjectKey}/assignments/{$assignmentId}/submissions/{$selectedStudentId}");
            $submission = $submissionRef->getValue();
        }

        return view('mio.head.teacher-panel', [
            'page' => 'assignment-body',
            'assignment' => $assignment,
            'subjectId' => $subjectId,
            'assignmentId' => $assignmentId,
            'gradeLevelKey' => $gradeLevelKey,
            'submission' => $submission,
            'selectedStudentId' => $selectedStudentId,
        ]);
    }

     public function saveReview(Request $request, $subjectId, $assignmentId, $studentId)
    {
        // Validate inputs
        $validated = $request->validate([
            'comments' => 'nullable|string',
            'feedback' => 'nullable|string',
            'score' => 'nullable|numeric|min:0',
        ]);

        $database = $this->database; // Your Firebase Realtime DB instance

        // Step 1: Find gradeLevelKey and subjectKey by subjectId
        $subjectsRef = $database->getReference('subjects');
        $allSubjects = $subjectsRef->getValue() ?? [];

        $gradeLevelKey = null;
        $subjectKey = null;

        foreach ($allSubjects as $gradeKey => $subjects) {
            foreach ($subjects as $key => $subject) {
                if (isset($subject['subject_id']) && $subject['subject_id'] === $subjectId) {
                    $gradeLevelKey = $gradeKey;
                    $subjectKey = $key;
                    break 2;
                }
            }
        }

        if (!$gradeLevelKey || !$subjectKey) {
            return response()->json(['message' => 'Subject not found'], 404);
        }

        // Step 2: Check assignment exists
        $assignmentRef = $database->getReference("subjects/{$gradeLevelKey}/{$subjectKey}/assignments/{$assignmentId}");
        $assignment = $assignmentRef->getValue();

        if (!$assignment) {
            return response()->json(['message' => 'Assignment not found'], 404);
        }

        // Step 3: Prepare review data to save (update or create)
        $reviewData = [];

        if (isset($validated['comments'])) {
            $reviewData['comments'] = $validated['comments'];
        }

        if (isset($validated['feedback'])) {
            $reviewData['feedback'] = $validated['feedback'];
        }

        if (isset($validated['score'])) {
            $reviewData['score'] = $validated['score'];
        }

        // Add/update timestamp
        $reviewData['reviewed_at'] = date('Y-m-d H:i:s');

        // Step 4: Save the review to the student's assignment 'people' node (or 'reviews' node if you prefer)
        // Using 'people' because you showed the assignment structure has "people" with student data and feedback fields.

        $studentReviewRef = $database->getReference("subjects/{$gradeLevelKey}/{$subjectKey}/assignments/{$assignmentId}/people/{$studentId}");

        // Get current data for merging
        $currentData = $studentReviewRef->getValue() ?? [];

        // Merge with current data, update with new review info
        $updatedData = array_merge($currentData, $reviewData);

        // Save
        $studentReviewRef->set($updatedData);

        return redirect()->back()->with([
            'message' => 'Review saved successfully',
            'review' => $updatedData,
            'studentId' => $studentId,
        ]);

    }

    public function editAssignment(Request $request, $subjectId, $assignmentId)
{
    $validated = $request->validate([
        'title' => 'required|string',
        'description' => 'nullable|string',
        'deadline' => 'nullable|date',
        'availability_start' => 'required',
        'availability_end' => 'nullable',
        'points_total' => 'required|integer',
        'attempts' => 'required|integer',
        'publish_date' => 'required|date',
        'attachments' => 'nullable|array',
        'attachments.*.link' => 'nullable|url',
        'attachments.*.file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
    ]);

    $subjectsRef = $this->database->getReference('subjects');
    $allSubjects = $subjectsRef->getValue() ?? [];

    $gradeLevelKey = null;
    $subjectKey = null;

    // Find subject location
    foreach ($allSubjects as $gradeKey => $subjects) {
        foreach ($subjects as $key => $subject) {
            if (isset($subject['subject_id']) && $subject['subject_id'] === $subjectId) {
                $gradeLevelKey = $gradeKey;
                $subjectKey = $key;
                break 2;
            }
        }
    }

    if (!$gradeLevelKey || !$subjectKey) {
        return abort(404, 'Subject not found.');
    }

    $assignmentPath = "subjects/{$gradeLevelKey}/{$subjectKey}/assignments/{$assignmentId}";
    $existingAssignment = $this->database->getReference($assignmentPath)->getValue();

    // Handle attachments
    $attachments = [];

    if ($request->has('attachments')) {
        foreach ($request->attachments as $index => $attachment) {
            $fileUrl = null;

            if (isset($attachment['file']) && $request->hasFile("attachments.$index.file")) {
                $file = $request->file("attachments.$index.file");
                $path = $file->store("assignments", 'public');
                $fileUrl = asset("storage/" . $path);
            } elseif (!empty($attachment['file'])) {
                $fileUrl = $attachment['file']; // Keep existing file URL
            }

            $attachments[] = [
                'link' => $attachment['link'] ?? '',
                'file' => $fileUrl ?? '',
            ];
        }
    } elseif (isset($existingAssignment['attachments'])) {
        $attachments = $existingAssignment['attachments']; // Keep existing if no update
    }

    $deadline = $validated['deadline'] ?? null;
    $endtime = $validated['availability_end'] ?? null;

    $updatedData = [
        'title' => $validated['title'],
        'description' => $validated['description'] ?? '',
        'deadline' => $deadline ?? '',
        'availability' => [
            'start' => $validated['availability_start'],
            'end' => $endtime ?? '',
        ],
        'total' => $validated['points_total'],
        'attempts' => $validated['attempts'],
        'attachments' => $attachments,
        'published_at' => $validated['publish_date'] . ' ' . $validated['availability_start'],
        // We retain 'people' and other fields as-is
    ];

    // Update assignment data
    $this->database->getReference($assignmentPath)->update($updatedData);

    return redirect()->back()->with('success', 'Assignment updated successfully.');
}

// ANNOUNCEMENTS

    public function showSubjectAnnouncements($subjectId)
    {
        // Fetch active school year
        $schoolYears = $this->database->getReference('schoolyears')->getValue() ?? [];
        $activeSchoolYear = null;
        foreach ($schoolYears as $year) {
            if ($year['status'] === 'active') {
                $activeSchoolYear = $year['schoolyearid'];
                break;
            }
        }

        // Fetch subject data
        $subjects = $this->database->getReference('subjects')->getValue() ?? [];
        $subject = null;
        $gradeLevelKey = null;

        foreach ($subjects as $gradeLevel => $items) {
            foreach ($items as $key => $item) {
                if ($item['subject_id'] === $subjectId) {
                    $subject = $item;
                    $gradeLevelKey = $gradeLevel;
                    break 2;
                }
            }
        }

        if (!$subject || !$gradeLevelKey) {
            return redirect()->route('mio.subject-teacher.announcement', ['subjectId' => $subjectId])->with('error', 'Subject not found.');

        }

        // Fetch announcements from correct path
        $announcementsRef = $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectId}/announcements");
        $announcementsSnapshot = $announcementsRef->getValue() ?? [];
        $announcements = [];

        foreach ($announcementsSnapshot as $key => $announcement) {
            $announcement['id'] = $key;
            $announcements[] = $announcement;
        }


        return view('mio.head.teacher-panel', [
            'page' => 'announcement',
            'subject' => $subject,
            'announcements' => $announcements,
            'subjectId' => $subjectId
        ]);
    }

    // Show specific announcement details
    public function showAnnouncementDetails($subjectId, $announcementId)
    {
        // Fetch active school year
        $schoolYears = $this->database->getReference('schoolyears')->getValue() ?? [];
        $activeSchoolYear = null;
        foreach ($schoolYears as $year) {
            if ($year['status'] === 'active') {
                $activeSchoolYear = $year['schoolyearid'];
                break;
            }
        }

        // Fetch subject and announcement by ID
        $subjects = $this->database->getReference('subjects')->getValue() ?? [];
        $subject = null;
        $gradeLevelKey = null;
        $announcement = null;

        foreach ($subjects as $gradeLevel => $items) {
            foreach ($items as $key => $item) {
                if ($item['subject_id'] === $subjectId) {
                    $subject = $item;
                    $gradeLevelKey = $gradeLevel;
                    // Find the announcement by ID
                    if (isset($item['announcements'][$announcementId])) {
                        $announcement = $item['announcements'][$announcementId];
                    }
                    break 2;
                }
            }
        }

        if (!$subject || !$announcement) {
            return redirect()->route('mio.subject-teacher.announcement', ['subjectId' => $subjectId])
                ->with('error', 'Announcement not found.');
        }


        return view('mio.head.teacher-panel', [
            'page' => 'announcement-body',
            'subject' => $subject,
            'announcement' => $announcement,
            'announcementId' => $announcementId,
            'subjectId' => $subjectId,
        ]);
    }

    public function editAnnouncement(Request $request, $subjectId, $announcementId)
    {

        $validated = $request->validate([
        'title' => 'required|string|max:255',
        'date_posted' => 'required|date',
        'description' => 'required|string',
        'link' => 'nullable|url',
    ]);

    // Fetch the subjects list
    $subjects = $this->database->getReference('subjects')->getValue() ?? [];

    $found = false;
    $gradeLevelKey = null;

    // Find the grade level where the subject exists
    foreach ($subjects as $gradeLevel => $subjectList) {
        if (isset($subjectList[$subjectId])) {
            $gradeLevelKey = $gradeLevel;
            $found = true;
            break;
        }
    }

    if (!$found || !$gradeLevelKey) {
        return redirect()->back()->with('error', 'Subject not found.');
    }

    // Build the Firebase path to the announcement
    $announcementPath = "subjects/{$gradeLevelKey}/{$subjectId}/announcements/{$announcementId}";

    // Update announcement data
    $updateData = [
        'title' => $validated['title'],
        'date_posted' => $validated['date_posted'],
        'description' => $validated['description'],
        'link' => $validated['link'] ?? '',
        'subject_id' => $subjectId,
    ];

    if ($request->hasFile('image_file')) {
    $file = $request->file('image_file');
    $filename = time() . '_' . $file->getClientOriginalName();
    $path = $file->storeAs('announcement_images', $filename, 'public');

        // Store public URL of the uploaded file
        $updateData['link'] = asset('storage/' . $path);
    } elseif (!empty($validated['link'])) {
        $updateData['link'] = $validated['link'];
    } else {
        $updateData['link'] = '';
    }


    $this->database->getReference($announcementPath)->update($updateData);

    return redirect()->route('mio.subject-teacher.announcement', ['subjectId' => $subjectId])
        ->with('success', 'Announcement updated successfully.');
    }


    public function storeReply(Request $request, $subjectId, $announcementId)
    {

        // Get the current logged-in user
        $user = session('firebase_user');
        $userId = $user['uid'] ?? null;
        $userName = $user['name'] ?? 'Anonymous';

        // Validate the reply input
        $request->validate([
            'reply' => 'required|string|max:500',
        ]);

        // Get the current timestamp
        $timestamp = now()->toDateTimeString();

        // Fetch all subjects from Firebase
        $subjectsRef = $this->database->getReference("subjects")->getValue();

        // Dynamically get the grade level for the subject
        $grade = $this->getGradeLevelForSubject($subjectId, $subjectsRef);

        // Check if the grade is found
        if ($grade === null) {
            return redirect()->back()->with('status', 'Subject not found.')->withInput();
        }

        // Get the announcement data path in Firebase
        $announcementRef = $this->database->getReference("subjects/{$grade}/{$subjectId}/announcements/{$announcementId}/replies");

        // Prepare the reply data
        $replyData = [
            'user_id' => $userId,
            'user_name' => $userName,
            'message' => $request->input('reply'),
            'timestamp' => $timestamp,
        ];

        // Push the reply data to Firebase
        try {
            $announcementRef->push($replyData);
            return redirect()->route('mio.subject.announcement-body', ['subjectId' => $subjectId, 'announcementId' => $announcementId])
                            ->with('success', 'Reply posted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('status', 'Failed to post reply: ' . $e->getMessage())->withInput();
        }
        }

    public function deleteReply($subjectId, $announcementId, $replyId)
    {
        // Fetch the grade level dynamically from Firebase
        $subjectsRef = $this->database->getReference("subjects")->getValue();
        $grade = $this->getGradeLevelForSubject($subjectId, $subjectsRef);

        if (!$grade) {
            return redirect()->back()->with('error', 'Grade level not found for the subject.');
        }

        // Get the specific reply reference path
        $replyRefPath = "subjects/{$grade}/{$subjectId}/announcements/{$announcementId}/replies/{$replyId}";

        // Fetch the specific reply data to check the user
        $reply = $this->database->getReference($replyRefPath)->getValue();

        // Get the current logged-in user
        $user = session('firebase_user');
        $userId = $user['uid'] ?? null;

        // Check if the reply exists and if the logged-in user matches the user who posted the reply
        if (!$reply || $reply['user_id'] !== $userId) {
            return redirect()->back()->with('error', 'You are not authorized to delete this reply.');
        }

        // Proceed with deletion
        try {
            $this->database->getReference($replyRefPath)->remove();

            return redirect()->route('mio.subject.announcement-body', [
                'subjectId' => $subjectId,
                'announcementId' => $announcementId
            ])->with('success', 'Reply deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete reply: ' . $e->getMessage());
        }
    }

    // Helper function to get grade level for a subject
    private function getGradeLevelForSubject($subjectId, $subjectsRef)
    {
        // Loop through the subjects and find the grade level for the given subjectId
        foreach ($subjectsRef as $grade => $subjects) {
            if (array_key_exists($subjectId, $subjects)) {
                return $grade;  // Return the grade level for the subject
            }
        }
        return null;  // Return null if subject is not found
    }

    public function showModules($subjectId)
    {
        $userSectionId = session('firebase_user')['section_id'] ?? null;

        $schoolYears = $this->database->getReference('schoolyears')->getValue() ?? [];
        $activeSchoolYear = collect($schoolYears)->firstWhere('status', 'active')['schoolyearid'] ?? null;

        $subjectsData = $this->database->getReference('subjects')->getValue() ?? [];

        $modulesList = [];
        $subjectDetails = null; // Initialize to store subject details

        foreach ($subjectsData as $gradeLevel => $subjects) {
            foreach ($subjects as $subject) {
                if ($subject['subject_id'] === $subjectId && isset($subject['modules']) && is_array($subject['modules'])) {
                    // Store the subject details in $subjectDetails when found
                    $subjectDetails = $subject;

                    foreach ($subject['modules'] as $index => $module) {
                        $modulesList[] = [
                            'title' => $module['title'] ?? 'Untitled Module',
                            'description' => $module['description'] ?? '',
                            'subject_id' => $subject['subject_id'],
                            'module_index' => $index
                        ];
                    }
                    break; // Exit the loop once the subject is found
                }
            }
            if ($subjectDetails) {
                break; // Exit the outer loop once the subject is found
            }
        }

        return view('mio.head.teacher-panel', [
            'page' => 'module',
            'modules' => $modulesList,
            'subject_id' => $subjectId,
            'subject' => $subjectDetails, // Pass the correct subject data to the view
        ]);
    }

    public function showModuleBody($subjectId, $moduleIndex)
    {
        // Get active school year
        $schoolYears = $this->database->getReference('schoolyears')->getValue() ?? [];
        $activeSchoolYear = null;
        foreach ($schoolYears as $year) {
            if ($year['status'] === 'active') {
                $activeSchoolYear = $year['schoolyearid'];
                break;
            }
        }

    // Get the grade level and subject data
    $subjects = $this->database->getReference('subjects')->getValue() ?? [];
    $subject = null;
    $gradeLevelKey = null;

    foreach ($subjects as $gradeLevel => $items) {
        foreach ($items as $key => $item) {
            if ($item['subject_id'] === $subjectId) {
                $subject = $item;
                $gradeLevelKey = $gradeLevel;
                break 2;
            }
        }
    }

    if (!$subject || !$gradeLevelKey) {
        return redirect()->route('mio.teacher-panel')->with('error', 'Subject not found.');
    }

    // Get the module using the module index
    $module = $subject['modules'][$moduleIndex] ?? null;

    if (!$module) {
        return redirect()->route('mio.teacher-panel')->with('error', 'Module not found.');
    }

    return view('mio.head.teacher-panel', [
        'page' => 'module-body',
        'subject' => $subject,
        'module' => $module,
        'moduleIndex' => $moduleIndex,

    ]);
}

    public function showProfile(){
        $userId = session('firebase_user.uid');
        $teacherName = session('firebase_user.name');

                // Get student data
                $teacherRef = $this->database->getReference('users/' . $userId);
                $teacher = $teacherRef->getValue();

                if (!$teacher) {
                    abort(404, 'Teacher not found.');
                }

                // // Find the section where this student is enrolled
                // $sectionsRef = $this->database->getReference('sections');
                // $sections = $sectionsRef->getValue();

                // $studentSection = null;

                // foreach ($sections as $sectionId => $sectionData) {
                //     if (isset($sectionData['students']) && array_key_exists($userId, $sectionData['students'])) {
                //         $studentSection = $sectionData;
                //         break;
                //     }
                // }

                return view('mio.head.teacher-panel', [
                    'page' => 'profile',
                    'teacher' => $teacher,
                    'name' => $teacherName,
                    'uid' => $userId,
                ]);
    }

    public function showPeople($subjectId)
    {
        // Get all subjects grouped by grade level
        $subjectsByGrade = $this->database->getReference('subjects')->getValue() ?? [];

        $subject = null;
        $gradeLevel = null;

        // Loop through grade levels to find the subject with the matching subject_id
        foreach ($subjectsByGrade as $grade => $subjects) {
            foreach ($subjects as $key => $s) {
                if (isset($s['subject_id']) && $s['subject_id'] === $subjectId) {
                    $subject = $s;
                    $gradeLevel = $grade;
                    break 2;
                }
            }
        }

        // If no matching subject or no people listed, show error
        if (!$subject || !isset($subject['people'])) {
            abort(404, 'Subject or people not found.');
        }

        // Sort people by last name
        $people = $subject['people'];
        uasort($people, function ($a, $b) {
            return strcmp(strtoupper($a['last_name']), strtoupper($b['last_name']));
        });

        // Return view with subject info included
        return view('mio.head.student-panel', [
            'page' => 'people',
            'subject' => $subject,              // ✅ include full subject info
            'subject_id' => $subjectId,
            'grade_level' => $gradeLevel,
            'people' => $people
        ]);
    }



}
