<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Exception\Auth\InvalidPassword;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Auth as FirebaseAuth;
use Carbon\Carbon;
use Kreait\Firebase\Exception\Auth\EmailExists;
use Illuminate\Support\Facades\Session;
use Kreait\Firebase\Exception\AuthException;

class AdminController extends Controller
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

        public function ShowDataAnalytics()
    {
        // Reference all subjects (structure: subjects > gradeLevelKey > subjects[])
        $subjectsRef = $this->database->getReference('subjects');
        $allSubjects = $subjectsRef->getValue() ?? [];

        $specializedSubjects = [];

        // Loop all subjects by grade level
        foreach ($allSubjects as $gradeLevelKey => $subjects) {
            foreach ($subjects as $subject) {
                // Check if subject is specialized and type speech
                if (
                     isset($subject['subjectType']) &&
                    $subject['subjectType'] === 'specialized' &&
                    isset($subject['specialized_type']) &&
                    $subject['specialized_type'] === 'speech'
                ) {
                    // Save grade level key & subject for later use
                    $specializedSubjects[] = [
                        'gradeLevelKey' => $gradeLevelKey,
                        'subject' => $subject
                    ];
                }
            }
        }

        if (empty($specializedSubjects)) {
            return abort(404, 'No specialized speech subjects found.');
        }

        $studentScores = [];  // studentId => ['total' => ..., 'count' => ...]

        // Loop through all matched specialized subjects
        foreach ($specializedSubjects as $item) {
            $gradeLevelKey = $item['gradeLevelKey'];
            $subject = $item['subject'];
            $subjectId = $subject['subject_id'] ?? null;

            if (!$subjectId) {
                continue;
            }

            // Get attempts for this subject
            $attemptsRef = $this->database->getReference("subjects/{$gradeLevelKey}/{$subjectId}/attempts");
            $allAttempts = $attemptsRef->getValue() ?? [];

        foreach ($allAttempts as $attemptType => $activityIds) {
            if (!is_array($activityIds) && !is_object($activityIds)) {
                // Skip non-iterables
                continue;
            }
            foreach ($activityIds as $activityId => $students) {
                if (!is_array($students) && !is_object($students)) {
                    continue;
                }
                foreach ($students as $studentId => $attempts) {
                    if (!is_array($attempts) && !is_object($attempts)) {
                        continue;
                    }
                    foreach ($attempts as $attemptId => $attemptData) {
                         $score = 0;
                            $scoreCount = 0;

                            if (isset($attemptData['pronunciation_details'])) {
                                $score = $attemptData['pronunciation_details']['pte_pronunciation_score'] ?? 0;
                                $scoreCount = 1;
                            } else if (isset($attemptData['score'])) {
                                $score = $attemptData['score'];
                                $scoreCount = 1;
                            } else if (isset($attemptData['answers'])) {
                                $score = count($attemptData['answers']);
                                $scoreCount = 1;
                            }

                            if ($scoreCount > 0) {
                                if (!isset($studentScores[$studentId])) {
                                    $studentScores[$studentId] = ['total' => 0, 'count' => 0];
                                }
                                $studentScores[$studentId]['total'] += $score;
                                $studentScores[$studentId]['count'] += $scoreCount;
                            }
                        }
                    }
                }
            }
        }


        // Calculate average score per student
        $averages = [];
        foreach ($studentScores as $studentId => $data) {
            $averages[$studentId] = $data['count'] > 0 ? $data['total'] / $data['count'] : 0;
        }

        // Prepare data for Chart.js (labels and data arrays)
        $labels = array_keys($averages);
        $dataScores = array_values($averages);

        // Pass data to your view for Chart.js rendering
        return view('mio.head.admin-panel', [
            'page' => 'admin-analytics',
            'labels' => json_encode($labels),
            'dataScores' => json_encode($dataScores),
            // You could pass all specialized subjects or some aggregate info if needed
            'subjects' => $specializedSubjects,
        ]);
    }

    public function verifyPassword(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        try {
            $signInResult = $this->auth->signInWithEmailAndPassword($email, $password);
            $firebaseUser = $signInResult->data();

            // Confirm it's actually an admin
            $uid = $firebaseUser['localId'];
            $userData = $this->database->getReference('users/' . $uid)->getValue();

            if (strtolower($userData['role'] ?? '') !== 'admin') {
                return response()->json(['error' => 'Not authorized.'], 403);
            }

            return response()->json(['success' => true]);

        } catch (AuthException $e) {
            return response()->json(['error' => 'Invalid credentials.'], 401);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



}
