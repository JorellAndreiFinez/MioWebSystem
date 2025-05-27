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

        // Check if status is exactly 'Assessment'
        if ($status !== 'Assessment') {
            return redirect()->route('enroll-dashboard')->with('error', 'You are not yet eligible for the assessment.');
        }

        $userId = $user['ID'];

        // Fetch statuses directly from Firebase
        $speechAuditoryStatusRef = $this->database->getReference('enrollment/enrollees/' . $userId . '/Assessment/Speech_Auditory/status');
        $speechAuditoryStatus = $speechAuditoryStatusRef->getValue();

        $readingStatusRef = $this->database->getReference('enrollment/enrollees/' . $userId . '/Assessment/Reading/status');
        $readingStatus = $readingStatusRef->getValue();

        return view('enrollment-panel.enrollment-panel', [
            'page' => 'enroll-assessment',
            'assessment' => $user['Assessment'] ?? null,
            'status' => $status,
            'speechAuditoryStatus' => $speechAuditoryStatus,
            'readingStatus' => $readingStatus,
            'user' => $user,
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

            $speechAuditoryStatusRef = $this->database->getReference('enrollment/enrollees/' . $userId . '/Assessment/Speech_Auditory/status');
            $speechAuditoryStatus = $speechAuditoryStatusRef->getValue();

            $readingStatusRef = $this->database->getReference('enrollment/enrollees/' . $userId . '/Assessment/Reading/status');
            $readingStatus = $readingStatusRef->getValue();

            $sentenceStatusRef = $this->database->getReference('enrollment/enrollees/' . $userId . '/Assessment/Sentence/status');
            $sentenceStatus = $sentenceStatusRef->getValue();

            // Check both statuses to determine what to show next
            if ($speechAuditoryStatus !== 'done') {
                return view('enrollment-panel.enrollment-panel', [
                    'page' => 'main-assessment',
                    'user' => $user,
                ]);
            }

            if ($readingStatus !== 'done') {
                return view('enrollment-panel.enrollment-panel', [
                    'page' => 'main-assessment2',
                    'user' => $user,
                ]);
            }

            if ($sentenceStatus !== 'done') {
                return view('enrollment-panel.enrollment-panel', [
                    'page' => 'main-assessment3',
                    'user' => $user,
                ]);
            }

             // Add your questions array here for the sentence test page:
            $questions = [
                // Multiple Choice (1-12)
                1 => [
                    'type' => 'multiple_choice',
                    'sentence' => 'She ___ to school every day.',
                    'choices' => ['go', 'goes', 'going', 'gone'],
                ],
                2 => [
                    'type' => 'multiple_choice',
                    'sentence' => 'They have ___ finished their homework.',
                    'choices' => ['already', 'yet', 'just', 'still'],
                ],
                3 => [
                    'type' => 'multiple_choice',
                    'sentence' => 'I will call you when I ___ home.',
                    'choices' => ['get', 'got', 'will get', 'getting'],
                ],
                4 => [
                    'type' => 'multiple_choice',
                    'sentence' => 'He ___ playing football now.',
                    'choices' => ['is', 'are', 'was', 'be'],
                ],
                5 => [
                    'type' => 'multiple_choice',
                    'sentence' => 'We ___ to the park yesterday.',
                    'choices' => ['go', 'goes', 'went', 'going'],
                ],
                6 => [
                    'type' => 'multiple_choice',
                    'sentence' => 'They ___ never been to Japan.',
                    'choices' => ['have', 'has', 'had', 'having'],
                ],
                7 => [
                    'type' => 'multiple_choice',
                    'sentence' => 'I ___ already eaten lunch.',
                    'choices' => ['has', 'have', 'had', 'having'],
                ],
                8 => [
                    'type' => 'multiple_choice',
                    'sentence' => 'She speaks English ___ than her sister.',
                    'choices' => ['good', 'well', 'better', 'best'],
                ],
                9 => [
                    'type' => 'multiple_choice',
                    'sentence' => 'If it ___ rain, we will cancel the picnic.',
                    'choices' => ['will', 'is', 'does', 'did'],
                ],
                10 => [
                    'type' => 'multiple_choice',
                    'sentence' => 'They ___ playing when I arrived.',
                    'choices' => ['were', 'was', 'are', 'is'],
                ],
                11 => [
                    'type' => 'multiple_choice',
                    'sentence' => 'She has been working here ___ five years.',
                    'choices' => ['since', 'for', 'during', 'while'],
                ],
                12 => [
                    'type' => 'multiple_choice',
                    'sentence' => 'This is the ___ movie I have ever seen.',
                    'choices' => ['good', 'better', 'best', 'well'],
                ],

                // Fill in the blanks (13-20)
                13 => [
                    'type' => 'fill_in_blank',
                    'sentence' => 'The cat is ___ the table.',
                    'answer_placeholder' => 'Type your answer here',
                ],
                14 => [
                    'type' => 'fill_in_blank',
                    'sentence' => 'He ___ playing football now.',
                    'answer_placeholder' => 'Type your answer here',
                ],
                15 => [
                    'type' => 'fill_in_blank',
                    'sentence' => 'We ___ to the mall yesterday.',
                    'answer_placeholder' => 'Type your answer here',
                ],
                16 => [
                    'type' => 'fill_in_blank',
                    'sentence' => 'They have been friends ___ childhood.',
                    'answer_placeholder' => 'Type your answer here',
                ],
                17 => [
                    'type' => 'fill_in_blank',
                    'sentence' => 'She is better at math ___ her brother.',
                    'answer_placeholder' => 'Type your answer here',
                ],
                18 => [
                    'type' => 'fill_in_blank',
                    'sentence' => 'I will call you when I ___ home.',
                    'answer_placeholder' => 'Type your answer here',
                ],
                19 => [
                    'type' => 'fill_in_blank',
                    'sentence' => 'They ___ finished their work already.',
                    'answer_placeholder' => 'Type your answer here',
                ],
                20 => [
                    'type' => 'fill_in_blank',
                    'sentence' => 'Please ___ the door when you leave.',
                    'answer_placeholder' => 'Type your answer here',
                ],

                // Sentence Ordering (21-25)
                21 => [
                    'type' => 'sentence_order',
                    'words' => ['is', 'the', 'dog', 'brown'],
                    'sentence_hint' => 'Arrange the words to form a correct sentence.',
                ],
                22 => [
                    'type' => 'sentence_order',
                    'words' => ['they', 'go', 'to', 'school', 'every', 'day'],
                    'sentence_hint' => 'Arrange the words to form a correct sentence.',
                ],
                23 => [
                    'type' => 'sentence_order',
                    'words' => ['my', 'favorite', 'color', 'blue', 'is'],
                    'sentence_hint' => 'Arrange the words to form a correct sentence.',
                ],
                24 => [
                    'type' => 'sentence_order',
                    'words' => ['she', 'reading', 'is', 'book', 'a'],
                    'sentence_hint' => 'Arrange the words to form a correct sentence.',
                ],
                25 => [
                    'type' => 'sentence_order',
                    'words' => ['we', 'going', 'are', 'to', 'park', 'the'],
                    'sentence_hint' => 'Arrange the words to form a correct sentence.',
                ],

                // True/False grammar questions (26-30)
                26 => [
                    'type' => 'true_false',
                    'statement' => 'The word "quickly" is an adjective.',
                ],
                27 => [
                    'type' => 'true_false',
                    'statement' => 'She don’t like apples.',
                ],
                28 => [
                    'type' => 'true_false',
                    'statement' => 'The past tense of "run" is "ran".',
                ],
                29 => [
                    'type' => 'true_false',
                    'statement' => 'We use "an" before words starting with a vowel sound.',
                ],
                30 => [
                    'type' => 'true_false',
                    'statement' => 'Adjectives describe verbs.',
                ],
            ];



            // If both are done, go to sentence test
            return view('enrollment-panel.enrollment-panel', [
                'page' => 'main-assessment4',
                'user' => $user,
                'questions' => $questions, // Pass questions to the view
            ]);
        }

    public function mainAssessment2()
    {
        // Retrieve flash data from session (results passed from submit)
        $speechResults = session('speech_results', []);
        $auditoryResults = session('auditory_results', []);

        $uid = Session::get('firebase_uid');

        if ($uid) {
            $statusRef = $this->database->getReference("enrollment/enrollees/{$uid}/Assessment/Reading/status");
            $status = $statusRef->getValue();

            // If already done, redirect to dashboard or another page
            if ($status === 'done') {
                return view('enrollment-panel.enrollment-panel', [
                    'page' => 'main-assessment3',
                    'speech_results' => $speechResults,
                    'auditory_results' => $auditoryResults,
                ]);
            }
        }

        return view('enrollment-panel.enrollment-panel', [
            'page' => 'main-assessment2',
            'speech_results' => $speechResults,
            'auditory_results' => $auditoryResults,
        ]);
    }

    public function mainAssessment3()
    {
        // Retrieve flash data from session (results passed from submit)
        $speechResults = session('speech_results', []);
        $auditoryResults = session('auditory_results', []);

        $uid = Session::get('firebase_uid');

        if ($uid) {
            $statusRef = $this->database->getReference("enrollment/enrollees/{$uid}/Assessment/Reading/status");
            $status = $statusRef->getValue();

            // If already done, redirect to dashboard or another page
            if ($status === 'done') {
                return view('enrollment-panel.enrollment-panel', [
                    'page' => 'main-assessment4',
                    'speech_results' => $speechResults,
                    'auditory_results' => $auditoryResults,
                ]);
            }
        }

        return view('enrollment-panel.enrollment-panel', [
            'page' => 'main-assessment3',
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

    public function editAssessment($type)
    {
        if (!in_array($type, ['physical', 'written'])) {
            abort(404);
        }

        $speech = $this->database
            ->getReference("enrollment/assessment_settings/$type/speech")
            ->getValue() ?? [];

        $auditory = $this->database
            ->getReference("enrollment/assessment_settings/$type/auditory")
            ->getValue() ?? [];

        $sentence = $this->database
            ->getReference("enrollment/assessment_settings/$type/sentences")
            ->getValue() ?? [];

        $blank = $this->database
            ->getReference("enrollment/assessment_settings/$type/fillblanks")
            ->getValue() ?? [];

        if($type === "physical"){
            return view('mio.head.admin-panel', [
            'page' => 'edit-assessment',
            'type' => $type,
            'speech' => $speech,
            'auditory' => $auditory,
            'sentence' => $sentence,
            'fillblanks' => $blank,
        ]);

        } elseif ($type === "written") {
            return view('mio.head.admin-panel', [
            'page' => 'edit-assessment2',
            'type' => $type,
            'speech' => $speech,
            'auditory' => $auditory,
            'sentence' => $sentence,
            'fillblanks' => $blank,
        ]);
     }
    }

    public function saveSpeechPhrases(Request $request, $type)
{
    if (!in_array($type, ['physical', 'written'])) {
        abort(404);
    }

    $allowedLevels = ['Easy', 'Medium', 'Hard'];

    // Load existing data from Firebase
    $existingData = $this->database
        ->getReference("enrollment/assessment_settings/$type/speech")
        ->getValue() ?? [];

    $speech = $request->input('speech', []);

    foreach ($speech as $key => $phrase) {
        // Skip if 'text' or 'level' keys are missing to avoid undefined key error
        if (!isset($phrase['text'], $phrase['level'])) {
            continue;
        }

        // Skip if level is invalid
        if (!in_array($phrase['level'], $allowedLevels)) {
            continue;
        }

        // Keep existing created_at if available, otherwise set current timestamp
        $createdAt = $existingData[$key]['created_at'] ?? now()->toDateTimeString();

        // Update or add phrase data in existingData array
        $existingData[$key] = [
            'text' => $phrase['text'],
            'level' => $phrase['level'],
            'speechID' => $key,
            'created_at' => $createdAt,
            'updated_at' => now()->toDateTimeString(),
        ];
    }

    // Handle new speech phrase addition if any
    $newPhrase = $request->input('new_speech');
    if ($newPhrase && isset($newPhrase['text'], $newPhrase['level'])) {
        if (in_array($newPhrase['level'], $allowedLevels)) {
            $newKey = 'SP' . now()->format('Ymd') . str_pad(count($existingData) + 1, 3, '0', STR_PAD_LEFT);

            $existingData[$newKey] = [
                'text' => $newPhrase['text'],
                'level' => $newPhrase['level'],
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];
        }
    }

    // Process deletions and updates directly to Firebase for consistency
    foreach ($speech as $speechID => $data) {
        if (isset($data['_delete']) && $data['_delete'] == '1') {
            // Delete phrase from Firebase
            $this->database->getReference("enrollment/assessment_settings/$type/speech/$speechID")->remove();

            // Also remove from local array to keep it in sync
            unset($existingData[$speechID]);
        } else {
            // Update phrase in Firebase (ensure keys exist before accessing)
            if (isset($data['text'], $data['level'])) {
                $this->database->getReference("enrollment/assessment_settings/$type/speech/$speechID")
                    ->update([
                        'text' => $data['text'],
                        'level' => $data['level'],
                        'updated_at' => now()->toDateTimeString(),
                        // Preserve created_at if exists in existingData
                        'created_at' => $existingData[$speechID]['created_at'] ?? now()->toDateTimeString(),
                    ]);
            }
        }
    }

    // Finally, write the full updated data back to Firebase (optional redundancy, but safer)
    $this->database
        ->getReference("enrollment/assessment_settings/$type/speech")
        ->set($existingData);

    return redirect()
        ->back()
        ->with('success', 'Speech phrases saved successfully!');
}

    public function saveAuditoryPhrases(Request $request, $type)
    {
        if (!in_array($type, ['physical', 'written'])) {
            abort(404);
        }

        $allowedLevels = ['Easy', 'Medium', 'Hard'];

        // Load existing auditory data from Firebase
        $existingData = $this->database
            ->getReference("enrollment/assessment_settings/$type/auditory")
            ->getValue() ?? [];

        $auditory = $request->input('auditory', []);

        foreach ($auditory as $key => $phrase) {
            // Skip if 'text' or 'level' keys are missing to avoid undefined key error
            if (!isset($phrase['text'], $phrase['level'])) {
                continue;
            }

            // Skip if level is invalid
            if (!in_array($phrase['level'], $allowedLevels)) {
                continue;
            }

            // Keep existing created_at if available, otherwise set current timestamp
            $createdAt = $existingData[$key]['created_at'] ?? now()->toDateTimeString();

            // Update or add phrase data in existingData array
            $existingData[$key] = [
                'text' => $phrase['text'],
                'level' => $phrase['level'],
                'auditoryID' => $key,
                'created_at' => $createdAt,
                'updated_at' => now()->toDateTimeString(),
            ];
        }

        // Handle new auditory phrase addition if any
        $newPhrase = $request->input('new_auditory');
        if ($newPhrase && isset($newPhrase['text'], $newPhrase['level'])) {
            if (in_array($newPhrase['level'], $allowedLevels)) {
                $newKey = 'AU' . now()->format('Ymd') . str_pad(count($existingData) + 1, 3, '0', STR_PAD_LEFT);

                $existingData[$newKey] = [
                    'text' => $newPhrase['text'],
                    'level' => $newPhrase['level'],
                    'created_at' => now()->toDateTimeString(),
                    'updated_at' => now()->toDateTimeString(),
                ];
            }
        }

        // Process deletions and updates directly to Firebase for consistency
        foreach ($auditory as $auditoryID => $data) {
            if (isset($data['_delete']) && $data['_delete'] == '1') {
                // Delete phrase from Firebase
                $this->database->getReference("enrollment/assessment_settings/$type/auditory/$auditoryID")->remove();

                // Also remove from local array to keep it in sync
                unset($existingData[$auditoryID]);
            } else {
                // Update phrase in Firebase (ensure keys exist before accessing)
                if (isset($data['text'], $data['level'])) {
                    $this->database->getReference("enrollment/assessment_settings/$type/auditory/$auditoryID")
                        ->update([
                            'text' => $data['text'],
                            'level' => $data['level'],
                            'updated_at' => now()->toDateTimeString(),
                            // Preserve created_at if exists in existingData
                            'created_at' => $existingData[$auditoryID]['created_at'] ?? now()->toDateTimeString(),
                        ]);
                }
            }
        }

        // Finally, write the full updated data back to Firebase (optional redundancy, but safer)
        $this->database
            ->getReference("enrollment/assessment_settings/$type/auditory")
            ->set($existingData);

        return redirect()
            ->back()
            ->with('success', 'Auditory phrases saved successfully!');
    }

    public function saveSentence(Request $request, $type)
    {
        if (!in_array($type, ['physical', 'written'])) {
            abort(404);
        }

        $allowedLevels = ['Easy', 'Medium', 'Hard'];

        // Load existing sentence data from Firebase
        $existingData = $this->database
            ->getReference("enrollment/assessment_settings/$type/sentences")
            ->getValue() ?? [];

        $sentences = $request->input('sentence', []);

        foreach ($sentences as $key => $sentence) {
            // Skip if 'text' or 'level' keys are missing to avoid undefined key error
            if (!isset($sentence['text'], $sentence['level'])) {
                continue;
            }

            // Skip if level is invalid
            if (!in_array($sentence['level'], $allowedLevels)) {
                continue;
            }

            // Keep existing created_at if available, otherwise set current timestamp
            $createdAt = $existingData[$key]['created_at'] ?? now()->toDateTimeString();

            // Update or add sentence data in existingData array
            $existingData[$key] = [
                'text' => $sentence['text'],
                'level' => $sentence['level'],
                'sentenceID' => $key,
                'created_at' => $createdAt,
                'updated_at' => now()->toDateTimeString(),
            ];
        }

        // Handle new sentence addition if any
        $newSentence = $request->input('new_sentence');
        if ($newSentence && isset($newSentence['text'], $newSentence['level'])) {
            if (in_array($newSentence['level'], $allowedLevels)) {
                $newKey = 'SN' . now()->format('Ymd') . str_pad(count($existingData) + 1, 3, '0', STR_PAD_LEFT);

                $existingData[$newKey] = [
                    'text' => $newSentence['text'],
                    'level' => $newSentence['level'],
                    'created_at' => now()->toDateTimeString(),
                    'updated_at' => now()->toDateTimeString(),
                ];
            }
        }

        // Process deletions and updates directly to Firebase for consistency
        foreach ($sentences as $sentenceID => $data) {
            if (isset($data['_delete']) && $data['_delete'] == '1') {
                // Delete sentence from Firebase
                $this->database->getReference("enrollment/assessment_settings/$type/sentences/$sentenceID")->remove();

                // Also remove from local array to keep it in sync
                unset($existingData[$sentenceID]);
            } else {
                // Update sentence in Firebase (ensure keys exist before accessing)
                if (isset($data['text'], $data['level'])) {
                    $this->database->getReference("enrollment/assessment_settings/$type/sentences/$sentenceID")
                        ->update([
                            'text' => $data['text'],
                            'level' => $data['level'],
                            'updated_at' => now()->toDateTimeString(),
                            // Preserve created_at if exists in existingData
                            'created_at' => $existingData[$sentenceID]['created_at'] ?? now()->toDateTimeString(),
                        ]);
                }
            }
        }

        // Finally, write the full updated data back to Firebase (optional redundancy, but safer)
        $this->database
            ->getReference("enrollment/assessment_settings/$type/sentences")
            ->set($existingData);

        return redirect()
            ->back()
            ->with('success', 'Sentences saved successfully!');
    }

    public function saveFillBlanks(Request $request, $type)
{
    if (!in_array($type, ['physical', 'written'])) {
        abort(404);
    }

    $allowedLevels = ['Easy', 'Medium', 'Hard'];

    // Load existing fill-in-the-blank data from Firebase
    $existingData = $this->database
        ->getReference("enrollment/assessment_settings/$type/fillblanks")
        ->getValue() ?? [];

    $fillblanks = $request->input('fillblanks', []);

    foreach ($fillblanks as $key => $item) {
        if (!isset($item['text'], $item['correct'], $item['a'], $item['b'], $item['c'], $item['level'])) {
            continue;
        }

        if (!in_array($item['level'], $allowedLevels)) {
            continue;
        }

        $createdAt = $existingData[$key]['created_at'] ?? now()->toDateTimeString();

        // Map correct letter to actual answer
        $correctAnswerMap = [
            'A' => $item['a'],
            'B' => $item['b'],
            'C' => $item['c'],
        ];
        $actualAnswer = $correctAnswerMap[$item['correct']] ?? '';

        $existingData[$key] = [
            'text' => $item['text'],
            'correct' => $item['correct'],
            'a' => $item['a'],
            'b' => $item['b'],
            'c' => $item['c'],
            'level' => $item['level'],
            'blankID' => $key,
            'created_at' => $createdAt,
            'updated_at' => now()->toDateTimeString(),
            'full_answer' => $this->cleanFillBlankAnswer($item['text'], $actualAnswer),
        ];
    }

    // Handle new fill-in-the-blank entry
    $newBlank = $request->input('new_blank');
    if ($newBlank && isset($newBlank['text'], $newBlank['correct'], $newBlank['a'], $newBlank['b'], $newBlank['c'], $newBlank['level'])) {
        if (in_array($newBlank['level'], $allowedLevels)) {
            $newKey = 'FB' . now()->format('Ymd') . str_pad(count($existingData) + 1, 3, '0', STR_PAD_LEFT);

            $correctAnswerMap = [
                'A' => $newBlank['a'],
                'B' => $newBlank['b'],
                'C' => $newBlank['c'],
            ];
            $actualAnswer = $correctAnswerMap[$newBlank['correct']] ?? '';

            $existingData[$newKey] = [
                'text' => $newBlank['text'],
                'correct' => $newBlank['correct'],
                'a' => $newBlank['a'],
                'b' => $newBlank['b'],
                'c' => $newBlank['c'],
                'level' => $newBlank['level'],
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
                'full_answer' => $this->cleanFillBlankAnswer($newBlank['text'], $actualAnswer),

            ];
        }
    }

    // Process deletions and updates
    foreach ($fillblanks as $blankID => $data) {
        if (isset($data['_delete']) && $data['_delete'] == '1') {
            $this->database->getReference("enrollment/assessment_settings/$type/fillblanks/$blankID")->remove();
            unset($existingData[$blankID]);
        } else {
            if (isset($data['text'], $data['correct'], $data['a'], $data['b'], $data['c'], $data['level'])) {
                $correctAnswerMap = [
                    'A' => $data['a'],
                    'B' => $data['b'],
                    'C' => $data['c'],
                ];
                $actualAnswer = $correctAnswerMap[$data['correct']] ?? '';

                $this->database->getReference("enrollment/assessment_settings/$type/fillblanks/$blankID")
                    ->update([
                        'text' => $data['text'],
                        'correct' => $data['correct'],
                        'a' => $data['a'],
                        'b' => $data['b'],
                        'c' => $data['c'],
                        'level' => $data['level'],
                        'updated_at' => now()->toDateTimeString(),
                        'created_at' => $existingData[$blankID]['created_at'] ?? now()->toDateTimeString(),
                        'full_answer' => $this->cleanFillBlankAnswer($data['text'], $actualAnswer),

                    ]);
            }
        }
    }

    // Final sync write to Firebase
    $this->database
        ->getReference("enrollment/assessment_settings/$type/fillblanks")
        ->set($existingData);

    return redirect()
        ->back()
        ->with('success', 'Fill-in-the-blank items saved successfully!');
}

    private function cleanFillBlankAnswer($text, $actualAnswer)
    {
        // Replace one or more underscores (blank) with the actual answer
        $full = preg_replace('/_+/', $actualAnswer, $text);

        // Optional: prevent repeated words (if answer repeats)
        $full = preg_replace('/\b(' . preg_quote($actualAnswer, '/') . ')\1\b/i', '$1', $full);

        return $full;
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
