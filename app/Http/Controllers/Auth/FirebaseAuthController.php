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
                $studentsCount = $this->database->getReference('users')->orderByChild('role')->equalTo('student')->getSnapshot()->numChildren();
                $teachersCount = $this->database->getReference('users')->orderByChild('role')->equalTo('teacher')->getSnapshot()->numChildren();
                $sectionsCount = $this->database->getReference('sections')->getSnapshot()->numChildren();

                $users = $this->database->getReference('users')->getValue() ?? [];
                $sections = $this->database->getReference('sections')->getValue() ?? [];

                $sectionsData = [];

                foreach ($sections as $sectionId => $section) {
                    if (!isset($section['section_name'])) continue;

                    // Get teacher info
                    $teacherId = $section['teacherid'] ?? null;
                    $teacherPhoto = null;
                    $teacherName = 'Unknown';

                    if ($teacherId && isset($users[$teacherId])) {
                        $teacherData = $users[$teacherId];
                        $teacherName = trim(($teacherData['fname'] ?? '') . ' ' . ($teacherData['lname'] ?? ''));
                        $teacherPhoto = $teacherData['photo_url'] ?? null;
                    }

                    // Get student photo URLs (up to 3)
                    $studentPhotos = [];
                    $studentsCountInSection = 0;

                    if (!empty($section['students']) && is_array($section['students'])) {
                        $studentsCountInSection = count($section['students']);
                        $shown = 0;

                        foreach ($section['students'] as $studentId => $studentData) {
                            if (isset($users[$studentId]['photo_url'])) {
                                $studentPhotos[] = $users[$studentId]['photo_url'];
                                $shown++;
                            }
                            if ($shown >= 3) break;
                        }
                    }

                    $sectionsData[] = [
                        'name' => $section['section_name'],
                        'grade' => $section['section_grade'] ?? '',
                        'teacher_photo' => $teacherPhoto,
                        'teacher_name' => $teacherName,
                        'student_photos' => $studentPhotos,
                        'students_count' => $studentsCountInSection,
                    ];
                }

                $departmentsRef = $this->database->getReference('departments')->getValue() ?? [];

                return view('mio.head.admin-panel', [
                    'page' => 'dashboard',
                    'studentsCount' => $studentsCount,
                    'teachersCount' => $teachersCount,
                    'sectionsCount' => $sectionsCount,
                    'sectionsData' => $sectionsData,
                    'departments' => $departmentsRef,
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
                // Skip malformed entries
                if (!isset($sect['sectionid'], $sect['section_name'])) {
                    continue;
                }

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
            try {
                // Convert 'none' to empty string before validation
                if ($request->section_id === 'none') {
                    $request->merge(['section_id' => '']);
                }

                // Validate all fields from the form
                $validatedData = $request->validate([
                    'category' => 'required|string|in:new,transfer,returning,international',
                    'studentid' => 'required|string|max:12',
                    'section_id' => 'nullable|string|max:20',

                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'gender' => 'required|string|in:Male,Female,Other',
                    'age' => 'required|integer|min:1|max:100',
                    'birthday' => 'required|date|before_or_equal:today',
                    'contact_number' => 'required|string|max:15',

                    'region' => 'required|string|max:100',
                    'province' => 'required|string|max:100',
                    'city' => 'required|string|max:100',
                    'barangay' => 'required|string|max:100',
                    'address' => 'required|string|max:255',
                    'zip_code' => 'required|digits:4',

                    'email' => 'required|email|max:255',

                    // Parent/Guardian info
                    'parent_firstname' => 'required|string|max:255',
                    'parent_lastname' => 'required|string|max:255',
                    'emergency_contact' => 'required|string|max:15',
                    'parent_role' => 'required|string|in:father,mother,guardian',

                    // Academic info
                    'previous_school' => 'required|string|max:255',
                    'previous_grade_level' => 'required|integer|min:1',
                    'enrollment_grade' => 'required|string|in:kinder,elementary,junior-highschool,senior-highschool,one-on-one-therapy',
                    'grade_level' => 'nullable|string|max:50',
                    'strand' => 'nullable|string|max:50',

                    // Health/Medical info
                    'medical_history' => 'required|string|max:255',
                    'hearing_loss' => 'nullable|string|max:255',
                    'hearing_identity' => 'required|string|in:deaf,hard-of-hearing,speech-delay',
                    'assistive_devices' => 'nullable|string|max:255',
                    'health_notes' => 'nullable|string|max:255',


                    // Account info
                    'username' => 'required|string|max:255',
                    'account_password' => 'required|string|min:6',
                    'account_status' => 'required|in:active,inactive',

                    'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                ]);

                $studentIdKey = $request->studentid;
                $sectionId = $request->section_id;

                // Check for existing studentid, email, username
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

                // Section and subject logic
                $section = null;
                $subjectId = null;

                if (!empty($sectionId)) {
                    $section = $this->database->getReference('sections/' . $sectionId)->getValue();
                    if (!$section) {
                        return redirect()->back()->with('status', 'Section not found!')->withInput();
                    }

                    $sectionGrade = $section['section_grade'] ?? null;
                    $subjects = $this->database->getReference('subjects')->getValue() ?? [];

                    $matchedSubjects = [];
                    foreach ($subjects as $gradeKey => $subjectGroup) {
                        $gradeOnly = substr($gradeKey, 2); // assuming format is like "GR1", "GR10"
                        if ((string) $sectionGrade === $gradeOnly) {
                            foreach ($subjectGroup as $subjId => $subject) {
                                if (($subject['section_id'] ?? null) === $section['sectionid']) {
                                    $matchedSubjects[$subjId] = $gradeKey; // retain full key like "GR1"
                                }
                            }
                        }
                    }
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

                // Upload profile picture if exists
                $profilePictureUrl = null;
                if ($request->hasFile('profile_picture')) {
                    $image = $request->file('profile_picture');
                    $imageName = 'profile_pictures/' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $firebaseStorage = app('firebase.storage');
                    $defaultBucket = $firebaseStorage->getBucket();
                    $uploadedFile = fopen($image->getRealPath(), 'r');

                    $defaultBucket->upload($uploadedFile, ['name' => $imageName]);
                    $profilePictureUrl = 'https://firebasestorage.googleapis.com/v0/b/' . $defaultBucket->name() . '/o/' . urlencode($imageName) . '?alt=media';
                }

                // Prepare student data
                $postData = [
                    'category' => $request->category,
                    'studentid' => $studentIdKey,
                    'section_id' => $sectionId,

                    'fname' => $request->first_name,
                    'lname' => $request->last_name,
                    'gender' => $request->gender,
                    'age' => $request->age,
                    'bday' => $request->birthday,
                    'contact_number' => $request->contact_number,

                    'region' => $request->region,
                    'province' => $request->province,
                    'city' => $request->city,
                    'barangay' => $request->barangay,
                    'address' => $request->address,
                    'zip_code' => $request->zip_code,

                    'email' => $request->email,

                    // Parent info
                    'parent_firstname' => $request->parent_firstname,
                    'parent_lastname' => $request->parent_lastname,
                    'emergency_contact' => $request->emergency_contact,
                    'parent_role' => $request->parent_role,

                    // Academic info
                    'previous_school' => $request->previous_school,
                    'previous_grade_level' => $request->previous_grade_level,
                    'enrollment_grade' => $request->enrollment_grade,
                    'grade_level' => $request->grade_level ?? null,
                    'strand' => $request->strand ?? null,

                    // Account info
                    'username' => $request->username,
                    'password' => bcrypt($request->account_password),
                    'account_status' => $request->account_status,

                    'role' => 'student',
                    'profile_picture' => $profilePictureUrl,
                    'schoolyear_id' => $activeSchoolYearId,

                    'date_created' => Carbon::now()->toDateTimeString(),
                    'date_updated' => Carbon::now()->toDateTimeString(),
                    'last_login' => null,
                    'already_login' => 'false',

                    // Medical info
                    'medical_history' => $request->medical_history,
                    'hearing_loss' => $request->hearing_loss,
                    'hearing_identity' => $request->hearing_identity,
                    'assistive_devices' => $request->assistive_devices,
                    'health_notes' => $request->health_notes,

                ];

                // Save student data to Firebase Realtime Database
                $this->database->getReference('users/' . $studentIdKey)->set($postData);

                // Add student to section if applicable
                if (!empty($sectionId)) {
                    $this->database->getReference("sections/{$sectionId}/students/{$studentIdKey}")->set([
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'role' => 'student',
                        'schoolyear_id' => $activeSchoolYearId,
                    ]);
                }

                // Add student to subject people if section and subject exist
                if (!empty($sectionId) && isset($section, $matchedSubjects)) {
                    foreach ($matchedSubjects as $subjectId => $gradeKey) {
                        $this->database
                            ->getReference("subjects/{$gradeKey}/{$subjectId}/people/{$studentIdKey}")
                            ->set([
                                'role' => 'student',
                                'first_name' => $request->first_name,
                                'last_name' => $request->last_name,
                            ]);
                    }
                }



                return redirect()->route('mio.students')->with('status', 'Student added successfully!');
            } catch (\Exception $e) {
                return redirect()->back()->with('status', 'Error adding student: ' . $e->getMessage())->withInput();
            }
        }




    // DISPLAY EDIT STUDENT
        public function showEditStudent($id)
        {
            // Get all users (students and possibly parents mixed together)
            $users = $this->database->getReference($this->tablename)->getValue();
            $editdata = null;

            // Find the student by studentid AND role == 'student'
            if ($users) {
                foreach ($users as $key => $user) {
                    if (
                        isset($user['studentid'], $user['role']) &&
                        $user['studentid'] == $id &&
                        $user['role'] === 'student'
                    ) {
                        $editdata = $user;
                        $editdata['firebase_key'] = $key; // Save Firebase key for update
                        break;
                    }
                }
            }

            $sectionsRaw = $this->database->getReference('sections')->getValue() ?? [];
            $sections = [];

            foreach ($sectionsRaw as $key => $sect) {
                if (!isset($sect['sectionid'], $sect['section_name'])) {
                    continue;
                }

                $sections[] = [
                    'sectionid' => $sect['sectionid'],
                    'section_name' => $sect['section_name']
                ];
            }

            if ($editdata) {
                return view('mio.head.admin-panel', [
                    'page' => 'edit-student',
                    'editdata' => $editdata,
                    'sections' => $sections,
                ]);
            } else {
                return redirect('mio/admin/students')->with('status', 'Student ID Not Found');
            }
        }


    // EDIT STUDENT
        public function editStudent(Request $request, $id)
        {
            try {
                $oldKey = $id;
                $newKey = $request->studentid;

                if ($request->section_id === 'none') {
                    $request->merge(['section_id' => '']);
                }

                // Validation rules updated for all form fields including new ones
                $validatedData = $request->validate([
                    // Personal Information
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

                    // Parent/Guardian Information
                    'parent_firstname' => 'required|string|max:255',
                    'parent_lastname' => 'required|string|max:255',
                    'emergency_contact' => 'required|string|max:15',
                    'parent_role' => 'required|string|in:father,mother,guardian',

                    // Academic Information
                    'previous_school' => 'required|string|max:255',
                    'previous_grade_level' => 'required|integer|min:1',
                    'enrollment_grade' => 'required|string|in:kinder,elementary,junior-highschool,senior-highschool,one-on-one-therapy',
                    'grade_level' => 'nullable|string|max:20',
                    'strand' => 'nullable|string|max:50',

                    // Medical Information
                    'medical_history' => 'nullable|string',
                    'hearing_loss' => 'nullable|string',
                    'hearing_identity' => 'required|string',
                    'assistive_devices' => 'nullable|string',
                    'health_notes' => 'nullable|string',



                    // Account Information
                    'studentid' => 'required|string|max:12',
                    'username' => 'required|string|max:255',
                    'account_password' => 'nullable|string|min:6',
                    'account_status' => 'required|in:active,inactive',

                    // Section
                    'section_id' => 'nullable|string|max:20',

                ]);

                $studentIdKey = $request->studentid;
                $emailInput = $request->email;
                $usernameInput = $request->username;

                // ✅ Get existing data early for comparison
                $existingData = $this->database->getReference('users/' . $oldKey)->getValue();

                // Duplicate checks for studentid, email, username
                $studentsRef = $this->database->getReference('users')->getValue();
                if (!empty($studentsRef)) {
                    foreach ($studentsRef as $key => $student) {
                        if ($key !== $oldKey) {
                            if (isset($student['studentid']) && $student['studentid'] === $studentIdKey && $studentIdKey !== $oldKey) {
                                return redirect()->back()->with('status', 'Student ID already exists!')->withInput();
                            }
                            if (isset($student['email']) && $student['email'] === $emailInput && $emailInput !== ($existingData['email'] ?? '')) {
                                return redirect()->back()->with('status', 'Email already exists!')->withInput();
                            }
                            if (isset($student['username']) && $student['username'] === $usernameInput && $usernameInput !== ($existingData['username'] ?? '')) {
                                return redirect()->back()->with('status', 'Username already exists!')->withInput();
                            }
                        }
                    }
                }


                // Handle section and related data as before
                $sectionId = $request->section_id;
                $subjectId = null;
                $section = null;
                $sectionGrade = null;

                if (!empty($sectionId)) {
                    $section = $this->database->getReference('sections/' . $sectionId)->getValue();
                    if (!$section) {
                        return redirect()->back()->with('status', 'Section not found!')->withInput();
                    }

                    $sectionGrade = $section['section_grade'] ?? null;
                    $subjects = $this->database->getReference('subjects')->getValue() ?? [];

                    foreach ($subjects as $grade => $subjectGroup) {
                        if ((string)$sectionGrade === substr($grade, 2)) {
                            foreach ($subjectGroup as $subjId => $subject) {
                                if (($subject['section_id'] ?? null) == ($section['sectionid'] ?? null)) {
                                    $subjectId = $subjId;
                                    break 2;
                                }
                            }
                        }
                    }

                    if (!$subjectId) {
                        return redirect()->back()->with('status', 'No related subject found for this section.')->withInput();
                    }
                }

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


                // Compose update data including new fields
                $updateData = [
                    // Personal Info
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
                    'email' => $request->email,

                    // Parent/Guardian Info
                    'parent_firstname' => $request->parent_firstname,
                    'parent_lastname' => $request->parent_lastname,
                    'emergency_contact' => $request->emergency_contact,
                    'parent_role' => $request->parent_role,

                    // Academic Info
                    'previous_school' => $request->previous_school,
                    'previous_grade_level' => $request->previous_grade_level,
                    'enrollment_grade' => $request->enrollment_grade,
                    'grade_level' => $request->grade_level ?? '',
                    'strand' => $request->strand ?? '',

                    // Medical Info
                    'medical_history' => $request->medical_history ?? '',
                    'hearing_loss' => $request->hearing_loss ?? '',
                    'hearing_identity' => $request->hearing_identity ?? '',
                    'assistive_devices' => $request->assistive_devices ?? '',
                    'health_notes' => $request->health_notes ?? '',



                    // Other student info
                    'category' => $request->category ?? ($existingData['category'] ?? ''),
                    'studentid' => $studentIdKey,
                    'role' => 'student',
                    'section_id' => $sectionId ?? '',
                    'schoolyear_id' => $activeSchoolYearId,
                    'section_grade' => $sectionGrade ?? '',
                    'username' => $usernameInput,
                    'already_login' => $existingData['already_login'] ?? 'false',

                    'account_status' => $request->account_status,
                    'date_updated' => Carbon::now()->toDateTimeString(),
                ];

                // Password handling
                if ($request->filled('account_password')) {
                    $updateData['password'] = bcrypt($request->account_password);
                } else if (isset($existingData['password'])) {
                    $updateData['password'] = $existingData['password'];
                }

                // Preserve creation and last login dates if exist
                if (isset($existingData['date_created'])) {
                    $updateData['date_created'] = $existingData['date_created'];
                }
                if (isset($existingData['last_login'])) {
                    $updateData['last_login'] = $existingData['last_login'];
                }

                // Update or rename key if studentid changed
                if ($oldKey === $newKey) {
                    $this->database->getReference('users/' . $oldKey)->update($updateData);
                } else {
                    $this->database->getReference('users/' . $newKey)->set($updateData);
                    $this->database->getReference('users/' . $oldKey)->remove();
                }

                // Update section reference only if section is assigned (you can finish this part)
                if (!empty($sectionId)) {
                    $this->database->getReference("sections/{$sectionId}/students/{$newKey}")->set([
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'role' => 'student',
                    ]);

                    if ($oldKey !== $newKey) {
                        $this->database->getReference("sections/{$sectionId}/students/{$oldKey}")->remove();
                    }
                }

                if (!empty($sectionId)) {
                    $subjects = $this->database->getReference('subjects')->getValue() ?? [];

                    foreach ($subjects as $gradeLevel => $subjectGroup) {
                        foreach ($subjectGroup as $subjId => $subject) {
                            if (($subject['section_id'] ?? null) === $section['sectionid']) {
                                // Assign student to this subject
                                $this->database->getReference("subjects/{$gradeLevel}/{$subjId}/people/{$newKey}")->set([
                                    'first_name' => $request->first_name,
                                    'last_name' => $request->last_name,
                                    'role' => 'student',
                                ]);

                                // Remove old entry if student ID was changed
                                if ($oldKey !== $newKey) {
                                    $this->database->getReference("subjects/{$gradeLevel}/{$subjId}/people/{$oldKey}")->remove();
                                }
                            }
                        }
                    }
                }




                return redirect()->route('mio.students')->with('status', 'Student updated successfully.');

            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Error updating student: ' . $e->getMessage())->withInput();
            }
        }

    // DELETE STUDENT
        public function deleteStudent(Request $request, $id)
        {
            $key = $id;

            // 1. Remove student from all sections
            $sections = $this->database->getReference("sections")->getValue();
            foreach ($sections as $sectionId => $section) {
                if (isset($section['students'][$key])) {
                    $this->database->getReference("sections/{$sectionId}/students/{$key}")->remove();
                }
            }

            // 2. Remove student from all subjects/gradelevel/subjectID/people
            $subjects = $this->database->getReference("subjects")->getValue();
            foreach ($subjects as $grade => $subjectList) {
                foreach ($subjectList as $subjectId => $subject) {
                    if (isset($subject['people'][$key])) {
                        $this->database->getReference("subjects/{$grade}/{$subjectId}/people/{$key}")->remove();
                    }
                }
            }

            // 3. Delete from users/
            $del_data = $this->database->getReference($this->tablename . '/' . $key)->remove();

            if ($del_data) {
                try {
                    // Delete from Firebase Authentication
                    $this->auth->deleteUser($key);
                    $request->session()->forget('admin_password_verified');

                    return redirect('mio/admin/students')->with('status', 'Student Info and Account Deleted Successfully');
                } catch (\Kreait\Firebase\Exception\Auth\UserNotFound $e) {
                    return redirect('mio/admin/students')->with('status', 'Student deleted, but Auth user not found');
                } catch (\Exception $e) {
                    return redirect('mio/admin/students')->with('status', 'Student deleted, but Auth user deletion failed: ' . $e->getMessage());
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

               // If the user selected "Other" and provided a custom university name
            if ($request->university === 'Other' && !empty($request->custom_university)) {
                $request->merge(['university' => $request->custom_university]);
            }

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
                'educational_attainment' => 'required|string|max:100',
                'course' => 'required|string|max:255',
                'university' => 'required|string|max:255',
                'custom_university' => 'nullable|string|max:255',
                'year_graduated' => 'required|digits:4',
                'let_passer' => 'nullable|in:Yes,No',
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
                'educational_attainment' => $request->educational_attainment,
                'course' => $request->course,
                'university' => $request->university,
                'year_graduated' => $request->year_graduated,
                'let_passer' => $request->let_passer ?? null,
                'category' => $request->category,
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
                'last_login' => null, // Leave empty on creation
                'already_login' => 'false',
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

            // Handle "Other" university selection
            if ($request->university === 'Other' && !empty($request->custom_university)) {
                $request->merge(['university' => $request->custom_university]);
            }

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
                'educational_attainment' => 'required|string|max:100',
                'course' => 'required|string|max:255',
                'university' => 'required|string|max:255',
                'custom_university' => 'nullable|string|max:255',
                'year_graduated' => 'required|digits:4',
                'let_passer' => 'nullable|in:Yes,No',
                'teacherid' => 'required|string|max:12',
                'category' => 'required|string',
                'username' => 'required|string|max:255',
                'account_status' => 'required|string|in:active,inactive',
                'account_password' => 'nullable|string|min:6',
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

            // Fetch all school years to get active one
            $schoolYears = $this->database->getReference('schoolyears')->getValue();
            $activeSchoolYearId = null;
            if (!empty($schoolYears)) {
                foreach ($schoolYears as $year) {
                    if (isset($year['status']) && $year['status'] === 'active') {
                        $activeSchoolYearId = $year['schoolyearid'];
                        break;
                    }
                }
            }

            if (!$activeSchoolYearId) {
                return redirect()->back()->with('status', 'No active school year found.')->withInput();
            }

            // Prepare update data
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
                'educational_attainment' => $request->educational_attainment,
                'course' => $request->course,
                'university' => $request->university,
                'year_graduated' => $request->year_graduated,
                'let_passer' => $request->let_passer ?? null,
                'category' => $request->category,
                'teacherid' => $teacherIdKey,
                'role' => 'teacher',
                'username' => $usernameInput,
                'account_status' => $request->account_status,
                'department_id' => $request->department_id,
                'schoolyear_id' => $activeSchoolYearId,
                'date_updated' => Carbon::now()->toDateTimeString(),
                'already_login' => $existingData['already_login'] ?? 'false',
                'date_created' => $existingData['date_created'] ?? Carbon::now()->toDateTimeString(),
                'last_login' => $existingData['last_login'] ?? null,

            ];

            if ($request->filled('account_password')) {
                $updateData['password'] = bcrypt($request->account_password);
            } elseif (isset($existingData['password'])) {
                $updateData['password'] = $existingData['password'];
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
        public function deleteTeacher(Request $request, $id)
        {
            $key = $id;

            // Delete from Realtime Database
            $del_data = $this->database->getReference($this->tablename.'/'.$key)->remove();

            if ($del_data) {
                try {
                    // Delete from Firebase Authentication
                    $this->auth->deleteUser($key); // $key must be the Firebase Auth UID

                    // ✅ Clear verification session
                    $request->session()->forget('admin_password_verified');

                    return redirect('mio/admin/teachers')->with('status', 'Teacher Info and Account Deleted Successfully');
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
        public function deleteAdmin(Request $request, $id)
        {
            $key = $id;

            // Fetch the admin record
            $admin = $this->database->getReference($this->tablename . '/' . $key)->getValue();

            // ✅ Prevent deleting Super Admin
            if ($admin && isset($admin['role']) && $admin['role'] === 'super_admin') {
                return redirect('mio/admin/admins')->with('status', 'You cannot delete the Super Admin!');
            }

            // ✅ Only Super Admin can delete admins
            if (!$this->isSuperAdmin()) {
                return redirect('mio/admin/admins')->with('status', 'Access denied. Only Super Admin can delete admins.');
            }

            // ✅ Delete from Realtime Database
            $del_data = $this->database->getReference($this->tablename . '/' . $key)->remove();

            if ($del_data) {
                try {
                    // ✅ Delete from Firebase Authentication
                    $this->auth->deleteUser($key); // $key must be Firebase Auth UID

                    // ✅ Clear verification session
                    $request->session()->forget('admin_password_verified');

                    return redirect('mio/admin/admins')->with('status', 'Admin Info and Account Deleted Successfully');
                } catch (UserNotFound $e) {
                    return redirect('mio/admin/admins')->with('status', 'Admin deleted, but Auth user not found');
                } catch (Exception $e) {
                    return redirect('mio/admin/admins')->with('status', 'Admin deleted, but Auth user deletion failed: ' . $e->getMessage());
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
        public function addParent(Request $request){
            try {
                    // Validate input
                $validatedData = $request->validate([
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'address' => 'required|string|max:255',
                    'barangay' => 'required|string|max:255',
                    'region' => 'required|string|max:100',
                    'province' => 'required|string|max:100',
                    'city' => 'required|string|max:100',
                    'zip_code' => 'required|digits:4',
                    'contact_number' => 'required|string|max:15',
                    'email' => 'required|email|max:255',
                    'parentid' => 'required|string|max:12',
                    'category' => 'required|string',
                    'username' => 'required|string|max:255',
                    'account_status' => 'required|in:active,inactive',
                    'studentid' => 'nullable|string|max:12'
                ]);

                $parentIdKey = $request->parentid;
                $emailInput = $request->email;
                $usernameInput = $request->username;
                $studentId = $request->studentid;

                $usersRef = $this->database->getReference($this->tablename)->getValue();

                // Check parent ID
                if (!empty($usersRef) && array_key_exists($parentIdKey, $usersRef)) {
                    return redirect()->back()->with('status', 'Parent ID already exists!')->withInput();
                }


                // Autofill address from student
                if (!empty($studentId) && isset($usersRef[$studentId]) && $usersRef[$studentId]['role'] === 'student') {
                    $studentData = $usersRef[$studentId];
                    $validatedData['address'] = $studentData['address'] ?? $validatedData['address'];
                    $validatedData['barangay'] = $studentData['barangay'] ?? $validatedData['barangay'];
                    $validatedData['region'] = $studentData['region'] ?? $validatedData['region'];
                    $validatedData['province'] = $studentData['province'] ?? $validatedData['province'];
                    $validatedData['city'] = $studentData['city'] ?? $validatedData['city'];
                    $validatedData['zip_code'] = $studentData['zip_code'] ?? $validatedData['zip_code'];
                }

                // Get active school year
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

                $hashedPassword = $request->account_password;

                // If student ID is provided and valid, use the student's password
                if (!empty($studentId) && isset($usersRef[$studentId]) && $usersRef[$studentId]['role'] === 'student') {
                    $studentPassword = $usersRef[$studentId]['password'] ?? null;
                    if ($studentPassword) {
                        $hashedPassword = $studentPassword;
                    }
                }

                // Parent data
                $postData = [
                    'fname' => $request->first_name,
                    'lname' => $request->last_name,
                    'address' => $validatedData['address'],
                    'barangay' => $validatedData['barangay'],
                    'region' => $validatedData['region'],
                    'province' => $validatedData['province'],
                    'city' => $validatedData['city'],
                    'zip_code' => $validatedData['zip_code'],
                    'contact_number' => $request->contact_number,
                    'email' => $request->email,
                    'category' => $request->category,
                    'parentid' => $parentIdKey,
                    'studentid' => $studentId ?? null,
                    'schoolyear_id' => $activeSchoolYearId,
                    'role' => 'parent',
                    'username' => $request->username,
                    'password' => $hashedPassword,
                    'account_status' => $request->account_status,
                    'date_created' => Carbon::now()->toDateTimeString(),
                    'date_updated' => Carbon::now()->toDateTimeString(),
                    'last_login' => null,
                    'already_login' => 'false',

                ];

                // Save to Realtime Database
                $this->database->getReference($this->tablename . '/' . $parentIdKey)->set($postData);

                return redirect('mio/admin/parents')->with('status', 'Parent Added Successfully');

            } catch (\Throwable $e){
                return redirect()->back()->with('status', 'Error: ' . $e->getMessage())->withInput();
            }
        }



        public function getStudentData($id)
        {
            $usersRef = $this->database->getReference($this->tablename)->getValue();

            if (!isset($usersRef[$id])) {
                return response()->json(['error' => 'User not found'], 404);
            }

            if ($usersRef[$id]['role'] !== 'student') {
                return response()->json(['error' => 'User is not a student'], 403);
            }


            $student = $usersRef[$id];

            return response()->json([
                'first_name' => $student['fname'] ?? '',
                'last_name' => $student['lname'] ?? '',
                'grade_level' => $student['grade_level'] ?? 'N/A',
                'address' => $student['address'] ?? '',
                'barangay' => $student['barangay'] ?? '',
                'region' => $student['region'] ?? '',
                'email' => $student['email'] ?? '',
                'password' => $student['password'] ?? '',
                'province' => $student['province'] ?? '',
                'city' => $student['city'] ?? '',
                'zip_code' => $student['zip_code'] ?? '',
                'emergency_contact' => $student['emergency_contact'] ?? '',

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
            try {
                $oldKey = $id;
                $newKey = $request->parentid;

                $validatedData = $request->validate([
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'address' => 'required|string|max:255',
                    'barangay' => 'required|string|max:255',
                    'region' => 'required|string|max:100',
                    'province' => 'required|string|max:100',
                    'city' => 'required|string|max:100',
                    'zip_code' => 'required|digits:4',
                    'contact_number' => 'required|string|max:15',
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
                        if ($key !== $oldKey && isset($parent['parentid']) && $parent['parentid'] == $parentIdKey) {
                            return redirect()->back()->with('error', 'Parent ID already exists!')->withInput();
                        }
                    }
                }

                // Autofill address fields if valid studentid is provided
                if (!empty($studentId) && isset($parentsRef[$studentId]) && $parentsRef[$studentId]['role'] === 'student') {
                    $parentData = $parentsRef[$studentId];
                    $validatedData['address'] = $parentData['address'] ?? $validatedData['address'];
                    $validatedData['barangay'] = $parentData['barangay'] ?? $validatedData['barangay'];
                    $validatedData['region'] = $parentData['region'] ?? $validatedData['region'];
                    $validatedData['province'] = $parentData['province'] ?? $validatedData['province'];
                    $validatedData['city'] = $parentData['city'] ?? $validatedData['city'];
                    $validatedData['zip_code'] = $parentData['zip_code'] ?? $validatedData['zip_code'];
                }

                $existingData = $this->database->getReference($this->tablename.'/'.$oldKey)->getValue();

                $updateData = [
                    'fname' => $request->first_name,
                    'lname' => $request->last_name,
                    'address' => $validatedData['address'],
                    'barangay' => $validatedData['barangay'],
                    'region' => $validatedData['region'],
                    'province' => $validatedData['province'],
                    'city' => $validatedData['city'],
                    'zip_code' => $validatedData['zip_code'],
                    'contact_number' => $request->contact_number,
                    'email' => $existingData['email'] ?? $request->email,
                    'category' => $request->category,
                    'studentid' => $studentId ?? null,
                    'role' => 'parent',
                    'username' => $usernameInput,
                    'account_status' => $request->account_status,
                    'date_updated' => Carbon::now()->toDateTimeString(),
                    'parentid' => $parentIdKey,
                ];

                if ($request->filled('account_password')) {
                    $updateData['password'] = bcrypt($request->account_password);
                } else {
                    $updateData['password'] = $existingData['password'] ?? null;
                }

                if ($oldKey === $newKey) {
                    $this->database->getReference($this->tablename.'/'.$oldKey)->update($updateData);
                } else {
                    $this->database->getReference($this->tablename.'/'.$newKey)->set($updateData);
                    $this->database->getReference($this->tablename.'/'.$oldKey)->remove();
                }

                return redirect('mio/admin/parents')->with('status', 'Parent updated successfully.');

            } catch (\Throwable $e) {
                return redirect()->back()->with('error', 'An error occurred while updating the parent: ' . $e->getMessage())->withInput();
            }
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
