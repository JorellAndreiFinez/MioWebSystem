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

class AnnouncementController extends Controller
{
    protected $database;
    protected $table = 'admin-announcements';
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

    public function school()
    {
        $rawData = $this->database->getReference($this->table)->getValue() ?? [];

        $announcements = [];

        foreach ($rawData as $key => $announcement) {
            // Ensure required fields exist
            if (isset($announcement['title'], $announcement['description'], $announcement['date'])) {
                $announcements[] = [
                    'id' => $key,
                    'title' => $announcement['title'],
                    'description' => $announcement['description'],
                    'date' => Carbon::parse($announcement['date'])->format('M d, Y'),
                ];
            }
        }

        return view('mio.head.admin-panel', [
            'page' => 'school',
            'announcements' => $announcements
        ]);
    }

    public function showAddAnnouncement()
    {
        // // Get teachers from Firebase
        // $teachersRaw = $this->database->getReference('users')->getValue() ?? [];

        // $teachers = [];
        // foreach ($teachersRaw as $key => $teacher) {
        //     if (isset($teacher['role']) && $teacher['role'] === 'teacher') {
        //         $teachers[] = [
        //             'teacherid' => $key,
        //             'name' => ($teacher['fname'] ?? '') . ' ' . ($teacher['lname'] ?? '')
        //         ];
        //     }
        // }

        return view('mio.head.admin-panel', [
            'page' => 'add-announcement',
        ]);
    }

    public function addAnnouncement(Request $request)
    {
        // Validate the announcement input
        $validated = $request->validate([
            'announce_title' => 'required|string|max:255',
            'announce_people' => 'required|string|in:all,students,teachers,parents,admin',
            'announce_date' => 'required|date|after_or_equal:today',
            'announce_description' => 'required|string|max:1000',
        ]);

        // Generate a unique key (or you can use a title-based slug or timestamp)
        $announcementId = uniqid('ADMIN');

        // Prepare the data
        $postData = [
            'id' => $announcementId,
            'title' => $validated['announce_title'],
            'people' => $validated['announce_people'],
            'date' => $validated['announce_date'],
            'description' => $validated['announce_description'],
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ];

        // Store in Firebase under `announcements/{id}`
        $this->database->getReference('admin-announcements/' . $announcementId)->set($postData);

        return redirect()->route('mio.school')->with('success', 'Announcement added successfully!');
    }

    public function viewAnnouncement($id)
    {
        // Get the announcement by ID
        $announcement = $this->database->getReference($this->table . '/' . $id)->getValue();

        if ($announcement) {
            return view('mio.head.admin-panel', [
                'page' => 'view-announcement',
                'announcement' => $announcement,
            ]);
        } else {
            return redirect()->route('mio.school')->with('status', 'Announcement not found.');
        }
    }


    // DISPLAY EDIT ANNOUNCEMENT
    public function showEditAnnouncement($id)
    {
        // Get the announcement by ID from the correct reference path
        $announcement = $this->database->getReference($this->table . '/' . $id)->getValue();

        // If the announcement exists, pass it to the view
        if ($announcement) {
            // Store the firebase key for later reference
            $announcement['firebase_key'] = $id;

            return view('mio.head.admin-panel', [
                'page' => 'edit-announcement',
                'editdata' => $announcement,
            ]);
        } else {
            // If announcement is not found, redirect with error message
            return redirect('mio/admin/school')->with('status', 'Announcement ID Not Found');
        }
    }


    public function editAnnouncement(Request $request, $id)
    {
        $validated = $request->validate([
            'announce_title' => 'required|string|max:255',
            'announce_people' => 'required|string|in:all,students,teachers,parents,admin',
            'announce_date' => 'required|date|after_or_equal:' . \Carbon\Carbon::today()->toDateString(),
            'announce_description' => 'required|string|max:1000',
            'retained_existing_files' => 'nullable|string', // JSON array of file info
        ]);

        $announcement = $this->database->getReference('admin-announcements/' . $id)->getValue();
        if (!$announcement) {
            return redirect()->route('mio.school')->with('status', 'Announcement ID Not Found');
        }

        $originalFiles = $announcement['files'] ?? [];

        // Get retained files from form (existing files that were not deleted)
        $retainedFiles = json_decode($request->input('retained_existing_files', '[]'), true);
        if (!is_array($retainedFiles)) {
            $retainedFiles = [];
        }

        $retainedUrls = array_column($retainedFiles, 'url');

        // Find files that were removed
        $deletedFiles = array_filter($originalFiles, fn($file) => !in_array($file['url'], $retainedUrls));

        // Delete removed files from Firebase
        $bucket = app('firebase.storage')->getBucket();
        foreach ($deletedFiles as $file) {
            $parsedUrl = parse_url($file['url']);
            $path = urldecode(trim($parsedUrl['path'], '/'));
            $path = preg_replace('#^v0/b/.+?/o/#', '', $path);
            $path = str_replace('%2F', '/', $path);
            try {
                $object = $bucket->object($path);
                if ($object->exists()) {
                    $object->delete();
                }
            } catch (\Exception $e) {
                \Log::error("Failed to delete Firebase file: {$path}", ['error' => $e->getMessage()]);
            }
        }

        // Upload new files
        $newFiles = [];
        $files = $request->file('announcements.0.files', []);
        foreach ($files as $file) {
            $path = 'admin-announcements/' . $id . '/files/' . uniqid() . '.' . $file->getClientOriginalExtension();
            $uploadedFile = $bucket->upload(
                file_get_contents($file->getRealPath()),
                ['name' => $path]
            );
            $newFiles[] = [
                'name' => $file->getClientOriginalName(),
                'url' => $uploadedFile->signedUrl(new \DateTime('+1 year')),
            ];
        }

        // Combine old retained + new
        $finalFiles = array_merge($retainedFiles, $newFiles);

        $updatedData = [
            'title' => $validated['announce_title'],
            'people' => $validated['announce_people'],
            'date' => $validated['announce_date'],
            'description' => $validated['announce_description'],
            'updated_at' => now()->toDateTimeString(),
            'files' => $finalFiles,
        ];

        $this->database->getReference('admin-announcements/' . $id)->update($updatedData);

        return redirect()->route('mio.school')->with('status', 'Announcement updated successfully!');
    }







// DELETE SECTION
    public function deleteAnnouncement($id)
    {
        $key = $id;
        $del_data = $this->database->getReference($this->table.'/'.$key)->remove();

        if ($del_data) {
            return redirect('mio/admin/school')->with('status', 'Announcement Deleted Successfully');
        } else {
            return redirect('mio/admin/school')->with('status', 'Announcement Not Deleted');
        }
   }


   //    FOR TEACHER

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

    public function storeTeacherAnnouncement(Request $request, $subjectId)
    {
        $validatedData = $request->validate([
            'announcements' => 'nullable|array',
            'announcements.*.title' => 'nullable|string|max:255',
            'announcements.*.description' => 'nullable|string|max:1000',
            'announcements.*.date' => 'nullable|date',
            'announcements.*.files.*' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,mp4,zip,jpg,jpeg,png,gif,bmp,webp,svg,heic,heif|max:20480',
            'announcements.*.link' => 'nullable|url',
        ]);

        $announcements = $validatedData['announcements'] ?? [];

        if (empty($announcements)) {
            return redirect()->back()->with('error', 'No announcements provided.');
        }

        // 🔍 Find grade level for the subject
        $subjects = $this->database->getReference('subjects')->getValue() ?? [];
        $gradeLevelKey = null;

        foreach ($subjects as $gradeLevel => $items) {
            foreach ($items as $key => $item) {
                if ($item['subject_id'] === $subjectId) {
                    $gradeLevelKey = $gradeLevel;
                    break 2;
                }
            }
        }

        if (!$gradeLevelKey) {
            return redirect()->back()->with('error', 'Unable to determine grade level for subject.');
        }

        foreach ($announcements as $index => $announcement) {
            // Skip incomplete entries
            if (empty($announcement['title']) || empty($announcement['date']) || empty($announcement['description'])) {
                continue;
            }

            // 🔑 Generate custom announcement ID
            $announcementId = "SUB-ANN" . now()->format('Ymd') . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);

            // 📁 Handle file uploads
            $uploadedFiles = [];

            if ($request->hasFile("announcements.$index.files")) {
                foreach ($request->file("announcements.$index.files") as $file) {
                    $uploadInfo = $this->uploadToFirebaseStorage($file, "subjects/{$subjectId}/announcements/{$announcementId}");
                    $uploadedFiles[] = $uploadInfo;
                }
            }

            // 📝 Prepare announcement data
            $data = [
                'title' => $announcement['title'],
                'date_posted' => $announcement['date'],
                'description' => $announcement['description'],
                'link' => $announcement['link'] ?? '',
                'subject_id' => $subjectId,
                'files' => $uploadedFiles,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];

            // ✅ Save under the correct path
            $this->database
                ->getReference("subjects/{$gradeLevelKey}/{$subjectId}/announcements/{$announcementId}")
                ->set($data);
        }

        return redirect()->route('mio.subject-teacher.announcement', ['subjectId' => $subjectId])
            ->with('success', 'Announcements posted successfully!');
    }



    public function deleteTeacherAnnouncement(Request $request, $subjectId, $announcementId)
    {
        // 🔍 Find the correct grade level key
        $subjects = $this->database->getReference('subjects')->getValue() ?? [];
        $gradeLevelKey = null;

        foreach ($subjects as $gradeLevel => $items) {
            foreach ($items as $key => $item) {
                if ($item['subject_id'] === $subjectId) {
                    $gradeLevelKey = $gradeLevel;
                    break 2;
                }
            }
        }

        if (!$gradeLevelKey) {
            return redirect()->back()->with('error', 'Unable to determine grade level for subject.');
        }

        // 🔍 Path to announcement
        $announcementPath = "subjects/{$gradeLevelKey}/{$subjectId}/announcements/{$announcementId}";

        $announcement = $this->database->getReference($announcementPath)->getValue();

        if (!$announcement) {
            return redirect()->back()->with('error', 'Announcement not found.');
        }

        // 🔥 Delete files from Firebase Storage
        if (!empty($announcement['files']) && is_array($announcement['files'])) {
            $bucket = $this->storageClient->bucket($this->bucketName);

            foreach ($announcement['files'] as $file) {
                if (is_array($file) && isset($file['path'])) {
                    try {
                        $object = $bucket->object($file['path']);
                        $object->delete();
                    } catch (FirebaseException $e) {
                        // Optional: Log the error, but continue
                    }
                }
            }
        }

        // ❌ Delete the announcement entry from the database
        $this->database->getReference($announcementPath)->remove();

        return redirect()->route('mio.subject-teacher.announcement', ['subjectId' => $subjectId])
            ->with('success', 'Announcement deleted successfully!');
    }






}
