<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;
use Carbon\Carbon;
use Kreait\Firebase\Exception\DatabaseException;

class AnnouncementController extends Controller
{
    protected $database;
    protected $table = 'admin-announcements';

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
        // Validate input
        $validated = $request->validate([
            'announce_title' => 'required|string|max:255',
            'announce_people' => 'required|string|in:all,students,teachers,parents,admin',
            'announce_date' => 'required|date|after_or_equal:' . \Carbon\Carbon::today()->toDateString(),
            'announce_description' => 'required|string|max:1000',
        ]);

        // Get the current announcement from Firebase
        $announcement = $this->database->getReference('admin-announcements/' . $id)->getValue();

        // If announcement doesn't exist, redirect with error message
        if (!$announcement) {
            return redirect()->route('mio.school')->with('status', 'Announcement ID Not Found');
        }

        // Prepare the updated data
        $updatedData = [
            'title' => $validated['announce_title'],
            'people' => $validated['announce_people'],
            'date' => $validated['announce_date'],
            'description' => $validated['announce_description'],
            'updated_at' => now()->toDateTimeString(),
        ];

        // Update the announcement in Firebase
        $this->database->getReference('admin-announcements/' . $id)->update($updatedData);

        // Redirect back to the announcement list with success message
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
}
