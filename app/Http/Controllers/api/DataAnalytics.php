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

            $pdf = Browsershot::html($html)
                ->format('A4')
                ->margins(10, 10, 10, 10)
                ->showBackground()
                ->pdf();

            return response($pdf, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="scorebook.pdf"');

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Failed to generate scorebook: ' . $e->getMessage(),
            ], 500);
        }
    }
}
