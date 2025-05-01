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
use Carbon\Carbon;

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
            return view('mio.head.admin-panel', ['page' => 'add-student']);
        }

    // ADD STUDENT
        public function addStudent(Request $request)
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
                'schedule' => 'required|array',
                'studentid' => 'required|string|max:12',
                'category' => 'required|string',
                'username' => 'required|string|max:255',
                'account_password' => 'required|string|min:6',
                'account_status' => 'required|in:active,inactive',
            ]);

            $studentIdKey = $request->studentid;
            $emailInput = $request->email;
            $usernameInput = $request->username;

            // Fetch all students
            $studentsRef = $this->database->getReference($this->tablename)->getValue();

            // Check if studentid already exists
            if (!empty($studentsRef) && array_key_exists($studentIdKey, $studentsRef)) {
                return redirect()->back()->with('status', 'Student ID already exists!')->withInput();
            }

            // Check if email or username already exists
            if (!empty($studentsRef)) {
                foreach ($studentsRef as $student) {
                    if ((isset($student['email']) && $student['email'] === $emailInput) ||
                        (isset($student['username']) && $student['username'] === $usernameInput)) {
                        return redirect()->back()->with('status', 'Email or Username already exists!')->withInput();
                    }
                }
            }

            // Prepare the data for saving
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
                'studentid' => $studentIdKey,
                'role' => 'student',

                // Account Info
                'username' => $request->username,
                'password' => bcrypt($request->account_password), // hash for basic security
                'account_status' => $request->account_status,

                // Timestamps
                'date_created' => Carbon::now()->toDateTimeString(),
                'date_updated' => Carbon::now()->toDateTimeString(),
                'last_login' => null // Leave empty on creation
            ];

            try {
                // Create Firebase Auth user with student_id as UID
                $this->auth->createUser([
                    'uid' => $request->studentid,
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
            $postRef = $this->database->getReference($this->tablename.'/'.$studentIdKey)->set($postData);

            if ($postRef) {
                return redirect('mio/admin/students')->with('status', 'Student Added Successfully');
            } else {
                return redirect('mio/admin/students')->with('status', 'Student Not Added');
            }
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

            // If student data is found, return the view with the data
            if ($editdata) {
                return view('mio.head.admin-panel', [
                    'page' => 'edit-student',
                    'editdata' => $editdata,  // Pass the student data including category
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
                'schedule' => 'required|array',
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
                'studentid' => $studentIdKey,
                'role' => 'student',
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
                $del_data = $this->database->getReference($this->tablename.'/'.$key)->remove();

                if ($del_data) {
                    return redirect('mio/admin/students')->with('status', 'Student Deleted Successfully');
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
            return view('mio.head.admin-panel', ['page' => 'add-teacher']);
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
            // Get all teachers
            $teachers = $this->database->getReference($this->tablename)->getValue();
            $editdata = null;

            // Find the student by studentid
            if ($teachers) {
                foreach ($teachers as $key => $teacher) {
                    if (isset($teacher['teacherid']) && $teacher['teacherid'] == $id) {
                        $editdata = $teacher;
                        $editdata['firebase_key'] = $key;  // Store Firebase key
                        break;
                    }
                }
            }

            // If student data is found, return the view with the data
            if ($editdata) {
                return view('mio.head.admin-panel', [
                    'page' => 'edit-teacher',
                    'editdata' => $editdata,  // Pass the student data including category
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
                $del_data = $this->database->getReference($this->tablename.'/'.$key)->remove();

                if ($del_data) {
                    return redirect('mio/admin/teachers')->with('status', 'Teacher Deleted Successfully');
                } else {
                    return redirect('mio/admin/teachers')->with('status', 'Teacher Not Deleted');
                }
        }

    // ADMIN - PAGE
        public function admins() {
            // Fetch all users
            $users = $this->database->getReference($this->tablename)->getValue();
            $users = $users ?? [];

            // Filter only students
            $admins = array_filter($users, function($user) {
                return isset($user['role']) && $user['role'] === 'admin';
            });

            return view('mio.head.admin-panel', ['page' => 'admin'], compact('admins'));
        }


    // DISPLAY ADD ADMIN
        public function showAddAdmin(){
            return view('mio.head.admin-panel', ['page' => 'add-admin']);
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
                    'schedule' => 'required|array',
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
                    'schedule' => $validatedData['schedule'],
                    'adminid' => $adminIdKey,
                    'teacherid' => $teacherId ?? null,
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

    // DISPLAY EDIT TEACHER
        public function showEditAdmin($id)
        {
            // Get all teachers
            $admins = $this->database->getReference($this->tablename)->getValue();
            $editdata = null;

            // Find the student by studentid
            if ($admins) {
                foreach ($admins as $key => $admin) {
                    if (isset($admin['teacherid']) && $admin['adminid'] == $id) {
                        $editdata = $admin;
                        $editdata['firebase_key'] = $key;  // Store Firebase key
                        break;
                    }
                }
            }

            // If student data is found, return the view with the data
            if ($editdata) {
                return view('mio.head.admin-panel', [
                    'page' => 'edit-admin',
                    'editdata' => $editdata,  // Pass the student data including category
                ]);
            } else {
                return redirect('mio/admin/admins')->with('status', 'Admin ID Not Found');
            }
        }

    // EDIT ADMIN
        public function editAdmin(Request $request, $id)
        {
            $oldKey = $id;
            $newKey = $request->adminid;

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
                            return redirect()->back()->with('status', 'Teacher ID already exists!')->withInput();
                        }
                        if (isset($admin['email']) && $admin['email'] == $emailInput) {
                            // Allow same email only if this record is the linked teacher
                            if (!empty($teacherId) && $key === $teacherId && $admin['role'] === 'teacher') {
                                // allowed: same email as linked teacher
                                continue;
                            }
                            return redirect()->back()->with('status', 'Email already exists!')->withInput();
                        }

                        if (isset($admin['username']) && $admin['username'] == $usernameInput) {
                            return redirect()->back()->with('status', 'Username already exists!')->withInput();
                        }
                    }
                }
            }

            // If teacherid is provided and valid, merge teacher data into updateData
            if (!empty($request->teacherid) && isset($adminsRef[$request->teacherid]) && $adminsRef[$request->teacherid]['role'] === 'teacher') {
                $teacherData = $adminsRef[$request->teacherid];

                // Overwrite values from teacher data if they exist
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


             // Get existing data to preserve date_created and last_login
             $existingData = $this->database->getReference($this->tablename.'/'.$oldKey)->getValue();

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
                'schedule' => $validatedData['schedule'],
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

            return redirect('mio/admin/teachers')->with('status', 'Admin Updated Successfully');
        }


    // DELETE ADMIN
         public function deleteAdmin($id)
         {
             $key = $id;
             $del_data = $this->database->getReference($this->tablename.'/'.$key)->remove();

             if ($del_data) {
                 return redirect('mio/admin/admins')->with('status', 'Admin Deleted Successfully');
             } else {
                 return redirect('mio/admin/admins')->with('status', 'Admin Not Deleted');
             }
        }

     // ADMIN - PAGE
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
             $key = $id;
             $del_data = $this->database->getReference($this->tablename.'/'.$key)->remove();

             if ($del_data) {
                 return redirect('mio/admin/parents')->with('status', 'Parent Deleted Successfully');
             } else {
                 return redirect('mio/admin/parents')->with('status', 'Parent Not Deleted');
             }
        }



}
