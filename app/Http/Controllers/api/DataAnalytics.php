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
use Illuminate\Support\Facades\View;
use Spatie\Browsershot\Browsershot;


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

        View::addNamespace('pdf', storage_path('app/public/pdf-templates'));
    }

    public function generateScoreBook(Request $request, string $subjectId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try {
            $snapshot  = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}")
                ->getSnapshot()
                ->getValue() ?? [];

            if (empty($snapshot['attempts']) || empty($snapshot['people'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Scorebook not found.',
                ], 404);
            }

            $people    = $snapshot['people'];
            $results   = [];

            foreach ($snapshot['attempts'] as $activityType => $byActivityId) {
                if (!is_array($byActivityId)) {
                    continue;
                }

                $studentData = [];

                foreach ($byActivityId as $activityId => $byStudent) {
                    if (!is_array($byStudent)) {
                        continue;
                    }

                    foreach ($byStudent as $studentId => $attempts) {
                        if (!is_array($attempts)) {
                            continue;
                        }

                        $latestAttempt = null;
                        $latestTs      = 0;
                        foreach ($attempts as $att) {
                            if (empty($att['submitted_at'])) {
                                continue;
                            }
                            $ts = strtotime($att['submitted_at']);
                            if ($ts > $latestTs) {
                                $latestTs       = $ts;
                                $latestAttempt  = $att;
                            }
                        }

                        if (! $latestAttempt) {
                            continue;
                        }

                        $lowFeedback = [];
                        if (!empty($latestAttempt['answers']) && is_array($latestAttempt['answers'])) {
                            foreach ($latestAttempt['answers'] as $itemId => $ans) {
                                if (
                                    isset($ans['feedback']['score'])
                                    && $ans['feedback']['score'] < 50
                                ) {
                                    $lowFeedback[] = [
                                        'item_id' => $itemId,
                                        'phone'   => $ans['feedback']['phoneme'] ?? null,
                                        'score'   => $ans['feedback']['score'],
                                    ];
                                }
                            }
                        }

                        $score = $latestAttempt['overall_score']
                            ?? $latestAttempt['score']
                            ?? null;

                        if (! is_numeric($score)) {
                            continue;
                        }

                        $studentData[$studentId][] = [
                            'score'       => $score,
                            'lowFeedback' => $lowFeedback,
                        ];
                    }
                }

                foreach ($studentData as $studentId => $entries) {
                    $scores      = array_column($entries, 'score');
                    $allLowFb    = array_reduce($entries, function($carry, $e) {
                        return array_merge($carry, $e['lowFeedback']);
                    }, []);
                    $average     = count($scores) ? array_sum($scores)/count($scores) : null;

                    $name = trim(
                        ($people[$studentId]['first_name'] ?? '')
                    . ' '
                    . ($people[$studentId]['last_name']  ?? '')
                    );

                    $results[] = [
                        'activity_type' => $activityType,
                        'student_id'    => $studentId,
                        'name'          => $name,
                        'overall_score' => $average !== null ? round($average, 2) : null,
                        'low_feedback'  => $allLowFb,
                    ];
                }
            }

            foreach ($results as &$row) {
                $labels = array_map(function($fb) {
                    return sprintf(
                        '/%s/ (%.1f%%)',
                        $fb['phone'],
                        $fb['score']
                    );
                }, $row['low_feedback']);

                $row['low_scoring_phonemes'] = implode(', ', $labels);
            }
            
            $html = view('pdf::scorebook', ['results' => $results])->render();

            try {
                $pdf = Browsershot::html($html)
                    ->format('A4')
                    ->margins(10, 10, 10, 10)
                    ->showBackground()
                    ->pdf();

                     return response($pdf, 200)
                        ->header('Content-Type', 'application/pdf')
                        ->header('Content-Disposition', 'attachment; filename="scorebook.pdf"');
            } catch (\Exception $e) {
                \Log::error('Browsershot error: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to generate PDF: ' . $e->getMessage(),
                ], 500);
            }

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Failed to generate scorebook: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function analyticsDashboard(Request $request){
        $userId = $request->get('firebase_user_id');

        $subjects_ref = $this->database->getReference("subjects/");
        $subjects_data = $subjects_ref->getSnapshot()->getValue() ?? [];

        $subjects = [];

        foreach ($subjects_data as $gradeLevel => $subjectList) {
            foreach ($subjectList as $subject_id => $subject) {
                if(isset($subject['people'][$userId])){
                    $subjects[$subject_id] = $subject;
                }
            }
        }

        $lastest_attempts = [];
        $sessions = 0;
        $completion_rate = [];

        foreach($subjects as $subjectId => $subject){
            $subject_type = $subject['specialized_type'];

            if(!empty($subject['attendance'])){
                $sessions += count($subject['attendance']);
            }

            if (!empty($subject['attempts'])) {
                foreach($subject['attempts'] as $activityType){
                    $activities = [];

                    foreach($activityType as $activity_id => $activity){
                        $latest_attempt = [];
                        $passed_student = [];

                        foreach($activity as $student_id => $student){
                            foreach($student as $attempt_id => $attempt){
                                if(empty($attempt['submitted_at'])){
                                    continue;
                                }

                                if (!isset($latest_attempt[$student_id]) || $attempt['submitted_at'] > $latest_attempt[$student_id]) {
                                    $latest_attempt[$student_id] = $attempt['submitted_at'];

                                    $score = $attempt['overall_score'] ?? $attempt['overall_score'] ?? 0;
                                    if($score > 75){
                                        $passed_student[$student_id] = $score;
                                    }
                                }
                            }
                        }

                        if (!empty($latest_attempt)) {
                            $lastest_attempts[$activity_id] = $latest_attempt;
                        }

                        if (!empty($passed_student)) {
                            $activities[$activity_id] = $passed_student;
                        }
                    }
                    $completion_rate[$activityType] = $activities;

                }
            }
        }

        $active_users = [];
        $today = date('Y-m-d');

        foreach ($lastest_attempts as $activity_id => $activity) {
            foreach ($activity as $student_id => $submitted_at) {
                $submitted_date = substr($submitted_at, 0, 10);

                if ($submitted_date === $today) {
                    $active_users[$student_id] = true;
                }
            }
        }


        return response()->json([
            'success' => true,
            'active_today' => count($active_users),
            'sessions' => $sessions,
            'completion_rate' => $completion_rate,
        ]);
    }
}
