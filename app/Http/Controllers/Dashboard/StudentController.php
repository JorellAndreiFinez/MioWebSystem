<?php
namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Database;
use Kreait\Firebase\Factory;

class StudentController extends Controller
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

        // Only add subjects for the active school year
        $gradeSubjects = array_filter($subjects, function($subject) use ($activeSchoolYear) {
            return isset($subject['schoolyear_id']) && $subject['schoolyear_id'] === $activeSchoolYear;
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

    // Fetch the current logged-in user's section_id
    $userSectionId = session('firebase_user')['section_id'] ?? null; // Default to null if section_id is not found

    // Filter active sections based on the logged-in user's section_id
    $filteredSections = array_filter($activeSections, function($section) use ($userSectionId) {
        return $section['sectionid'] === $userSectionId;
    });

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
            if (isset($subject['section_id']) && $subject['section_id'] === $userSectionId) {
                if (isset($subject['modules']) && !empty($subject['modules'])) {
                    $modulesForUserSection[] = $subject['modules'];
                }
            }
        }
    }

    // Pass filtered data to the view
    return view('mio.head.student-panel', [
        'page' => 'dashboard',
        'subjects' => $modulesForUserSection, // Display only the modules related to the user's section
        'allSubjects' => $allSubjects,
        'activeSchoolYear' => $activeSchoolYear,
        'activeSections' => $filteredSections, // Display only the filtered sections for the logged-in user
        'sectionUsers' => $sectionUsers
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
            return redirect()->route('mio.student-panel')->with('error', 'Subject not found.');
        }

        // Fetch announcements from correct path
        $announcementsRef = $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectId}/announcements");
        $announcements = $announcementsRef->getValue() ?? [];

        return view('mio.head.student-panel', [
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
            return redirect()->route('mio.student-panel')->with('error', 'Announcement not found.');
        }

        return view('mio.head.student-panel', [
            'page' => 'announcement-body',
            'subject' => $subject,
            'announcement' => $announcement,
            'announcementId' => $announcementId,
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









}
