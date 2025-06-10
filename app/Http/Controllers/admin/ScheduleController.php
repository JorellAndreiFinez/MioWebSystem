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
        $enrolleesRaw = $this->database->getReference('enrollment/enrollees')->getValue() ?? [];

        // Map teacherid to assigned schedule name
        $teacherScheduleMap = [];
        foreach ($schedulesRaw as $sched) {
            if (!empty($sched['teacherid'])) {
                $teacherScheduleMap[$sched['teacherid']] = $sched['schedule_name'];
            }
        }

        // Prepare teachers list
        $teachers = [];
        foreach ($usersRaw as $key => $user) {
            if (($user['role'] ?? null) === 'teacher') {
                $teachers[] = [
                    'teacherid' => $key,
                    'name' => ($user['fname'] ?? '') . ' ' . ($user['lname'] ?? ''),
                    'schedulename' => $teacherScheduleMap[$key] ?? 'Unassigned',
                ];
            }
        }

        // Prepare sections list
        $sections = [];
        foreach ($sectionsRaw as $key => $section) {
            $sections[] = [
                'id' => $key,
                'name' => $section['section_name'] ?? 'Unnamed',
                'level' => $section['section_grade'] ?? 'N/A',
            ];
        }

        // Combine students from users/ and enrollees/
        $students = [];

        // From users/
        foreach ($usersRaw as $key => $user) {
            if (($user['role'] ?? null) === 'student') {
                $isTherapy = ($user['enrollment_grade'] ?? '') === 'one-on-one-therapy';
                $students[] = [
                    'id' => $key,
                    'name' => ($user['fname'] ?? '') . ' ' . ($user['lname'] ?? ''),
                    'grade' => $user['enrollment_grade'] ?? ($user['grade_level'] ?? 'N/A'),
                    'is_therapy' => $isTherapy,
                ];
            }
        }

        // Sort students: therapy students first
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
        // Validate form input
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
        ]);

        // Enforce at least one assignment type
        $teacherSelected = $request->filled('teacherid');
        $sectionSelected = $request->filled('section_id');
        $studentsSelected = $request->filled('student_ids');

        if (!$teacherSelected && !$sectionSelected && !$studentsSelected) {
            return back()->with('status', 'You must assign the schedule to a Teacher, Section, or Students.')->withInput();
        }

        // Prevent duplicate Schedule ID
        $scheduleIdKey = $validated['scheduleid'];
        $existingSchedule = $this->database->getReference('schedules')->getValue();
        if (!empty($existingSchedule) && array_key_exists($scheduleIdKey, $existingSchedule)) {
            return back()->with('status', 'Schedule ID already exists!')->withInput();
        }

        // Get active school year
        $schoolYears = $this->database->getReference('schoolyears')->getValue();
        $activeSchoolYearId = null;
        if (!empty($schoolYears)) {
            foreach ($schoolYears as $key => $sy) {
                if (isset($sy['status']) && $sy['status'] === 'active') {
                    $activeSchoolYearId = $key;
                    break;
                }
            }
        }

        if (!$activeSchoolYearId) {
            return back()->with('status', 'No active school year found.')->withInput();
        }

        // Build data to be saved
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

        // Add optional assignments
        if ($teacherSelected) {
            $postData['teacherid'] = $validated['teacherid'];
        }

        if ($sectionSelected) {
            $postData['section_id'] = $validated['section_id'];
        }

        if ($studentsSelected) {
            $postData['student_ids'] = $validated['student_ids'];
        }

        // Save to Firebase
        $this->database->getReference('schedules/' . $scheduleIdKey)->set($postData);

        return redirect()->route('mio.ViewSchedule')->with('success', 'Schedule added successfully.');
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
                    $editdata['firebase_key'] = $key;  // Store Firebase key
                    break;
                }
            }
        }

        // Get all teachers
        $teachersRaw = $this->database->getReference('users')->getValue() ?? [];
        $schedulesRaw = $this->database->getReference('schedules')->getValue() ?? [];

        // Map each teacher ID to their Schedule name (if assigned)
        $teacherScheduleMap = [];
        foreach ($schedulesRaw as $sched) {
            if (!empty($sched['teacherid'])) {
                $teacherScheduleMap[$sched['teacherid']] = $sched['schedule_name'];
            }
        }

        $teachers = [];
        foreach ($teachersRaw as $key => $teacher) {
            if (isset($teacher['role']) && $teacher['role'] === 'teacher') {
                $teachers[] = [
                    'teacherid' => $key,
                    'name' => ($teacher['fname'] ?? '') . ' ' . ($teacher['lname'] ?? ''),
                    'schedulename' => $teacherScheduleMap[$key] ?? 'Unassigned'
                ];
            }
        }

        if ($editdata) {
            return view('mio.head.admin-panel', [
                'page' => 'edit-Schedule',
                'editdata' => $editdata,
                'teachers' => $teachers,
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
        $key = $id;
        $del_data = $this->database->getReference($this->table.'/'.$key)->remove();

        if ($del_data) {
            return redirect('mio/admin/Schedule')->with('status', 'Schedule Deleted Successfully');
        } else {
            return redirect('mio/admin/Schedule')->with('status', 'Schedule Not Deleted');
        }
   }
}
