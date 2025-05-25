<?php

namespace App\Http\Controllers\Enrollment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Kreait\Firebase\Factory;
use Carbon\Carbon;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Exception\Auth\EmailExists;
use Kreait\Firebase\Exception\AuthException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\InvalidPassword;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Exception\Auth\InvalidArgument;
use Illuminate\Support\Str;

use function Illuminate\Log\log;

class EnrollController extends Controller
{
    protected $database;
    protected $auth;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(base_path('storage/firebase/firebase.json'))
            ->withDatabaseUri('https://miolms-default-rtdb.firebaseio.com');

        $this->database = $factory->createDatabase();
        $this->auth = $factory->createAuth(); // Firebase Auth instance
    }

    public function showDashboard()
    {
        $user = session('enrollment_user');
        $enrollmentId = $user['ID'] ?? null;

        $enrollStatus = $this->getEnrollStatus();

        $adminFeedback = null;

        if ($enrollmentId) {
            $enrolleeRef = $this->database->getReference("enrollment/enrollees/{$enrollmentId}");
            $enrolleeSnapshot = $enrolleeRef->getSnapshot();

            if ($enrolleeSnapshot->exists()) {
                $enrolleeData = $enrolleeSnapshot->getValue();
                $adminFeedback = $enrolleeData['feedback_admin'] ?? null;
            }
        }

        return view('enrollment-panel.enrollment-panel', [
            'page' => 'enroll-dashboard',
            'user' => $user,
            'enrollStatus' => $enrollStatus,
            'adminFeedback' => $adminFeedback, // 💡 pass to view
        ]);
    }

   public function getEnrollStatus()
    {
        // Assume you have the logged-in user's enrollment ID or Firebase UID
        // For example, if stored in session or from Firebase Auth:
        $enrollmentId = session('enrollment_user')['ID'] ?? null;

        if (!$enrollmentId) {
            // Handle missing ID, maybe return default or redirect
            return null;
        }

        // Reference path to the enrollee in Firebase
        $enrolleeRef = $this->database->getReference("enrollment/enrollees/{$enrollmentId}");

        // Get enrollee data snapshot
        $enrolleeSnapshot = $enrolleeRef->getSnapshot();

        if (!$enrolleeSnapshot->exists()) {
            // Handle case if no data found
            return null;
        }

        Log::info($enrollmentId);



        $enrolleeData = $enrolleeSnapshot->getValue();


        Log::info($enrolleeData);


        // Return the enroll_status field if exists
        return $enrolleeData['enroll_status'] ?? null;
    }

    public function showAssessmentPage()
    {
        $user = session('enrollment_user');

        if (!$user || !isset($user['ID'])) {
            return redirect()->route('enroll-login')->with('error', 'Please log in first.');
        }

        $status = $user['enroll_status'] ?? null;

        // ✅ Check if status is exactly 'Assessment'
        if ($status !== 'Assessment') {
            return redirect()->route('enroll-dashboard')->with('error', 'You are not yet eligible for the assessment.');

        }

        $assessment = $user['Assessment'] ?? null;

        return view('enrollment-panel.enrollment-panel', [
            'page' => 'enroll-assessment',
            'assessment' => $assessment,
            'status' => $status,
        ]);
    }

        public function startAssessment(Request $request)
        {
            // Correct session key
            $userId = session('firebase_uid');

            if (!$userId) {
                return redirect()->back()->with('error', 'User session expired or invalid.');
            }

            $userRef = $this->database->getReference('enrollment/enrollees/' . $userId);
            $user = $userRef->getValue();

            if (!$user || ($user['enroll_status'] ?? null) !== 'Assessment') {
                return redirect()->back()->with('error', 'You are not yet eligible for the assessment.');
            }

            return view('enrollment-panel.enrollment-panel', [
                'page' => 'main-assessment',
                'user' => $user
            ]);
        }

    public function mainAssessment2()
    {
        // Retrieve flash data from session (results passed from submit)
        $speechResults = session('speech_results', []);
        $auditoryResults = session('auditory_results', []);

        return view('enrollment-panel.enrollment-panel', [
            'page' => 'main-assessment2',
            'speech_results' => $speechResults,
            'auditory_results' => $auditoryResults,
        ]);
    }




    public function assessmentPhysical()
    {
        $userId = session('firebase_uid');
        $user = $this->database->getReference('enrollment/enrollees/' . $userId)->getValue();

        return view('enrollment-panel.pages.assessment.physical', compact('user'));
    }




    public function login(Request $request)
    {
        $request->validate([
            'user_login' => 'required|string',
            'user_pass' => 'required|string',
        ]);

        $usernameOrEmail = $request->input('user_login');
        $password = $request->input('user_pass');
        $email = null;

        try {
            if (filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
                $email = $usernameOrEmail;
            } else {
                // Find email by username in enrollment/users
                $allUsers = $this->database->getReference('enrollment/enrollees')->getValue();
                if ($allUsers) {
                    foreach ($allUsers as $uid => $data) {
                        if (isset($data['username']) && $data['username'] === $usernameOrEmail) {
                            $email = $data['email'] ?? null;
                            break;
                        }
                    }
                }
                if (!$email) {
                    return redirect()->back()->with('error', 'Username not found.');
                }
            }

            $signInResult = $this->auth->signInWithEmailAndPassword($email, $password);
            $firebaseUser = $signInResult->firebaseUserId();
            Log::info('Firebase UID after login: ' . $firebaseUser);

            // Get user data for session if needed
            $user = $this->database->getReference('enrollment/enrollees/' . $firebaseUser)->getValue();

            if (!$user) {
                return redirect()->back()->with('error', 'User not found in database.');
            }

            session([
                'firebase_uid' => $firebaseUser,
                'enrollment_user' => $user, // Store entire user info for middleware access
            ]);


            return redirect()->route('enroll-dashboard');

        } catch (InvalidPassword $e) {
            return redirect()->back()->with('error', 'Incorrect password.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Login failed: ' . $e->getMessage());
        }
    }



    public function signup(Request $request)
    {
        $request->validate([
            'user_email' => 'required|email',
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'user_pass' => 'required|string|confirmed|min:6',
        ]);

        $email = $request->input('user_email');
        $fname = $request->input('fname');
        $lname = $request->input('lname');
        $password = $request->input('user_pass');

        try {
            // Generate unique enrollee ID: EN[YEAR][MONTH][DAY]XXX
            $dateCode = Carbon::now()->format('Ymd');
            $baseRef = $this->database->getReference('enrollment/enrollees');
            $existing = $baseRef->getValue();

            $countToday = 0;
            if ($existing) {
                foreach ($existing as $key => $value) {
                    if (Str::startsWith($key, 'EN' . $dateCode)) {
                        $countToday++;
                    }
                }
            }

            $suffix = str_pad($countToday + 1, 3, '0', STR_PAD_LEFT);
            $generatedID = 'EN' . $dateCode . $suffix;

            // Create user in Firebase Auth with the generated enrollee ID as UID
            $userProperties = [
                'uid' => $generatedID, // Set custom UID
                'email' => $email,
                'password' => $password,
                'displayName' => $fname . ' ' . $lname,
            ];

            $createdUser = $this->auth->createUser($userProperties);

            $uid = $createdUser->uid;

            $now = Carbon::now()->toDateTimeString();

            // Save enrollee record
            $enrolleeData = [
                'ID' => $generatedID,
                'fname' => $fname,
                'lname' => $lname,
                'enroll_status' => 'NotStarted',
                'Assessment' => '',
                'created_at' => $now,
                'enrolled_at' => '',
                'email' => $email,
                'username' => $email, // Use email as username
                'password' => Hash::make($password),
                'feedback_admin' => '',
            ];

            $this->database
                ->getReference('enrollment/enrollees/' . $generatedID)
                ->set($enrolleeData);

            return redirect()->back()->with('status', 'Sign up successful! Please log in.');

        } catch (EmailExists $e) {
            return redirect()->back()->with('error', 'Email already in use.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Sign up failed: ' . $e->getMessage());
        }
    }

    public function showEnrollmentForm()
    {
        Log::info('Session contents: ' . json_encode(session()->all()));

        $user = session('enrollment_user'); // Assuming you store authenticated user in session

        if (!$user || !isset($user['email'])) {
            return redirect()->route('enroll-login')->with('error', 'Please login first.');
        }

        $email = $user['email'];
        $enrollees = $this->database->getReference('enrollment/enrollees')->getValue();
        $enrollData = null;
        $enrollId = null;

        foreach ($enrollees as $id => $info) {
            if (isset($info['email']) && $info['email'] === $email) {
                $enrollId = $id;
                $enrollData = $info;
                break;
            }
        }

        if (!$enrollId) {
            return redirect()->route('enroll-login')->with('error', 'Enrollment record not found.');
        }

        $form = $enrollData['enrollment_form'] ?? null;

        $status = $enrollData['enroll_status'] ?? 'NotStarted';

        return view('enrollment-panel.enrollment-panel', [
            'page' => 'enroll-form',
            'form' => $form,
            'status' => $status,
        ]);

    }


    public function submitEnrollmentForm(Request $request)
    {
        Log::info('submitEnrollmentForm called');
        // Validate input — adjust rules based on your form field names
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'gender' => 'required|string',
            'age' => 'required|integer',
            'birthday' => 'required|date',
            'street' => 'required|string',
            'barangay' => 'required|string',
            'region' => 'required|string',
            'province' => 'required|string',
            'city' => 'required|string',
            'zip_code' => 'required|string|min:4|max:4',
            'contact_number' => 'required|string',
            'emergency_contact' => 'required|string',
            'emergency_name' => 'required|string',
            'previous_school' => 'required|string',
            'grade_level' => 'required|integer',
            'medical_history' => 'nullable|string',
            'disability' => 'required|string',
            'hearing_loss' => 'nullable|string',
            'hearing_identity' => 'required|string',
            'assistive_devices' => 'nullable|string',
            'health_notes' => 'nullable|string',

            'payment' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'good_moral_files' => 'required|array|min:1',
            'good_moral_files.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',

            'health_certificate_files' => 'required|array|min:1',
            'health_certificate_files.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',

            'psa_birth_certificate_files' => 'required|array|min:1',
            'psa_birth_certificate_files.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',

            'form_137_files' => 'required|array|min:1',
            'form_137_files.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Get currently logged in user's enrollee ID from session
    $enrollmentUser = session('enrollment_user');
        if (!$enrollmentUser || !isset($enrollmentUser['ID'])) {
            return redirect()->back()->with('error', 'User not logged in.');
        }
        $enrolleeId = $enrollmentUser['ID'];
        Log::info('Enrollee ID: ' . $enrolleeId);

        // Collect form data except files
        $formData = $request->only([
            'first_name', 'last_name', 'gender', 'age', 'birthday', 'street', 'barangay', 'region',
            'province', 'city', 'zip_code', 'contact_number', 'emergency_contact', 'emergency_name',
            'previous_school', 'grade_level', 'medical_history', 'disability', 'hearing_loss',
            'hearing_identity', 'assistive_devices', 'health_notes'
        ]);

        // Upload files helper
        $uploadMultipleFiles = function ($files, $folder, $prefix) {
            $paths = [];
            foreach ($files as $file) {
                $filename = time() . '_' . $prefix . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/enrollment/' . $folder, $filename);
                $paths[] = 'storage/enrollment/' . $folder . '/' . $filename;
            }
            return $paths;
        };

        // Upload proof of payment (single file)
        if ($request->hasFile('payment')) {
            $paymentFile = $request->file('payment');
            $paymentFilename = time() . '_payment.' . $paymentFile->getClientOriginalExtension();
            $paymentFile->storeAs('public/enrollment/payment', $paymentFilename);
            $formData['payment_proof_path'] = 'storage/enrollment/payment/' . $paymentFilename;
        }

        // Upload multiple files for each category
        if ($request->hasFile('good_moral_files')) {
            $formData['good_moral_paths'] = $uploadMultipleFiles($request->file('good_moral_files'), 'good_moral', 'goodmoral');
        }

        if ($request->hasFile('health_certificate_files')) {
            $formData['health_certificate_paths'] = $uploadMultipleFiles($request->file('health_certificate_files'), 'health_certificate', 'healthcert');
        }

        if ($request->hasFile('psa_birth_certificate_files')) {
            $formData['psa_birth_certificate_paths'] = $uploadMultipleFiles($request->file('psa_birth_certificate_files'), 'psa_birth_certificate', 'psa');
        }

        if ($request->hasFile('form_137_files')) {
            $formData['form_137_paths'] = $uploadMultipleFiles($request->file('form_137_files'), 'form_137', 'form137');
        }

        // Add timestamps
        $formData['submitted_at'] = now()->toDateTimeString();

        // Save the enrollment form data under
        // enrollment/enrollees/{enrolleeId}/enrollment_form
        Log::info(session()->all());
        $this->database
            ->getReference('enrollment/enrollees/' . $enrolleeId . '/enrollment_form')
            ->set($formData);

            // Update enroll_status to "Registered"
        $this->database
            ->getReference('enrollment/enrollees/' . $enrolleeId . '/enroll_status')
            ->set('Registered');


        return redirect()->back()->with('success', 'Enrollment form submitted successfully!');
    }


public function logout(Request $request)
{
    // Clear all session data related to enrollment
    $request->session()->forget(['firebase_uid', 'enrollment_user']);
    $request->session()->flush(); // optional, clears all session data

    // Redirect to enrollment login page with a message
    return redirect()->route('enroll-login')->with('status', 'You have been logged out successfully.');
}


    public function showAdminEnrollment()
    {
        $enrollees = $this->database->getReference('enrollment/enrollees')->getValue();

        return view('mio.head.admin-panel', [
            'page' => 'admin-enrollment',
            'enrollees' => $enrollees
        ]);
    }

    public function viewAdminEnrollee($id)
    {
        $enrollee = $this->database->getReference('enrollment/enrollees/' . $id)->getValue();

        if (!$enrollee) {
            return redirect()->route('enrollment')->with('error', 'Enrollee not found.');
        }

        return view('mio.head.admin-panel', [
            'page' => 'admin-enrollee',
            'enrollee' => $enrollee,
            'id' => $id
        ]);
    }

    public function updateEnrolleeStatus(Request $request, $id)
    {
        $assessment = $request->input('feedback_admin');
        $status = $request->input('enroll_status');

        $enrolleeRef = $this->database->getReference('enrollment/enrollees/' . $id);

        // Check if enrollee exists
        if (!$enrolleeRef->getSnapshot()->exists()) {
            return redirect()->route('mio.view-enrollee', ['id' => $id])->with('error', 'Enrollee not found.');
        }

        // Update fields
        $enrolleeRef->update([
            'feedback_admin' => $assessment,
            'enroll_status' => $status
        ]);

        return redirect()->route('mio.view-enrollee', ['id' => $id])
            ->with('success', 'Enrollee feedback and status updated successfully.');
    }






}
