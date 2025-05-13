<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Database;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Log;


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
            'announcements' => $announcements
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

        foreach ($subjectsData as $gradeLevel => $subjects) {
            foreach ($subjects as $subject) {
                if (
                    $subject['subject_id'] === $subjectId &&
                    isset($subject['modules']) &&
                    is_array($subject['modules'])  // check if modules are in array format
                ) {
                    foreach ($subject['modules'] as $index => $module) {
                        $modulesList[] = [
                            'title' => $module['title'] ?? 'Untitled Module',
                            'description' => $module['description'] ?? '',
                            'subject_id' => $subject['subject_id'],
                            'module_index' => $index
                        ];
                    }
                }
            }
        }

        return view('mio.head.student-panel', [
            'page' => 'module',
            'modules' => $modulesList,
            'subject_id' => $subjectId
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
            'moduleIndex' => $moduleIndex
        ]);
    }

    public function showProfile(){

    }



}
