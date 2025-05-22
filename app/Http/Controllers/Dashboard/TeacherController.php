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


class TeacherController extends Controller
{

    protected $database;

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
        $quizData = $request->input('quiz');
        $questions = $request->input('questions');

        // 1. Locate the subject’s gradeLevelKey
        $subjectsRef = $this->database->getReference('subjects');
        $allSubjects = $subjectsRef->getValue() ?? [];

        $gradeLevelKey = null;
        foreach ($allSubjects as $grade => $subjects) {
            if (isset($subjects[$subjectId])) {
                $gradeLevelKey = $grade;
                break;
            }
        }

        if (!$gradeLevelKey) {
            return back()->with('error', 'Subject not found.');
        }

        // 2. Generate quizId in the format QU[YEAR][MONTH][DAY]XXX
        $today = Carbon::now()->format('Ymd'); // e.g., 20250518
        $prefix = 'QU' . $today;

        // Fetch existing quizzes under this subject to find matching prefix
        $quizzesRef = $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectId}/quizzes");
        $existingQuizzes = $quizzesRef->getValue() ?? [];

        $count = 0;
        foreach (array_keys($existingQuizzes) as $existingId) {
            if (Str::startsWith($existingId, $prefix)) {
                $count++;
            }
        }

        $quizId = $prefix . str_pad($count + 1, 3, '0', STR_PAD_LEFT); // e.g., QU20250518001

        // 3. Fetch students and prepare people field
        $studentsRef = $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectId}/people");
        $students = $studentsRef->getValue() ?? [];

        $people = [];
        foreach ($students as $studentId => $studentInfo) {
            $people[$studentId] = [
                'work' => '',
                'name' => ($studentInfo['first_name'] ?? '') . ' ' . ($studentInfo['last_name'] ?? ''),
                'submitted_at' => null,
                'attempts' => 0,
                'comments' => '',
                'feedback' => '',
                'score' => null,
                'timestamp' => now()->toDateTimeString(),
            ];
        }

        // 4. Prepare quiz payload
        $quizPayload = [
            'title' => $quizData['title'] ?? '',
            'description' => $quizData['description'] ?? '',
            'publish_date' => $quizData['publish_date'],
            'start_time' => $quizData['start_time'],
            'deadline' => $quizData['deadline_date'] ?? null,
            'end_time' => $quizData['end_time'] ?? null,
            'time_limit' => (int) $quizData['time_limit'],
            'total' => (int) $quizData['total_points'],
            'attempts' => (int) $quizData['attempts'],
            'created_at' => Carbon::now()->toDateTimeString(),
            'questions' => [],
            'people' => $people, // ✅ add populated student info here
        ];

        // 5. Attach questions
        foreach ($questions as $index => $q) {
            $questionId = 'q' . ($index + 1);
            $question = [
                'type' => $q['type'] ?? 'multiple_choice',
                'question' => $q['question'] ?? '',
                'answer' => $q['answer'] ?? '',
            ];

            if (isset($q['options']) && is_array($q['options'])) {
                $question['options'] = $q['options'];
            }

            $quizPayload['questions'][$questionId] = $question;
        }

        // 6. Save quiz to Firebase
        $quizRef = $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectId}/quizzes/{$quizId}");
        $quizRef->set($quizPayload);

        return redirect()->route('mio.subject-teacher.quiz', $subjectId)->with('success', 'Quiz successfully created.');
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
        ]);
    }

    public function editQuiz(Request $request, $subjectId, $quizId)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'deadline_date' => 'nullable|date',
            'start_time' => 'required',
            'end_time' => 'nullable',
            'time_limit' => 'required|integer',
            'total_points' => 'required|integer',
            'attempts' => 'required|integer',
            'publish_date' => 'required|date',
            'questions' => 'nullable|array',
            'questions.*.type' => 'required|string',
            'questions.*.question' => 'required|string',
            'questions.*.options' => 'nullable|array',
            'questions.*.answer' => 'nullable|string',
        ]);

        // Locate subject in Firebase
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

        $quizPath = "subjects/{$gradeLevelKey}/{$subjectKey}/quizzes/{$quizId}";
        $existingQuiz = $this->database->getReference($quizPath)->getValue();

        // Combine publish_date and start_time
        $publishedAt = "{$validated['publish_date']} {$validated['start_time']}";
        $deadline = $validated['deadline_date'] ?? '';
        $endTime = $validated['end_time'] ?? '';

        // Build formatted questions
        $questions = [];
        if (!empty($validated['questions'])) {
            $qIndex = 1;
            foreach ($validated['questions'] as $q) {
                $question = [
                    'type' => $q['type'],
                    'question' => $q['question'],
                ];

                if (in_array($q['type'], ['multiple_choice', 'dropdown'])) {
                    $question['options'] = $q['options'] ?? [];
                    $question['answer'] = $q['answer'] ?? '';
                } elseif ($q['type'] === 'fill_blank') {
                    $question['answer'] = $q['answer'] ?? '';
                }

                $questions["q{$qIndex}"] = $question;
                $qIndex++;
            }
        }

        // Build the full quiz data
        $updatedData = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? '',
            'deadline' => $deadline,
            'start_time' => $validated['start_time'],
            'end_time' => $endTime,
            'time_limit' => $validated['time_limit'],
            'total' => $validated['total_points'],
            'attempts' => $validated['attempts'],
            'publish_date' => $validated['publish_date'],
            'created_at' => $existingQuiz['created_at'] ?? now()->toDateTimeString(),
            'questions' => $questions,
        ];

        // Preserve existing fields
        if (isset($existingQuiz['people'])) {
            $updatedData['people'] = $existingQuiz['people'];
        }
        if (isset($existingQuiz['attachments'])) {
            $updatedData['attachments'] = $existingQuiz['attachments'];
        }

        // Save to Firebase
        $this->database->getReference($quizPath)->update($updatedData);

        return redirect()->route('mio.subject-teacher.edit-acads-quiz', [
            'subjectId' => $subjectId,
            'quizId' => $quizId,
        ])->with('success', 'Quiz updated successfully.');

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
