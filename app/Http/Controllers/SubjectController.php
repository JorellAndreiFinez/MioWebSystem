<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Carbon\Carbon;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageObject;
use Barryvdh\DomPDF\Facade\Pdf;

class SubjectController extends Controller
{
    protected $database;
    protected $tablename;
    protected $bucketName;
    protected $storageClient;


    protected $gradeLevelsTable = 'gradelevel'; // Firebase collection for grade levels

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


     // Fetch grade levels from Firebase and display them in the view
     public function showGradeLevels()
     {
        $gradeLevels = $this->database->getReference($this->gradeLevelsTable)->getSnapshot()->getValue();

        // Sort the grade levels from GR7 to GR10
        uksort($gradeLevels, function ($a, $b) {
            return (int) filter_var($a, FILTER_SANITIZE_NUMBER_INT) - (int) filter_var($b, FILTER_SANITIZE_NUMBER_INT);
        });

         return view('mio.head.admin-panel', ['page' => 'subjects'],compact('gradeLevels'));
     }

     // View subjects under a grade
    public function viewSubjects($grade)
    {
        $subjects = $this->database->getReference("subjects/{$grade}")->getSnapshot()->getValue() ?? [];
        $gradeLevel = $this->database->getReference("gradelevel/$grade")->getValue();

        return view('mio.head.admin-panel', [
            'page' => 'view-subject',
            'subjects' => $subjects,
            'gradeLevel' => $gradeLevel
        ], compact('subjects', 'grade'));
    }

    public function viewSubjectsApi(Request $request)
    {
        // Retrieve the Firebase user ID from the request
        $uid = $request->get('firebase_user');

        if (!$uid) {
            return response()->json([
                'success' => false,
                'error' => 'User ID is missing.',
            ], 400);
        }

        // Fetch user data from Firebase
        $gradeLevel = $this->database->getReference('users/' . $uid  . "/section_grade")->getValue();

        if (!$gradeLevel) {
            return response()->json([
                'success' => false,
                'error' => 'User data or grade level not found.',
            ], 404);
        }

        // Fetch subjects by grade level
        $subjects = $this->database
            ->getReference("subjects/GR" . $gradeLevel)
            ->getSnapshot()
            ->getValue() ?? [];

        // Filter subjects by subject ID, section, title, and description
        $filteredSubjects = [];
        foreach ($subjects as $subjectId => $subjectData) {
            $filteredSubjects[] = [
                'subject_id' => $subjectId,
                'section' => $subjectData['code'] ?? null,
                'title' => $subjectData['title'] ?? null,
                'description' => $subjectData['modules']['MOD00']['description'] ?? null,
                'subjectType' => $subjectData['subjectType'] ?? null,
            ];
        }

        // Return a RESTful API response
        return response()->json([
            'success' => true,
            'subjects' => $filteredSubjects,
        ], 200);
    }

    public function getSubjectModulesApi(Request $request, string $subjectId)
    {
        $uid = $request->get('firebase_user');

        if (!$subjectId) {
            return response()->json([
                'success' => false,
                'error' => 'Subject ID is required.',

            ], 400);
        }

        if (!$uid) {
            return response()->json([
                'success' => false,
                'error' => 'User ID is missing.',
            ], 400);
        }

        $gradeLevel = $this->database->getReference('users/' . $uid  . "/section_grade")->getValue();

        if (!$gradeLevel) {
            return response()->json([
                'success' => false,
                'error' => 'User data or grade level not found.',
            ], 404);
        }


        $modules = $this->database
            ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/modules")
            ->getSnapshot()
            ->getValue()
            ?? [];

        $filteredmodules = [];

        if (!empty($modules) && is_array($modules)) {
            foreach ($modules as $key => $item) {
                $filteredmodules[] = [
                    'module_id' => $key,
                    'title' => $item['title'] ?? null,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'modules' => $filteredmodules,
        ], 200);
    }

    public function getSubjectAnnouncementsApi(Request $request, string $subjectId)
    {
        $uid = $request->get('firebase_user');

        if (!$subjectId) {
            return response()->json([
                'success' => false,
                'error' => 'Subject ID is required.',

            ], 400);
        }

        if (!$uid) {
            return response()->json([
                'success' => false,
                'error' => 'User ID is missing.',
            ], 400);
        }

        $gradeLevel = $this->database->getReference('users/' . $uid  . "/section_grade")->getValue();

        if (!$gradeLevel) {
            return response()->json([
                'success' => false,
                'error' => 'User data or grade level not found.',
            ], 404);
        }

        $announcements = $this->database
            ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/announcements")
            ->getSnapshot()
            ->getValue();

        $filteredAnnouncements = [];
        if (!empty($announcements) && is_array($announcements)) {
            foreach ($announcements as $key => $item) {
                $filteredAnnouncements[] = [
                    'announcement_id' => $key,
                    'date_posted'     => $item['date_posted']    ?? null,
                    'description'     => $item['description']    ?? null,
                    'subject_id'      => $item['subject_id']     ?? null,
                    'title'           => $item['title']          ?? null,
                ];
            }
        }

        return response()->json([
            'success'       => true,
            'announcements' => $filteredAnnouncements,
        ], 200);
    }

    public function getSubjectAssignmentsApi(Request $request, string $subjectId)
    {
        $uid = $request->get('firebase_user');

        if (!$subjectId) {
            return response()->json([
                'success' => false,
                'error' => 'Subject ID is required.',

            ], 400);
        }

        if (!$uid) {
            return response()->json([
                'success' => false,
                'error' => 'User ID is missing.',
            ], 400);
        }

        $gradeLevel = $this->database->getReference('users/' . $uid  . "/section_grade")->getValue();

        if (!$gradeLevel) {
            return response()->json([
                'success' => false,
                'error' => 'User data or grade level not found.',
            ], 404);
        }

        $assignments = $this->database
            ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/assignments")
            ->getSnapshot()
            ->getValue();

        $filteredAssignments = [];
        if (!empty($assignments) && is_array($assignments)) {
            foreach ($assignments as $key => $item) {
                $filteredAssignments[] = [
                    'assignment_id' => $key,
                    'attempts' => $item['attempts'] ?? null,
                    'deadline' => $item['deadline'] ?? null,
                    'availability' => $item['availability'] ?? null,
                    'createdAt' => $item['created_at'] ?? null,
                    'description' => $item['description'] ?? null,
                    'points' => $item['points'] ?? null,
                    'title' => $item['title'] ?? null,
                ];
            }
        }

        return response()->json([
            'success'       => true,
            'assignments' => $filteredAssignments,
        ], 200);
    }

    public function getSubjectScoresApi(Request $request, string $subjectId)
    {
        $uid = $request->get('firebase_user');

        if (!$subjectId) {
            return response()->json([
                'success' => false,
                'error' => 'Subject ID is required.',

            ], 400);
        }

        if (!$uid) {
            return response()->json([
                'success' => false,
                'error' => 'User ID is missing.',
            ], 400);
        }

        $userData = $this->database->getReference('users/' . $uid)->getValue();

        if (!$userData || !isset($userData['grade_level'])) {
            return response()->json([
                'success' => false,
                'error' => 'User data or grade level not found.',
            ], 404);
        }

        $gradeLevel = $userData['grade_level'];

        $scores = $this->database
            ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/scores")
            ->getSnapshot()
            ->getValue();

        return response()->json([
            'success' => true,
            'scores' => $scores,
        ], 200);
    }


    // Show add subject form
    public function showAddSubjectForm($grade)
        {
            // Get teachers from Firebase
            $teachersRaw = $this->database->getReference('users')->getValue() ?? [];

            $teachers = [];
            foreach ($teachersRaw as $key => $teacher) {
                if (isset($teacher['role']) && $teacher['role'] === 'teacher') {
                    $teachers[] = [
                        'teacherid' => $key,
                        'name' => ($teacher['fname'] ?? '') . ' ' . ($teacher['lname'] ?? '')
                    ];
                }
            }

            // Get sections from Firebase
            $sectionsRaw = $this->database->getReference('sections')->getValue() ?? [];

            $sections = [];
            foreach ($sectionsRaw as $key => $section) {
                $sections[] = [
                    'sectionid' => $key,
                    'name' => $section['section_name'] ?? 'Unnamed',
                    'status' => $section['section_status'] ?? 'unknown',
                    'teacherid' => $section['teacherid'] ?? null
                ];
            }

            // Get all subjects for the grade level
            $subjectsRaw = $this->database->getReference("subjects/{$grade}")->getValue() ?? [];

            // Map of section schedules: section_id => array of schedules
            $sectionSchedules = [];

            foreach ($subjectsRaw as $subjectID => $subjectData) {
                if (!isset($subjectData['section_id']) || !isset($subjectData['title'])) continue;

                $occurrenceData = $subjectData['schedule']['occurrence'] ?? [];

                foreach ($occurrenceData as $day => $time) {
                    $schedule = [
                        'title' => $subjectData['title'],
                        'start_time' => $time['start'] ?? null,
                        'end_time' => $time['end'] ?? null,
                        'occurrences' => [$day], // single day per entry
                    ];

                    $section_id = $subjectData['section_id'];
                    $sectionSchedules[$section_id][] = $schedule;
                }
            }



            return view('mio.head.admin-panel', [
                'page' => 'add-subjects',
                'teachers' => $teachers,
                'sections' => $sections,
                'grade' => $grade,
                'sectionSchedules' => $sectionSchedules
            ]);
        }

    public function addSubject(Request $request, $grade)
    {
        try {
            $now = now();
            $announcementKey = "SUB-ANN" . $now->format('Ymd') . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);

            $validatedData = $request->validate([
                'subject_id' => 'required|string|max:100',
                'code' => 'required|string|max:50',
                'title' => 'required|string|max:255',
                'subjectType' => 'required|in:academics,specialized',
                'specialized_type' => 'nullable|string|max:50',
                'teacher_id' => 'required|string|max:50',
                'section_id' => 'required|string|max:50',
                'modules' => 'nullable|array',
                'modules.*.title' => 'required|string|max:255',
                'modules.*.description' => 'nullable|string',
                'modules.*.files.*' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,mp4,zip,jpg,jpeg,png,gif,bmp,webp,svg,heic,heif|max:20480',
                'modules.*.external_link' => 'nullable|url',
                'announcements' => 'nullable|array',
                'announcements.*.title' => 'nullable|string|max:255',
                'announcements.*.description' => 'nullable|string|max:1000',
                'announcements.*.date' => 'nullable|date',
                'announcements.*.files.*' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,mp4,zip,jpg,jpeg,png,gif,bmp,webp,svg,heic,heif|max:20480',
                'announcements.*.link' => 'nullable|url',

                'occurrences' => 'required|array|min:1',
                'occurrences.*' => 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                'common_start_time' => 'nullable|date_format:H:i',
                'common_end_time' => 'nullable|date_format:H:i|after:common_start_time',
                'day_times' => 'nullable|array',
                'day_times.*.start' => 'nullable|date_format:H:i',
                'day_times.*.end' => 'nullable|date_format:H:i|after:day_times.*.start',


            ]);

            $subjectId = $validatedData['subject_id'];
            $subjectsRef = $this->database->getReference("subjects/{$grade}")->getValue();
            if (!empty($subjectsRef) && array_key_exists($subjectId, $subjectsRef)) {
                return redirect()->back()->with('status', 'Subject ID already exists!')->withInput();
            }

            $schoolYears = $this->database->getReference('schoolyears')->getValue();
            $activeSchoolYearId = collect($schoolYears)->firstWhere('status', 'active')['schoolyearid'] ?? null;
            if (!$activeSchoolYearId) {
                return redirect()->back()->with('status', 'No active school year found.')->withInput();
            }

            // Step 1: Get the submitted schedule
            $newSchedule = [];

            if ($request->has('sameTimeToggle') && $request->input('sameTimeToggle') === 'on') {
                foreach ($validatedData['occurrences'] as $day) {
                    $newSchedule[$day] = [
                        'start' => $request->input('common_start_time'),
                        'end' => $request->input('common_end_time'),
                    ];
                }
            } elseif ($request->has('day_times')) {
                foreach ($request->input('day_times') as $day => $times) {
                    $newSchedule[$day] = [
                        'start' => $times['start'],
                        'end' => $times['end'],
                    ];
                }
            }

            // Step 2: Get existing subjects for the same section
            $subjects = $this->database->getReference("subjects/{$grade}")->getValue() ?? [];
            $sectionId = $validatedData['section_id'];

            foreach ($subjects as $existingSubject) {
                if (!isset($existingSubject['section_id']) || $existingSubject['section_id'] !== $sectionId) continue;

                $existingSchedule = $existingSubject['schedule']['occurrence'] ?? [];

                foreach ($existingSchedule as $day => $time) {
                    if (!isset($newSchedule[$day])) continue; // No overlap if days are different

                    $newStart = strtotime($newSchedule[$day]['start']);
                    $newEnd = strtotime($newSchedule[$day]['end']);
                    $existingStart = strtotime($time['start']);
                    $existingEnd = strtotime($time['end']);

                    // Check for time overlap
                    if ($newStart < $existingEnd && $existingStart < $newEnd) {
                        return redirect()->back()
                            ->with('status', "Schedule conflict with existing subject '{$existingSubject['title']}' on $day ({$time['start']} - {$time['end']}).")
                            ->withInput();
                    }
                }
            }


            $postData = [
                'subject_id' => $subjectId,
                'code' => $validatedData['code'],
                'title' => $validatedData['title'],
                'subjectType' => $validatedData['subjectType'],
                'specialized_type' => $validatedData['specialized_type'] ?? '',
                'teacher_id' => $validatedData['teacher_id'],
                'section_id' => $validatedData['section_id'],
                'schoolyear_id' => $activeSchoolYearId,
                'modules' => [],
                'assignments' => '',
                'scores' => '',
                'announcements' => [],
                'attendance' => '',
                'people' => [],
                'posted_by' => 'admin',
                'date_created' => now()->toDateTimeString(),
                'date_updated' => now()->toDateTimeString(),
                'schedule' => [
                        'occurrence' => $newSchedule
                    ],
            ];

            // Modules
            if (!empty($validatedData['modules'])) {
                $modules = [];
                foreach ($validatedData['modules'] as $index => $module) {
                    $moduleKey = 'MOD' . str_pad($index, 2, '0', STR_PAD_LEFT);
                    $moduleData = [
                        'title' => $module['title'],
                        'description' => $module['description'] ?? '',
                        'files' => [],
                        'external_link' => $module['external_link'] ?? '',
                    ];

                    if ($request->hasFile("modules.$index.files")) {
                        foreach ($request->file("modules.$index.files") as $file) {
                            $uploadInfo = $this->uploadToFirebaseStorage($file, "subjects/{$subjectId}/modules");
                            $moduleData['files'][] = $uploadInfo;
                        }
                    }

                    $modules[$moduleKey] = $moduleData;
                }
                $postData['modules'] = $modules;
            }

            // Announcements
            if (!empty($validatedData['announcements'])) {
                foreach ($validatedData['announcements'] as $index => $announcement) {
                    $key = "SUB-ANN" . now()->format('Ymd') . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
                    $announcementData = [
                        'title' => $announcement['title'] ?? '',
                        'description' => $announcement['description'] ?? '',
                        'date_posted' => $announcement['date'] ?? now()->toDateString(),
                        'subject_id' => $subjectId,
                        'files' => [],
                        'link' => $announcement['link'] ?? '',
                    ];

                    if ($request->hasFile("announcements.$index.files")) {
                        foreach ($request->file("announcements.$index.files") as $file) {
                            $uploadInfo = $this->uploadToFirebaseStorage($file, "subjects/{$subjectId}/announcements");
                            $announcementData['files'][] = $uploadInfo;
                        }
                    }

                    $postData['announcements'][$key] = $announcementData;
                }
            }

            // Add section students
            $section = $this->database->getReference("sections/{$validatedData['section_id']}")->getValue();
            if (isset($section['students']) && is_array($section['students'])) {
                foreach ($section['students'] as $studentId => $value) {
                    $student = $this->database->getReference("users/{$studentId}")->getValue();
                    $postData['people'][$studentId] = [
                        'role' => 'student',
                        'first_name' => $student['fname'] ?? '',
                        'last_name' => $student['lname'] ?? '',
                    ];
                }
            }

            // Add teacher
            $teacher = $this->database->getReference("users/{$validatedData['teacher_id']}")->getValue();
            $postData['people'][$validatedData['teacher_id']] = [
                'role' => 'teacher',
                'first_name' => $teacher['fname'] ?? '',
                'last_name' => $teacher['lname'] ?? '',
            ];

            // Save to Firebase
            $this->database->getReference("subjects/{$grade}/{$subjectId}")->set($postData);

            return redirect()->route('mio.ViewSubject', ['grade' => $grade])
                            ->with('success', 'Subject added successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('status', 'Failed to add subject: ' . $e->getMessage())->withInput();
        }
    }




// DISPLAY EDIT SUBJECT
    public function showEditSubject($grade, $subject_id)
    {
        $subjectRef = $this->database->getReference("subjects/{$grade}/{$subject_id}")->getValue();

        $teachersRaw = $this->database->getReference('users')->getValue() ?? [];
        $teachers = [];

        foreach ($teachersRaw as $key => $teacher) {
            if (isset($teacher['role']) && $teacher['role'] === 'teacher') {
                $teachers[] = [
                    'teacherid' => $key,
                    'name' => trim(($teacher['fname'] ?? '') . ' ' . ($teacher['lname'] ?? '')),
                ];
            }
        }

        $sectionsRaw = $this->database->getReference('sections')->getValue() ?? [];
        $sections = [];

        $modules = $editdata['modules'] ?? [];

        foreach ($sectionsRaw as $key => $section) {
            $sections[] = [
                'sectionid' => $key,
                'name' => $section['section_name'] ?? 'Unnamed',
                'status' => $section['section_status'] ?? 'unknown',
                'teacherid' => $section['teacherid'] ?? null
            ];
        }

        if ($subjectRef) {
            return view('mio.head.admin-panel', [
                'page' => 'edit-subject',
                'grade' => $grade,
                'editdata' => $subjectRef,
                'subject_id' => $subject_id,
                'teachers' => $teachers,
                'sections' => $sections,
                'modules' => $modules,
            ]);
        } else {
            return redirect()->route('mio.ViewSubject', ['grade' => $grade])
                            ->with('status', 'Subject not found.');
        }
    }

 // EDIT SUBJECT
    public function editSubject(Request $request, $grade, $oldSubjectId)
    {
        $validatedData = $request->validate([
            'subject_id' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'teacher_id' => 'required|string|max:50',
            'section_id' => 'required|string|max:50',
            'subjectType' => 'required|in:academics,specialized',
            'modules' => 'nullable|array',
            'modules.*.title' => 'required|string|max:255',
            'modules.*.description' => 'nullable|string',
        ]);

        $newSubjectId = $validatedData['subject_id'];

        $subjects = $this->database->getReference("subjects/{$grade}")->getValue();

        // Check for duplicates if ID changed
        if ($oldSubjectId !== $newSubjectId && isset($subjects[$newSubjectId])) {
            return redirect()->back()->with('status', 'Subject ID already exists!')->withInput();
        }

        $existing = $subjects[$oldSubjectId] ?? [];

        $updateData = [
            'subject_id' => $newSubjectId,
            'code' => $validatedData['code'],
            'title' => $validatedData['title'],
            'teacher_id' => $validatedData['teacher_id'],
            'section_id' => $validatedData['section_id'],
            'subjectType' => $validatedData['subjectType'],
            'posted_by' => 'admin',
            'date_created' => $existing['date_created'] ?? Carbon::now()->toDateTimeString(),
            'date_updated' => Carbon::now()->toDateTimeString(),
        ];

        $updateData['modules'] = [];
        if (!empty($validatedData['modules'])) {
            foreach ($validatedData['modules'] as $module) {
                $updateData['modules'][] = [
                    'title' => $module['title'],
                    'description' => $module['description'] ?? '',
                ];
            }
        }

        // Update data in Firebase
        if ($oldSubjectId === $newSubjectId) {
            $this->database->getReference("subjects/{$grade}/{$oldSubjectId}")->update($updateData);
        } else {
            $this->database->getReference("subjects/{$grade}/{$newSubjectId}")->set($updateData);
            $this->database->getReference("subjects/{$grade}/{$oldSubjectId}")->remove();
        }

        return redirect()->route('mio.ViewSubject', ['grade' => $grade])
                        ->with('success', 'Subject updated successfully.');
    }



    // Delete subject

    public function deleteSubject($grade, $subjectId)
    {
        $path = "subjects/{$grade}/{$subjectId}";
        $delete = $this->database->getReference($path)->remove();

        // Delete related files in Firebase Storage
        $this->deleteStorageFolder("subjects/{$subjectId}/modules");
        $this->deleteStorageFolder("subjects/{$subjectId}/announcements");

        if ($delete) {
            return redirect()->route('mio.ViewSubject', ['grade' => $grade])
                            ->with('status', 'Subject and related files deleted successfully.');
        } else {
            return redirect()->route('mio.ViewSubject', ['grade' => $grade])
                            ->with('status', 'Failed to delete subject.');
        }
    }

    protected function deleteStorageFolder($folderPath)
    {
        $bucket = $this->storageClient->bucket($this->bucketName);
        $objects = $bucket->objects(['prefix' => $folderPath]);

        foreach ($objects as $object) {
            $object->delete();
        }
    }



}
