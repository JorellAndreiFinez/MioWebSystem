<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Kreait\Firebase\Messaging\CloudMessage;


class DataAnalytics extends Controller
{
    public function __construct()
    {
        $path = base_path('storage/firebase/firebase.json');

        if (!file_exists($path)) {
            die("This File Path .{$path}. does not exist.");
        }

        $this->database = (new Factory)
            ->withServiceAccount($path)
            ->withDatabaseUri('https://miolms-default-rtdb.firebaseio.com')
            ->createDatabase();

        $this->storage = (new Factory())
            ->withServiceAccount($path)
            ->withDefaultStorageBucket('miolms.firebasestorage.app')
            ->createStorage();

        $this->messaging = (new Factory())
            ->withServiceAccount($path)
            ->createMessaging();
    }

    public function generateScoreBook(Request $request, string $subjectId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try {
            $activities = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}")
                ->getSnapshot()
                ->getValue() ?? [];

            if (empty($activities) || !is_array($activities)) {
                return response()->json([
                    'success' => false,
                    'message' => "Scorebook not found."
                ], 404);
            }

            $results = [];

            $peoples = $activities['people'];

            foreach ($activities['attempts'] as $activityType => $byActivityId) {
                if (!is_array($byActivityId)) continue;

                $studentScores = [];

                foreach ($byActivityId as $activityId => $byStudent) {
                    if (!is_array($byStudent)) continue;

                    foreach ($byStudent as $studentId => $attempts) {
                        if (!is_array($attempts)) continue;

                        $latestAttempt = null;
                        $latestTime = 0;

                        foreach ($attempts as $attempt) {
                            if (!isset($attempt['submitted_at'])) continue;

                            $ts = strtotime($attempt['submitted_at']);
                            if ($ts > $latestTime) {
                                $latestTime = $ts;
                                $latestAttempt = $attempt;
                            }
                        }

                        if ($latestAttempt) {
                            $score = $latestAttempt['overall_score'] ?? $latestAttempt['score'] ?? null;
                            if (!is_numeric($score)) continue;

                            $studentScores[$studentId][] = $score;
                        }
                    }
                }

                foreach ($studentScores as $studentId => $scores) {
                    $average = count($scores) > 0 ? array_sum($scores) / count($scores) : null;

                    $name = $peoples[$studentId]['first_name'] . " " . $peoples[$studentId]['last_name'];

                    $results[] = [
                        'activity_type' => $activityType,
                        'student_id' => $studentId,
                        'overall_score' => round($average, 2),
                        'name' => $name,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $results
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to generate scorebook: ' . $e->getMessage(),
            ], 500);
        }
    }
}
