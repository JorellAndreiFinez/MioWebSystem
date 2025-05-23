<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Kreait\Firebase\Database;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


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
    $studentId = Session::get('firebase_user')['uid'];

    $usersRef = $this->database->getReference('users');
    $studentData = $usersRef->getChild($studentId)->getValue();

    if (!$studentData || $studentData['role'] !== 'student') {
        abort(403, 'Unauthorized');
    }

    $sectionId = $studentData['section_id'] ?? null;

    $messagesRef = $this->database->getReference('messages');
    $allThreads = $messagesRef->getValue();

    $contacts = [];

    // --- Contacts from message threads ---
    if ($allThreads) {
        foreach ($allThreads as $threadKey => $threadMessages) {
            if (strpos($threadKey, $studentId) !== false) {
                $userIds = explode('_', $threadKey);
                $otherUserId = ($userIds[0] === $studentId) ? $userIds[1] : $userIds[0];

                if (!isset($contacts[$otherUserId])) {
                    $user = $usersRef->getChild($otherUserId)->getValue();

                    if ($user) {
                        $contacts[$otherUserId] = [
                            'id' => $otherUserId,
                            'name' => ($user['fname'] ?? '') . ' ' . ($user['lname'] ?? ''),
                            'role' => $user['role'] ?? '',
                            'profile_pic' => $user['profile_pic'] ?? null
                        ];
                    }
                }
            }
        }
    }

    $subjectsRef = $this->database->getReference('subjects');
    $subjectsSnapshot = $subjectsRef->getValue();

    $filteredSubjects = [];

    // --- Subjects and teachers for the student's section ---
    if ($subjectsSnapshot && $sectionId) {
        foreach ($subjectsSnapshot as $grade => $subjectList) {
            foreach ($subjectList as $subjectId => $subject) {
                if (isset($subject['section_id']) && $subject['section_id'] === $sectionId) {
                    $teacherId = $subject['teacher_id'] ?? null;
                    if ($teacherId) {
                        $teacherData = $usersRef->getChild($teacherId)->getValue();

                        if ($teacherData) {
                            $filteredSubjects[] = [
                                'subject_id' => $subjectId,
                                'subject_name' => $subject['title'] ?? $subjectId,
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
        'subjects' => $filteredSubjects,
        'contacts' => array_values($contacts)
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

    public function getMessages($studentId, $teacherId)
    {
        $db = $this->database;
        $messagesRef = $db->getReference('messages');

        // Check both key combinations (student-teacher thread)
        $threadKey1 = $studentId . '_' . $teacherId;
        $threadKey2 = $teacherId . '_' . $studentId;

        // Get messages from either of the thread keys
        $messages = $messagesRef->getChild($threadKey1)->getValue();
        if (!$messages) {
            $messages = $messagesRef->getChild($threadKey2)->getValue();
        }

        $formatted = [];
        $usersRef = $db->getReference('users');

        // Get teacher data (as the receiver)
        $receiverData = $usersRef->getChild($teacherId)->getValue();

        $contacts = [];

        if ($receiverData) {
            $contacts[] = [
                'id' => $teacherId,
                'name' => ($receiverData['fname'] ?? '') . ' ' . ($receiverData['lname'] ?? ''),
                'role' => $receiverData['role'],
                'profile_pic' => $receiverData['profile_pic'] ?? null
            ];
        }

        // Format messages
        if ($messages) {
            foreach ($messages as $msgId => $msg) {
                $senderData = $usersRef->getChild($msg['sender_id'])->getValue();
                $formatted[] = [
                    'message' => $msg['message'],
                    'sender_id' => $msg['sender_id'],
                    'receiver_id' => $msg['receiver_id'],
                    'timestamp' => $msg['timestamp'],
                    'name' => ($senderData['fname'] ?? '') . ' ' . ($senderData['lname'] ?? '')
                ];
            }
        }

        Log::info($formatted);

        return response()->json([
            'messages' => $formatted,
            'contacts' => $contacts
        ]);
    }



    public function showTeacherInbox()
    {
        $teacherId = Session::get('firebase_user')['uid'];

        $usersRef = $this->database->getReference('users');
        $teacherData = $usersRef->getChild($teacherId)->getValue();

        if (!$teacherData || $teacherData['role'] !== 'teacher') {
            abort(403, 'Unauthorized');
        }

        $messagesRef = $this->database->getReference('messages');
        $allThreads = $messagesRef->getValue();

        $contacts = [];

        // --- Existing messages-based contacts
        if ($allThreads) {
            foreach ($allThreads as $threadKey => $threadMessages) {
                if (strpos($threadKey, $teacherId) !== false) {
                    $userIds = explode('_', $threadKey);
                    $otherUserId = ($userIds[0] === $teacherId) ? $userIds[1] : $userIds[0];

                    if (!isset($contacts[$otherUserId])) {
                        $user = $usersRef->getChild($otherUserId)->getValue();

                        if ($user) {
                            $contacts[$otherUserId] = [
                                'id' => $otherUserId,
                                'name' => ($user['fname'] ?? '') . ' ' . ($user['lname'] ?? ''),
                                'role' => $user['role'] ?? '',
                                'profile_pic' => $user['profile_pic'] ?? null
                            ];
                        }
                    }
                }
            }
        }

        $subjectsRef = $this->database->getReference('subjects');
        $subjectsSnapshot = $subjectsRef->getValue();

        $sectionsRef = $this->database->getReference('sections');
        $sectionsData = $sectionsRef->getValue();

        $filteredSubjects = [];
        $filteredSections = [];
        $studentsCache = [];

        // --- Subjects the teacher handles
            if ($subjectsSnapshot) {
                foreach ($subjectsSnapshot as $grade => $subjectList) {
                    foreach ($subjectList as $subjectId => $subject) {

                        // Check if the subject is assigned to the teacher
                        if (isset($subject['teacher_id']) && $subject['teacher_id'] === $teacherId) {
                            $subjectName = $subject['title'] ?? $subjectId;
                            $people = [];

                            // Loop through people in the subject to get student information
                           if (isset($subject['people']) && is_array($subject['people'])) {
                            foreach ($subject['people'] as $studentId => $studentInfo) {
                                $studentData = $usersRef->getChild($studentId)->getValue();

                                if ($studentData) {
                                    $people[] = [
                                        'id' => $studentId,
                                        'name' => ($studentData['fname'] ?? $studentInfo['first_name'] ?? '') . ' ' . ($studentData['lname'] ?? $studentInfo['last_name'] ?? '')
                                    ];
                                }
                            }
                        }

                            // Add the subject to the filtered list
                            $filteredSubjects[] = [
                                'subject_id' => $subjectId,
                                'subject_name' => $subjectName,
                                'people' => $people
                            ];
                        }
                    }
                }
            }

            Log::info($filteredSubjects);

        return view('mio.head.teacher-panel', [
            'page' => 'teacher-inbox',
            'subjects' => $filteredSubjects,
            'contacts' => array_values($contacts)
        ]);
    }

    public function getTeacherMessages($userId1, $userId2)
    {
        $db = $this->database;
        $messagesRef = $db->getReference('messages');

        // Check both key combinations
        $threadKey1 = $userId1 . '_' . $userId2;
        $threadKey2 = $userId2 . '_' . $userId1;

        $messages = $messagesRef->getChild($threadKey1)->getValue();

        if (!$messages) {
            $messages = $messagesRef->getChild($threadKey2)->getValue();
        }

        $formatted = [];
        $usersRef = $db->getReference('users');
        $receiverData = $usersRef->getChild($userId2)->getValue();

        $contacts = [];

        if ($receiverData) {
            $contacts[] = [
                'id' => $userId2,
                'name' => ($receiverData['fname'] ?? '') . ' ' . ($receiverData['lname'] ?? ''),
                'role' => $receiverData['role'],
                'profile_pic' => $receiverData['profile_pic'] ?? null
            ];
        }

        if ($messages) {
            foreach ($messages as $msgId => $msg) {
                $senderData = $usersRef->getChild($msg['sender_id'])->getValue();
                $formatted[] = [
                    'message' => $msg['message'],
                    'sender_id' => $msg['sender_id'],
                    'receiver_id' => $msg['receiver_id'],
                    'timestamp' => $msg['timestamp'],
                    'name' => ($senderData['fname'] ?? '') . ' ' . ($senderData['lname'] ?? '')
                ];
            }
        }

        return response()->json([
            'messages' => $formatted,
            'contacts' => $contacts
        ]);
    }




    public function sendTeacherMessage(Request $request)
    {
        $senderId = Session::get('firebase_user')['uid'];
        $receiverId = $request->input('receiver_id');
        $subject = $request->input('subject') ?? '';
        $message = $request->input('message');

        if (empty($receiverId) || empty($message)) {
            return response()->json(['success' => false, 'message' => 'Receiver and message are required.']);
        }

        // Build consistent conversation ID
        $conversationId = $senderId < $receiverId ? "{$senderId}_{$receiverId}" : "{$receiverId}_{$senderId}";

        // Save attachments if available
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('messages/attachments', $filename, 'public');
                $attachments[] = Storage::url($path);
            }
        }

        // Prepare message payload
        $messageData = [
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'subject' => $subject,
            'message' => $message,
            'attachments' => $attachments,
            'timestamp' => now()->timestamp,
        ];

        // Store in Firebase
        $this->database->getReference("messages/{$conversationId}")->push($messageData);

        // return response()->json(['success' => true, 'message' => 'Message sent successfully.']);

       return redirect()->route('mio.teacher-inbox', [
        ])->with('success', 'Message sent!');

    }



}
