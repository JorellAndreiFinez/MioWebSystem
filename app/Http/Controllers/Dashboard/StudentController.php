<?php
namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Database;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageObject;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Arr;


class StudentController extends Controller
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



   public function showDashboard()
    {
        Log::debug('Session firebase_user:', Session::get('firebase_user'));

        // Fetch the current logged-in user's section_id and teacher_id
        $userSectionId = session('firebase_user')['section_id'] ?? null; // Default to null if section_id is not found
        $userTeacherId = session('firebase_user')['teacher_id'] ?? null; // Fetch the student ID from the session

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

            // Only add subjects for the active school year AND the user's section or teacher
            $gradeSubjects = array_filter($subjects, function($subject) use ($activeSchoolYear, $userSectionId, $userTeacherId) {
                return isset($subject['schoolyear_id']) && $subject['schoolyear_id'] === $activeSchoolYear &&
                    (
                        // Check if the subject is assigned to the user's section
                        (isset($subject['section_id']) && $subject['section_id'] === $userSectionId) ||
                        // Or check if the teacher is assigned to the subject
                        (isset($subject['teacher_id']) && $subject['teacher_id'] === $userTeacherId)
                    );
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

        // Filter active sections based on the logged-in user's section_id
        $filteredSections = $userSectionId
        ? array_filter($activeSections, function($section) use ($userSectionId) {
            return isset($section['sectionid']) && $section['sectionid'] === $userSectionId;
        })
        : [];


        // Fetch users (students and teachers) for the active sections
        $usersRef = $this->database->getReference('users');
        $users = $usersRef->getValue();

        // Organize users by section
        $sectionUsers = [];
        foreach ($filteredSections as $section) {
            $sectionId = $section['sectionid'];
            $sectionUsers[$sectionId] = [
                'teachers' => [],
                'students' => []
            ];

            // Add teachers based on the modules in the section
            if (isset($section['modules'])) {
                foreach ($section['modules'] as $module) {
                    if (isset($module['people'])) {
                        foreach ($module['people'] as $person) {
                            if ($person['role'] === 'teacher') {
                                $sectionUsers[$sectionId]['teachers'][] = $users[$person['teacher_id']] ?? null;
                            }
                        }
                    }
                }
            }

            // Add students based on the section ID
            foreach ($users as $user) {
                if (isset($user['category']) && $user['category'] === 'new' && isset($user['schoolyear_id']) && $user['schoolyear_id'] === $activeSchoolYear) {
                    if (isset($user['section_id']) && $user['section_id'] === $sectionId) {
                        $sectionUsers[$sectionId]['students'][] = $user;
                    }
                }
            }
        }

        // Fetch modules for the logged-in user's section and ensure they are assigned correctly
        $modulesForUserSection = [];
        foreach ($allSubjects as $gradeLevelKey => $subjects) {
            foreach ($subjects as $subject) {
                // Ensure the student’s section matches the subject's section or the teacher is assigned
                if ($subject['section_id'] === $userSectionId || $subject['teacher_id'] === $userTeacherId) {
                    // Ensure subject is linked to the active school year
                    if ($subject['schoolyear_id'] === $activeSchoolYear) {
                        $modulesForUserSection[] = $subject;
                    }
                }
            }
        }

        // Fetch and display announcements
        $adminAnnouncementsRef = $this->database->getReference('admin-announcements');
        $adminAnnouncements = $adminAnnouncementsRef->getValue() ?? [];

        $subjectAnnouncements = [];
        foreach ($allSubjects as $gradeLevelKey => $subjects) {
            foreach ($subjects as $subject) {
                if ($subject['section_id'] === $userSectionId && $subject['schoolyear_id'] === $activeSchoolYear) {
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

        // Merge and sort all announcements
        $allAnnouncements = [];
        foreach ($adminAnnouncements as $announcementId => $announcement) {
            $announcement['subject'] = 'General';
            $announcement['date'] = $announcement['date'] ?? 'Unknown Date';
            $announcement['type'] = 'general';
            $announcement['id'] = $announcementId;
            $allAnnouncements[] = $announcement;
        }
        $allAnnouncements = array_merge($allAnnouncements, $subjectAnnouncements);

        // Sort by date (latest first)
        usort($allAnnouncements, function ($a, $b) {
            return strtotime($b['date']) <=> strtotime($a['date']);
        });

        // Pass filtered data to the view
        return view('mio.head.student-panel', [
            'page' => 'dashboard',
            'subjects' => $modulesForUserSection,
            'allSubjects' => $allSubjects,
            'activeSchoolYear' => $activeSchoolYear,
            'activeSections' => $filteredSections,
            'sectionUsers' => $sectionUsers,
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
            return redirect()->route('mio.student-panel')->with('error', 'Subject not found.');
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
        return view('mio.head.student-panel', [
            'page' => 'subject',
            'subject' => $subject,
            'modules' => $subjectModules
        ]);
    }

    // STUDENT QUIZZES
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

        return view('mio.head.student-panel', [
            'page' => 'quiz',
            'quizzes' => $quizzes,
            'subjectId' => $subjectId,
            'subject' => $matchedSubject,
        ]);
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

        if ($selectedStudentId) {
            $submissionRef = $this->database
                ->getReference("subjects/{$gradeLevelKey}/{$subjectKey}/quizzes/{$quizId}/submissions/{$selectedStudentId}");
            $submission = $submissionRef->getValue();
        }

        $questions = isset($quiz['questions']) ? $quiz['questions'] : [];


        return view('mio.head.student-panel', [
            'page' => 'quiz-body',
            'quiz' => $quiz,
            'questions' => $quiz['questions'] ?? [],
            'subjectId' => $subjectId,
            'quizId' => $quizId,
            'gradeLevelKey' => $gradeLevelKey,
            'submission' => $submission,
            'selectedStudentId' => $selectedStudentId,
            'subject' => $subject,
        ]);
    }



    public function submitQuiz(Request $request, $subjectId, $quizId)
    {
    Log::info('All request data:', $request->all());

        $startTime = session('quiz_start_time');
        $endTime = now();

        $timeDiff = $startTime ? $endTime->diff($startTime) : null;

        $formattedTimeSpent = $timeDiff ? $timeDiff->format('%H:%I:%S') : null;
        $hours = $timeDiff ? $timeDiff->h : null;
        $minutes = $timeDiff ? $timeDiff->i : null;
        $seconds = $timeDiff ? $timeDiff->s : null;

        $studentId = session('firebase_user.uid');
        $studentName = session('firebase_user.first_name') . ' ' . session('firebase_user.last_name');

        if (!$studentId) {
            return back()->with('error', 'Session expired. Please login again.');
        }

        $subjectsRef = $this->database->getReference('subjects');
        $allSubjects = $subjectsRef->getValue() ?? [];

        $gradeLevelKey = null;
        $subjectKey = null;

        foreach ($allSubjects as $gradeKey => $subjects) {
            foreach ($subjects as $key => $subject) {
                if ($subject['subject_id'] === $subjectId) {
                    $gradeLevelKey = $gradeKey;
                    $subjectKey = $key;
                    break 2;
                }
            }
        }


        if (!$gradeLevelKey || !$subjectKey) {
            return back()->with('error', 'Subject not found.');
        }

        $quizRef = $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectKey}/quizzes/{$quizId}");
        $quiz = $quizRef->getValue();

        if (!$quiz) {
            return back()->with('error', 'Quiz not found.');
        }

        $maxAttempts = (int) ($quiz['attempts'] ?? 1);
        $studentAttempts = (int) ($quiz['people'][$studentId]['total_student_attempts'] ?? 0);


        if ($studentAttempts >= $maxAttempts) {
            return back()->with('error', 'Maximum attempts reached.');
        }

        // Answers submitted by student
        $answers = $request->input('answers', []);
        // Files submitted by student for file_upload questions
        Log::info('Submitted answers:', ['answers' => $answers]);


        $files = $request->file('answers', []);
        $questions = $quiz['questions'] ?? [];

        $score = 0;
        $totalPoints = 0;
        $submissionData = [];



        foreach ($questions as $questionId => $question) {
            $studentAnswer = $answers[$questionId] ?? null;
            $correctAnswer = $question['answer'] ?? '';
            $points = (float) ($question['points'] ?? 0);

            $totalPoints += $points;

            $isCorrect = null; // Default for non-auto-gradable or essay/file_upload questions

            $type = $question['type'] ?? '';

            if ($type === 'file_upload') {
                // Handle file upload question
                if (isset($files[$questionId]) && $files[$questionId]->isValid()) {
                    // Store the file and save the path as answer
                    $uploadedFilePath = $files[$questionId]->store('quiz_uploads', 'public');
                    $studentAnswer = $uploadedFilePath;
                } else {
                    $studentAnswer = null;
                }
            }

            Log::info("Question ID: {$questionId}, Type: {$type}, Student answer:", ['answer' => $studentAnswer]);

            // Auto-grade multiple choice and fill_in_the_blank
            if ($type === 'multiple_choice') {
                $isCorrect = ($studentAnswer === $correctAnswer);
                if ($isCorrect) {
                    $score += $points;
                }
            } elseif ($type === 'fill_in_the_blank') {
                // Case-insensitive trim compare
                $isCorrect = (strtolower(trim($studentAnswer)) === strtolower(trim($correctAnswer)));
                if ($isCorrect) {
                    $score += $points;
                }
            } elseif ($type === 'essay') {
                // No auto-grading: is_correct remains null, score not affected
                $isCorrect = null;
            } elseif ($type === 'file_upload') {
                // No auto-grading, is_correct remains null
                $isCorrect = null;
            } else {
                // For any unknown type, don't auto-grade
                $isCorrect = null;
            }

            $submissionData[$questionId] = [
                'question_id' => $questionId,
                'question' => $question['question'],
                'student_answer' => $studentAnswer,
                'correct_answer' => $correctAnswer,
                'is_correct' => $isCorrect,
                'points' => $points,
            ];
        }


        $attemptNumber = $studentAttempts + 1;
        $attemptId = 'ATTM' . now()->format('Ymd') . str_pad($attemptNumber, 3, '0', STR_PAD_LEFT);

        // Save the submission data
        $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectKey}/quizzes/{$quizId}/people/{$studentId}/{$attemptId}")
            ->set([
                'submitted_at' => now()->format('Y-m-d H:i:s'),
                'time_spent' => [
                    'formatted' => $formattedTimeSpent,
                    'hours' => $hours,
                    'minutes' => $minutes,
                    'seconds' => $seconds,
                ],
                'score' => $score,
                'total_points' => $totalPoints,
                'answers' => $submissionData,
            ]);

        // Update student's attempt count and status
        $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectKey}/quizzes/{$quizId}/people/{$studentId}")
            ->update([
                'status' => 'submitted',
                'total_student_attempts' => $attemptNumber,
                'name' => $studentName,
            ]);

        // Clear quiz start time session
        session()->forget('quiz_start_time');


        return redirect()->route('mio.subject.quiz', ['subjectId' => $subjectId])
            ->with('success', 'Quiz submitted successfully.');
    }


    public function saveAnswer(Request $request, $subjectId, $quizId, $questionId)
    {
        $studentId = session('firebase_user.uid');
        if (!$studentId) {
            return response()->json(['error' => 'Session expired'], 401);
        }



        $answer = $request->input('answer');
        $currentIndex = (int) $request->input('current_question_index', 0);

        // Find gradeLevelKey and subjectKey as in your submitQuiz function...

        // Save the answer and mark question as answered
        $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectKey}/quizzes/{$quizId}/people/{$studentId}/answers/{$questionId}")
            ->set([
                'answer' => $answer,
                'status' => 'answered',
                'answered_at' => now()->format('Y-m-d H:i:s'),
            ]);

        // Save current question index so you can resume
        $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectKey}/quizzes/{$quizId}/people/{$studentId}")
            ->update([
                'current_question_index' => $currentIndex,
                'status' => 'in_progress',
            ]);

        return response()->json(['message' => 'Answer saved']);
    }








    // ASSIGNMENTS

    public function showAssignment($subjectId)
    {
        $studentId = session('firebase_user')['uid'] ?? null;
        $studentSectionId = session('firebase_user')['section_id'] ?? null;

        if (!$studentId || !$studentSectionId) {
            return back()->with('error', 'Student session data missing.');
        }

        $subjectsRef = $this->database->getReference('subjects');
        $allSubjects = $subjectsRef->getValue() ?? [];

        $studentAssignments = [];
        $matchedSubject = null; // Add this line before the loops

        foreach ($allSubjects as $gradeKey => $subjects) {
            foreach ($subjects as $id => $subject) {
                if (
                    $id === $subjectId &&
                    isset($subject['section_id']) &&
                    $subject['section_id'] === $studentSectionId &&
                    isset($subject['people'][$studentId])
                ) {
                    $matchedSubject = $subject; // Save subject
                    if (isset($subject['assignments'])) {
                        foreach ($subject['assignments'] as $assignmentKey => $assignment) {
                            $assignment['id'] = $assignmentKey;
                            $assignment['subject_id'] = $subjectId;
                            $assignment['subject_title'] = $subject['title'] ?? 'Untitled Subject';

                            $assignment['student_data'] = $assignment['people'][$studentId] ?? [
                                'attempts' => 0,
                                'work' => '',
                                'comments' => '',
                                'feedback' => '',
                                'timestamp' => null,
                            ];

                            $studentAssignments[] = $assignment;
                        }
                    }
                }
            }
        }



        return view('mio.head.student-panel', [
            'page' => 'assignment',
            'assignments' => $studentAssignments,
            'subjectId' => $subjectId,
            'subject' => $matchedSubject
        ]);
    }

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
        $submissions = $submissionRef->getValue() ?? [];
        } else {
            $submissions = [];
        }

        $subject = $allSubjects[$gradeLevelKey][$subjectKey];


        return view('mio.head.student-panel', [
            'page' => 'assignment-body',
            'assignment' => $assignment,
            'subjectId' => $subjectId,
            'assignmentId' => $assignmentId,
            'gradeLevelKey' => $gradeLevelKey,
            'submission' => $submissions,
            'subject' => $subject,
        ]);
    }

    public function submitAssignment(Request $request, $subjectId, $assignmentId)
    {
        $request->validate([
            'work' => 'required|file|max:10240', // 10MB max
        ]);

        $studentId = session('firebase_user.uid');
        $studentSectionId = session('firebase_user.section_id');

        if (!$studentId || !$studentSectionId) {
            return back()->with('error', 'Student session data missing.');
        }

        // Upload the file to Laravel storage
        $file = $request->file('work');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('submissions', $fileName, 'public');
        $fileUrl = asset('storage/' . $filePath);

        // Find subject
        $subjectsRef = $this->database->getReference('subjects');
        $allSubjects = $subjectsRef->getValue() ?? [];

        foreach ($allSubjects as $gradeKey => $subjects) {
            foreach ($subjects as $key => $subject) {
                if ($key === $subjectId && isset($subject['assignments'][$assignmentId])) {
                    // Get current attempts
                    $currentData = $subject['assignments'][$assignmentId]['people'][$studentId] ?? [
                        'attempts' => 0,
                        'comments' => '',
                        'feedback' => '',
                        'name' => session('firebase_user.first_name') . ' ' . session('firebase_user.last_name'),
                    ];

                    $currentAttempts = $currentData['attempts'] ?? 0;

                    // Update submission
                    $this->database->getReference("subjects/{$gradeKey}/{$key}/assignments/{$assignmentId}/people/{$studentId}")
                        ->update([
                            'work' => $fileUrl,
                            'attempts' => $currentAttempts + 1,
                            'timestamp' => now()->format('Y-m-d H:i:s'),
                            'name' => $currentData['name'] ?? 'Student',
                        ]);

                    return back()->with('success', 'Assignment submitted successfully.');
                }
            }
        }

        return back()->with('error', 'Assignment or subject not found.');
    }

// SCORES

    public function showScores($subjectId)
{
    $studentId = session('firebase_user')['uid'] ?? null;
    $studentSectionId = session('firebase_user')['section_id'] ?? null;

    if (!$studentId || !$studentSectionId) {
        return back()->with('error', 'Student session data missing.');
    }

    $subjectsRef = $this->database->getReference('subjects');
    $allSubjects = $subjectsRef->getValue() ?? [];

    $assignmentScores = [];
    $quizScores = [];
    $matchedSubject = null;

    foreach ($allSubjects as $gradeKey => $subjects) {
        foreach ($subjects as $id => $subject) {
            if (
                $id === $subjectId &&
                isset($subject['section_id']) &&
                $subject['section_id'] === $studentSectionId &&
                isset($subject['people'][$studentId])
            ) {
                $matchedSubject = $subject;
                $matchedSubject['subject_id'] = $id;

                // Assignments
            if (isset($subject['assignments'])) {
                foreach ($subject['assignments'] as $assignmentId => $assignment) {
                    $studentData = $assignment['people'][$studentId] ?? null;

                    // Default score display
                    $scoreDisplay = '- / -';

                    // Use points from assignment node, not student node
                    if (isset($assignment['points'])) {
                        $earned = $studentData['score'] ?? '-';
                        $total = $assignment['points']['total'] ?? null;

                        if ($earned !== null && $total !== null) {
                            $scoreDisplay = "{$earned} / {$total}";
                        }
                    }

                    $assignmentScores[] = [
                        'title' => $assignment['title'] ?? 'Untitled',
                        'submitted' => $studentData && !empty($studentData['work']) ? 'Submitted' : '-',
                        'score' => $scoreDisplay,
                    ];
                }
            }

        // Quizzes
        if (isset($subject['quizzes'])) {
            foreach ($subject['quizzes'] as $quizId => $quiz) {
                $studentData = $quiz['people'][$studentId] ?? null;

                $scoreDisplay = '- / -';

                // Use points from quiz node, not student node
                if (isset($quiz['points'])) {
                    $earned = $studentData['score'] ?? '-';
                    $total = $quiz['points']['total'] ?? null;

                    if ($earned !== null && $total !== null) {
                        $scoreDisplay = "{$earned} / {$total}";
                    }
                }

                $quizScores[] = [
                    'title' => $quiz['title'] ?? 'Untitled',
                    'submitted' => $studentData && !empty($studentData['work']) ? 'Submitted' : '-',
                    'score' => $scoreDisplay,
                ];
            }
        }

            }
        }
    }

    return view('mio.head.student-panel', [
        'page' => 'scores',
        'assignmentScores' => $assignmentScores,
        'quizScores' => $quizScores,
        'subjectId' => $subjectId,
        'subject' => $matchedSubject,
    ]);
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
            return redirect()->route('mio.subject.announcements', ['subjectId' => $subjectId])->with('error', 'Subject not found.');

        }

        // Fetch announcements from correct path
        $announcementsRef = $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectId}/announcements");
        $announcementsSnapshot = $announcementsRef->getValue() ?? [];
        $announcements = [];

        foreach ($announcementsSnapshot as $key => $announcement) {
            $announcement['id'] = $key;
            $announcements[] = $announcement;
        }


        return view('mio.head.student-panel', [
            'page' => 'announcement',
            'subject' => $subject,
            'announcements' => $announcements,
             'subjectId' => $subjectId,
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
            return redirect()->route('mio.subject.announcements', ['subjectId' => $subjectId])->with('error', 'Announcement not found.');

        }

        return view('mio.head.student-panel', [
            'page' => 'announcement-body',
            'subject' => $subject,
            'announcement' => $announcement,
            'announcementId' => $announcementId,
             'subjectId' => $subjectId,
        ]);
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

        return view('mio.head.student-panel', [
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
        return redirect()->route('mio.student-panel')->with('error', 'Subject not found.');
    }

    // Get the module using the module index
    $module = $subject['modules'][$moduleIndex] ?? null;

    if (!$module) {
        return redirect()->route('mio.student-panel')->with('error', 'Module not found.');
    }

    return view('mio.head.student-panel', [
        'page' => 'module-body',
        'subject' => $subject,
        'module' => $module,
        'moduleIndex' => $moduleIndex,

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

    public function showProfile(){
        $userId = session('firebase_user.uid');
        $teacherName = session('firebase_user.name');

        // Get teacher data
        $teacherRef = $this->database->getReference('users/' . $userId);
        $teacher = $teacherRef->getValue();

        if (!$teacher) {
            abort(404, 'Teacher not found.');
        }

        // ✅ Get departments
        $departmentsRef = $this->database->getReference('departments');
        $departmentsData = $departmentsRef->getValue() ?? [];

        $departments = [];
        foreach ($departmentsData as $id => $dept) {
            $departments[] = [
                'departmentid' => $id,
                'department_name' => $dept['department_name'] ?? 'Unnamed'
            ];
        }

        return view('mio.head.teacher-panel', [
            'page' => 'profile',
            'teacher' => $teacher,
            'name' => $teacherName,
            'uid' => $userId,
            'departments' => $departments, // 👈 include this
        ]);
    }


    public function updateProfile(Request $request)
    {
        $userId = session('firebase_user.uid'); // Use session, not auth()->user()

        // Validate only editable fields (exclude readonly fields)
        $data = $request->validate([
            'bio' => 'nullable|string',
            'social_link' => 'nullable|url',
            'profile_picture' => 'nullable|image|max:2048',
        ]);

        try {
            // Handle profile picture upload if exists
            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $filename = 'images/profile_pictures/' . uniqid() . '.' . $file->getClientOriginalExtension();

                // Upload to Firebase Storage
                $bucket = $this->storageClient->bucket($this->bucketName);
                $bucket->upload(
                    fopen($file->getRealPath(), 'r'), // safer than file_get_contents
                    ['name' => $filename]
                );


                $object = $bucket->object($filename);

                // Make public (optional, if you're not using signed URLs)
                $object->update(['acl' => []], ['predefinedAcl' => 'PUBLICREAD']);

                $data['photo_url'] = 'https://storage.googleapis.com/' . $this->bucketName . '/' . $filename;

            }

            Log::info('Final data to update in Firebase:', $data);


            // Update only the fields provided by the form
            $this->database->getReference('users/' . $userId)->update($data);

            return redirect()->back()->with('success', 'Profile updated successfully.');

    } catch (\Throwable $e) {
        // Log error (optional)
        Log::error('Profile update failed: ' . $e->getMessage());

        return redirect()->back()->with('error', 'Failed to update profile. Please try again.');
    }
}





}
