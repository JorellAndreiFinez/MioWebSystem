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
use Kreait\Firebase\Storage;
use Illuminate\Support\Facades\Storage as LocalStorage;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageObject;
use Barryvdh\DomPDF\Facade\Pdf;
use function Illuminate\Log\log;
use Kreait\Firebase\Exception\Auth\AuthError;
use App\Mail\EnrollmentSuccessMail;
use Illuminate\Support\Facades\Mail;
use Mailgun\Mailgun;

class EnrollController extends Controller
{
    protected $database;
    protected $auth;

    protected $bucketName;
    protected $storageClient;



    public function __construct()
    {
        $path = base_path('storage/firebase/firebase.json');

        $factory = (new Factory)
            ->withServiceAccount(base_path('storage/firebase/firebase.json'))
            ->withDatabaseUri('https://miolms-default-rtdb.firebaseio.com');

        $this->database = $factory->createDatabase();
        $this->auth = $factory->createAuth(); // Firebase Auth instance

          // Create Google Cloud Storage client
        $this->storageClient = new StorageClient([
            'keyFilePath' => $path,
        ]);

        // Your Firebase Storage bucket name
        $this->bucketName = 'miolms.firebasestorage.app';
    }

     protected function uploadToFirebaseStorage($file, $storagePath)
        {
            $bucket = $this->storageClient->bucket($this->bucketName);
            $fileName = $file->getClientOriginalName();
            $firebasePath = "{$storagePath}/" . uniqid() . '_' . $fileName;

            $bucket->upload(
                fopen($file->getRealPath(), 'r'),
                ['name' => $firebasePath]
            );

            return [
                'name' => $fileName,
                'path' => $firebasePath,
                'url' => "https://firebasestorage.googleapis.com/v0/b/{$this->bucketName}/o/" . urlencode($firebasePath) . "?alt=media",
            ];
        }





    public function showDashboard()
    {
        $enrollmentUser = session('enrollment_user');
        $enrollmentId = $enrollmentUser['ID'] ?? null;

        $adminFeedback = null;
        if ($enrollmentId) {
            $enrolleeData = $this->database->getReference("enrollment/enrollees/{$enrollmentId}")->getValue();
            if ($enrolleeData) {
                $adminFeedback = $enrolleeData['feedback_admin'] ?? null;
            }
        }

        $enrollStatus = $enrollmentUser['enroll_status'] ?? 'NotStarted';

        return view('enrollment-panel.enrollment-panel', [
            'page' => 'enroll-dashboard',
            'user' => $enrollmentUser,
            'enrollStatus' => $enrollStatus,
            'adminFeedback' => $adminFeedback,
        ]);
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
            // 1. Identify email from username/email input
            if (filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
                $email = $usernameOrEmail;
            } else {
                // Lookup in enrollees and users by username to get email
                $allEnrollees = $this->database->getReference('enrollment/enrollees')->getValue() ?? [];
                $allUsers = $this->database->getReference('users')->getValue() ?? [];

                foreach ($allEnrollees as $id => $data) {
                    if (($data['username'] ?? '') === $usernameOrEmail) {
                        $email = $data['email'] ?? null;
                        break;
                    }
                }

                if (!$email) {
                    foreach ($allUsers as $uid => $data) {
                        if (($data['username'] ?? '') === $usernameOrEmail) {
                            $email = $data['email'] ?? null;
                            break;
                        }
                    }
                }

                if (!$email) {
                    return redirect()->back()->with('error', 'Username not found.');
                }
            }

            // 2. Firebase Auth login
            $signInResult = $this->auth->signInWithEmailAndPassword($email, $password);
            $firebaseUid = $signInResult->firebaseUserId();

            // 3. Get user info from users/{uid}
            $user = $this->database->getReference('users/' . $firebaseUid)->getValue();

            if (!$user) {
                // Not enrolled yet? Try find enrollee by email:
                $enrollees = $this->database->getReference('enrollment/enrollees')->getValue() ?? [];
                $enrollee = null;
                foreach ($enrollees as $id => $data) {
                    if (($data['email'] ?? '') === $email) {
                        $enrollee = $data;
                        $enrollee['ID'] = $id; // store the enrollee ID (key)
                        break;
                    }
                }

                if (!$enrollee) {
                    return redirect()->back()->with('error', 'User not found in database.');
                }

                // Store enrollee only if not enrolled yet
                session([
                    'firebase_uid' => $firebaseUid,
                    'enrollment_user' => $enrollee,
                    'user_account' => null,
                ]);

            } else {
                // Enrolled user found, get enrollee_id from user
                $enrolleeId = $user['enrollee_id'] ?? null;
                $enrolleeData = null;
                if ($enrolleeId) {
                    $enrolleeData = $this->database->getReference("enrollment/enrollees/{$enrolleeId}")->getValue();
                    if ($enrolleeData) {
                        $enrolleeData['ID'] = $enrolleeId; // add ID key to data for consistency
                    }
                }

                // Store both user account and enrollee data
                session([
                    'firebase_uid' => $firebaseUid,
                    'user_account' => $user,
                    'enrollment_user' => $enrolleeData ?? null,
                ]);
            }

            return redirect()->route('enroll-dashboard');

        } catch (InvalidPassword $e) {
            return redirect()->back()->with('error', 'Incorrect password.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Login failed: ' . $e->getMessage());
        }
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

        $user = session('enrollment_user');
        $enrollmentId = $user['ID'] ?? null;

        $enrollStatus = $this->getEnrollStatus();

        // Check if status is exactly 'Assessment'
        if ($enrollStatus !== 'Assessment') {

            if ($enrollStatus === 'Enrolled') {

                return redirect()->route('enroll-dashboard')->with('error', 'You are done in assessment.');
            }

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
            'status' => $enrollStatus,
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

            $fillblankStatusRef = $this->database->getReference('enrollment/enrollees/' . $userId . '/Assessment/fillblanks/status');
            $fillblankStatus = $fillblankStatusRef->getValue();

            // Fetch speech phrases from your Firebase path, e.g.:
            $speechDataRef = $this->database->getReference('enrollment/assessment_settings/physical/speech');
            $speechData = $speechDataRef->getValue();

            // Fetch auditory answers similarly
            $auditoryDataRef = $this->database->getReference('enrollment/assessment_settings/physical/auditory');
            $auditoryData = $auditoryDataRef->getValue();

            // 🔥 Fetch sentences from Firebase
            $sentencesRef = $this->database->getReference("enrollment/assessment_settings/physical/sentences");
            $sentencesData = $sentencesRef->getValue();

            // ✅ Re-index sentences (to match your loop in Blade template)
            $sentences = [];
            if ($sentencesData && is_array($sentencesData)) {
                foreach ($sentencesData as $sentence) {
                    $sentences[] = $sentence['text'] ?? '';  // push just the text
                }
            }

            // ✅ Fix this block
            $fillblanksRef = $this->database->getReference("enrollment/assessment_settings/physical/fillblanks");
            $fillblanksData = $fillblanksRef->getValue();

            $fillblanks = [];
            if ($fillblanksData && is_array($fillblanksData)) {
                foreach ($fillblanksData as $item) {
                    $fillblanks[] = $item;
                }
            }

             $phrases = [];
            if ($speechData) {
                foreach ($speechData as $item) {
                    $phrases[] = [
                        'text' => $item['text'] ?? '',
                        'image_url' => $item['image_url'] ?? null,
                    ];
                }
            }

            $auditoryAnswers = [];
            if ($auditoryData) {
                $index = 1;
                foreach ($auditoryData as $item) {
                    $auditoryAnswers[$index++] = $item['text'] ?? '';
                }

            }


            // Check both statuses to determine what to show next
            if ($speechAuditoryStatus !== 'done') {
                return view('enrollment-panel.enrollment-panel', [
                    'page' => 'main-assessment',
                    'user' => $user,
                    'phrases' => $phrases,
                    'auditoryAnswers' => $auditoryAnswers,
                ]);
            }

            if ($readingStatus !== 'done') {
                return view('enrollment-panel.enrollment-panel', [
                    'page' => 'main-assessment2',
                    'user' => $user,
                    'sentences' => $sentences ?? [],
                ]);
            }

           if ($fillblankStatus !== 'done') {
            return view('enrollment-panel.enrollment-panel', [
                'page' => 'main-assessment3',
                'user' => $user,
                'fillblanks' => $fillblanks,
            ]);
        }

            $levelMap = [
                'kinder' => 'kinder',
                'elementary' => 'elementary',
                'junior-highschool' => 'highschool',
                'senior-highschool' => 'highschool',
            ];

             // Add your questions array here for the sentence test page:
            $enrollmentGrade = $user['enrollment_form']['enrollment_grade'] ?? null;
            $enrollmentLevel = $levelMap[$enrollmentGrade] ?? null;

            $questionsRef = $this->database->getReference('enrollment/assessment_settings/written/questions');
            $allQuestions = $questionsRef->getValue();

            $questions = [];
            if ($allQuestions && $enrollmentLevel) {
                $index = 1;
                foreach ($allQuestions as $questionId => $questionData) {
                    // Filter by level
                    if (($questionData['level'] ?? '') === $enrollmentLevel) {
                        $type = $questionData['type'] ?? '';

                        // Convert Firebase structure into your expected view format
                        switch ($type) {
                            case 'multiple_single':
                                $questions[$index++] = [
                                    'type' => 'multiple_choice',
                                    'sentence' => $questionData['question'] ?? '',
                                    'choices' => array_values($questionData['options'] ?? []),
                                ];
                                break;

                            case 'multiple_multiple':
                                $questions[$index++] = [
                                    'type' => 'multiple_choice',
                                    'sentence' => $questionData['question'] ?? '',
                                    'choices' => array_values($questionData['options'] ?? []),
                                ];
                                break;

                            case 'fill_blank':
                                $questions[$index++] = [
                                    'type' => 'fill_in_blank',
                                    'sentence' => $questionData['question'] ?? '',
                                    'answer_placeholder' => 'Type your answer here',
                                ];
                                break;

                            case 'syntax':
                                // Clean and split the 'correct' sentence into words
                                $correctSentence = $questionData['correct'] ?? '';
                                // Remove punctuation using regex, then split on spaces
                                $cleaned = preg_replace('/[^\w\s]/u', '', $correctSentence);
                                $words = preg_split('/\s+/', strtolower($cleaned), -1, PREG_SPLIT_NO_EMPTY);

                                $questions[$index++] = [
                                    'type' => 'sentence_order',
                                    'words' => $words,
                                    'sentence_hint' => 'Arrange the words to form a correct sentence.',
                                    'image_url' => $questionData['image_url'] ?? null,
                                ];
                                break;

                            case 'true_false':
                                $questions[$index++] = [
                                    'type' => 'true_false',
                                    'statement' => $questionData['question'] ?? '',
                                ];
                                break;

                            default:
                                // skip unknown types
                                break;
                        }
                    }
                }
            }

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
                    'sentences' => $sentences ?? [],
                ]);
            }
        }

        // 🔥 Fetch sentences from Firebase
            $sentencesRef = $this->database->getReference("enrollment/assessment_settings/physical/sentences");
            $sentencesData = $sentencesRef->getValue();

            // ✅ Re-index sentences (to match your loop in Blade template)
            $sentences = [];
            if ($sentencesData && is_array($sentencesData)) {
                foreach ($sentencesData as $sentence) {
                    $sentences[] = $sentence['text'] ?? '';  // push just the text
                }
            }


        return view('enrollment-panel.enrollment-panel', [
            'page' => 'main-assessment2',
            'speech_results' => $speechResults,
            'auditory_results' => $auditoryResults,
            'sentences' => $sentences ?? [],

        ]);
    }

    public function mainAssessment3()
    {
        $speechResults = session('speech_results', []);
        $auditoryResults = session('auditory_results', []);

        $uid = Session::get('firebase_uid');


        if ($uid) {
            $statusRef = $this->database->getReference("enrollment/enrollees/{$uid}/Assessment/fillblanks/status");
            $status = $statusRef->getValue();

            if ($status === 'done') {
                return view('enrollment-panel.enrollment-panel', [
                    'page' => 'main-assessment4',
                    'speech_results' => $speechResults,
                    'auditory_results' => $auditoryResults,
                ]);
            }
        }

            Log::info($status);


        // ✅ Fix this block
        $fillblanksRef = $this->database->getReference("enrollment/assessment_settings/physical/fillblanks");
        $fillblanksData = $fillblanksRef->getValue();

        $fillblanks = [];
        if ($fillblanksData && is_array($fillblanksData)) {
            foreach ($fillblanksData as $item) {
                $fillblanks[] = $item;
            }
        }

        return view('enrollment-panel.enrollment-panel', [
            'page' => 'main-assessment3',
            'speech_results' => $speechResults,
            'auditory_results' => $auditoryResults,
            'fillblanks' => $fillblanks,
        ]);
    }

    public function mainAssessment4()
    {
        $speechResults = session('speech_results', []);
        $auditoryResults = session('auditory_results', []);

        $uid = Session::get('firebase_uid');

        if ($uid) {
            $statusRef = $this->database->getReference("enrollment/enrollees/{$uid}/Assessment/Reading/status");
            $status = $statusRef->getValue();

            if ($status === 'done') {
                return view('enrollment-panel.enrollment-panel', [
                    'page' => 'main-assessment4',
                    'speech_results' => $speechResults,
                    'auditory_results' => $auditoryResults,
                ]);
            }
        }

        // ✅ Fix this block
        $fillblanksRef = $this->database->getReference("enrollment/assessment_settings/physical/fillblanks");
        $fillblanksData = $fillblanksRef->getValue();

        $fillblanks = [];
        if ($fillblanksData && is_array($fillblanksData)) {
            foreach ($fillblanksData as $item) {
                $fillblanks[] = $item;
            }
        }

        return view('enrollment-panel.enrollment-panel', [
            'page' => 'main-assessment4',
            'speech_results' => $speechResults,
            'auditory_results' => $auditoryResults,
            'fillblanks' => $fillblanks,
        ]);
    }


    public function assessmentPhysical()
    {
        $userId = session('firebase_uid');
        $user = $this->database->getReference('enrollment/enrollees/' . $userId)->getValue();

        return view('enrollment-panel.pages.assessment.physical', compact('user'));
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

        $form = $enrollData['enrollment_form'] ?? [];
        $form['first_name'] = $enrollData['fname'] ?? '';
        $form['last_name'] = $enrollData['lname'] ?? '';
        $form['email'] = $enrollData['email'] ?? '';


        $status = $enrollData['enroll_status'] ?? 'NotStarted';

        return view('enrollment-panel.enrollment-panel', [
            'page' => 'enroll-form',
            'form' => $form,
            'status' => $status,
        ]);

    }


    public function submitEnrollmentForm(Request $request)
    {
        Log::info('All request data:', $request->all());
        Log::info('All files:', $request->files->all());

        try {
            $request->validate([
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'gender' => 'required|string',
                'age' => 'required|integer',
                'birthday' => 'required|date',
                'address' => 'required|string',
                'barangay' => 'required|string',
                'region' => 'required|string',
                'province' => 'required|string',
                'city' => 'required|string',
                'zip_code' => 'required|string|min:4|max:4',
                'contact_number' => 'required|string',

                'emergency_contact' => 'required|string',
                'parent_firstname' => 'required|string',
                'parent_lastname' => 'required|string',
                'parent_role' => 'required|string|in:father,mother,guardian',


                'previous_school' => 'required|string',
                'previous_grade_level' => 'required|integer',
                'medical_history' => 'required|string',
                'hearing_loss' => 'nullable|string',
                'hearing_identity' => 'required|string',
                'assistive_devices' => 'nullable|string',
                'health_notes' => 'nullable|string',
                'enrollment_grade' => 'required|string',
                'grade_level' => 'nullable|string',
                'strand' => 'nullable|string',

               'payment' => $request->hasFile('payment') ? 'file|mimes:pdf,jpg,jpeg,png|max:5120' : 'nullable',
                'good_moral_file' => $request->hasFile('good_moral_file') ? 'file|mimes:pdf,jpg,jpeg,png|max:5120' : 'nullable',
                'health_certificate_file' => $request->hasFile('health_certificate_file') ? 'file|mimes:pdf,jpg,jpeg,png|max:5120' : 'nullable',
                'psa_birth_certificate_file' => $request->hasFile('psa_birth_certificate_file') ? 'file|mimes:pdf,jpg,jpeg,png|max:5120' : 'nullable',
                'form_137_file' => $request->hasFile('form_137_file') ? 'file|mimes:pdf,jpg,jpeg,png|max:5120' : 'nullable',
            ]);

            $enrollmentUser = session('enrollment_user');
            if (!$enrollmentUser || !isset($enrollmentUser['ID'])) {
                return redirect()->back()->with('error', 'User not logged in.');
            }

            $enrolleeId = $enrollmentUser['ID'];
            Log::info('Enrollee ID: ' . $enrolleeId);

            // Retrieve all form data
            $formData = $request->only([
                'first_name', 'last_name', 'gender', 'age', 'birthday', 'address', 'barangay', 'region',
                'province', 'city', 'zip_code', 'contact_number', 'emergency_contact',
                'previous_school', 'previous_grade_level', 'medical_history', 'hearing_loss',
                'hearing_identity', 'assistive_devices', 'health_notes', 'enrollment_grade', 'grade_level', 'strand', 'parent_role', 'parent_firstname', 'parent_lastname'
            ]);

            // ✅ Add this line
            $formData['category'] = 'new';

            $activeSchoolYearId = null;

            // Fetch all school years (flat structure)
            $schoolYears = $this->database->getReference('schoolyears')->getValue() ?? [];

            // Look for the one with status "active"
            foreach ($schoolYears as $schoolYearId => $schoolYearData) {
                if (
                    isset($schoolYearData['status']) &&
                    strtolower($schoolYearData['status']) === 'active'
                ) {
                    $activeSchoolYearId = $schoolYearId;
                    break;
                }
            }

            // Set it in the form data
            $formData['schoolyear_id'] = $activeSchoolYearId;

            // Apply conditional logic
            $enrollmentGrade = $formData['enrollment_grade'];

            if (in_array($enrollmentGrade, ['kinder', 'one-on-one-therapy'])) {
                $formData['grade_level'] = '';
            }

            if (in_array($enrollmentGrade, ['elementary', 'junior-highschool'])) {
                $formData['strand'] = '';
            }

            // Upload files
            if ($request->hasFile('payment')) {
                $uploadResult = $this->uploadToFirebaseStorage($request->file('payment'), "enrollment/{$enrolleeId}");
                $formData['payment_proof_path'] = $uploadResult['url'];
            } elseif ($request->filled('existing_payment')) {
                $formData['payment_proof_path'] = $request->input('existing_payment');
            }

            // Good Moral
            if ($request->hasFile('good_moral_file')) {
                $uploadResult = $this->uploadToFirebaseStorage($request->file('good_moral_file'), "enrollment/{$enrolleeId}");
                $formData['good_moral_path'] = $uploadResult['url'];
            } elseif ($request->filled('existing_good_moral_file')) {
                $formData['good_moral_path'] = $request->input('existing_good_moral_file');
            }

            // Health Certificate
            if ($request->hasFile('health_certificate_file')) {
                $uploadResult = $this->uploadToFirebaseStorage($request->file('health_certificate_file'), "enrollment/{$enrolleeId}");
                $formData['health_certificate_path'] = $uploadResult['url'];
            } elseif ($request->filled('existing_health_certificate_file')) {
                $formData['health_certificate_path'] = $request->input('existing_health_certificate_file');
            }

            // PSA Birth Certificate
            if ($request->hasFile('psa_birth_certificate_file')) {
                $uploadResult = $this->uploadToFirebaseStorage($request->file('psa_birth_certificate_file'), "enrollment/{$enrolleeId}");
                $formData['psa_birth_certificate_path'] = $uploadResult['url'];
            } elseif ($request->filled('existing_psa_birth_certificate_file')) {
                $formData['psa_birth_certificate_path'] = $request->input('existing_psa_birth_certificate_file');
            }

            // Form 137
            if ($request->hasFile('form_137_file')) {
                $uploadResult = $this->uploadToFirebaseStorage($request->file('form_137_file'), "enrollment/{$enrolleeId}");
                $formData['form_137_path'] = $uploadResult['url'];
            } elseif ($request->filled('existing_form_137_file')) {
                $formData['form_137_path'] = $request->input('existing_form_137_file');
            }


            $formData['submitted_at'] = now()->toDateTimeString();

            // Save to Firebase
            $this->database
                ->getReference("enrollment/enrollees/{$enrolleeId}/enrollment_form")
                ->set($formData);

            $this->database
                ->getReference("enrollment/enrollees/{$enrolleeId}/enroll_status")
                ->set('Registered');

            return redirect()->back()->with('success', 'Enrollment form submitted successfully!');
        } catch (\Throwable $e) {
            Log::error('Enrollment form submission error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while submitting the form. Please try again.');
        }
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

        $written_questions = $this->database
            ->getReference("enrollment/assessment_settings/$type/questions")
            ->getValue() ?? [];

        $mcqs = [];

        foreach ($written_questions as $key => $question) {
            $level = $question['level'] ?? 'unknown';
            if (!isset($mcqs[$level])) {
                $mcqs[$level] = [];
            }

            // Use question_id from the question itself if available, otherwise use the key
            $questionID = $question['question_id'] ?? $key;
            $mcqs[$level][$questionID] = $question;
        }


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
            'questions' => $written_questions,
            'mcqs' => $mcqs,
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

        // Handle deletions and updates
        foreach ($speech as $speechID => $data) {
            // Deletion
            if (isset($data['_delete']) && $data['_delete'] == '1') {
                $this->database
                    ->getReference("enrollment/assessment_settings/$type/speech/$speechID")
                    ->remove();

                unset($existingData[$speechID]);
                continue;
            }

            // Validation
            if (!isset($data['text'], $data['level']) || !in_array($data['level'], $allowedLevels)) {
                continue;
            }

            // Build update data
            $updateData = [
                'text' => $data['text'],
                'level' => $data['level'],
                'speechID' => $speechID,
                'created_at' => $existingData[$speechID]['created_at'] ?? now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];

            // Handle image upload
            if ($request->hasFile("speech.$speechID.image")) {
                $image = $request->file("speech.$speechID.image");
                $imagePath = "images/enrollment/assessment_settings/{$type}_{$speechID}_" . time() . '.' . $image->getClientOriginalExtension();

                $bucket = $this->storageClient->bucket($this->bucketName);
                $bucket->upload(
                    fopen($image->getRealPath(), 'r'),
                    ['name' => $imagePath]
                );

                $imageUrl = "https://firebasestorage.googleapis.com/v0/b/miolms.firebasestorage.app/o/" . urlencode($imagePath) . "?alt=media";
                $updateData['image_url'] = $imageUrl;
            } elseif (isset($data['existing_image'])) {
                $updateData['image_url'] = $data['existing_image'];
            } elseif (isset($existingData[$speechID]['image_url'])) {
                // Preserve previous image if nothing new is uploaded and no existing_image field is sent
                $updateData['image_url'] = $existingData[$speechID]['image_url'];
            }

            // Update Firebase and local mirror
            $this->database
                ->getReference("enrollment/assessment_settings/$type/speech/$speechID")
                ->set($updateData);

            $existingData[$speechID] = $updateData;
        }

        // Handle new phrase
        $newPhrase = $request->input('new_speech');
        if ($newPhrase && isset($newPhrase['text'], $newPhrase['level'])) {
            if (in_array($newPhrase['level'], $allowedLevels)) {
                $newKey = 'SP' . now()->format('Ymd') . str_pad(count($existingData) + 1, 3, '0', STR_PAD_LEFT);

                $imageUrl = null;

                // Upload image if present
                if ($request->hasFile('new_speech.image')) {
                    $image = $request->file('new_speech.image');
                    $imagePath = "images/enrollment/assessment_settings/{$type}_{$newKey}_" . time() . '.' . $image->getClientOriginalExtension();

                    $bucket = $this->storageClient->bucket($this->bucketName);
                    $bucket->upload(
                        fopen($image->getRealPath(), 'r'),
                        ['name' => $imagePath]
                    );

                    $imageUrl = "https://firebasestorage.googleapis.com/v0/b/miolms.firebasestorage.app/o/" . urlencode($imagePath) . "?alt=media";
                }

                $newData = [
                    'text' => $newPhrase['text'],
                    'level' => $newPhrase['level'],
                    'speechID' => $newKey,
                    'created_at' => now()->toDateTimeString(),
                    'updated_at' => now()->toDateTimeString(),
                    'image_url' => $imageUrl,
                ];

                // Save immediately to Firebase
                $this->database
                    ->getReference("enrollment/assessment_settings/$type/speech/$newKey")
                    ->set($newData);

                $existingData[$newKey] = $newData;
            }
        }

        // Final sync: Save updated collection to Firebase
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

    public function saveQuestion(Request $request, $type)
    {
        if (!in_array($type, ['physical', 'written'])) {
            abort(404);
        }

        $data = $request->input('new_mcq', []);
        Log::info('Form data:', $data);

        $typeRule = $data['type'] ?? null;

        // Base rules
        $rules = [
            'new_mcq.type' => 'required|in:multiple_single,multiple_multiple,fill_blank,syntax',
            'new_mcq.question' => 'required|string|max:255',
            'new_mcq.level' => 'required|in:kinder,elementary,highschool,seniorhigh',
        ];

        // Conditional rules based on question type
        if (in_array($typeRule, ['multiple_single', 'multiple_multiple'])) {
            $rules['new_mcq.options'] = 'required|array|min:2';
            $rules['new_mcq.options.*'] = 'required|string';

            // Allowed keys for correct answer(s)
            $optionKeys = [];
            if (!empty($data['options'])) {
                foreach (array_keys($data['options']) as $index) {
                    $optionKeys[] = chr(65 + $index); // A, B, C ...
                }
            }
            if ($typeRule === 'multiple_single') {
                $rules['new_mcq.correct'] = ['required', 'string', 'in:' . implode(',', $optionKeys)];
            } else {
                $rules['new_mcq.correct'] = ['required', 'array'];
                $rules['new_mcq.correct.*'] = ['string', 'in:' . implode(',', $optionKeys)];
            }
        } elseif ($typeRule === 'fill_blank' || $typeRule === 'syntax') {
            $rules['new_mcq.correct'] = 'required|string|max:255';
        } elseif ($typeRule === 'match_pair') {
            $rules['new_mcq.pair_a'] = 'required|array|min:1';
            $rules['new_mcq.pair_b'] = 'required|array|min:1';
            $rules['new_mcq.pair_a.*'] = 'required|string';
            $rules['new_mcq.pair_b.*'] = 'required|string';
        }

        try {
            $validated = $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed:', $e->errors());
            throw $e; // Let it redirect back with errors
        }

        // Prepare options in a keyed way: 'A' => optionText, 'B' => optionText ...
        $options = null;
        if (!empty($validated['new_mcq']['options'])) {
            $options = [];
            foreach ($validated['new_mcq']['options'] as $i => $opt) {
                $key = chr(65 + $i);
                $options[$key] = $opt;
            }
        }

        // For 'correct' multiple choice, we keep keys (A, B, etc), otherwise it's string or array as is
        $correct = $validated['new_mcq']['correct'] ?? null;

        $newData = [
            'type' => $validated['new_mcq']['type'],
            'question' => $validated['new_mcq']['question'],
            'level' => $validated['new_mcq']['level'],
            'options' => $options,
            'correct' => $correct,
            'pair_a' => $validated['new_mcq']['pair_a'] ?? null,
            'pair_b' => $validated['new_mcq']['pair_b'] ?? null,
        ];

        // Handle image upload if present
        if ($request->hasFile('new_mcq.image')) {
            $image = $request->file('new_mcq.image');
            $imagePath = "images/enrollment/assessment_settings/{$type}_" . time() . '.' . $image->getClientOriginalExtension();

            $bucket = $this->storageClient->bucket($this->bucketName);
            $bucket->upload(fopen($image->getRealPath(), 'r'), ['name' => $imagePath]);

            $imageUrl = "https://firebasestorage.googleapis.com/v0/b/miolms.firebasestorage.app/o/" . urlencode($imagePath) . "?alt=media";
            $newData['image_url'] = $imageUrl;
        }


        // Save to Firebase
        $date = now()->format('Ymd'); // e.g. 20250601
        $randomDigits = str_pad(mt_rand(0, 999), 3, '0', STR_PAD_LEFT); // e.g. 042
        $customId = 'ENQ' . $date . $randomDigits;

        $refPath = "enrollment/assessment_settings/$type/questions/$customId";
        $this->database->getReference($refPath)->set($newData);


        return redirect()->back()->with('success', 'Question saved successfully!');
    }

    public function deleteQuestion($type, $id)
    {
        $refPath = "enrollment/assessment_settings/$type/questions/$id";
        $this->database->getReference($refPath)->remove();

        return redirect()->back()->with('success', 'Question deleted successfully!');
    }


    public function updateQuestion(Request $request, $type, $id)
    {
        if (!in_array($type, ['physical', 'written'])) {
            abort(404);
        }

        $data = $request->input('edit_mcq', []);
        Log::info('Update form data:', $data);

        $typeRule = $data['type'] ?? null;

        // Base rules
        $rules = [
            'edit_mcq.type' => 'required|in:multiple_single,multiple_multiple,fill_blank,match_pair,syntax',
            'edit_mcq.question' => 'required|string|max:255',
            'edit_mcq.level' => 'required|in:kinder,elementary,highschool,seniorhigh',
        ];

        // Conditional rules based on question type
        if (in_array($typeRule, ['multiple_single', 'multiple_multiple'])) {
            $rules['edit_mcq.options'] = 'required|array|min:2';
            $rules['edit_mcq.options.*'] = 'required|string';

            // Allowed keys for correct answer(s)
            $optionKeys = [];
            if (!empty($data['options'])) {
                foreach (array_keys($data['options']) as $index) {
                    $optionKeys[] = chr(65 + $index); // A, B, C ...
                }
            }
            if ($typeRule === 'multiple_single') {
                $rules['edit_mcq.correct'] = ['required', 'string', 'in:' . implode(',', $optionKeys)];
            } else {
                $rules['edit_mcq.correct'] = ['required', 'array'];
                $rules['edit_mcq.correct.*'] = ['string', 'in:' . implode(',', $optionKeys)];
            }
        } elseif ($typeRule === 'fill_blank') {
            $rules['edit_mcq.correct'] = 'required|string|max:255';
        } elseif ($typeRule === 'match_pair') {
            $rules['edit_mcq.pair_a'] = 'required|array|min:1';
            $rules['edit_mcq.pair_b'] = 'required|array|min:1';
            $rules['edit_mcq.pair_a.*'] = 'required|string';
            $rules['edit_mcq.pair_b.*'] = 'required|string';
        } elseif ($typeRule === 'syntax') {
            $rules['edit_mcq.correct'] = 'required|string|max:255';
        }

        try {
            $validated = $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed during update:', $e->errors());
            throw $e; // Redirect back with errors
        }

        // Prepare options in keyed way: 'A' => optionText, 'B' => optionText ...
        $options = null;
        if (!empty($validated['edit_mcq']['options'])) {
            $options = [];
            foreach ($validated['edit_mcq']['options'] as $i => $opt) {
                $key = chr(65 + $i);
                $options[$key] = $opt;
            }
        }

        // For 'correct' multiple choice, keep keys (A, B, etc), otherwise string or array as is
        $correct = $validated['edit_mcq']['correct'] ?? null;

        $updateData = [
            'type' => $validated['edit_mcq']['type'],
            'question' => $validated['edit_mcq']['question'],
            'level' => $validated['edit_mcq']['level'],
            'options' => $options,
            'correct' => $correct,
            'pair_a' => $validated['edit_mcq']['pair_a'] ?? null,
            'pair_b' => $validated['edit_mcq']['pair_b'] ?? null,
        ];

        // Handle image upload if present
        if ($request->hasFile('edit_mcq.image')) {
            $image = $request->file('edit_mcq.image');
            $imagePath = "images/enrollment/assessment_settings/{$type}_" . time() . '.' . $image->getClientOriginalExtension();

            $bucket = $this->storageClient->bucket($this->bucketName);
            $bucket->upload(fopen($image->getRealPath(), 'r'), ['name' => $imagePath]);

            $imageUrl = "https://firebasestorage.googleapis.com/v0/b/miolms.firebasestorage.app/o/" . urlencode($imagePath) . "?alt=media";
            $updateData['image_url'] = $imageUrl;
        }


        // Update existing question at given id
        $refPath = "enrollment/assessment_settings/$type/questions/$id";
        $this->database->getReference($refPath)->update($updateData);

        return redirect()->back()->with('success', 'Question updated successfully!');
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
        $snapshot = $enrolleeRef->getSnapshot();

        if (!$snapshot->exists()) {
            return redirect()->route('mio.view-enrollee', ['id' => $id])->with('error', 'Enrollee not found.');
        }

        // Update status and feedback
        $enrolleeRef->update([
            'feedback_admin' => $assessment,
            'enroll_status' => $status
        ]);

        if (strtolower($status) === 'enrolled') {
            $enrollee = $snapshot->getValue();
            $form = $enrollee['enrollment_form'] ?? [];

            // Generate unique student ID
            $now = Carbon::now();
            $year = $now->year;
            $week = str_pad($now->weekOfYear, 2, '0', STR_PAD_LEFT);
            $day = str_pad($now->day, 2, '0', STR_PAD_LEFT);
            $random = str_pad(mt_rand(0, 99), 2, '0', STR_PAD_LEFT);
            $studentID = 'ST' . $year . $week . $day . $random;

            $email = $enrollee['email'] ?? '';
            $bday = $form['birthday'] ?? null;
            $rawPassword = $bday ? Carbon::parse($bday)->format('Y-m-d') : 'default123';
            $hashedPassword = $rawPassword;
            $username = $enrollee['username'] ?? $email;

            // Check duplicate email in Realtime DB users node
            $existingUsers = $this->database->getReference('users')->getValue() ?? [];
            foreach ($existingUsers as $user) {
                if (($user['email'] ?? '') === $email) {
                    return redirect()->back()->with('error', 'A user with this email already exists.');
                }
            }

            // Find active school year
            $schoolId = $form['school_id'] ?? null;
            $activeSchoolYearId = null;
            if ($schoolId) {
                $schoolYearsRef = $this->database->getReference('schoolyears/' . $schoolId)->getValue() ?? [];
                foreach ($schoolYearsRef as $schoolYearId => $schoolYearData) {
                    if (isset($schoolYearData['status']) && strtolower($schoolYearData['status']) === 'active') {
                        $activeSchoolYearId = $schoolYearId;
                        break;
                    }
                }
            }

            // Create Firebase Auth user or update UID if user with this email exists
            try {
                    // Try to get user by email
                    $firebaseUser = $this->auth->getUserByEmail($email);

                    // If found, delete and recreate to use new UID
                    $oldUid = $firebaseUser->uid;
                    $this->auth->deleteUser($oldUid);

                    // Recreate user with correct UID and password
                    $createdUser = $this->auth->createUser([
                        'uid' => $studentID,
                        'email' => $email,
                        'emailVerified' => false,
                        'password' => $rawPassword, // ✅ Use raw password
                        'displayName' => trim(($form['first_name'] ?? '') . ' ' . ($form['last_name'] ?? '')),
                        'disabled' => false,
                    ]);
                } catch (\Kreait\Firebase\Exception\Auth\UserNotFound $e) {
                    // User not found in Firebase Auth, create new user
                    $createdUser = $this->auth->createUser([
                        'uid' => $studentID,
                        'email' => $email,
                        'emailVerified' => false,
                        'password' => $rawPassword, // ✅ Use raw password
                        'displayName' => trim(($form['first_name'] ?? '') . ' ' . ($form['last_name'] ?? '')),
                        'disabled' => false,
                    ]);
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', 'Failed to update Firebase Authentication user: ' . $e->getMessage());
                }


            // Save student user in Realtime Database
            $studentData = [
                'fname' => $form['first_name'] ?? '',
                'lname' => $form['last_name'] ?? '',
                'gender' => $form['gender'] ?? '',
                'age' => $form['age'] ?? '',
                'bday' => $form['birthday'] ?? '',
                'address' => $form['address'] ?? '',
                'barangay' => $form['barangay'] ?? '',
                'region' => $form['region'] ?? '',
                'province' => $form['province'] ?? '',
                'city' => $form['city'] ?? '',
                'zip_code' => $form['zip_code'] ?? '',
                'contact_number' => $form['contact_number'] ?? '',
                'emergency_contact' => $form['emergency_contact'] ?? '',
                'email' => $email,
                'previous_school' => $form['previous_school'] ?? '',
                'previous_grade_level' => $form['previous_grade_level'] ?? '',
                'category' => $form['category'] ?? '',
                'studentid' => $studentID,
                'role' => 'student',
                'profile_picture' => '',
                'schoolyear_id' => $activeSchoolYearId,
                'username' => $username,
                'password' => Hash::make($hashedPassword),
                'account_status' => 'active',
                'date_created' => $now->toDateTimeString(),
                'date_updated' => $now->toDateTimeString(),
                'last_login' => null,
                'already_login' => 'false',
                'enrollee_id' => $id,
            ];

            $this->database->getReference('users/' . $studentID)->set($studentData);

            // Create parent user data
            $parentId = 'PA' . $year . $week . $day . str_pad(mt_rand(0, 99), 2, '0', STR_PAD_LEFT);

            if (isset($existingUsers[$parentId])) {
                $parentId .= '1'; // Avoid collision
            }

            $parentData = [
                'fname' => $form['parent_firstname'] ?? '',
                'lname' => $form['parent_lastname'] ?? '',
                'contact_number' => $form['emergency_contact'] ?? '',
                'email' => $email,
                'username' => $email,
                'password' => $hashedPassword,
                'address' => $form['address'] ?? '',
                'barangay' => $form['barangay'] ?? '',
                'region' => $form['region'] ?? '',
                'province' => $form['province'] ?? '',
                'city' => $form['city'] ?? '',
                'zip_code' => $form['zip_code'] ?? '',
                'category' => $form['parent_role'] ?? '',
                'parentid' => $parentId,
                'studentid' => $studentID,
                'role' => 'parent',
                'schoolyear_id' => $activeSchoolYearId,
                'account_status' => 'active',
                'date_created' => $now->toDateTimeString(),
                'date_updated' => $now->toDateTimeString(),
                'last_login' => null,
                'already_login' => 'false',
            ];

            $this->database->getReference('users/' . $parentId)->set($parentData);

            // Update enrollee with timestamp
            $enrolleeRef->update(['enrolled_at' => $now->toDateTimeString()]);
        }



        return redirect()->route('mio.enrollment', ['id' => $id])
            ->with('success', 'Enrollee feedback and status updated successfully.');
    }












}
