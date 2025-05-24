<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Carbon\Carbon;


class SubjectController extends Controller
{
    protected $database;
    protected $tablename;

    protected $gradeLevelsTable = 'gradelevel'; // Firebase collection for grade levels

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

     // Fetch grade levels from Firebase and display them in the view
     public function showGradeLevels()
     {
        $gradeLevels = $this->database->getReference($this->gradeLevelsTable)->getSnapshot()->getValue();

        // Sort the grade levels from GR7 to GR10
        uksort($gradeLevels, function ($a, $b) {
            return (int) filter_var($a, FILTER_SANITIZE_NUMBER_INT) - (int) filter_var($b, FILTER_SANITIZE_NUMBER_INT);
        });

         return view('mio.head.admin-panel', ['page' => 'subjects'],compact('gradeLevels'));
     }

     // View subjects under a grade
    public function viewSubjects($grade)
    {
        $subjects = $this->database->getReference("subjects/{$grade}")->getSnapshot()->getValue() ?? [];
        $gradeLevel = $this->database->getReference("gradelevel/$grade")->getValue();

        return view('mio.head.admin-panel', [
            'page' => 'view-subject',
            'subjects' => $subjects,
            'gradeLevel' => $gradeLevel
        ], compact('subjects', 'grade'));
    }

    // Show add subject form
    public function showAddSubjectForm($grade)
        {
            // Get teachers from Firebase
            $teachersRaw = $this->database->getReference('users')->getValue() ?? [];

            $teachers = [];
            foreach ($teachersRaw as $key => $teacher) {
                if (isset($teacher['role']) && $teacher['role'] === 'teacher') {
                    $teachers[] = [
                        'teacherid' => $key,
                        'name' => ($teacher['fname'] ?? '') . ' ' . ($teacher['lname'] ?? '')
                    ];
                }
            }

            // Get sections from Firebase
            $sectionsRaw = $this->database->getReference('sections')->getValue() ?? [];

            $sections = [];
            foreach ($sectionsRaw as $key => $section) {
                $sections[] = [
                    'sectionid' => $key,
                    'name' => $section['section_name'] ?? 'Unnamed',
                    'status' => $section['section_status'] ?? 'unknown',
                    'teacherid' => $section['teacherid'] ?? null
                ];
            }

            return view('mio.head.admin-panel', [
                'page' => 'add-subjects',
                'teachers' => $teachers,
                'sections' => $sections,
                'grade' => $grade,
            ]);
        }

    public function addSubject(Request $request, $grade)
        {
           // Get current date components
            $now = now(); // Carbon instance
            $currentYear = $now->year;
            $currentMonth = str_pad($now->month, 2, '0', STR_PAD_LEFT); // Ensure month is two digits
            $currentDay = str_pad($now->day, 2, '0', STR_PAD_LEFT); // Ensure day is two digits
            $randomDigits = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);

            // Construct the custom key for the announcement (e.g., SUB-ANN20250511XXX)
            $announcementKey = "SUB-ANN{$currentYear}{$currentMonth}{$currentDay}{$randomDigits}";

            $validatedData = $request->validate([
                'subject_id' => 'required|string|max:100',
                'code' => 'required|string|max:50',
                'title' => 'required|string|max:255',
                'subjectType' => 'required|in:academics,specialized',
                'teacher_id' => 'required|string|max:50',
                'section_id' => 'required|string|max:50',
                'modules' => 'nullable|array',
                'modules.*.title' => 'required|string|max:255',
                'modules.*.description' => 'nullable|string',
                'modules.*.file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,mp4,zip|max:20480',

                // Announcement
                'announcement.title' => 'nullable|string|max:255',
                'announcement.description' => 'nullable|string|max:1000',
                'announcement.date' => 'nullable|date',
                'announcement.file' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:20480',
                'announcement.link' => 'nullable|url',
            ]);

            $subjectId = $validatedData['subject_id'];
            $subjectsRef = $this->database->getReference("subjects/{$grade}")->getValue();

            if (!empty($subjectsRef) && array_key_exists($subjectId, $subjectsRef)) {
                return redirect()->back()->with('status', 'Subject ID already exists!')->withInput();
            }

            // Find active school year
            $schoolYears = $this->database->getReference('schoolyears')->getValue();
            $activeSchoolYearId = null;

            if (!empty($schoolYears)) {
                foreach ($schoolYears as $id => $year) {
                    if (isset($year['status']) && $year['status'] === 'active') {
                        $activeSchoolYearId = $year['schoolyearid'];
                        break;
                    }
                }
            }

            if (!$activeSchoolYearId) {
                return redirect()->back()->with('status', 'No active school year found.')->withInput();
            }

            // Prepare base data
            $postData = [
                'subject_id' => $validatedData['subject_id'],
                'code' => $validatedData['code'],
                'title' => $validatedData['title'],
                'teacher_id' => $validatedData['teacher_id'],
                'section_id' => $validatedData['section_id'],
                'schoolyear_id' => $activeSchoolYearId,
                'subjectType' => $validatedData['subjectType'],
                'modules' => [],
                'assignments' => '',
                'scores' => '',
                'announcements' => [],
                'attendance' => '',
                'people' => [],
                'posted_by' => 'admin',
                'date_created' => Carbon::now()->toDateTimeString(),
                'date_updated' => Carbon::now()->toDateTimeString(),
            ];

            // Handle module uploads with keys like MOD00, MOD01
            if (isset($validatedData['modules'])) {
                $moduleDataArray = [];
                foreach ($validatedData['modules'] as $index => $module) {
                    $moduleKey = 'MOD' . str_pad($index, 2, '0', STR_PAD_LEFT); // e.g., MOD00, MOD01

                    $moduleData = [
                'title' => $module['title'],
                'description' => $module['description'] ?? '',
            ];

            if ($request->hasFile("modules.{$index}.file")) {
                $file = $request->file("modules.{$index}.file");
                $filePath = $file->storeAs('modules', $file->getClientOriginalName(), 'public');
                $moduleData['file'] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $filePath,
                    'url' => asset('storage/' . $filePath),
                ];
            }

            $moduleDataArray[$moduleKey] = $moduleData;
        }

        $postData['modules'] = $moduleDataArray;
    }


        // Handle single announcement
        if (isset($validatedData['announcement']['title']) && isset($validatedData['announcement']['description'])) {
            $announcementData = [
                'title' => $validatedData['announcement']['title'],
                'description' => $validatedData['announcement']['description'],
                'date_posted' => $validatedData['announcement']['date'] ?? Carbon::now()->toDateTimeString(),
                'subject_id' => $validatedData['subject_id'],  // Add subject_id here
            ];

            if ($request->hasFile('announcement.file')) {
                $file = $request->file('announcement.file');
                $filePath = $file->storeAs('announcements', $file->getClientOriginalName(), 'public');
                $announcementData['file'] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $filePath,
                    'url' => asset('storage/' . $filePath),
                ];
            }

            if (!empty($validatedData['announcement']['link'])) {
                $announcementData['link'] = $validatedData['announcement']['link'];
            }

            // Add the announcement with the custom key
        $postData['announcements'][$announcementKey] = $announcementData;
        }

        // Get section students
        $sectionRef = $this->database->getReference("sections/{$validatedData['section_id']}")->getValue();
        if (isset($sectionRef['students']) && is_array($sectionRef['students'])) {
            foreach ($sectionRef['students'] as $studentId => $value) {
                // Now $studentId is the actual ID like STU123456
                $studentData = $this->database->getReference("users/{$studentId}")->getValue();

                $postData['people'][] = [
                    'student_id' => $studentId,
                    'role' => 'student',
                    'first_name' => $studentData['fname'] ?? '',
                    'last_name' => $studentData['lname'] ?? '',
                ];

            }
        }

        // Add teacher to people
        $postData['people'][] = [
            'teacher_id' => $validatedData['teacher_id'],
            'role' => 'teacher',
        ];

        // Save to Firebase
        try {
            $this->database->getReference("subjects/{$grade}/{$subjectId}")->set($postData);
            return redirect()->route('mio.ViewSubject', ['grade' => $grade])
                            ->with('success', 'Subject added successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('status', 'Failed to add subject: ' . $e->getMessage())->withInput();
        }
    }


// DISPLAY EDIT SUBJECT
    public function showEditSubject($grade, $subject_id)
    {
        $subjectRef = $this->database->getReference("subjects/{$grade}/{$subject_id}")->getValue();

        $teachersRaw = $this->database->getReference('users')->getValue() ?? [];
        $teachers = [];

        foreach ($teachersRaw as $key => $teacher) {
            if (isset($teacher['role']) && $teacher['role'] === 'teacher') {
                $teachers[] = [
                    'teacherid' => $key,
                    'name' => trim(($teacher['fname'] ?? '') . ' ' . ($teacher['lname'] ?? '')),
                ];
            }
        }

        $sectionsRaw = $this->database->getReference('sections')->getValue() ?? [];
        $sections = [];

        $modules = $editdata['modules'] ?? [];

        foreach ($sectionsRaw as $key => $section) {
            $sections[] = [
                'sectionid' => $key,
                'name' => $section['section_name'] ?? 'Unnamed',
                'status' => $section['section_status'] ?? 'unknown',
                'teacherid' => $section['teacherid'] ?? null
            ];
        }

        if ($subjectRef) {
            return view('mio.head.admin-panel', [
                'page' => 'edit-subject',
                'grade' => $grade,
                'editdata' => $subjectRef,
                'subject_id' => $subject_id,
                'teachers' => $teachers,
                'sections' => $sections,
                'modules' => $modules,
            ]);
        } else {
            return redirect()->route('mio.ViewSubject', ['grade' => $grade])
                            ->with('status', 'Subject not found.');
        }
    }

 // EDIT SUBJECT
    public function editSubject(Request $request, $grade, $oldSubjectId)
    {
        $validatedData = $request->validate([
            'subject_id' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'teacher_id' => 'required|string|max:50',
            'section_id' => 'required|string|max:50',
            'subjectType' => 'required|in:academics,specialized',
            'modules' => 'nullable|array',
            'modules.*.title' => 'required|string|max:255',
            'modules.*.description' => 'nullable|string',
        ]);

        $newSubjectId = $validatedData['subject_id'];

        $subjects = $this->database->getReference("subjects/{$grade}")->getValue();

        // Check for duplicates if ID changed
        if ($oldSubjectId !== $newSubjectId && isset($subjects[$newSubjectId])) {
            return redirect()->back()->with('status', 'Subject ID already exists!')->withInput();
        }

        $existing = $subjects[$oldSubjectId] ?? [];

        $updateData = [
            'subject_id' => $newSubjectId,
            'code' => $validatedData['code'],
            'title' => $validatedData['title'],
            'teacher_id' => $validatedData['teacher_id'],
            'section_id' => $validatedData['section_id'],
            'subjectType' => $validatedData['subjectType'],
            'posted_by' => 'admin',
            'date_created' => $existing['date_created'] ?? Carbon::now()->toDateTimeString(),
            'date_updated' => Carbon::now()->toDateTimeString(),
        ];

        $updateData['modules'] = [];
        if (!empty($validatedData['modules'])) {
            foreach ($validatedData['modules'] as $module) {
                $updateData['modules'][] = [
                    'title' => $module['title'],
                    'description' => $module['description'] ?? '',
                ];
            }
        }

        // Update data in Firebase
        if ($oldSubjectId === $newSubjectId) {
            $this->database->getReference("subjects/{$grade}/{$oldSubjectId}")->update($updateData);
        } else {
            $this->database->getReference("subjects/{$grade}/{$newSubjectId}")->set($updateData);
            $this->database->getReference("subjects/{$grade}/{$oldSubjectId}")->remove();
        }

        return redirect()->route('mio.ViewSubject', ['grade' => $grade])
                        ->with('success', 'Subject updated successfully.');
    }



    // Delete subject
    public function deleteSubject($grade, $subjectId)
    {
        $path = "subjects/{$grade}/{$subjectId}";
        $delete = $this->database->getReference($path)->remove();

        if ($delete) {
            return redirect()->route('mio.ViewSubject', ['grade' => $grade])
                            ->with('status', 'Subject deleted successfully.');
        } else {
            return redirect()->route('mio.ViewSubject', ['grade' => $grade])
                            ->with('status', 'Failed to delete subject.');
        }
    }


}
