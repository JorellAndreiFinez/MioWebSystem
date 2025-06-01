<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Database;
use Kreait\Firebase\Factory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DepartmentController extends Controller
{
    protected $database;
    protected $table = 'departments';

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

    public function departments()
    {

        // Fetch sections from the database
        $departments = $this->database->getReference($this->table)->getValue() ?? [];

        return view('mio.head.admin-panel', [
            'page' => 'view-department',
            'departments' => $departments
        ]);
    }

    public function showAddDepartment()
    {
        $teachersRaw = $this->database->getReference('users')->getValue() ?? [];
        $departmentsRaw = $this->database->getReference('departments')->getValue() ?? [];

        // Create mapping of teacherid => department_name
        $teacherDepartmentMap = [];
        foreach ($departmentsRaw as $dept) {
            if (!empty($dept['teacherid'])) {
                $teacherDepartmentMap[$dept['teacherid']] = $dept['department_name'];
            }
        }

        $teachers = [];
        foreach ($teachersRaw as $key => $teacher) {
            if (isset($teacher['role']) && $teacher['role'] === 'teacher') {
                $teachers[] = [
                    'teacherid' => $key,
                    'name' => ($teacher['fname'] ?? '') . ' ' . ($teacher['lname'] ?? ''),
                    'departmentname' => $teacherDepartmentMap[$key] ?? 'Unassigned'
                ];
            }
        }

        return view('mio.head.admin-panel', [
            'page' => 'add-department',
            'teachers' => $teachers
        ]);
    }

    public function addDepartment(Request $request)
    {
        // Validate input based on HTML names
        $validated = $request->validate([
            'departmentid' => 'required|string|max:20',
            'department_name' => 'required|string|max:255',
            'department_type' => 'required|string|in:academic,admin_support,specialized',
            'department_code' => 'required|string|max:50',
            'description' => 'nullable|string|max:1000',
            'teacherid' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
        ]);

        $departmentIdKey = $request->input('departmentid');

        // Check for duplicate section ID in Firebase
        $existingDepartment = $this->database->getReference('departments')->getValue();
        if (!empty($existingDepartment) && array_key_exists($departmentIdKey, $existingDepartment)) {
            return back()->with('status', 'Department ID already exists!')->withInput();
        }

        // Get the active school year
        $schoolYears = $this->database->getReference('schoolyears')->getValue();
        Log::info($schoolYears);
        $activeSchoolYearId = null;

        if (!empty($schoolYears)) {
            foreach ($schoolYears as $key => $sy) {
            if (isset($sy['status']) && $sy['status'] === 'active') {
                $activeSchoolYearId = $key; // use the key as schoolyearid
                break;
            }
        }

        }

        if (!$activeSchoolYearId) {
            return back()->with('status', 'No active school year found. Please set an active school year first.')->withInput();
        }

        // Prepare data to store
        $postData = [
            'departmentid' => $departmentIdKey,
            'department_name' => $validated['department_name'],
            'department_type' => $validated['department_type'],
            'department_code' => $validated['department_code'],
            'description' => $validated['description'] ?? '',
            'teacherid' => $validated['teacherid'] ?? null,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
            'schoolyearid' => $activeSchoolYearId,
            'status' => $validated['status'],
        ];

        // Save to Firebase
        $this->database->getReference('departments/' . $departmentIdKey)->set($postData);

        return redirect()->route('mio.ViewDepartment')->with('success', 'Department added successfully.');
    }

    // DISPLAY EDIT DEPARTMENT
    public function showEditDepartment($id)
    {
        // Get all departments
        $departments = $this->database->getReference($this->table)->getValue();
        $editdata = null;

        // Find the department by departmentid
        if ($departments) {
            foreach ($departments as $key => $department) {
                if (isset($department['departmentid']) && $department['departmentid'] == $id) {
                    $editdata = $department;
                    $editdata['firebase_key'] = $key;  // Store Firebase key
                    break;
                }
            }
        }

        // Get all teachers
        $teachersRaw = $this->database->getReference('users')->getValue() ?? [];
        $departmentsRaw = $this->database->getReference('departments')->getValue() ?? [];

        // Map each teacher ID to their department name (if assigned)
        $teacherDepartmentMap = [];
        foreach ($departmentsRaw as $dept) {
            if (!empty($dept['teacherid'])) {
                $teacherDepartmentMap[$dept['teacherid']] = $dept['department_name'];
            }
        }

        $teachers = [];
        foreach ($teachersRaw as $key => $teacher) {
            if (isset($teacher['role']) && $teacher['role'] === 'teacher') {
                $teachers[] = [
                    'teacherid' => $key,
                    'name' => ($teacher['fname'] ?? '') . ' ' . ($teacher['lname'] ?? ''),
                    'departmentname' => $teacherDepartmentMap[$key] ?? 'Unassigned'
                ];
            }
        }

        if ($editdata) {
            return view('mio.head.admin-panel', [
                'page' => 'edit-department',
                'editdata' => $editdata,
                'teachers' => $teachers,
            ]);
        } else {
            return redirect('mio/admin/departments')->with('status', 'Department ID Not Found');
        }
    }


    public function editDepartment(Request $request, $id)
    {
        $oldKey = $id; // Original section ID from URL
        $newKey = $request->departmentid; // Possibly updated section ID

        // Validate input
       $validated = $request->validate([
            'departmentid' => 'required|string|max:20',
            'department_name' => 'required|string|max:255',
            'department_type' => 'required|string|in:academic,admin_support,specialized',
            'department_code' => 'required|string|max:50',
            'description' => 'nullable|string|max:1000',
            'teacherid' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
        ]);

        // Reference to Firebase
        $departmentsRef = $this->database->getReference($this->table)->getValue();

        // Check if section ID has changed and the new key already exists
        if ($oldKey !== $newKey && !empty($departmentsRef) && array_key_exists($newKey, $departmentsRef)) {
            return redirect()->back()->with('status', 'Department ID already exists!')->withInput();
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
            'departmentid' => $newKey,
            'department_name' => $validated['department_name'],
            'department_type' => $validated['department_type'],
            'department_code' => $validated['department_code'],
            'description' => $validated['description'] ?? '',
            'teacherid' => $validated['teacherid'] ?? null,
            'created_at' => $sectionsRef[$oldKey]['created_at'] ?? Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
            'status' => $validated['status'],
            'schoolyearid' => $activeSchoolYear
        ];

        // If section ID changed, remove old key and create new one
        if ($oldKey !== $newKey) {
            // Remove old
            $this->database->getReference($this->table . '/' . $oldKey)->remove();
        }

        // Save under new or same key
        $this->database->getReference($this->table . '/' . $newKey)->set($postData);

        return redirect()->route('mio.ViewDepartment')->with('status', 'Department updated successfully!');
    }


// DELETE DEPARTMENT
    public function deleteDepartment($id)
    {
        $key = $id;
        $del_data = $this->database->getReference($this->table.'/'.$key)->remove();

        if ($del_data) {
            return redirect('mio/admin/department')->with('status', 'Department Deleted Successfully');
        } else {
            return redirect('mio/admin/department')->with('status', 'Department Not Deleted');
        }
   }
}


