<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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


    // Handle form submission
    public function addSubject(Request $request, $grade)
    {
        // Step 1: Validate all required fields
        $validatedData = $request->validate([
            'subject_id' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'teacher_id' => 'required|string|max:50',
            'section_id' => 'required|string|max:50',
            'modules' => 'nullable|array',
            'modules.*.title' => 'required|string|max:255',
            'modules.*.description' => 'nullable|string',
        ]);

        $subjectId = $validatedData['subject_id'];
        $subjectsRef = $this->database->getReference("subjects/{$grade}")->getValue();

        // Step 2: Check for duplicate subject ID
        if (!empty($subjectsRef) && array_key_exists($subjectId, $subjectsRef)) {
            return redirect()->back()->with('status', 'Subject ID already exists!')->withInput();
        }

        // Fetch all school years
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


        // Step 3: Format subject data
        $postData = [
            'subject_id' => $validatedData['subject_id'],
            'code' => $validatedData['code'],
            'title' => $validatedData['title'],
            'teacher_id' => $validatedData['teacher_id'],
            'section_id' => $validatedData['section_id'],
            'schoolyear_id' => $activeSchoolYearId,
            'modules' => [],
            'date_created' => Carbon::now()->toDateTimeString(),
            'date_updated' => Carbon::now()->toDateTimeString(),
        ];

        // Step 4: Append module data (if any)
        if (isset($validatedData['modules'])) {
            foreach ($validatedData['modules'] as $index => $module) {
                $postData['modules'][] = [
                    'title' => $module['title'],
                    'description' => $module['description'] ?? '',
                ];
            }
        }

        // Step 5: Save to Firebase
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
