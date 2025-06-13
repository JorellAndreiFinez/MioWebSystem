<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Database;
use Kreait\Firebase\Factory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ScheduleController extends Controller
{
    protected $database;
    protected $table = 'schedules';

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
    }

    public function schedules()
    {
        // Fetch sections from the database
        $schedules = $this->database->getReference($this->table)->getValue() ?? [];

        return view('mio.head.admin-panel', [
            'page' => 'view-schedule',
            'schedules' => $schedules
        ]);
    }

    public function showAddSchedule()
    {
        $usersRaw = $this->database->getReference('users')->getValue() ?? [];
        $schedulesRaw = $this->database->getReference('schedules')->getValue() ?? [];
        $sectionsRaw = $this->database->getReference('sections')->getValue() ?? [];
        $subjectsRaw = $this->database->getReference('subjects')->getValue() ?? [];

        // Step 1: Get schedule names from schedules/
        $teacherScheduleMap = [];
        foreach ($schedulesRaw as $sched) {
            if (!empty($sched['teacherid'])) {
                $teacherScheduleMap[$sched['teacherid']][] = $sched['schedule_name'];
            }
        }

        // Step 2: Add subject-based schedule occurrences for teachers
        $teacherSubjectSchedules = [];
        foreach ($subjectsRaw as $gradeLevel => $subjectsById) {
            foreach ($subjectsById as $subjectId => $subject) {
                if (!empty($subject['teacher_id']) && !empty($subject['schedule']['occurrence'])) {
                    $teacherId = $subject['teacher_id'];
                    foreach ($subject['schedule']['occurrence'] as $day => $timeslot) {
                        $teacherSubjectSchedules[$teacherId][] = [
                            'subject' => $subject['title'] ?? 'Unnamed Subject',
                            'day' => $day,
                            'start' => $timeslot['start'] ?? '',
                            'end' => $timeslot['end'] ?? '',
                        ];
                    }
                }
            }
        }

        // Step 3: Prepare teachers list (combine both schedule sources)
        $teachers = [];
        foreach ($usersRaw as $key => $user) {
            if (($user['role'] ?? null) === 'teacher') {
                $manualSchedNames = $teacherScheduleMap[$key] ?? [];
                $subjectScheds = $teacherSubjectSchedules[$key] ?? [];

                // Combine readable schedule summary
               $schedulename = [];

                if (!empty($manualSchedNames)) {
                    $schedulename[] = implode(', ', $manualSchedNames);
                }

                if (!empty($subjectScheds)) {
                    $subjectSummary = collect($subjectScheds)
                        ->map(fn($s) => "{$s['subject']} ({$s['day']} {$s['start']}-{$s['end']})")
                        ->implode(', ');
                    $schedulename[] = $subjectSummary;
                }

                $schedulename = !empty($schedulename) ? implode(' | ', $schedulename) : 'Unassigned';


                $teachers[] = [
                    'teacherid' => $key,
                    'name' => ($user['fname'] ?? '') . ' ' . ($user['lname'] ?? ''),
                    'schedulename' => $schedulename,
                    'subject_schedules' => $subjectScheds,
                ];
            }
        }

        // Step 4: Prepare sections
        $sections = [];
        foreach ($sectionsRaw as $key => $section) {
            $sections[] = [
                'id' => $key,
                'name' => $section['section_name'] ?? 'Unnamed',
                'level' => $section['section_grade'] ?? 'N/A',
            ];
        }

        // Step 5: Prepare students
        $students = [];
        foreach ($usersRaw as $key => $user) {
            if (($user['role'] ?? null) === 'student') {
                $isTherapy = ($user['enrollment_grade'] ?? '') === 'one-on-one-therapy';
                $students[$key] = [
                    'id' => $key,
                    'name' => ($user['fname'] ?? '') . ' ' . ($user['lname'] ?? ''),
                    'grade' => $user['enrollment_grade'] ?? ($user['grade_level'] ?? 'N/A'),
                    'is_therapy' => $isTherapy,
                    'schedules' => [],
                ];
            }
        }

        // Step 6: Append subject-based schedules per student
        foreach ($subjectsRaw as $gradeLevel => $subjectsById) {
            foreach ($subjectsById as $subjectId => $subject) {
                if (!empty($subject['people'])) {
                    foreach ($subject['people'] as $studentId => $personData) {
                        if (($personData['role'] ?? '') === 'student' && isset($students[$studentId])) {
                            if (!empty($subject['schedule']['occurrence'])) {
                                foreach ($subject['schedule']['occurrence'] as $day => $timeslot) {
                                    $students[$studentId]['schedules'][] = [
                                        'subject' => $subject['title'] ?? 'Unnamed Subject',
                                        'day' => $day,
                                        'start' => $timeslot['start'] ?? '',
                                        'end' => $timeslot['end'] ?? '',
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        // Sort students: therapy first
        $students = array_values($students);
        usort($students, function ($a, $b) {
            return $b['is_therapy'] <=> $a['is_therapy'];
        });

        return view('mio.head.admin-panel', [
            'page' => 'add-schedule',
            'teachers' => $teachers,
            'sections' => $sections,
            'students' => $students,
        ]);
    }



    public function addSchedule(Request $request)
    {
        try {
            // Validate basic fields
            $validated = $request->validate([
                'scheduleid' => 'required|string|max:20',
                'schedule_name' => 'required|string|max:255',
                'schedule_type' => 'required|string|in:academic,admin_support,specialized',
                'schedule_code' => 'required|string|max:50',
                'description' => 'nullable|string|max:1000',
                'status' => 'required|in:active,inactive',
                'teacherid' => 'nullable|string|max:50',
                'section_id' => 'nullable|string|max:50',
                'student_ids' => 'nullable|array',
                'student_ids.*' => 'string|max:50',
                'occurrences' => 'required|array',
                'occurrences.*' => 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            ]);

            // Check at least one assignment
            if (!$request->filled('teacherid') && !$request->filled('section_id') && !$request->filled('student_ids')) {
                return back()->with('status', 'You must assign the schedule to a Teacher, Section, or Students.')->withInput();
            }

            // Check for duplicate schedule ID
            $scheduleIdKey = $validated['scheduleid'];
            $existing = $this->database->getReference('schedules')->getValue();
            if (!empty($existing) && array_key_exists($scheduleIdKey, $existing)) {
                return back()->with('status', 'Schedule ID already exists!')->withInput();
            }

            // Get active school year
            $schoolYears = $this->database->getReference('schoolyears')->getValue();
            $activeSchoolYearId = null;
            foreach ($schoolYears ?? [] as $key => $sy) {
                if (($sy['status'] ?? null) === 'active') {
                    $activeSchoolYearId = $key;
                    break;
                }
            }

            if (!$activeSchoolYearId) {
                return back()->with('status', 'No active school year found.')->withInput();
            }


            // Prepare base data
            $postData = [
                'scheduleid' => $scheduleIdKey,
                'schedule_name' => $validated['schedule_name'],
                'schedule_type' => $validated['schedule_type'],
                'schedule_code' => $validated['schedule_code'],
                'description' => $validated['description'] ?? '',
                'status' => $validated['status'],
                'schoolyearid' => $activeSchoolYearId,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];



            if ($request->filled('teacherid')) $postData['teacherid'] = $request->input('teacherid');
            if ($request->filled('section_id')) $postData['section_id'] = $request->input('section_id');
            if ($request->filled('student_ids')) $postData['student_ids'] = $request->input('student_ids');

            // Handle schedule times
            $sameTime = $request->has('sameTimeToggle');
            $occurrences = $validated['occurrences'];
            $postData['occurrences'] = [];

            if ($sameTime) {
                $request->validate([
                    'common_start_time' => 'required|date_format:H:i',
                    'common_end_time' => 'required|date_format:H:i|after:common_start_time',
                ]);

                foreach ($occurrences as $day) {
                    $postData['occurrences'][$day] = [
                        'start_time' => $request->input('common_start_time'),
                        'end_time' => $request->input('common_end_time'),
                    ];
                }
            } else {
                $dayTimes = $request->input('day_times', []);
                foreach ($occurrences as $day) {
                    $start = $dayTimes[$day]['start'] ?? null;
                    $end = $dayTimes[$day]['end'] ?? null;

                    if (!$start || !$end) {
                        return back()->with('status', "Start and end times are required for $day.")->withInput();
                    }

                    if (strtotime($end) <= strtotime($start)) {
                        return back()->with('status', "End time must be after start time on $day.")->withInput();
                    }

                    $postData['occurrences'][$day] = [
                        'start_time' => $start,
                        'end_time' => $end,
                    ];
                }
            }

            $allSchedules = $this->database->getReference('schedules')->getValue();
            $allSubjects = $this->database->getReference('subjects')->getValue();

            foreach ($allSchedules ?? [] as $existingSchedule) {
                $days = array_keys($postData['occurrences']);
                foreach ($days as $day) {
                    if (isset($existingSchedule['occurrences'][$day])) {
                        $existingStart = $existingSchedule['occurrences'][$day]['start_time'];
                        $existingEnd = $existingSchedule['occurrences'][$day]['end_time'];
                        $newStart = $postData['occurrences'][$day]['start_time'];
                        $newEnd = $postData['occurrences'][$day]['end_time'];

                        // Match Teacher
                        if (
                            isset($postData['teacherid'], $existingSchedule['teacherid']) &&
                            $postData['teacherid'] === $existingSchedule['teacherid'] &&
                            $this->isTimeOverlap($existingStart, $existingEnd, $newStart, $newEnd)
                        ) {
                            return back()->with('status', "Schedule conflict for teacher on $day.")->withInput();
                        }

                        // Match Students
                        $newStudents = $postData['student_ids'] ?? [];
                        $existingStudents = $existingSchedule['student_ids'] ?? [];
                        foreach ($newStudents as $studentId) {
                            if (in_array($studentId, $existingStudents) &&
                                $this->isTimeOverlap($existingStart, $existingEnd, $newStart, $newEnd)
                            ) {
                                return back()->with('status', "Schedule conflict for student $studentId on $day.")->withInput();
                            }
                        }
                    }
                }
            }

            foreach ($allSubjects ?? [] as $gradeLevel => $subjectsByGrade) {
                foreach ($subjectsByGrade ?? [] as $subjectId => $subject) {
                    $days = array_keys($postData['occurrences']);
                    foreach ($days as $day) {
                        if (isset($subject['schedule']['occurrence'][$day])) {
                            $existingStart = $subject['schedule']['occurrence'][$day]['start'];
                            $existingEnd = $subject['schedule']['occurrence'][$day]['end'];
                            $newStart = $postData['occurrences'][$day]['start_time'];
                            $newEnd = $postData['occurrences'][$day]['end_time'];

                            // Match teacher
                            if (
                                isset($postData['teacherid'], $subject['teacher_id']) &&
                                $postData['teacherid'] === $subject['teacher_id'] &&
                                $this->isTimeOverlap($existingStart, $existingEnd, $newStart, $newEnd)
                            ) {
                                return back()->with('status', "Teacher is already assigned to subject schedule on $day.")->withInput();
                            }

                            // Match students
                            $newStudents = $postData['student_ids'] ?? [];
                            foreach ($newStudents as $studentId) {
                                if (isset($subject['people'][$studentId]) &&
                                    $subject['people'][$studentId]['role'] === 'student' &&
                                    $this->isTimeOverlap($existingStart, $existingEnd, $newStart, $newEnd)
                                ) {
                                    return back()->with('status', "Student $studentId has a subject schedule conflict on $day.")->withInput();
                                }
                            }
                        }
                    }
                }
            }




            // Save to Firebase
            $this->database->getReference('schedules/' . $scheduleIdKey)->set($postData);

            return redirect()->route('mio.ViewSchedule')->with('status', 'Schedule added successfully.');
        } catch (\Throwable $e) {
            return back()->with('status', 'An error occurred: ' . $e->getMessage())->withInput();
        }
    }

    private function isTimeOverlap($startA, $endA, $startB, $endB)
    {
        return strtotime($startA) < strtotime($endB) && strtotime($startB) < strtotime($endA);
    }





    // DISPLAY EDIT Schedule
    public function showEditSchedule($id)
    {
        // Get all schedules
        $schedules = $this->database->getReference($this->table)->getValue();
        $editdata = null;

        // Find the Schedule by scheduleid
        if ($schedules) {
            foreach ($schedules as $key => $Schedule) {
                if (isset($Schedule['scheduleid']) && $Schedule['scheduleid'] == $id) {
                    $editdata = $Schedule;
                    $editdata['firebase_key'] = $key;

                    // Normalize student_ids
                    $studentIds = $Schedule['student_ids'] ?? [];
                    if (!is_array($studentIds)) {
                        $studentIds = array_values((array)$studentIds);
                    }
                    $editdata['student_ids'] = $studentIds;
                    break;
                }
            }
        }

        // Get all teachers
        $teachersRaw = $this->database->getReference('users')->getValue() ?? [];
        $schedulesRaw = $this->database->getReference('schedules')->getValue() ?? [];
        $subjectsRaw = $this->database->getReference('subjects')->getValue() ?? [];

        // Map each teacher ID to their Schedule name (if assigned)
        $teacherScheduleMap = [];
        foreach ($schedulesRaw as $sched) {
            if (!empty($sched['teacherid'])) {
                $teacherScheduleMap[$sched['teacherid']][] = $sched['schedule_name'];
            }
        }

        // Include subject-based teacher schedule mapping
        $teacherSubjectSchedules = [];
        foreach ($subjectsRaw as $gradeLevel => $subjectsById) {
            foreach ($subjectsById as $subjectId => $subject) {
                if (!empty($subject['teacher_id']) && !empty($subject['schedule']['occurrence'])) {
                    $teacherId = $subject['teacher_id'];
                    foreach ($subject['schedule']['occurrence'] as $day => $timeslot) {
                        $teacherSubjectSchedules[$teacherId][] = [
                            'subject' => $subject['title'] ?? 'Unnamed Subject',
                            'day' => $day,
                            'start' => $timeslot['start'] ?? '',
                            'end' => $timeslot['end'] ?? '',
                        ];
                    }
                }
            }
        }

        // Final teacher structure
        $teachers = [];
        foreach ($teachersRaw as $key => $user) {
            if (($user['role'] ?? '') === 'teacher') {
                $manualSchedNames = $teacherScheduleMap[$key] ?? [];
                $subjectScheds = $teacherSubjectSchedules[$key] ?? [];

                $schedulename = [];

                if (!empty($manualSchedNames)) {
                    $schedulename[] = implode(', ', $manualSchedNames);
                }

                if (!empty($subjectScheds)) {
                    $subjectSummary = collect($subjectScheds)
                        ->map(fn($s) => "{$s['subject']} ({$s['day']} {$s['start']}-{$s['end']})")
                        ->implode(', ');
                    $schedulename[] = $subjectSummary;
                }

                $teachers[] = [
                    'teacherid' => $key,
                    'name' => ($user['fname'] ?? '') . ' ' . ($user['lname'] ?? ''),
                    'schedulename' => !empty($schedulename) ? implode(' | ', $schedulename) : 'Unassigned',
                    'subject_schedules' => $subjectScheds,
                ];
            }
        }

        // Get all students
        $allUsers = $teachersRaw; // reuse $teachersRaw which contains all users
        $students = [];

        foreach ($allUsers as $key => $user) {
            if (($user['role'] ?? '') === 'student') {
                $isTherapy = ($user['enrollment_grade'] ?? '') === 'one-on-one-therapy';
                $students[$key] = [
                    'id' => $key,
                    'name' => ($user['fname'] ?? '') . ' ' . ($user['lname'] ?? ''),
                    'grade' => $user['enrollment_grade'] ?? ($user['grade_level'] ?? 'N/A'),
                    'is_therapy' => $isTherapy,
                    'schedules' => [], // to be filled with subject-based schedules
                ];
            }
        }



        // Fill subject-based schedules for students
        foreach ($subjectsRaw as $gradeLevel => $subjectsById) {
            foreach ($subjectsById as $subjectId => $subject) {
                if (!empty($subject['people'])) {
                    foreach ($subject['people'] as $studentId => $personData) {
                        if (($personData['role'] ?? '') === 'student' && isset($students[$studentId])) {
                            if (!empty($subject['schedule']['occurrence'])) {
                                foreach ($subject['schedule']['occurrence'] as $day => $timeslot) {
                                    $students[$studentId]['schedules'][] = [
                                        'subject' => $subject['title'] ?? 'Unnamed Subject',
                                        'day' => $day,
                                        'start' => $timeslot['start'] ?? '',
                                        'end' => $timeslot['end'] ?? '',
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        // Merge one-on-one/manual schedules for each student
        foreach ($schedulesRaw as $schedule) {
            if (!empty($schedule['student_ids']) && is_array($schedule['student_ids'])) {
                foreach ($schedule['student_ids'] as $studentId) {
                    if (isset($students[$studentId]) && !empty($schedule['occurrences'])) {
                        foreach ($schedule['occurrences'] as $day => $time) {
                            $students[$studentId]['schedules'][] = [
                                'subject' => $schedule['schedule_name'] ?? 'Scheduled',
                                'day' => $day,
                                'start' => $time['start_time'] ?? '',
                                'end' => $time['end_time'] ?? '',
                                'isManual' => true
                            ];
                        }
                    }
                }
            }
        }


        // Sort students: therapy first
        $students = array_values($students);
        usort($students, fn($a, $b) => $b['is_therapy'] <=> $a['is_therapy']);

        if ($editdata) {
            return view('mio.head.admin-panel', [
                'page' => 'edit-schedule',
                'editdata' => $editdata,
                'teachers' => $teachers,
                'students' => $students,
            ]);
        } else {
            return redirect('mio/admin/schedules')->with('status', 'Schedule ID Not Found');
        }
    }



    public function editSchedule(Request $request, $id)
    {
        $oldKey = $id; // Original section ID from URL
        $newKey = $request->scheduleid; // Possibly updated section ID

        // Validate input
       $validated = $request->validate([
            'scheduleid' => 'required|string|max:20',
            'schedule_name' => 'required|string|max:255',
            'schedule_type' => 'required|string|in:academic,admin_support,specialized',
            'schedule_code' => 'required|string|max:50',
            'description' => 'nullable|string|max:1000',
            'teacherid' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
        ]);

        // Reference to Firebase
        $schedulesRef = $this->database->getReference($this->table)->getValue();

        // Check if section ID has changed and the new key already exists
        if ($oldKey !== $newKey && !empty($schedulesRef) && array_key_exists($newKey, $schedulesRef)) {
            return redirect()->back()->with('status', 'Schedule ID already exists!')->withInput();
        }

        // Fetch the active school year from Firebase
        $activeSchoolYearRef = $this->database->getReference('schoolyears');
        $schoolYears = $activeSchoolYearRef->getValue() ?? [];
        $activeSchoolYear = null;

        // Find the active school year
        foreach ($schoolYears as $schoolYear) {
            if ($schoolYear['status'] === 'active') {
                $activeSchoolYear = $schoolYear['schoolyearid'];
                break;
            }
        }

        if (!$activeSchoolYear) {
            return back()->with('status', 'No active school year found!')->withInput();
        }


        // Prepare updated data
        $postData = [
            'scheduleid' => $newKey,
            'schedule_name' => $validated['schedule_name'],
            'schedule_type' => $validated['schedule_type'],
            'schedule_code' => $validated['schedule_code'],
            'description' => $validated['description'] ?? '',
            'teacherid' => $validated['teacherid'] ?? null,
            'created_at' => $sectionsRef[$oldKey]['created_at'] ?? Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
            'status' => $validated['status'],
            'schoolyearid' => $activeSchoolYear
        ];

        // If section ID changed, remove old key and create new one
        if ($oldKey !== $newKey) {
            // Remove old
            $this->database->getReference($this->table . '/' . $oldKey)->remove();
        }

        // Save under new or same key
        $this->database->getReference($this->table . '/' . $newKey)->set($postData);

        return redirect()->route('mio.ViewSchedule')->with('status', 'Schedule updated successfully!');
    }


// DELETE Schedule
    public function deleteSchedule($id)
    {
        $schedules = $this->database->getReference($this->table)->getValue();

        if ($schedules) {
            foreach ($schedules as $key => $schedule) {
                if (isset($schedule['scheduleid']) && $schedule['scheduleid'] == $id) {
                    $this->database->getReference($this->table . '/' . $key)->remove();
                    return redirect()->route('mio.ViewSchedule')->with('status', 'Schedule Deleted Successfully');

                }
            }
        }

        return redirect()->route('mio.ViewSchedule')->with('status', 'Schedule Not Deleted - Not Found');

    }


}
