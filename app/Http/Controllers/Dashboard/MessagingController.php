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

    private function checkIfUserHasUnreadMessages(string $userId): bool
    {
        $messagesRef = $this->database->getReference('messages');
        $messages = $messagesRef->getValue() ?? [];

        foreach ($messages as $threadKey => $thread) {
            foreach ($thread as $messageId => $message) {
                if (
                    isset($message['receiver_id'], $message['read']) &&
                    $message['receiver_id'] === $userId &&
                    $message['read'] === false

                ) {
                    return true;
                }
            }
        }

        return false;
    }

public function showInbox()
{
    $studentId = Session::get('firebase_user')['uid'];
       $hasUnreadMessages = $this->checkIfUserHasUnreadMessages($studentId);

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
                        $hasUnread = false;
                            foreach ($threadMessages as $msg) {
                                if (
                                    isset($msg['receiver_id'], $msg['read']) &&
                                    $msg['receiver_id'] === $studentId &&
                                    $msg['read'] === false
                                ) {
                                    $hasUnread = true;
                                    break;
                                }
                            }

                            $contacts[$otherUserId] = [
                                'id' => $otherUserId,
                                'name' => ($user['fname'] ?? '') . ' ' . ($user['lname'] ?? ''),
                                'role' => $user['role'] ?? '',
                                'profile_pic' => $user['photo_url'] ?? null,
                                'has_unread' => $hasUnread
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
                                         'profile_pic' => $teacherData['photo_url'] ?? null,
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
        'contacts' => array_values($contacts),
        'hasUnreadMessages' => $hasUnreadMessages,
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

        // Build consistent conversation ID (sorted alphabetically)
        $conversationId = $senderId < $receiverId ? "{$senderId}_{$receiverId}" : "{$receiverId}_{$senderId}";

        // Handle attachments if needed
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('messages/attachments', $filename, 'public');
                $attachments[] = Storage::url($path);
            }
        }

        // Build message data
        $messageData = [
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'subject' => $subject,
            'message' => $message,
            'attachments' => $attachments,
            'timestamp' => now()->timestamp,
            'read' => false,
        ];

        // Push to Firebase
        $this->database->getReference("messages/{$conversationId}")->push($messageData);

        // Redirect or refresh view
        return redirect()->route('mio.inbox')->with('success', 'Message sent!');
    }


    public function getMessages($studentId, $teacherId)
    {
        $db = $this->database;
        $messagesRef = $db->getReference('messages');

        // Check both key combinations (student-teacher thread)
        $threadKey1 = $studentId . '_' . $teacherId;
        $threadKey2 = $teacherId . '_' . $studentId;

        $threadRef = $messagesRef->getChild($threadKey1);
        $messages = $threadRef->getValue();
        if (!$messages) {
            $threadRef = $messagesRef->getChild($threadKey2);
            $messages = $threadRef->getValue();
        }

        $formatted = [];
        $usersRef = $db->getReference('users');
        $receiverData = $usersRef->getChild($teacherId)->getValue();
        $contacts = [];

        $hasUnread = false;
        foreach ($messages as $msgId => $msg) {
            if ($msg['receiver_id'] == $studentId && !($msg['read'] ?? false)) {
                $hasUnread = true;
            }
        }


        if ($receiverData) {
            $contacts[] = [
                'id' => $teacherId,
                'name' => ($receiverData['fname'] ?? '') . ' ' . ($receiverData['lname'] ?? ''),
                'role' => $receiverData['role'],
                'profile_pic' => $receiverData['photo_url'] ?? null,
                'has_unread' => $hasUnread
            ];
        }

        // Mark unread messages as read
        foreach ($messages as $msgId => $msg) {
            if ($msg['receiver_id'] == $studentId && !isset($msg['read'])) {
                $threadRef->getChild($msgId)->update(['read' => true]);
            }

            $senderData = $usersRef->getChild($msg['sender_id'])->getValue();
            $formatted[] = [
                'message' => $msg['message'],
                'sender_id' => $msg['sender_id'],
                'receiver_id' => $msg['receiver_id'],
                'timestamp' => $msg['timestamp'],
                'name' => ($senderData['fname'] ?? '') . ' ' . ($senderData['lname'] ?? '')
            ];
        }

        

        return response()->json([
            'messages' => $formatted,
            'contacts' => $contacts
        ]);
    }



    public function showTeacherInbox()
    {
        $teacherId = Session::get('firebase_user')['uid'];
         $hasUnreadMessages = $this->checkIfUserHasUnreadMessages($teacherId);

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
                            // Check for unread messages
                            $hasUnread = false;
                            foreach ($threadMessages as $message) {
                                if (
                                    isset($message['receiver_id'], $message['read']) &&
                                    $message['receiver_id'] === $teacherId &&
                                    $message['read'] === false
                                ) {
                                    $hasUnread = true;
                                    break;
                                }
                            }

                            $contacts[$otherUserId] = [
                                'id' => $otherUserId,
                                'name' => ($user['fname'] ?? '') . ' ' . ($user['lname'] ?? ''),
                                'role' => $user['role'] ?? '',
                                'profile_pic' => $user['photo_url'] ?? null,
                                'has_unread' => $hasUnread
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
                                         'profile_pic' => $teacherData['photo_url'] ?? null,
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


        return view('mio.head.teacher-panel', [
            'page' => 'teacher-inbox',
            'subjects' => $filteredSubjects,
            'contacts' => array_values($contacts),
        'hasUnreadMessages' => $hasUnreadMessages,


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
