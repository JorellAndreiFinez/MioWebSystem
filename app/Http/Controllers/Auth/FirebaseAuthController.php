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

        public function students(){
            // Fetch students from Firebase
            $students = $this->database->getReference($this->tablename)->getValue();

            // Ensure $students is not null (convert null to an empty array)
            $students = $students ?? [];

            // Pass the students variable to the view
            return view('mio.head.admin-panel', ['page' => 'students'], compact('students'));
        }

        public function showAddStudent(){
            return view('mio.head.admin-panel', ['page' => 'add-student']);
        }

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

        public function editStudent(Request $request, $id)
        {
            $key = $id;
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
                'category' => $request->category,  // Assuming category field exists
                'schedule' => $request->schedule, // Assuming schedule is an array of schedule IDs
                'studentid' => $request->studentid,
                'role' => 'student', // Set the default role as 'student'
            ];

            $res_updated = $this->database->getReference($this->tablename.'/'.$key)->update($updateData);

            if($res_updated) {
                return redirect('mio/admin1/students')->with('status', 'Student Updated Successfully');
            } else {
                return redirect('mio/admin1/students')->with('status', 'Student Not Updated');
            }
        }


        public function addStudent(Request $request){
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
                'schedule' => 'required|array',
                'studentid' => 'required|string|max:12',
            ]);

            // Prepare the data for saving to the database, including 'role'
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
                'category' => $request->category,  // Assuming category field exists
                'schedule' => $request->schedule, // Assuming schedule is an array of schedule IDs
                'studentid' => $request->studentid,
                'role' => 'student', // Set the default role as 'student'
            ];

            // Push the data to Firebase or another database
            $postRef = $this->database->getReference($this->tablename)->push($postData);

            // Check if the data was saved successfully
            if($postRef) {
                return redirect('mio/admin1/students')->with('status', 'Student Added Successfully');
            } else {
                return redirect('mio/admin1/students')->with('status', 'Student Not Added');
            }
        }



}
