<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;
use Carbon\Carbon;
use Kreait\Firebase\Exception\DatabaseException;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageObject;
use Barryvdh\DomPDF\Facade\Pdf;
use Kreait\Firebase\Exception\FirebaseException;
use Illuminate\Support\Facades\Log;
use Carbon\CarbonPeriod;



class CalendarController extends Controller
{

     protected $database;
    protected $table;
    protected $storageClient;
    protected $bucketName;

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

        // Create Google Cloud Storage client
        $this->storageClient = new StorageClient([
            'keyFilePath' => $path,
        ]);

        // Your Firebase Storage bucket name
        $this->bucketName = 'miolms.firebasestorage.app';
    }

   public function showCalendarStudent()
    {
        $user = session('firebase_user');
        if (!$user || !isset($user['uid']) || $user['role'] !== 'student') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Step 1: Get the active school year
        $schoolYears = $this->database->getReference("schoolyears")->getValue();
        $activeSchoolYear = collect($schoolYears)->firstWhere('status', 'active');

        if (!$activeSchoolYear) {
            return response()->json(['error' => 'Active school year not found'], 500);
        }

        // Step 2: Map month names to numbers
        $monthMap = [
            'January' => 1, 'February' => 2, 'March' => 3, 'April' => 4,
            'May' => 5, 'June' => 6, 'July' => 7, 'August' => 8,
            'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12
        ];

        $studentId = $user['uid'];
        $sectionId = $user['section_id'];

        $subjectsByGrade = $this->database->getReference("subjects")->getValue();
        $studentSubjects = [];

       foreach ($subjectsByGrade as $gradeLevel => $subjects) {
            foreach ($subjects as $subjectId => $subject) {
                if (isset($subject['people']) && array_key_exists($studentId, $subject['people'])) {
                    $studentSubjects[$subjectId] = $subject;
                }

                Log::info("Checking subject {$subjectId} for student {$studentId}", [
                    'subjectPeople' => array_keys($subject['people'] ?? []),
                    'match' => array_key_exists($studentId, $subject['people'] ?? [])
                ]);
            }
        }


        // Get global schedules for this student
        $globalSchedulesRef = $this->database->getReference("schedules")->getValue();
        $studentGlobalSchedules = [];

        foreach ($globalSchedulesRef as $scheduleId => $schedule) {
            if (
                isset($schedule['student_ids']) &&
                in_array($studentId, $schedule['student_ids'])
            ) {
                $studentGlobalSchedules[] = [
                    'title' => $schedule['schedule_name'] ?? 'Global Schedule',
                    'days' => $schedule['occurrences'],
                    'schoolyearid' => $schedule['schoolyearid'] ?? null,
                    'description' => $schedule['description'] ?? null,
                    'schedule_code' => $schedule['schedule_code'] ?? null,
                    'schedule_type' => $schedule['schedule_type'] ?? null,
                    'teacherid' => $schedule['teacherid'] ?? null,
                    'scheduleid' => $schedule['scheduleid'] ?? null,
                    'type' => 'global', 
                ];


            }
        }

        

        // Get subject-based schedules
        $subjectSchedules = [];
        foreach ($studentSubjects as $subject) {
            if (isset($subject['schedule']) &&
        isset($subject['schedule']['occurrence']) &&
        !empty($subject['schedule']['occurrence'])) {
             
            Log::info("Adding subject schedule:", [
                'subject_id' => $subject['subject_id'] ?? 'unknown',
                'title' => $subject['title'] ?? 'unknown',
                'days' => $subject['schedule']['occurrence'] ?? []
            ]);
                $subjectSchedules[] = [
                    'title' => $subject['title'] ?? 'Subject Schedule',
                    'days' => $subject['schedule']['occurrence'] ?? [],
                    'code' => $subject['code'] ?? '',
                    'teacher_id' => $subject['teacher_id'] ?? '',
                    'people' => $subject['people'] ?? [],
                    'modules' => $subject['modules'] ?? [],
                    'announcements' => $subject['announcements'] ?? [],
                    'schedule' => $subject['schedule'] ?? [],
                    'schoolyearid' => $subject['schoolyear_id'] ?? $activeSchoolYear['schoolyearid'],
                    'type' => 'subject', // <- ADD THIS
                    
                ];

            } else {
                Log::warning("No valid schedule for subject {$subjectId}", [
                    'has_schedule' => isset($subject['schedule']),
                    'has_occurrence' => isset($subject['schedule']['occurrence']),
                    'occurrence_is_empty' => empty($subject['schedule']['occurrence']),
                    'subject' => $subject
                ]);
            }
            
        } 

        

        // Format for FullCalendar
        $events = [];

        // Re-index school years by ID for fast lookup
        $schoolYearsById = collect($schoolYears)->keyBy('schoolyearid');

        foreach (array_merge($studentGlobalSchedules, $subjectSchedules) as $sched) {
            $schoolyearId = $sched['schoolyearid'] ?? $activeSchoolYear['schoolyearid'];
            $schoolYear = $schoolYearsById[$schoolyearId] ?? $activeSchoolYear;

            $startMonth = $monthMap[$schoolYear['start_month']];
            $endMonth = $monthMap[$schoolYear['end_month']];
            $yearCreated = \Carbon\Carbon::parse($schoolYear['created_at'])->year;

            $startYear = $yearCreated;
            $endYear = $startMonth > $endMonth ? $yearCreated + 1 : $yearCreated;

            $startOfMonth = \Carbon\Carbon::create($startYear, $startMonth, 1)->startOfMonth();
            $endOfMonth = \Carbon\Carbon::create($endYear, $endMonth, 1)->endOfMonth();

            foreach ($sched['days'] as $day => $time) {
                $startTime = !empty($time['start_time']) ? $time['start_time'] : ($time['start'] ?? null);
                $endTime = !empty($time['end_time']) ? $time['end_time'] : ($time['end'] ?? null);


                if (!$startTime || !$endTime) continue;

                $validDays = [
                    'Mon' => 'Monday', 'Tue' => 'Tuesday', 'Wed' => 'Wednesday',
                    'Thu' => 'Thursday', 'Fri' => 'Friday', 'Sat' => 'Saturday', 'Sun' => 'Sunday',
                    'Monday' => 'Monday', 'Tuesday' => 'Tuesday', 'Wednesday' => 'Wednesday',
                    'Thursday' => 'Thursday', 'Friday' => 'Friday', 'Saturday' => 'Saturday', 'Sunday' => 'Sunday',
                ];

                $dayNormalized = $validDays[$day] ?? null;

                if (!$dayNormalized || !$startTime || !$endTime) continue;

                $current = $startOfMonth->copy()->modify('last ' . $dayNormalized);

                while ($current->addWeek()->lte($endOfMonth)) {
                    $start = $current->copy()->setTimeFromTimeString($startTime);
                    $end = $current->copy()->setTimeFromTimeString($endTime);

                    $events[] = [
                        'title' => $sched['title'],
                        'start' => $start->toIso8601String(),
                        'end' => $end->toIso8601String(),
                        'type' => $sched['type'],
                        'data' => $sched // Attach full schedule data
                    ];
                }
            }
        }
        foreach ($events as &$event) {
            if ($event['type'] === 'global') {
                $event['color'] = '#007bff'; // Blue for global
            } elseif ($event['type'] === 'subject') {
                $event['color'] = '#28a745'; // Green for subject
            }
        }


        // Logs
        Log::info('Student UID: ' . $studentId);
        Log::info('Subject count: ' . count($studentSubjects));

        return view('mio.head.student-panel', [
            'page' => 'calendar',
            'events' => $events
        ]);
    }






}
