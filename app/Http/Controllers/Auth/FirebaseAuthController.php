<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Exception\Auth\InvalidPassword;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Auth as FirebaseAuth;
use Carbon\Carbon;
use Kreait\Firebase\Exception\Auth\EmailExists;

class FirebaseAuthController extends Controller
{
    protected $auth;
    protected $database;
    protected $tablename;

        public function __construct()
        {
            $path = base_path('storage/firebase/firebase.json');

            if(!file_exists($path)) {
                die("This File Path .{$path}. is not exists.");
            }

            $this->auth = (new Factory)
                    ->withServiceAccount($path)
                    ->createAuth();

            $this->database = (new Factory)
                    ->withServiceAccount($path)
                    ->withDatabaseUri('https://miolms-default-rtdb.firebaseio.com')
                    ->createDatabase();

            $this->tablename = 'users';
        }

        public function showAdminPanel()
        {
            // Get the total number of users per role
            $studentsCount = $this->database->getReference('users')->orderByChild('role')->equalTo('student')->getSnapshot()->numChildren();
            $teachersCount = $this->database->getReference('users')->orderByChild('role')->equalTo('teacher')->getSnapshot()->numChildren();
            $sectionsCount = $this->database->getReference('sections')->getSnapshot()->numChildren(); // Example for courses

            //  Fetch departments
            $departmentsRef = $this->database->getReference('departments')->getValue();
            $teachers = $this->database->getReference('users')->orderByChild('role')->equalTo('teacher')->getValue();

            // Pass data to the view
            return view('mio.head.admin-panel', [
                'page' => 'dashboard',
                'studentsCount' => $studentsCount,
                'teachersCount' => $teachersCount,
                'sectionsCount' => $sectionsCount,
                'departments' => $departmentsRef ?? [],
                'teachers' => $teachers ?? [],
            ]);
        }

    // STUDENT - PAGE
        public function students() {
            // Fetch all users
            $users = $this->database->getReference($this->tablename)->getValue();
            $users = $users ?? [];

            // Filter only students
            $students = array_filter($users, function($user) {
                return isset($user['role']) && $user['role'] === 'student';
            });

            return view('mio.head.admin-panel', ['page' => 'students'], compact('students'));
        }


    // DISPLAY ADD STUDENT
        public function showAddStudent(){
            $sectionsRaw = $this->database->getReference('sections')->getValue() ?? [];
            $sections = [];
            foreach ($sectionsRaw as $key => $sect) {
                $sections[] = [
                    'sectionid' => $sect['sectionid'],
                    'section_name' => $sect['section_name']
                ];
            }
            return view('mio.head.admin-panel', [
                'page' => 'add-student',
                'sections' => $sections
            ]);
        }

    // ADD STUDENT


public function addStudent(Request $request)
{
    $validatedData = $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'gender' => 'required|string|max:10',
        'age' => 'required|integer|min:1',
        'birthday' => 'required|date',
        'address' => 'required|string|max:255',
        'barangay' => 'required|string|max:255',
        'region' => 'required|string|max:100',
        'province' => 'required|string|max:100',
        'city' => 'required|string|max:100',
        'zip_code' => 'required|digits:4',
        'contact_number' => 'required|string|max:15',
        'emergency_contact' => 'required|string|max:15',
        'email' => 'required|email|max:255',
        'previous_school' => 'required|string|max:255',
        'grade_level' => 'required|integer|min:1',
        'studentid' => 'required|string|max:12',
        'category' => 'required|string',
        'username' => 'required|string|max:255',
        'account_password' => 'required|string|min:6',
        'account_status' => 'required|in:active,inactive',
        'section_id' => 'required|string|max:20',
    ]);

    $studentIdKey = $request->studentid;
    $sectionId = $request->section_id;

    // Check for existing UID or Email
    $students = $this->database->getReference('students')->getValue();
    if (!empty($students)) {
        foreach ($students as $student) {
            if ($student['studentid'] == $studentIdKey) {
                return redirect()->back()->with('status', 'Student ID already exists!')->withInput();
            }
            if ($student['email'] == $request->email) {
                return redirect()->back()->with('status', 'Email already exists!')->withInput();
            }
            if ($student['username'] == $request->username) {
                return redirect()->back()->with('status', 'Username already exists!')->withInput();
            }
        }
    }

    // Fetch section
    $section = $this->database->getReference('sections/' . $sectionId)->getValue();
    if (!$section) {
        return redirect()->back()->with('status', 'Section not found!')->withInput();
    }

    // Create Firebase Auth user
    try {
        $this->auth->createUser([
            'uid' => $studentIdKey,
            'email' => $request->email,
            'password' => $request->account_password,
            'displayName' => $request->first_name . ' ' . $request->last_name,
            'disabled' => $request->account_status === 'inactive',
        ]);
    } catch (\Kreait\Firebase\Exception\Auth\AuthError $e) {
        return redirect()->back()->with('status', 'Firebase Auth Error: ' . $e->getMessage())->withInput();
    }

    // Prepare Realtime DB data
    $postData = [
        'fname' => $request->first_name,
        'lname' => $request->last_name,
        'gender' => $request->gender,
        'age' => $request->age,
        'bday' => $request->birthday,
        'address' => $request->address,
        'barangay' => $request->barangay,
        'region' => $request->region,
        'province' => $request->province,
        'city' => $request->city,
        'zip_code' => $request->zip_code,
        'contact_number' => $request->contact_number,
        'emergency_contact' => $request->emergency_contact,
        'email' => $request->email,
        'previous_school' => $request->previous_school,
        'grade_level' => $request->grade_level,
        'category' => $request->category,
        'studentid' => $studentIdKey,
        'section_id' => $sectionId,
        'role' => 'student',

        'username' => $request->username,
        'password' => bcrypt($request->account_password),
        'account_status' => $request->account_status,

        'date_created' => Carbon::now()->toDateTimeString(),
        'date_updated' => Carbon::now()->toDateTimeString(),
        'last_login' => null
    ];

    // Save under users/{studentid}
    $this->database->getReference('users/' . $studentIdKey)->set($postData);

    // Add to section’s student list
    $this->database->getReference('sections/' . $sectionId . '/students')->push($studentIdKey);

    return redirect('mio/admin/students')->with('status', 'Student Added Successfully');
}




    // DISPLAY EDIT STUDENT
        public function showEditStudent($id)
        {
            // Get all students
            $students = $this->database->getReference($this->tablename)->getValue();
            $editdata = null;

            // Find the student by studentid
            if ($students) {
                foreach ($students as $key => $student) {
                    if (isset($student['studentid']) && $student['studentid'] == $id) {
                        $editdata = $student;
                        $editdata['firebase_key'] = $key;  // Store Firebase key
                        break;
                    }
                }
            }

            $sectionsRaw = $this->database->getReference('sections')->getValue() ?? [];
            $sections = [];
            foreach ($sectionsRaw as $key => $sect) {
                $sections[] = [
                    'sectionid' => $sect['sectionid'],
                    'section_name' => $sect['section_name']
                ];
            }

            // If student data is found, return the view with the data
            if ($editdata) {
                return view('mio.head.admin-panel', [
                    'page' => 'edit-student',
                    'editdata' => $editdata,
                    'sections' => $sections,  // Pass the student data including category
                ]);
            } else {
                return redirect('mio/admin/students')->with('status', 'Student ID Not Found');
            }
        }

    // EDIT STUDENT
    public function editStudent(Request $request, $id)
    {
        $oldKey = $id;
        $newKey = $request->studentid;

        // Validate the incoming request data
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|string|max:10',
            'age' => 'required|integer|min:1',
            'birthday' => 'required|date',
            'address' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
            'region' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'zip_code' => 'required|digits:4',
            'contact_number' => 'required|string|max:15',
            'emergency_contact' => 'required|string|max:15',
            'email' => 'required|email|max:255',
            'previous_school' => 'required|string|max:255',
            'grade_level' => 'required|integer|min:1',
            'studentid' => 'required|string|max:12',
            'category' => 'required|string',
            'username' => 'required|string|max:255',
            'account_password' => 'nullable|string|min:6',
            'account_status' => 'required|in:active,inactive',
        ]);


        $studentIdKey = $request->studentid;
        $emailInput = $request->email;
        $usernameInput = $request->username;

        // Fetch all students
        $studentsRef = $this->database->getReference($this->tablename)->getValue();

        if (!empty($studentsRef)) {
            foreach ($studentsRef as $key => $student) {
                if ($key !== $oldKey) {
                    if (isset($student['studentid']) && $student['studentid'] == $studentIdKey) {
                        return redirect()->back()->with('status', 'Student ID already exists!')->withInput();
                    }
                    if (isset($student['email']) && $student['email'] == $emailInput) {
                        return redirect()->back()->with('status', 'Email already exists!')->withInput();
                    }
                    if (isset($student['username']) && $student['username'] == $usernameInput) {
                        return redirect()->back()->with('status', 'Username already exists!')->withInput();
                    }
                }
            }
        }

        // Fetch active school year (if it's not passed in the request)
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

        // Get existing data to preserve date_created and last_login
        $existingData = $this->database->getReference($this->tablename.'/'.$oldKey)->getValue();

        // Prepare updated data
        $updateData = [
            'fname' => $request->first_name,
            'lname' => $request->last_name,
            'gender' => $request->gender,
            'age' => $request->age,
            'bday' => $request->birthday,
            'address' => $request->address,
            'barangay' => $request->barangay,
            'region' => $request->region,
            'province' => $request->province,
            'city' => $request->city,
            'zip_code' => $request->zip_code,
            'contact_number' => $request->contact_number,
            'emergency_contact' => $request->emergency_contact,
            'email' => $request->email,
            'previous_school' => $request->previous_school,
            'grade_level' => $request->grade_level,
            'category' => $request->category,
            'studentid' => $studentIdKey,
            'role' => 'student',
            'section_id' => $request->section_id,
            'schoolyear_id' => $activeSchoolYearId,  // Ensure this is included
            'username' => $usernameInput,
            'account_status' => $request->account_status,
            'date_updated' => Carbon::now()->toDateTimeString(),
        ];

        // Only update password if user entered a new one
        if ($request->filled('account_password')) {
            $updateData['password'] = bcrypt($request->account_password);
        } else {
            // Retain existing password if not updated
            if (isset($existingData['password'])) {
                $updateData['password'] = $existingData['password'];
            }
        }

        // Preserve date_created and last_login if available
        if (isset($existingData['date_created'])) {
            $updateData['date_created'] = $existingData['date_created'];
        }
        if (isset($existingData['last_login'])) {
            $updateData['last_login'] = $existingData['last_login'];
        }

        // Update Firebase data
        if ($oldKey === $newKey) {
            $this->database->getReference($this->tablename.'/'.$oldKey)->update($updateData);
        } else {
            $this->database->getReference($this->tablename.'/'.$newKey)->set($updateData);
            $this->database->getReference($this->tablename.'/'.$oldKey)->remove();
        }

        return redirect('mio/admin/students')->with('status', 'Student Updated Successfully');
    }


    // DELETE STUDENT
        public function deleteStudent($id)
        {
            $key = $id;

            // Delete from Realtime Database
            $del_data = $this->database->getReference($this->tablename.'/'.$key)->remove();

            if ($del_data) {
                try {
                    // Delete from Firebase Authentication
                    $this->auth->deleteUser($key); // Assuming $key is also the Firebase UID

                    return redirect('mio/admin/students')->with('status', 'Student Info and Account Deleted Successfully');
                } catch (UserNotFound $e) {
                    return redirect('mio/admin/students')->with('status', 'Student deleted, but Auth user not found');
                } catch (Exception $e) {
                    return redirect('mio/admin/students')->with('status', 'Student deleted, but Auth user deletion failed: '.$e->getMessage());
                }
            } else {
                return redirect('mio/admin/students')->with('status', 'Student Not Deleted');
            }
        }

    // ------ TEACHER - PAGE
        public function teachers() {
            // Fetch all users
            $users = $this->database->getReference($this->tablename)->getValue();
            $users = $users ?? [];

            // Filter only teachers
            $teachers = array_filter($users, function($user) {
                return isset($user['role']) && $user['role'] === 'teacher';
            });

            return view('mio.head.admin-panel', ['page' => 'teachers'], compact('teachers'));
        }

        public function showAddTeacher(){
            $usersRaw = $this->database->getReference('users')->getValue() ?? [];
            $departmentsRaw = $this->database->getReference('departments')->getValue() ?? [];

            $teachers = [];
            foreach ($usersRaw as $key => $user) {
                if (isset($user['role']) && $user['role'] === 'teacher') {
                    $teachers[] = [
                        'teacherid' => $key,
                        'name' => ($user['fname'] ?? '') . ' ' . ($user['lname'] ?? '')
                    ];
                }
            }

            $departments = [];
            foreach ($departmentsRaw as $key => $dept) {
                $departments[] = [
                    'departmentid' => $dept['departmentid'],
                    'department_name' => $dept['department_name']
                ];
            }

            return view('mio.head.admin-panel', [
                'page' => 'add-teacher',
                'teachers' => $teachers,
                'departments' => $departments
            ]);
        }

    // ADD Teacher
        public function addTeacher(Request $request)
        {
            // Validate basic fields first
            $validatedData = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'gender' => 'required|string|max:10',
                'age' => 'required|integer|min:1',
                'birthday' => 'required|date',
                'address' => 'required|string|max:255',
                'barangay' => 'required|string|max:255',
                'region' => 'required|string|max:100',
                'province' => 'required|string|max:100',
                'city' => 'required|string|max:100',
                'zip_code' => 'required|digits:4',
                'contact_number' => 'required|string|max:15',
                'emergency_contact' => 'required|string|max:15',
                'email' => 'required|email|max:255',
                'previous_school' => 'required|string|max:255',
                'grade_level' => 'required|integer|min:1',
                'schedule' => 'required|array',
                'teacherid' => 'required|string|max:12',
                'category' => 'required|string',
                'username' => 'required|string|max:255',
                'account_password' => 'required|string|min:6',
                'account_status' => 'required|string|in:active,inactive',
                'department_id' => 'required|string|max:20',

            ]);


            $teacherIdKey = $request->teacherid;
            $emailInput = $request->email;

            $teachersRef = $this->database->getReference($this->tablename)->getValue();

            // Check if Teacher ID or Email exists
            if (!empty($teachersRef)) {
                foreach ($teachersRef as $teacher) {
                    if (isset($teacher['teacherid']) && $teacher['teacherid'] == $teacherIdKey) {
                        return redirect()->back()->with('status', 'Teacher ID already exists!')->withInput();
                    }
                    if (isset($teacher['email']) && $teacher['email'] == $emailInput) {
                        return redirect()->back()->with('status', 'Email already exists!')->withInput();
                    }
                    if (isset($teacher['username']) && $teacher['username'] == $request->username) {
                        return redirect()->back()->with('status', 'Username already exists!')->withInput();
                    }
                }
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


            // Prepare data
            $postData = [
                'fname' => $request->first_name,
                'lname' => $request->last_name,
                'gender' => $request->gender,
                'age' => $request->age,
                'bday' => $request->birthday,
                'address' => $request->address,
                'barangay' => $request->barangay,
                'region' => $request->region,
                'province' => $request->province,
                'city' => $request->city,
                'zip_code' => $request->zip_code,
                'contact_number' => $request->contact_number,
                'emergency_contact' => $request->emergency_contact,
                'email' => $request->email,
                'previous_school' => $request->previous_school,
                'grade_level' => $request->grade_level,
                'category' => $request->category,
                'schedule' => $request->schedule,
                'teacherid' => $teacherIdKey,
                'role' => 'teacher',
                'username' => $request->username,
                'password' => bcrypt($request->account_password), // <- note: bcrypt account_password
                'account_status' => $request->account_status,
                'department_id' => $request->department_id,
                'schoolyear_id' => $activeSchoolYearId,

                // Timestamps
                'date_created' => Carbon::now()->toDateTimeString(),
                'date_updated' => Carbon::now()->toDateTimeString(),
                'last_login' => null // Leave empty on creation
            ];

            try {
                // Create Firebase Auth user with student_id as UID
                $this->auth->createUser([
                    'uid' => $request->teacherid,
                    'email' => $request->email,
                    'password' => $request->account_password,
                    'displayName' => $request->first_name . ' ' . $request->last_name,
                    'disabled' => $request->account_status === 'inactive',
                ]);
            } catch (\Kreait\Firebase\Exception\Auth\AuthError $e) {
                return redirect()->back()->with('status', 'Firebase Auth Error: ' . $e->getMessage())->withInput();
            }

            // Proceed to save to Realtime Database as before

            $postRef = $this->database->getReference($this->tablename.'/'.$teacherIdKey)->set($postData);

            if ($postRef) {
                return redirect('mio/admin/teachers')->with('status', 'Teacher Added Successfully');
            } else {
                return redirect('mio/admin/teachers')->with('status', 'Teacher Not Added');
            }
        }

    // DISPLAY EDIT TEACHER
        public function showEditTeacher($id)
        {
            $teachers = $this->database->getReference($this->tablename)->getValue();
            $editdata = null;

            if ($teachers) {
                foreach ($teachers as $key => $teacher) {
                    if (isset($teacher['teacherid']) && $teacher['teacherid'] == $id) {
                        $editdata = $teacher;
                        $editdata['firebase_key'] = $key;
                        break;
                    }
                }
            }

            if ($editdata) {
                // Fetch departments
                $departmentsRaw = $this->database->getReference('departments')->getValue() ?? [];
                $departments = [];

                foreach ($departmentsRaw as $dept) {
                    $departments[] = [
                        'departmentid' => $dept['departmentid'],
                        'department_name' => $dept['department_name']
                    ];
                }

                return view('mio.head.admin-panel', [
                    'page' => 'edit-teacher',
                    'editdata' => $editdata,
                    'departments' => $departments
                ]);
            } else {
                return redirect('mio/admin/teachers')->with('status', 'Teacher ID Not Found');
            }
        }


    // EDIT TEACHER
        public function editTeacher(Request $request, $id)
        {
            $oldKey = $id;
            $newKey = $request->teacherid;

            $validatedData = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'gender' => 'required|string|max:10',
                'age' => 'required|integer|min:1',
                'birthday' => 'required|date',
                'address' => 'required|string|max:255',
                'barangay' => 'required|string|max:255',
                'region' => 'required|string|max:100',
                'province' => 'required|string|max:100',
                'city' => 'required|string|max:100',
                'zip_code' => 'required|digits:4',
                'contact_number' => 'required|string|max:15',
                'emergency_contact' => 'required|string|max:15',
                'email' => 'required|email|max:255',
                'previous_school' => 'required|string|max:255',
                'grade_level' => 'required|integer|min:1',
                'schedule' => 'required|array',
                'teacherid' => 'required|string|max:12',
                'category' => 'required|string',
                'username' => 'required|string|max:255',
                'account_status' => 'required|string|in:active,inactive',
                'account_password' => 'nullable|string|min:6', // Optional, only if changing password
                'department_id' => 'required|string|max:20',

            ]);

            $teacherIdKey = $request->teacherid;
            $emailInput = $request->email;
            $usernameInput = $request->username;

            $teachersRef = $this->database->getReference($this->tablename)->getValue();

            if (!empty($teachersRef)) {
                foreach ($teachersRef as $key => $teacher) {
                    if ($key !== $oldKey) {
                        if (isset($teacher['teacherid']) && $teacher['teacherid'] == $teacherIdKey) {
                            return redirect()->back()->with('status', 'Teacher ID already exists!')->withInput();
                        }
                        if (isset($teacher['email']) && $teacher['email'] == $emailInput) {
                            return redirect()->back()->with('status', 'Email already exists!')->withInput();
                        }
                        if (isset($teacher['username']) && $teacher['username'] == $usernameInput) {
                            return redirect()->back()->with('status', 'Username already exists!')->withInput();
                        }
                    }
                }
            }

             // Get existing data to preserve date_created and last_login
             $existingData = $this->database->getReference($this->tablename.'/'.$oldKey)->getValue();

            // Prepare updated data
            $updateData = [
                'fname' => $request->first_name,
                'lname' => $request->last_name,
                'gender' => $request->gender,
                'age' => $request->age,
                'bday' => $request->birthday,
                'address' => $request->address,
                'barangay' => $request->barangay,
                'region' => $request->region,
                'province' => $request->province,
                'city' => $request->city,
                'zip_code' => $request->zip_code,
                'contact_number' => $request->contact_number,
                'emergency_contact' => $request->emergency_contact,
                'email' => $request->email,
                'previous_school' => $request->previous_school,
                'grade_level' => $request->grade_level,
                'category' => $request->category,
                'schedule' => $request->schedule,
                'teacherid' => $teacherIdKey,
                'role' => 'teacher',
                'username' => $usernameInput,
                'account_status' => $request->account_status,
                'date_updated' => Carbon::now()->toDateTimeString(),
                'department_id' => $request->department_id,

            ];

            // Only update password if user entered a new one
            if ($request->filled('account_password')) {
                $updateData['password'] = bcrypt($request->account_password);
            } else {
                // Retain existing password if not updated
                if (isset($existingData['password'])) {
                    $updateData['password'] = $existingData['password'];
                }
            }

            if ($oldKey === $newKey) {
                $this->database->getReference($this->tablename.'/'.$oldKey)->update($updateData);
            } else {
                $this->database->getReference($this->tablename.'/'.$newKey)->set($updateData);
                $this->database->getReference($this->tablename.'/'.$oldKey)->remove();
            }

            return redirect('mio/admin/teachers')->with('status', 'Teacher Updated Successfully');
        }

    // DELETE TEACHER
        public function deleteTeacher($id)
        {
            $key = $id;

            // Delete from Realtime Database
            $del_data = $this->database->getReference($this->tablename.'/'.$key)->remove();

            if ($del_data) {
                try {
                    // Delete from Firebase Authentication
                    $this->auth->deleteUser($key); // $key must be the Firebase Auth UID

                    return redirect('mio/admin/teachers')->with('status', 'Teacher Info and Account Deleted Deleted Successfully');
                } catch (UserNotFound $e) {
                    return redirect('mio/admin/teachers')->with('status', 'Teacher deleted, but Auth user not found');
                } catch (Exception $e) {
                    return redirect('mio/admin/teachers')->with('status', 'Teacher deleted, but Auth user deletion failed: '.$e->getMessage());
                }
            } else {
                return redirect('mio/admin/teachers')->with('status', 'Teacher Not Deleted');
            }
        }

    // ADMIN - PAGE

        protected function isSuperAdmin()
        {
            $uid = session('firebase_user.uid'); // This is how you stored it in login()

            $admin = $this->database->getReference('users/'.$uid)->getValue(); // Adjust path if needed
            return isset($admin['category']) && $admin['category'] === "head_admin";
        }

        public function admins() {
            // Fetch all users
            $users = $this->database->getReference($this->tablename)->getValue();
            $users = $users ?? [];

            // Filter only students
            $admins = array_filter($users, function($user) {
                return isset($user['role']) && $user['role'] === 'admin';
            });

            $isHeadAdmin = $this->isSuperAdmin();
            return view('mio.head.admin-panel', ['page' => 'admin', 'isHeadAdmin' => $isHeadAdmin], compact('admins'));
        }


    // DISPLAY ADD ADMIN
        public function showAddAdmin(){
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
                    'page' => 'add-admin',
                    'teachers' => $teachers
                ]);
        }

    // ADD ADMIN
        public function addAdmin(Request $request)
            {
                // Validate all fields including account info
                $validatedData = $request->validate([
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'gender' => 'required|string|max:10',
                    'age' => 'required|integer|min:1',
                    'birthday' => 'required|date',
                    'address' => 'required|string|max:255',
                    'barangay' => 'required|string|max:255',
                    'region' => 'required|string|max:100',
                    'province' => 'required|string|max:100',
                    'city' => 'required|string|max:100',
                    'zip_code' => 'required|digits:4',
                    'contact_number' => 'required|string|max:15',
                    'emergency_contact' => 'required|string|max:15',
                    'email' => 'required|email|max:255',
                    'previous_school' => 'required|string|max:255',
                    'grade_level' => 'required|integer|min:1',
                    'adminid' => 'required|string|max:12',
                    'category' => 'required|string',
                    'username' => 'required|string|max:255',
                    'account_password' => 'required|string|min:6',
                    'account_status' => 'required|in:active,inactive',
                    'teacherid' => 'nullable|string|max:12'
                ]);

                $adminIdKey = $request->adminid;
                $emailInput = $request->email;
                $usernameInput = $request->username;
                $teacherId = $request->teacherid;

                // Fetch all users
                $usersRef = $this->database->getReference($this->tablename)->getValue();

                // Check if admin ID already exists
                if (!empty($usersRef) && array_key_exists($adminIdKey, $usersRef)) {
                    return redirect()->back()->with('status', 'Admin ID already exists!')->withInput();
                }

                // Check if email or username already exists
                if (!empty($usersRef)) {
                    foreach ($usersRef as $user) {
                        if (
                            isset($user['email']) && $user['email'] === $emailInput &&
                            (!isset($user['role']) || $user['role'] !== 'teacher' || $teacherId !== array_search($user, $usersRef))
                        ) {
                            return redirect()->back()->with('status', 'Email already exists!')->withInput();
                        }

                        if (isset($user['username']) && $user['username'] === $usernameInput) {
                            return redirect()->back()->with('status', 'Username already exists!')->withInput();
                        }

                    }
                }

                // If teacher ID is provided, fetch teacher info
                if (!empty($teacherId) && isset($usersRef[$teacherId]) && $usersRef[$teacherId]['role'] === 'teacher') {
                    $teacherData = $usersRef[$teacherId];

                    // Overwrite form values with teacher's data
                    $validatedData['first_name'] = $teacherData['fname'] ?? $validatedData['first_name'];
                    $validatedData['last_name'] = $teacherData['lname'] ?? $validatedData['last_name'];
                    $validatedData['gender'] = $teacherData['gender'] ?? $validatedData['gender'];
                    $validatedData['birthday'] = $teacherData['bday'] ?? $validatedData['birthday'];
                    $validatedData['age'] = $teacherData['age'] ?? $validatedData['age'];
                    $validatedData['address'] = $teacherData['address'] ?? $validatedData['address'];
                    $validatedData['barangay'] = $teacherData['barangay'] ?? $validatedData['barangay'];
                    $validatedData['region'] = $teacherData['region'] ?? $validatedData['region'];
                    $validatedData['province'] = $teacherData['province'] ?? $validatedData['province'];
                    $validatedData['city'] = $teacherData['city'] ?? $validatedData['city'];
                    $validatedData['zip_code'] = $teacherData['zip_code'] ?? $validatedData['zip_code'];
                    $validatedData['contact_number'] = $teacherData['contact_number'] ?? $validatedData['contact_number'];
                    $validatedData['email'] = $teacherData['email'] ?? $validatedData['email'];
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


                // Prepare admin data for Firebase
                $postData = [
                    'fname' => $validatedData['first_name'],
                    'lname' => $validatedData['last_name'],
                    'gender' => $validatedData['gender'],
                    'age' => $validatedData['age'],
                    'bday' => $validatedData['birthday'],
                    'address' => $validatedData['address'],
                    'barangay' => $validatedData['barangay'],
                    'region' => $validatedData['region'],
                    'province' => $validatedData['province'],
                    'city' => $validatedData['city'],
                    'zip_code' => $validatedData['zip_code'],
                    'contact_number' => $validatedData['contact_number'],
                    'emergency_contact' => $validatedData['emergency_contact'],
                    'email' => $validatedData['email'],
                    'previous_school' => $validatedData['previous_school'],
                    'grade_level' => $validatedData['grade_level'],
                    'category' => $validatedData['category'],
                    'adminid' => $adminIdKey,
                    'teacherid' => $teacherId ?? null,
                    'schoolyear_id' => $activeSchoolYearId,

                    'role' => 'admin',
                    'username' => $validatedData['username'],
                    'password' => bcrypt($validatedData['account_password']),
                    'account_status' => $validatedData['account_status'],
                    'date_created' => Carbon::now()->toDateTimeString(),
                    'date_updated' => Carbon::now()->toDateTimeString(),
                    'last_login' => null
                ];

                try {
                    // Create Firebase Auth user with student_id as UID
                    $this->auth->createUser([
                        'uid' => $request->adminid,
                        'email' => $request->email,
                        'password' => $request->account_password,
                        'displayName' => $request->first_name . ' ' . $request->last_name,
                        'disabled' => $request->account_status === 'inactive',
                    ]);
                } catch (\Kreait\Firebase\Exception\Auth\AuthError $e) {
                    return redirect()->back()->with('status', 'Firebase Auth Error: ' . $e->getMessage())->withInput();
                }

                // Proceed to save to Realtime Database as before


                // Save to Firebase
                $postRef = $this->database->getReference($this->tablename . '/' . $adminIdKey)->set($postData);

                if ($postRef) {
                    return redirect('mio/admin/admins')->with('status', 'Admin Added Successfully');
                } else {
                    return redirect('mio/admin/admins')->with('status', 'Admin Not Added');
                }
        }


    // DISPLAY EDIT ADMIN
        public function showEditAdmin($id)
        {
            // Get all admins from the database
            $admins = $this->database->getReference($this->tablename)->getValue();
            $editdata = null;
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

            // Find the admin by adminid
            if ($admins) {
                foreach ($admins as $key => $admin) {
                    if (isset($admin['adminid']) && $admin['adminid'] == $id) {
                        // Check if teacherid exists, otherwise set it to null
                        $admin['teacherid'] = isset($admin['teacherid']) ? $admin['teacherid'] : null;

                        $editdata = $admin;
                        $editdata['firebase_key'] = $key;  // Store Firebase key for reference
                        break;
                    }
                }
            }

            // If admin data is found, return the view with the data
            if ($editdata) {
                return view('mio.head.admin-panel', [
                    'page' => 'edit-admin',
                    'editdata' => $editdata,
                    'teachers' => $teachers
                      // Pass the admin data
                ]);
            } else {
                return redirect('mio/admin/admins')->with('status', 'Admin ID Not Found');
            }
        }


        // EDIT ADMIN
        public function editAdmin(Request $request, $id)
        {
            $oldKey = $id;
            $newKey = $request->adminid;  // Ensure this matches the data structure

            // Validate incoming data
            $validatedData = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'gender' => 'required|string|max:10',
                'age' => 'required|integer|min:1',
                'birthday' => 'required|date',
                'address' => 'required|string|max:255',
                'barangay' => 'required|string|max:255',
                'region' => 'required|string|max:100',
                'province' => 'required|string|max:100',
                'city' => 'required|string|max:100',
                'zip_code' => 'required|digits:4',
                'contact_number' => 'required|string|max:15',
                'emergency_contact' => 'required|string|max:15',
                'email' => 'required|email|max:255',
                'previous_school' => 'required|string|max:255',
                'grade_level' => 'required|integer|min:1',
                'adminid' => 'required|string|max:12',
                'category' => 'required|string',
                'username' => 'required|string|max:255',
                'account_status' => 'required|string|in:active,inactive',
                'account_password' => 'nullable|string|min:6',
                'teacherid' => 'nullable|string|max:12'
            ]);

            $adminIdKey = $request->adminid;
            $emailInput = $request->email;
            $usernameInput = $request->username;
            $teacherId = $request->teacherid;

            $adminsRef = $this->database->getReference($this->tablename)->getValue();

            if (!empty($adminsRef)) {
                foreach ($adminsRef as $key => $admin) {
                    if ($key !== $oldKey) {
                        if (isset($admin['adminid']) && $admin['adminid'] == $adminIdKey) {
                            return redirect()->back()->with('status', 'Admin ID already exists!')->withInput();
                        }
                        if (isset($admin['email']) && $admin['email'] == $emailInput) {
                            return redirect()->back()->with('status', 'Email already exists!')->withInput();
                        }

                        if (isset($admin['username']) && $admin['username'] == $usernameInput) {
                            return redirect()->back()->with('status', 'Username already exists!')->withInput();
                        }
                    }
                }
            }

            // Get existing data to preserve date_created and last_login
            $existingData = $this->database->getReference($this->tablename.'/'.$oldKey)->getValue();

            // Prepare the data to update
            $updateData = [
                'fname' => $validatedData['first_name'],
                'lname' => $validatedData['last_name'],
                'gender' => $validatedData['gender'],
                'age' => $validatedData['age'],
                'bday' => $validatedData['birthday'],
                'address' => $validatedData['address'],
                'barangay' => $validatedData['barangay'],
                'region' => $validatedData['region'],
                'province' => $validatedData['province'],
                'city' => $validatedData['city'],
                'zip_code' => $validatedData['zip_code'],
                'contact_number' => $validatedData['contact_number'],
                'emergency_contact' => $validatedData['emergency_contact'],
                'email' => $validatedData['email'],
                'previous_school' => $validatedData['previous_school'],
                'grade_level' => $validatedData['grade_level'],
                'category' => $validatedData['category'],
                'teacherid' => $teacherId ?? null,
                'role' => 'admin',
                'username' => $validatedData['username'],
                'account_status' => $validatedData['account_status'],
                'date_updated' => Carbon::now()->toDateTimeString(),
            ];

            // Only update password if user entered a new one
            if ($request->filled('account_password')) {
                $updateData['password'] = bcrypt($request->account_password);
            } else {
                // Retain existing password if not updated
                if (isset($existingData['password'])) {
                    $updateData['password'] = $existingData['password'];
                }
            }

            if ($oldKey === $newKey) {
                $this->database->getReference($this->tablename.'/'.$oldKey)->update($updateData);
            } else {
                $this->database->getReference($this->tablename.'/'.$newKey)->set($updateData);
                $this->database->getReference($this->tablename.'/'.$oldKey)->remove();
            }

            return redirect('mio/admin/admins')->with('status', 'Admin Updated Successfully');
        }



    // DELETE ADMIN
        public function deleteAdmin($id)
        {
            // Prevent deleting the Super Admin
            $admin = $this->database->getReference($this->tablename.'/'.$id)->getValue();

            if ($admin && isset($admin['role']) && $admin['role'] === 'super_admin') {
                return redirect('mio/admin/admins')->with('status', 'You cannot delete the Super Admin!');
            }

            // Allow only Super Admin to delete
            if (!$this->isSuperAdmin()) {
                return redirect('mio/admin/admins')->with('status', 'Access denied. Only Super Admin can delete admins.');
            }

            // Delete from Realtime Database
            $del_data = $this->database->getReference($this->tablename.'/'.$id)->remove();

            if ($del_data) {
                try {
                    // Delete from Firebase Authentication
                    $this->auth->deleteUser($id); // Assuming $id is the Firebase Auth UID

                    return redirect('mio/admin/admins')->with('status', 'Admin Info and Account Deleted Deleted Successfully');
                } catch (UserNotFound $e) {
                    return redirect('mio/admin/admins')->with('status', 'Admin deleted, but Auth user not found');
                } catch (Exception $e) {
                    return redirect('mio/admin/admins')->with('status', 'Admin deleted, but Auth user deletion failed: '.$e->getMessage());
                }
            } else {
                return redirect('mio/admin/admins')->with('status', 'Admin Not Deleted');
            }
        }


         // GET TEACHER
        public function getTeacherData($id)
        {
             $usersRef = $this->database->getReference($this->tablename)->getValue();

             if (!isset($usersRef[$id]) || $usersRef[$id]['role'] !== 'teacher') {
                 return response()->json(['error' => 'Teacher not found'], 404);
             }

             $teacher = $usersRef[$id];

             return response()->json([
                 'first_name' => $teacher['fname'] ?? '',
                 'last_name' => $teacher['lname'] ?? '',
                 'gender' => $teacher['gender'] ?? '',
                 'birthday' => $teacher['bday'] ?? '',
                 'age' => $teacher['age'] ?? '',
                 'address' => $teacher['address'] ?? '',
                 'barangay' => $teacher['barangay'] ?? '',
                 'region' => $teacher['region'] ?? '',
                 'province' => $teacher['province'] ?? '',
                 'city' => $teacher['city'] ?? '',
                 'zip_code' => $teacher['zip_code'] ?? '',
                 'contact_number' => $teacher['contact_number'] ?? '',
                 'email' => $teacher['email'] ?? ''
             ]);
        }

     // GET SECTION
        public function getSectionData($id)
        {
            // Fetch all sections from Firebase
            $sectionsRef = $this->database->getReference('sections')->getValue();

            // Check if the section with the given ID exists
            if (!isset($sectionsRef[$id])) {
                return response()->json(['error' => 'Section not found'], 404);
            }

            $section = $sectionsRef[$id];

            // Return the section data
            return response()->json([
                'section_id' => $section['sectionid'] ?? '',
                'section_name' => $section['section_name'] ?? '',
                'section_status' => $section['section_status'] ?? '',
                'max_students' => $section['max_students'] ?? '',
                'teacher_id' => $section['teacherid'] ?? '',
                'status' => $section['status'] ?? '',
                'created_at' => $section['created_at'] ?? '',
                'updated_at' => $section['updated_at'] ?? '',
            ]);
        }

     // GET DEPARTMENT
        public function getDepartmentData($id)
        {
            // Fetch all departments from Firebase
            $departmentsRef = $this->database->getReference('departments')->getValue();

            // Check if the department with the given ID exists
            if (!isset($departmentsRef[$id])) {
                return response()->json(['error' => 'Department not found'], 404);
            }

            $department = $departmentsRef[$id];

            // Return the department data
            return response()->json([
                'department_id'    => $department['departmentid'] ?? '',
                'department_name'  => $department['department_name'] ?? '',
                'department_code'  => $department['department_code'] ?? '',
                'department_type'  => $department['department_type'] ?? '',
                'description'      => $department['description'] ?? '',
                'teacher_id'       => $department['teacherid'] ?? '',
                'created_at'       => $department['created_at'] ?? '',
                'updated_at'       => $department['updated_at'] ?? '',
            ]);
        }


        // PARENTS - PAGE
        public function parents() {
        // Fetch all users
        $users = $this->database->getReference($this->tablename)->getValue();
        $users = $users ?? [];

        // Filter only parents
        $parents = array_filter($users, function($user) {
            return isset($user['role']) && $user['role'] === 'parent';
        });

        return view('mio.head.admin-panel', ['page' => 'parent'], compact('parents'));
        }


    // DISPLAY ADD ADMIN
        public function showAddParent(){
            return view('mio.head.admin-panel', ['page' => 'add-parent']);
        }

        // ADD PARENT
        public function addParent(Request $request)
        {
            // Validate all fields including account info
            $validatedData = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'gender' => 'required|string|max:10',
                'age' => 'required|integer|min:1',
                'birthday' => 'required|date',
                'address' => 'required|string|max:255',
                'barangay' => 'required|string|max:255',
                'region' => 'required|string|max:100',
                'province' => 'required|string|max:100',
                'city' => 'required|string|max:100',
                'zip_code' => 'required|digits:4',
                'contact_number' => 'required|string|max:15',
                'emergency_contact' => 'required|string|max:15',
                'email' => 'required|email|max:255',
                'parentid' => 'required|string|max:12',
                'category' => 'required|string',
                'username' => 'required|string|max:255',
                'account_password' => 'required|string|min:6',
                'account_status' => 'required|in:active,inactive',
                'studentid' => 'nullable|string|max:12'
            ]);

            $parentIdKey = $request->parentid;
            $emailInput = $request->email;
            $usernameInput = $request->username;
            $studentId = $request->studentid;

            // Fetch all users
            $usersRef = $this->database->getReference($this->tablename)->getValue();

            // Check if parent ID already exists
            if (!empty($usersRef) && array_key_exists($parentIdKey, $usersRef)) {
                return redirect()->back()->with('status', 'Parent ID already exists!')->withInput();
            }

            // Check if email or username already exists
            if (!empty($usersRef)) {
                foreach ($usersRef as $user) {
                    if (
                        isset($user['email']) && $user['email'] === $emailInput &&
                        (!isset($user['role']) || $user['role'] !== 'parent' || $studentId !== array_search($user, $usersRef))
                    ) {
                        return redirect()->back()->with('status', 'Email already exists!')->withInput();
                    }

                    if (isset($user['username']) && $user['username'] === $usernameInput) {
                        return redirect()->back()->with('status', 'Username already exists!')->withInput();
                    }
                }
            }

            // If student ID is provided, fetch student info and autofill address
            if (!empty($studentId) && isset($usersRef[$studentId]) && $usersRef[$studentId]['role'] === 'student') {
                $studentData = $usersRef[$studentId];
                $validatedData['address'] = $studentData['address'] ?? $validatedData['address'];
                $validatedData['barangay'] = $studentData['barangay'] ?? $validatedData['barangay'];
                $validatedData['region'] = $studentData['region'] ?? $validatedData['region'];
                $validatedData['province'] = $studentData['province'] ?? $validatedData['province'];
                $validatedData['city'] = $studentData['city'] ?? $validatedData['city'];
                $validatedData['zip_code'] = $studentData['zip_code'] ?? $validatedData['zip_code'];
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


            // Prepare parent data for Firebase
            $postData = [
                'fname' => $request->first_name,
                'lname' => $request->last_name,
                'gender' => $request->gender,
                'age' => $request->age,
                'bday' => $request->birthday,
                'address' => $validatedData['address'],
                'barangay' => $validatedData['barangay'],
                'region' => $validatedData['region'],
                'province' => $validatedData['province'],
                'city' => $validatedData['city'],
                'zip_code' => $validatedData['zip_code'],
                'contact_number' => $request->contact_number,
                'emergency_contact' => $request->emergency_contact,
                'email' => $request->email,
                'category' => $request->category,
                'parentid' => $parentIdKey,
                'studentid' => $studentId ?? null, // Save student ID if provided
                'schoolyear_id' => $activeSchoolYearId,
                'role' => 'parent',
                'username' => $request->username,
                'password' => bcrypt($request->account_password),
                'account_status' => $request->account_status,
                'date_created' => Carbon::now()->toDateTimeString(),
                'date_updated' => Carbon::now()->toDateTimeString(),
                'last_login' => null
            ];

            try {
                // Create Firebase Auth user with student_id as UID
                $this->auth->createUser([
                    'uid' => $request->parentid,
                    'email' => $request->email,
                    'password' => $request->account_password,
                    'displayName' => $request->first_name . ' ' . $request->last_name,
                    'disabled' => $request->account_status === 'inactive',
                ]);
            } catch (\Kreait\Firebase\Exception\Auth\AuthError $e) {
                return redirect()->back()->with('status', 'Firebase Auth Error: ' . $e->getMessage())->withInput();
            }

            // Proceed to save to Realtime Database as before


            // Save parent data to Firebase
            $postRef = $this->database->getReference($this->tablename . '/' . $parentIdKey)->set($postData);

            if ($postRef) {
                return redirect('mio/admin/parents')->with('status', 'Parent Added Successfully');
            } else {
                return redirect('mio/admin/parents')->with('status', 'Parent Not Added');
            }
        }


        public function getStudentData($id)
        {
            $usersRef = $this->database->getReference($this->tablename)->getValue();

            if (!isset($usersRef[$id]) || $usersRef[$id]['role'] !== 'student') {
                return response()->json(['error' => 'Student not found'], 404);
            }

            $student = $usersRef[$id];

            return response()->json([
                'first_name' => $student['fname'] ?? '',
                'last_name' => $student['lname'] ?? '',
                'grade_level' => $student['grade_level'] ?? 'N/A',
                'address' => $student['address'] ?? '',
                'barangay' => $student['barangay'] ?? '',
                'region' => $student['region'] ?? '',
                'province' => $student['province'] ?? '',
                'city' => $student['city'] ?? '',
                'zip_code' => $student['zip_code'] ?? '',
            ]);
        }

    // DISPLAY EDIT PARENT
        public function showEditParent($id)
        {
            // Get all parents
            $parents = $this->database->getReference($this->tablename)->getValue();
            $editdata = null;

            // Find the student by studentid
            if ($parents) {
                foreach ($parents as $key => $parent) {
                    if (isset($parent['parentid']) && $parent['parentid'] == $id) {
                        $editdata = $parent;
                        $editdata['firebase_key'] = $key;  // Store Firebase key
                        break;
                    }
                }
            }

            // If student data is found, return the view with the data
            if ($editdata) {
                return view('mio.head.admin-panel', [
                    'page' => 'edit-parent',
                    'editdata' => $editdata,  // Pass the student data including category
                ]);
            } else {
                return redirect('mio/admin/parents')->with('status', 'Parent ID Not Found');
            }
        }

    // EDIT PARENT
        public function editParent(Request $request, $id)
        {
            $oldKey = $id;
            $newKey = $request->parentid;

            $validatedData = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'gender' => 'required|string|max:10',
                'age' => 'required|integer|min:1',
                'birthday' => 'required|date',
                'address' => 'required|string|max:255',
                'barangay' => 'required|string|max:255',
                'region' => 'required|string|max:100',
                'province' => 'required|string|max:100',
                'city' => 'required|string|max:100',
                'zip_code' => 'required|digits:4',
                'contact_number' => 'required|string|max:15',
                'emergency_contact' => 'required|string|max:15',
                'email' => 'required|email|max:255',
                'parentid' => 'required|string|max:12',
                'category' => 'required|string',
                'username' => 'required|string|max:255',
                'account_status' => 'required|string|in:active,inactive',
                'account_password' => 'nullable|string|min:6',
                'studentid' => 'nullable|string|max:12'
            ]);

            $parentIdKey = $request->parentid;
            $emailInput = $request->email;
            $usernameInput = $request->username;
            $studentId = $request->studentid;

            $parentsRef = $this->database->getReference($this->tablename)->getValue();

            if (!empty($parentsRef)) {
                foreach ($parentsRef as $key => $parent) {
                    if ($key !== $oldKey) {
                        if (isset($parent['parentid']) && $parent['parentid'] == $parentIdKey) {
                            return redirect()->back()->with('status', 'Parent ID already exists!')->withInput();
                        }
                        if (isset($parent['email']) && $parent['email'] == $emailInput) {

                            return redirect()->back()->with('status', 'Email already exists!')->withInput();
                        }

                        if (isset($parent['username']) && $parent['username'] == $usernameInput) {
                            return redirect()->back()->with('status', 'Username already exists!')->withInput();
                        }
                    }
                }
            }

            // If teacherid is provided and valid, merge teacher data into updateData
            if (!empty($request->studentid) && isset($parentsRef[$request->studentid]) && $parentsRef[$request->studentid]['role'] === 'teacher') {
                $parentData = $parentsRef[$request->studentid];

                // Overwrite values from teacher data if they exist
                $validatedData['address'] = $parentData['address'] ?? $validatedData['address'];
                $validatedData['barangay'] = $parentData['barangay'] ?? $validatedData['barangay'];
                $validatedData['region'] = $parentData['region'] ?? $validatedData['region'];
                $validatedData['province'] = $parentData['province'] ?? $validatedData['province'];
                $validatedData['city'] = $parentData['city'] ?? $validatedData['city'];
                $validatedData['zip_code'] = $parentData['zip_code'] ?? $validatedData['zip_code'];
            }


            // Get existing data to preserve date_created and last_login
            $existingData = $this->database->getReference($this->tablename.'/'.$oldKey)->getValue();

            $updateData = [
                'fname' => $request->first_name,
                'lname' => $request->last_name,
                'gender' => $request->gender,
                'age' => $request->age,
                'bday' => $request->birthday,
                'address' => $validatedData['address'],
                'barangay' => $validatedData['barangay'],
                'region' => $validatedData['region'],
                'province' => $validatedData['province'],
                'city' => $validatedData['city'],
                'zip_code' => $validatedData['zip_code'],
                'contact_number' => $request->contact_number,
                'emergency_contact' => $request->emergency_contact,
                'email' => $request->email,
                'category' => $request->category,
                'studentid' => $studentId ?? null,
                'role' => 'parent',
                'username' => $usernameInput,
                'account_status' => $request->account_status,
                'date_updated' => Carbon::now()->toDateTimeString(),
                'parentid' => $parentIdKey,
            ];


            // Only update password if user entered a new one
            if ($request->filled('account_password')) {
                $updateData['password'] = bcrypt($request->account_password);
            } else {
                // Retain existing password if not updated
                if (isset($existingData['password'])) {
                    $updateData['password'] = $existingData['password'];
                }
            }

            if ($oldKey === $newKey) {
                $this->database->getReference($this->tablename.'/'.$oldKey)->update($updateData);
            } else {
                $this->database->getReference($this->tablename.'/'.$newKey)->set($updateData);
                $this->database->getReference($this->tablename.'/'.$oldKey)->remove();
            }

            return redirect('mio/admin/parents')->with('status', 'Parent Updated Successfully');
        }

    // DELETE PARENT
        public function deleteParent($id)
        {
            // Delete from Realtime Database
            $del_data = $this->database->getReference($this->tablename.'/'.$id)->remove();

            if ($del_data) {
                try {
                    // Delete from Firebase Authentication
                    $this->auth->deleteUser($id); // Assuming $id is the Firebase Auth UID

                    return redirect('mio/admin/parents')->with('status', 'Parent Info and Account Deleted Successfully');
                } catch (UserNotFound $e) {
                    return redirect('mio/admin/parents')->with('status', 'Parent deleted, but Auth user not found');
                } catch (Exception $e) {
                    return redirect('mio/admin/parents')->with('status', 'Parent deleted, but Auth user deletion failed: '.$e->getMessage());
                }
            } else {
                return redirect('mio/admin/parents')->with('status', 'Parent Not Deleted');
            }
        }




}
