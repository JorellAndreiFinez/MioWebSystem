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

        public function registerForm(){

        }

        public function loginForm(){
            return view('mio.user-access.login');
        }

        public function login(Request $request){
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|max:100'
            ]);

            if ($validator) {
                return redirect()->back()->withErrors($validator)->withInput($request->all())->with('error', 'Invalid credentials');
            } else {
                try{
                    $email = $request->input('email');
                    $password = $request->input('password');

                    $user = $this->auth->signInWithEmailAndPassword($email, $password);

                    if($user) {
                        return redirect()->route('mio.student-panel')->with('success', 'Login Successful');
                    } else {
                        return redirect()->back()->with('error', 'Invalid credentials');
                    }


                } catch(InvalidPassword $e) {
                    return redirect()->back()->with('error', 'Invalid Password');
                } catch(UserNotFound $e) {
                    return redirect()->back()->with('error', 'User not found');
                } catch(Exception $e) {
                    return redirect()->back()->with('error', 'Please Try Again');
                }
            }

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
                // Validate basic field formats first
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
                ]);

                $studentIdKey = $request->studentid;
                $emailInput = $request->email;

                // Fetch all students
                $studentsRef = $this->database->getReference($this->tablename)->getValue();

                // Check if studentid already exists
                if (!empty($studentsRef) && array_key_exists($studentIdKey, $studentsRef)) {
                    return redirect()->back()->with('status', 'Student ID already exists!')->withInput();
                }

                // Check if email already exists
                if (!empty($studentsRef)) {
                    foreach ($studentsRef as $student) {
                        if (isset($student['email']) && $student['email'] === $emailInput) {
                            return redirect()->back()->with('status', 'Email already exists!')->withInput();
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
                ];

                // Save the data under studentid key
                $postRef = $this->database->getReference($this->tablename.'/'.$studentIdKey)->set($postData);

                if ($postRef) {
                    return redirect('mio/admin1/students')->with('status', 'Student Added Successfully');
                } else {
                    return redirect('mio/admin1/students')->with('status', 'Student Not Added');
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
                return redirect('mio/admin1/students')->with('status', 'Student ID Not Found');
            }
        }

        // EDIT STUDENT
        public function editStudent(Request $request, $id)
        {
            $oldKey = $id;
            $newKey = $request->studentid;

            // Validate the basic field formats first
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
            ]);

            $studentIdKey = $request->studentid;
            $emailInput = $request->email;

            // Fetch all students
            $studentsRef = $this->database->getReference($this->tablename)->getValue();

            // Check if the new studentid already exists (and it's not the same as the old key)
            if (!empty($studentsRef) && array_key_exists($studentIdKey, $studentsRef) && $studentIdKey !== $oldKey) {
                return redirect()->back()->with('status', 'Student ID already exists!')->withInput();
            }

            // Check if the new email already exists for a different student
            if (!empty($studentsRef)) {
                foreach ($studentsRef as $key => $student) {
                    if ($key !== $oldKey && isset($student['email']) && $student['email'] === $emailInput) {
                        return redirect()->back()->with('status', 'Email already exists!')->withInput();
                    }
                }
            }

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
            ];

            if ($oldKey === $newKey) {
                // No change in ID, just update the existing record
                $this->database->getReference($this->tablename.'/'.$oldKey)->update($updateData);
            } else {
                // ID changed: move data to new key, delete old key
                $this->database->getReference($this->tablename.'/'.$newKey)->set($updateData);
                $this->database->getReference($this->tablename.'/'.$oldKey)->remove();
            }

            return redirect('mio/admin1/students')->with('status', 'Student Updated Successfully');
        }


        // DELETE STUDENT
        public function deleteStudent($id)
            {
                $key = $id;
                $del_data = $this->database->getReference($this->tablename.'/'.$key)->remove();

                if ($del_data) {
                    return redirect('mio/admin1/students')->with('status', 'Student Deleted Successfully');
                } else {
                    return redirect('mio/admin1/students')->with('status', 'Student Not Deleted');
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
                // Validate basic field formats first
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
                ]);

                $teacherIdKey = $request->teacherid;
                $emailInput = $request->email;

                // Fetch all students
                $teachersRef = $this->database->getReference($this->tablename)->getValue();

                // Check if studentid already exists
                if (!empty($teachersRef) && array_key_exists($teacherIdKey, $teachersRef)) {
                    return redirect()->back()->with('status', 'Teacher ID already exists!')->withInput();
                }

                // Check if email already exists
                if (!empty($teachersRef)) {
                    foreach ($teachersRef as $teacher) {
                        if (isset($teacher['email']) && $teacher['email'] === $emailInput) {
                            return redirect()->back()->with('status', 'Email already exists!')->withInput();
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
                    'teacherid' => $teacherIdKey,
                    'role' => 'teacher',
                ];

                // Save the data under studentid key
                $postRef = $this->database->getReference($this->tablename.'/'.$teacherIdKey)->set($postData);

                if ($postRef) {
                    return redirect('mio/admin1/teachers')->with('status', 'Student Added Successfully');
                } else {
                    return redirect('mio/admin1/teachers')->with('status', 'Student Not Added');
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
                return redirect('mio/admin1/teachers')->with('status', 'Teacher ID Not Found');
            }
        }

        // EDIT STUDENT
        public function editTeacher(Request $request, $id)
        {
            $oldKey = $id;
            $newKey = $request->teacherid;

            // Validate the basic field formats first
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
            ]);

            $teacherIdKey = $request->teacherid;
            $emailInput = $request->email;

            // Fetch all teachers
            $teachersRef = $this->database->getReference($this->tablename)->getValue();

            // Check if the new teacherid already exists (and it's not the same as the old key)
            if (!empty($teachersRef) && array_key_exists($teacherIdKey, $teachersRef) && $teacherIdKey !== $oldKey) {
                return redirect()->back()->with('status', 'Teacher ID already exists!')->withInput();
            }

            // Check if the new email already exists for a different student
            if (!empty($teachersRef)) {
                foreach ($teachersRef as $key => $teacher) {
                    if ($key !== $oldKey && isset($teacher['email']) && $teacher['email'] === $emailInput) {
                        return redirect()->back()->with('status', 'Email already exists!')->withInput();
                    }
                }
            }

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
            ];

            if ($oldKey === $newKey) {
                // No change in ID, just update the existing record
                $this->database->getReference($this->tablename.'/'.$oldKey)->update($updateData);
            } else {
                // ID changed: move data to new key, delete old key
                $this->database->getReference($this->tablename.'/'.$newKey)->set($updateData);
                $this->database->getReference($this->tablename.'/'.$oldKey)->remove();
            }

            return redirect('mio/admin1/teachers')->with('status', 'Teacher Updated Successfully');
        }

        // DELETE TEACHER
        public function deleteTeacher($id)
            {
                $key = $id;
                $del_data = $this->database->getReference($this->tablename.'/'.$key)->remove();

                if ($del_data) {
                    return redirect('mio/admin1/teachers')->with('status', 'Teacher Deleted Successfully');
                } else {
                    return redirect('mio/admin1/teachers')->with('status', 'Teacher Not Deleted');
                }
        }

}
