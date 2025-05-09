<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Contract\Database;
use Carbon\Carbon;

class SchoolYearController extends Controller
{
    protected $auth;
    protected $database;
    protected $tablename = 'schoolyears';

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
    }

        public function viewSchoolYear()
        {
            $schoolyears = $this->database->getReference($this->tablename)->getValue() ?? [];

            // Automatically update school years to inactive if their end date has passed
            $this->updateExpiredSchoolYears($schoolyears);

            // Get the count of students, teachers, sections, and courses for each school year
            foreach ($schoolyears as $key => &$schoolyear) {
                $totals = $this->getTotalsBySchoolYear($schoolyear['schoolyearid']);
                $schoolyear['schoolyear_students'] = $totals['students'];
                $schoolyear['schoolyear_teachers'] = $totals['teachers'];
                $schoolyear['schoolyear_sections'] = $totals['sections'];
                $schoolyear['schoolyear_courses'] = $totals['courses'];
            }

            // Pass data to the view
            return view('mio.head.admin-panel', [
                'page' => 'view-schoolyear',
                'schoolyears' => $schoolyears
            ]);
        }


    public function showCreateSchoolYear()
    {
        $schoolyears = $this->database->getReference($this->tablename)->getValue() ?? [];

        $latestYear = 0;

        foreach ($schoolyears as $sy) {
            if (isset($sy['schoolyearid']) && preg_match('/SY(\d{4})(\d{4})/', $sy['schoolyearid'], $matches)) {
                $latestYear = max($latestYear, (int) $matches[1]);
            }
        }

        // Generate next school year ID
        $startYear = $latestYear ? $latestYear + 1 : now()->year;
        $endYear = $startYear + 1;
        $nextSchoolYearID = "SY{$startYear}{$endYear}";

        return view('mio.head.admin-panel', [
            'page' => 'add-schoolyear',
            'generated_schoolyear_id' => $nextSchoolYearID,
            'startYear' => $startYear,
            'endYear' => $endYear,
        ]);
    }

    public function addSchoolYear(Request $request)
    {
        // Validate the incoming data
        $validated = $request->validate([
            'schoolyearid' => 'required|string|max:20',
            'status' => 'required|in:active,inactive',
            'start_month' => 'required|string|max:9', // Month in text
            'end_month' => 'required|string|max:9', // Month in text
        ]);

        // Extract data from validated request
        $schoolyearid = $validated['schoolyearid'];
        $status = $validated['status'];
        $startMonth = $validated['start_month'];
        $endMonth = $validated['end_month'];

        // Check if the school year ID already exists in the database
        $schoolyears = $this->database->getReference($this->tablename)->getValue() ?? [];

        // Check if the school year exists and needs to be incremented
        if (array_key_exists($schoolyearid, $schoolyears)) {
            // Increment the school year ID by extracting the numeric part and adding 1
            preg_match('/SY(\d{4})(\d{4})/', $schoolyearid, $matches);
            if ($matches) {
                $startYear = (int)$matches[1];
                $endYear = (int)$matches[2];
                $startYear++;
                $endYear++;
                $schoolyearid = 'SY' . $startYear . $endYear; // Generate the new School Year ID
            }
        }

        // Check if there is already an active school year
        $hasActiveSchoolYear = false;

        foreach ($schoolyears as $id => $year) {
            if ($year['status'] === 'active') {
                $hasActiveSchoolYear = true;
                break;
            }
        }

        // If the new school year was requested as active, but one already exists, force it to inactive
        if ($status === 'active' && $hasActiveSchoolYear) {
            $status = 'inactive';
        } elseif ($status === 'active' && !$hasActiveSchoolYear) {
            // No active school year exists, allow this one to be active
            // Optionally archive/transfer from "active" if applicable (this part depends on your logic)
            $this->transferDataToNewSchoolYear($schoolyearid);
        } elseif ($status === 'inactive') {
            // If it's inactive, do nothing further
        }



        // Prepare the data to be saved for the new school year
        $postData = [
            'schoolyearid' => $schoolyearid,
            'status' => $status,
            'start_month' => $startMonth,
            'end_month' => $endMonth,
            'created_at' => Carbon::now()->toDateTimeString(),
        ];

        // Save the new school year to Firebase
        $this->database->getReference("{$this->tablename}/{$schoolyearid}")->set($postData);

        // If the new school year is active, transfer data from the active section
        if ($status === 'active') {
            $this->transferDataToNewSchoolYear($schoolyearid);
        }

        return redirect()->route('mio.view-schoolyear')->with('status', 'School Year Created');
    }




    // Archive data for inactive school year
    protected function archiveDataForSchoolYear($yearId)
    {
        // Archive all relevant data for the year under the school year ID
        $dataKeys = ['students', 'teachers', 'sections', 'subjects', 'courses'];

        foreach ($dataKeys as $dataKey) {
            $data = $this->database->getReference("$dataKey/{$yearId}")->getValue() ?? [];
            if ($data) {
                // Archive current data under this school year ID
                $archivePath = "archived/{$dataKey}/{$yearId}";
                $this->database->getReference($archivePath)->set($data);
                // Clear the data from the current year
                $this->database->getReference("$dataKey/{$yearId}")->remove();
            }
        }
    }

    // Transfer current data to the new school year
    protected function transferDataToNewSchoolYear($newYearId)
    {
        $dataKeys = ['students', 'teachers', 'sections', 'subjects', 'courses'];

        foreach ($dataKeys as $dataKey) {
            $data = $this->database->getReference("$dataKey/active")->getValue() ?? [];
            if ($data) {
                // Transfer current data to the new school year
                $this->database->getReference("$dataKey/{$newYearId}")->set($data);
                // Optionally, clear data from the active section if necessary
                $this->database->getReference("$dataKey/active")->remove();
            }
        }
    }


    // New method to update expired school years
    protected function updateExpiredSchoolYears($schoolyears)
    {
        $currentDate = Carbon::now();

        foreach ($schoolyears as $id => $year) {
            $startMonth = $year['start_month'] ?? '';
            $endMonth = $year['end_month'] ?? '';

            // Parse the year from the schoolyearid like "SY20252026"
            if (isset($year['schoolyearid']) && preg_match('/SY(\d{4})(\d{4})/', $year['schoolyearid'], $matches)) {
                $startYear = (int)$matches[1];
                $endYear = (int)$matches[2];

                if ($startMonth && $endMonth) {
                    try {
                        $endDate = Carbon::createFromFormat('F Y', "{$endMonth} {$endYear}");

                        // If the current date is after the end of the school year, mark it inactive
                        if ($currentDate->greaterThan($endDate)) {
                            $this->database->getReference("{$this->tablename}/{$id}/status")->set('inactive');
                        }
                    } catch (\Exception $e) {
                        // Invalid month or other error, skip
                        continue;
                    }
                }
            }
        }
    }

    // DISPLAY EDIT SCHOOL YEAR
        public function showEditSchoolYear($id)
        {
            // Get all school years
            $schoolyears = $this->database->getReference($this->tablename)->getValue();
            $editdata = null;

            // Find the school year by schoolyearid
            if ($schoolyears) {
                foreach ($schoolyears as $key => $year) {
                    if (isset($year['schoolyearid']) && $year['schoolyearid'] == $id) {
                        $editdata = $year;
                        $editdata['firebase_key'] = $key;  // Store Firebase key
                        break;
                    }
                }
            }

            if ($editdata) {
                return view('mio.head.admin-panel', [
                    'page' => 'edit-schoolyear',
                    'editdata' => $editdata,
                ]);
            } else {
                return redirect('mio/admin/schoolyear')->with('status', 'School Year ID Not Found');
            }
        }

        // UPDATE SCHOOL YEAR
        public function editSchoolYear(Request $request, $id)
        {
            $oldKey = $id; // Original school year ID from URL
            $newKey = $request->schoolyearid; // Possibly updated school year ID

            // Validate input
            $validated = $request->validate([
                'schoolyearid' => 'required|string|max:20',
                'status' => 'required|in:active,inactive',
                'start_month' => 'required|string|max:9', // Month in text
                'end_month' => 'required|string|max:9', // Month in text
            ]);

            // Reference to Firebase
            $schoolyearsRef = $this->database->getReference($this->tablename)->getValue();

            // Check if school year ID has changed and the new key already exists
            if ($oldKey !== $newKey && !empty($schoolyearsRef) && array_key_exists($newKey, $schoolyearsRef)) {
                return redirect()->back()->with('status', 'School Year ID already exists!')->withInput();
            }

            // Prepare updated data
            $postData = [
                'schoolyearid' => $newKey,
                'status' => $validated['status'],
                'start_month' => $validated['start_month'],
                'end_month' => $validated['end_month'],
                'created_at' => $schoolyearsRef[$oldKey]['created_at'] ?? Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString(),
            ];

            // If school year ID changed, remove old key and create new one
            if ($oldKey !== $newKey) {
                // Remove old
                $this->database->getReference($this->tablename . '/' . $oldKey)->remove();
            }

            // Save under new or same key
            $this->database->getReference($this->tablename . '/' . $newKey)->set($postData);

            return redirect()->route('mio.view-schoolyear')->with('status', 'School Year updated successfully!');
        }

        public function getTotalsBySchoolYear($schoolYearId)
{
    $users = $this->database->getReference('users')->getValue() ?? [];
    $sections = $this->database->getReference('sections')->getValue() ?? [];
    $subjects = $this->database->getReference('subjects')->getValue() ?? [];

    $studentCount = 0;
    $teacherCount = 0;
    $sectionCount = 0;
    $courseCount = 0;

    // Count students and teachers for the given school year
    foreach ($users as $user) {
        if (isset($user['role']) && isset($user['schoolyear_id']) && $user['schoolyear_id'] === $schoolYearId) {
            if ($user['role'] === 'student') {
                $studentCount++;
            } elseif ($user['role'] === 'teacher') {
                $teacherCount++;
            }
        }
    }

    // Count sections under the given school year
    foreach ($sections as $section) {
        if (isset($section['schoolyear_id']) && $section['schoolyear_id'] === $schoolYearId) {
            $sectionCount++;
        }
    }

    // Count courses under the given school year
    foreach ($subjects as $gradeLevel => $courses) {
        foreach ($courses as $course) {
            if (isset($course['schoolyear_id']) && $course['schoolyear_id'] === $schoolYearId) {
                $courseCount++;
            }
        }
    }

    return [
        'students' => $studentCount,
        'teachers' => $teacherCount,
        'sections' => $sectionCount,
        'courses' => $courseCount,
    ];
}






}

