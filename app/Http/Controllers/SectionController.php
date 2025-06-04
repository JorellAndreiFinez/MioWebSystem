<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Database;
use Kreait\Firebase\Factory;
use Carbon\Carbon;

class SectionController extends Controller
{
    protected $database;
    protected $table = 'sections';

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

    public function sections()
    {
        // Fetch sections from the database
        $sections = $this->database->getReference($this->table)->getValue() ?? [];

        $teachersRaw = $this->database->getReference('users')->getValue() ?? [];

        $teachers = [];
        foreach ($teachersRaw as $key => $user) {
            if (isset($user['role']) && $user['role'] === 'teacher') {
                $teachers[] = [
                    'id' => $key,
                    'name' => ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')
                ];
            }
        }

        // Loop through sections and ensure 'teacherid' exists
        foreach ($sections as &$section) {
            if (!isset($section['teacherid'])) {
                $section['teacherid'] = null; // Set default if not found
            }
        }

        return view('mio.head.admin-panel', [
            'page' => 'view-section',
            'sections' => $sections,
            'teachers' => $teachers  // Pass teacher list to the view
        ]);
    }

    public function showAddSection()
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

        return view('mio.head.admin-panel', [
            'page' => 'add-section',
            'teachers' => $teachers
        ]);
    }

    public function addSection(Request $request)
{
    // Validate input based on HTML names
    $validated = $request->validate([
        'sectionid' => 'required|string|max:20',
        'section_name' => 'required|string|max:255',
        'status' => 'required|in:active,inactive',
        'section_status' => 'required|in:open,closed',
        'max_students' => 'required|integer|min:1',
        'teacherid' => 'nullable|string|max:50',
         'section_grade' => 'required|integer|min:1|max:10'
    ]);

    $sectionIdKey = $request->input('sectionid');

    // Fetch the active school year from Firebase
    $activeSchoolYearRef = $this->database->getReference('schoolyears');
    $schoolYears = $activeSchoolYearRef->getValue() ?? [];
    $activeSchoolYear = null;

    // Find the active school year
    foreach ($schoolYears as $schoolYear) {
        if ($schoolYear['status'] === 'active') {
            $activeSchoolYear = $schoolYear['schoolyearid'];
            break;
        }
    }

    if (!$activeSchoolYear) {
        return back()->with('status', 'No active school year found!')->withInput();
    }

    // Check for duplicate section ID in Firebase
    $existingSections = $this->database->getReference('sections')->getValue();
    if (!empty($existingSections) && array_key_exists($sectionIdKey, $existingSections)) {
        return back()->with('status', 'Section ID already exists!')->withInput();
    }

    // Prepare data to store, including schoolyear_id
    $postData = [
        'sectionid' => $sectionIdKey,
        'section_name' => $validated['section_name'],
        'status' => $validated['status'],
        'section_status' => $validated['section_status'],
        'max_students' => $validated['max_students'],
        'teacherid' => $validated['teacherid'] ?? null,
        'section_grade' => $validated['section_grade'],
        'students' => [],  // Initialize empty students array
        'schoolyear_id' => $activeSchoolYear,  // Include active school year
        'created_at' => Carbon::now()->toDateTimeString(),
        'updated_at' => Carbon::now()->toDateTimeString(),
    ];

    // Save to Firebase
    $this->database->getReference('sections/' . $sectionIdKey)->set($postData);

    return redirect()->route('mio.ViewSection')->with('success', 'Section added successfully.');
}


    // DISPLAY EDIT TEACHER
    public function showEditSection($id)
    {
        // Get all students
        $sections = $this->database->getReference($this->table)->getValue();
        $editdata = null;

        // Find the student by studentid
        if ($sections) {
            foreach ($sections as $key => $section) {
                if (isset($section['sectionid']) && $section['sectionid'] == $id) {
                    $editdata = $section;
                    $editdata['firebase_key'] = $key;  // Store Firebase key
                    break;
                }
            }
        }

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

        // If student data is found, return the view with the data
        if ($editdata) {
            return view('mio.head.admin-panel', [
                'page' => 'edit-section',
                'editdata' => $editdata,
                'teachers' => $teachers,
            ]);
        } else {
            return redirect('mio/admin/section')->with('status', 'Section ID Not Found');
        }
    }

    public function editSection(Request $request, $id)
    {
        $oldKey = $id; // Original section ID from URL
        $newKey = $request->sectionid; // Possibly updated section ID

        // Validate input
        $validated = $request->validate([
            'sectionid' => 'required|string|max:20',
            'section_name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'section_status' => 'required|in:open,closed',
            'max_students' => 'required|integer|min:1',
            'teacherid' => 'nullable|string|max:50',
            'section_grade' => 'nullable|integer|min:1|max:10'
        ]);

        // Reference to Firebase
        $sectionsRef = $this->database->getReference('sections')->getValue();

        // Check if section ID has changed and the new key already exists
        if ($oldKey !== $newKey && !empty($sectionsRef) && array_key_exists($newKey, $sectionsRef)) {
            return redirect()->back()->with('status', 'Section ID already exists!')->withInput();
        }

        // Fetch the active school year from Firebase
        $activeSchoolYearRef = $this->database->getReference('schoolyears');
        $schoolYears = $activeSchoolYearRef->getValue() ?? [];
        $activeSchoolYear = null;

        // Find the active school year
        foreach ($schoolYears as $schoolYear) {
            if ($schoolYear['status'] === 'active') {
                $activeSchoolYear = $schoolYear['schoolyearid'];
                break;
            }
        }

        if (!$activeSchoolYear) {
            return back()->with('status', 'No active school year found!')->withInput();
        }

        // Prepare updated data
        $postData = [
            'sectionid' => $newKey,
            'section_name' => $validated['section_name'],
            'status' => $validated['status'],
            'section_status' => $validated['section_status'],
            'max_students' => $validated['max_students'],
            'teacherid' => $validated['teacherid'] ?? null,
            'section_grade' => $validated['section_grade'],
            'students' => $sectionsRef[$oldKey]['students'] ?? [],  // Keep existing students array
            'schoolyear_id' => $sectionsRef[$oldKey]['schoolyear_id'],
            'created_at' => $sectionsRef[$oldKey]['created_at'] ?? Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
            'schoolyearid' => $activeSchoolYear,
        ];

        // If section ID changed, remove old key and create new one
        if ($oldKey !== $newKey) {
            // Remove old
            $this->database->getReference('sections/' . $oldKey)->remove();
        }

        // Save under new or same key
        $this->database->getReference('sections/' . $newKey)->set($postData);

        return redirect()->route('mio.ViewSection')->with('status', 'Section updated successfully!');
    }



// DELETE SECTION
    public function deleteSection($id)
    {
        $key = $id;
        $del_data = $this->database->getReference($this->table.'/'.$key)->remove();

        if ($del_data) {
            return redirect('mio/admin/section')->with('status', 'Section Deleted Successfully');
        } else {
            return redirect('mio/admin/section')->with('status', 'Section Not Deleted');
        }
   }
}
