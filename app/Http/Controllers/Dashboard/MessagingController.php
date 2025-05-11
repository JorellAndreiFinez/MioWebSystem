<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;


class MessagingController extends Controller
{
    protected $database;

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

public function showInbox()
{
    $studentId = Session::get('firebase_user')['uid']; // Firebase UID of logged-in student

    // Log the UID value for debugging
    Log::info('Student UID:', ['uid' => $studentId]);

    $usersRef = $this->database->getReference('users');
    $studentData = $usersRef->getChild($studentId)->getValue();

    if (!$studentData || $studentData['role'] !== 'student') {
        abort(403, 'Unauthorized');
    }

    $sectionId = $studentData['section_id'];

    // Fetch teacher from the student's section
    $sectionTeacher = null;
    $sectionsRef = $this->database->getReference('sections');
    $sectionData = $sectionsRef->getChild($sectionId)->getValue();

    if ($sectionData && isset($sectionData['teacherid'])) {
        $teacherId = $sectionData['teacherid'];

        // Log the teacher ID for debugging
        Log::info('Teacher ID:', ['teacherId' => $teacherId]);

        // Fetch teacher's data
        $teacherData = $usersRef->getChild($teacherId)->getValue();


       if ($teacherData) {
            $sectionTeacher = [
                'id' => $teacherId,
                'role' => 'teacher',
                'name' => ($teacherData['fname'] ?? '') . ' ' . ($teacherData['lname'] ?? ''),
                'section_name' => $sectionData['section_name']
            ];
        }
    }

    // Fetch subjects and get related teachers
    $subjectsRef = $this->database->getReference('subjects');
    $subjectsSnapshot = $subjectsRef->getValue();
    $subjectTeachers = [];

    if ($subjectsSnapshot) {
        foreach ($subjectsSnapshot as $grade => $subjectList) {
            foreach ($subjectList as $subjectId => $subject) {
                if (isset($subject['section_id']) && $subject['section_id'] === $sectionId) {
                    if (isset($subject['teacher_id'])) {
                        $teacherId = $subject['teacher_id'];
                        $teacherData = $usersRef->getChild($teacherId)->getValue();

                        if ($teacherData) {
                            $subjectTeachers[$subjectId] = [
                                'subject_name' => $subject['title'],
                                'people' => [
                                    [
                                        'id' => $teacherId,
                                        'role' => 'Teacher',
                                        'name' => ($teacherData['fname'] ?? '') . ' ' . ($teacherData['lname'] ?? '')
                                    ]
                                ]
                            ];
                        }
                    }
                }

            }
        }
    }

    // Format data for Blade view
    $sections = [];
    if ($sectionTeacher) {
        $sections[] = [
            'section_name' => $sectionTeacher['section_name'],
            'people' => [
                [
                    'id' => $sectionTeacher['id'],
                    'role' => $sectionTeacher['role'],
                    'name' => $sectionTeacher['name']
                ]
            ]
        ];
    }

    return view('mio.head.student-panel', [
        'page' => 'inbox',
        'sections' => $sections,
        'subjects' => array_values($subjectTeachers),
    ]);
}

    public function sendMessage(Request $request)
{
    $senderId = Session::get('firebase_user')['uid'];
    $receiverId = $request->input('receiver_id');
    $subject = $request->input('subject');
    $message = $request->input('message');

    if (empty($receiverId) || empty($message)) {
        return response()->json(['success' => false, 'message' => 'Receiver and message are required.']);
    }

    // Save attachments logic (optional, based on your actual implementation)
    $attachments = [];

    // Prepare the message
    $messageData = [
        'sender_id' => $senderId,
        'receiver_id' => $receiverId,
        'subject' => $subject,
        'message' => $message,
        'attachments' => $attachments,
        'timestamp' => now()->timestamp,
    ];

    $messageRef = $this->database->getReference('messages/' . $senderId . '_' . $receiverId);
    $messageRef->push($messageData);

    // ↓↓↓ RE-FETCH SECTIONS & SUBJECTS ↓↓↓
    $usersRef = $this->database->getReference('users');
    $studentData = $usersRef->getChild($senderId)->getValue();
    $sectionId = $studentData['section_id'];
    $sections = [];
    $subjectTeachers = [];

    $sectionsRef = $this->database->getReference('sections');
    $sectionData = $sectionsRef->getChild($sectionId)->getValue();
    if ($sectionData && isset($sectionData['teacherid'])) {
        $teacherId = $sectionData['teacherid'];
        $teacherData = $usersRef->getChild($teacherId)->getValue();
        if ($teacherData) {
            $sections[] = [
                'section_name' => $sectionData['section_name'],
                'people' => [
                    [
                        'id' => $teacherId,
                        'role' => 'teacher',
                        'name' => ($teacherData['fname'] ?? '') . ' ' . ($teacherData['lname'] ?? '')
                    ]
                ]
            ];
        }
    }

    $subjectsRef = $this->database->getReference('subjects');
    $subjectsSnapshot = $subjectsRef->getValue();
    if ($subjectsSnapshot) {
        foreach ($subjectsSnapshot as $grade => $subjectList) {
            foreach ($subjectList as $subjectId => $subject) {
                if (isset($subject['section_id']) && $subject['section_id'] === $sectionId) {
                    if (isset($subject['teacher_id'])) {
                        $teacherId = $subject['teacher_id'];
                        $teacherData = $usersRef->getChild($teacherId)->getValue();

                        if ($teacherData) {
                            $subjectTeachers[$subjectId] = [
                                'subject_name' => $subject['title'],
                                'people' => [
                                    [
                                        'id' => $teacherId,
                                        'role' => 'Teacher',
                                        'name' => ($teacherData['fname'] ?? '') . ' ' . ($teacherData['lname'] ?? '')
                                    ]
                                ]
                            ];
                        }
                    }
                }
            }
        }
    }

    return view('mio.head.student-panel', [
        'page' => 'inbox',
        'sections' => $sections,
        'subjects' => array_values($subjectTeachers),
    ]);
}





}
