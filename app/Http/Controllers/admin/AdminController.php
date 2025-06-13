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
use Carbon\CarbonPeriod;

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
            $enrollees = $this->database
                ->getReference('enrollment/enrollees')
                ->getValue() ?? [];

            // Labels for January to December
            $labels = [];
            $createdCounts = [];
            $enrolledCounts = [];

            for ($m = 1; $m <= 12; $m++) {
                $monthName = Carbon::createFromDate(null, $m, 1)->format('F');
                $labels[] = $monthName;
                $createdCounts[$monthName] = 0;
                $enrolledCounts[$monthName] = 0;
            }

            foreach ($enrollees as $enrollee) {
                foreach (['created_at', 'enrolled_at'] as $type) {
                    if (!empty($enrollee[$type])) {
                        $date = Carbon::parse($enrollee[$type]);
                        $monthName = $date->format('F');

                        if (in_array($monthName, $labels)) {
                            if ($type === 'created_at') {
                                $createdCounts[$monthName]++;
                            } elseif ($type === 'enrolled_at') {
                                $enrolledCounts[$monthName]++;
                            }
                        }
                    }
                }
            }

            $createdData = array_map(fn($month) => $createdCounts[$month], $labels);
            $enrolledData = array_map(fn($month) => $enrolledCounts[$month], $labels);

            // === NEW: Hearing Identity Doughnut Data ===
            $users = $this->database
                ->getReference('users')
                ->getValue() ?? [];

            $hearingCounts = [
                'deaf' => 0,
                'hard-of-hearing' => 0,
                'speech-delay' => 0,
                'others' => 0,
            ];

            foreach ($users as $user) {
                if (($user['role'] ?? null) === 'student') {
                    $identity = strtolower(trim($user['hearing_identity'] ?? 'others'));

                    if (in_array($identity, ['deaf', 'hard-of-hearing', 'speech-delay'])) {
                        $hearingCounts[$identity]++;
                    } else {
                        $hearingCounts['others']++;
                    }
                }
            }

            // === NEW: Average Logins per User per Week ===
            $loginCountsByGrade = [];
            $studentsPerGrade = [];

            foreach ($users as $user) {
                if (($user['role'] ?? null) === 'student') {
                    $rawGrade = strtolower(trim($user['grade_level'] ?? ''));

                    // Normalize grade
                    $normalized = preg_replace('/^grade[- ]*/', '', $rawGrade);

                    // Assign grade bucket
                    if (in_array($normalized, ['k', 'kinder', 'kindergarten'])) {
                        $grade = 'K';
                    } elseif (is_numeric($normalized)) {
                        $num = (int)$normalized;
                        $grade = ($num >= 1 && $num <= 12) ? (string)$num : 'Others';
                    } else {
                        $grade = 'Others'; // for missing, empty, or other services
                    }

                    $created = $user['date_created'] ?? null;
                    $lastLogin = $user['last_login'] ?? null;

                    if (!isset($loginCountsByGrade[$grade])) {
                        $loginCountsByGrade[$grade] = 0;
                        $studentsPerGrade[$grade] = 0;
                    }

                    if ($created && $lastLogin) {
                        $createdAt = Carbon::parse($created);
                        $lastLoginAt = Carbon::parse($lastLogin);
                        $weeks = max($createdAt->diffInWeeks($lastLoginAt), 1);
                        $avgLogin = 1 / $weeks;

                        $loginCountsByGrade[$grade] += $avgLogin;
                        $studentsPerGrade[$grade]++;
                    }
                }
            }

            // Calculate averages
            $averageLoginsPerGrade = [];
            foreach ($loginCountsByGrade as $grade => $totalAvgLogins) {
                $average = $studentsPerGrade[$grade] > 0
                    ? round($totalAvgLogins / $studentsPerGrade[$grade], 2)
                    : 0;

                $averageLoginsPerGrade[$grade] = $average;
            }

            // Custom sort order: K, 1, 2, ..., 12
            $gradeOrder = array_merge(['K'], range(1, 12), ['Others']);
            $averageLoginsPerGrade = [];

            foreach ($gradeOrder as $grade) {
                $key = (string)$grade;

                // Initialize missing grades with 0
                if (!isset($loginCountsByGrade[$key])) {
                    $loginCountsByGrade[$key] = 0;
                    $studentsPerGrade[$key] = 0;
                }

                $average = $studentsPerGrade[$key] > 0
                    ? round($loginCountsByGrade[$key] / $studentsPerGrade[$key], 2)
                    : 0;

                $averageLoginsPerGrade[$key] = $average;
            }





            // Send to view
            return view('mio.head.admin-panel', [
                'page' => 'admin-analytics',
                'enrollmentLabels' => $labels,
                'createdCounts' => $createdData,
                'enrolledCounts' => $enrolledData,
                'hearingChartData' => array_values($hearingCounts), // [deaf, hard-of-hearing, speech-delay, others]
                'loginLabels' => array_keys($averageLoginsPerGrade),
                'loginData' => array_values($averageLoginsPerGrade),
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
