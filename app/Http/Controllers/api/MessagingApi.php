<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Support\Str;

class MessagingApi extends Controller
{
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

        $this->storage = (new Factory())
            ->withServiceAccount($path)
            ->withDefaultStorageBucket('miolms.firebasestorage.app')
            ->createStorage();

        $this->messaging = (new Factory())
            ->withServiceAccount($path)
            ->createMessaging();
    }

    public function sendMessage(Request $request, string $receiver_id){
        $sender_id = $request->get('firebase_user_id');

        $validated = $request->validate([
            'body' => 'required|string|min:1|max:250',
        ]);

        try{
            $user = $this->database->getReference("users/{$receiver_id}")->getSnapshot()->getValue() ?? [];

            if(empty($user)){
                return response()->json([
                    'success' => false,
                    'message' => "user not found"
                ]);
            }

            $message_info = $sender_id . "_" . $receiver_id;
            $message_id = (String) Str::uuid();
            $this->database->getReference("messages/{$message_info}/{$message_id}")->set([
                'message' => $validated['body'],
                'receiver_id' => $receiver_id,
                'sender_id' => $sender_id,
                'timestamp' => now()->timestamp
            ]);

            $name = $user['fname'] . " " . $user['lname'];
            if(!empty($user['fcm_token'])){
                $message = CloudMessage::withTarget('token', $user['fcm_token'])
                    ->withNotification(['title' => $name, 'body' => $validated['body']])
                    ->withData([
                        'type' => 'message',
                        'screen' => 'EmergencyScreen',
                        'thread_id' => $message_info,
                    ]);
                    
                $this->messaging->send($message);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'thread' => $message_info,
                'name' => $name
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function replyMessage(Request $request, string $receiver_id){
        $sender_id = $request->get('firebase_user_id');

        $validated = $request->validate([
            'body' => 'required|string|min:1|max:250',
        ]);

        try{
            $user = $this->database->getReference("users/{$receiver_id}")->getSnapshot()->getValue() ?? [];

            if(empty($user)){
                return response()->json([
                    'success' => false,
                    'message' => "user not found"
                ]);
            }

            $message_info = $receiver_id . "_" . $sender_id;
            $message_id = (String) Str::uuid();
            $this->database->getReference("messages/{$message_info}/{$message_id}")->set([
                'message' => $validated['body'],
                'receiver_id' => $receiver_id,
                'sender_id' => $sender_id,
                'timestamp' => now()->timestamp
            ]);

            if(!empty($user['fcm_token'])){
                $name = $user['fname'] . " " . $user['lname'];

                $message = CloudMessage::withTarget('token', $user['fcm_token'])
                    ->withNotification(['title' => $name, 'body' => $validated['body']])
                    ->withData([
                        'type' => 'message',
                        'screen' => 'EmergencyScreen',
                        'thread_id' => $message_info,
                    ]);
                    
                $this->messaging->send($message);
            }

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getInboxMessages(Request $request)
    {
        $userId = $request->get('firebase_user_id');

        try {
            $allMessageThreads = $this->database->getReference("messages")->getSnapshot()->getValue() ?? [];

            $filteredMessages = [];
            foreach ($allMessageThreads as $threadId => $messages) {
                if (strpos($threadId, '_') === false) continue;
                list($senderId, $receiverId) = explode('_', $threadId);

                if ($receiverId === $userId) {
                    $lastMessage = 1;

                    foreach ($messages as $messageId => $message) {
                        if (!is_array($message)) continue;

                        if (
                            !$lastMessage ||
                            ($message['timestamp'] ?? 0) > ($lastMessage['timestamp'] ?? 0)
                        ) {
                            $lastMessage = $message;
                        }
                    }

                    $users = $this->database->getReference("users/{$senderId}")->getSnapshot()->getValue();
                    $name = $users['fname'] . " " . $users['lname'];

                    if ($lastMessage) {
                        $filteredMessages[] = [
                            'thread' => $threadId,
                            'name' => $name,
                            'last_message' => $lastMessage['message'] ?? null,
                            'timestamp' => $lastMessage['timestamp'] ?? null,
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'messages' => $filteredMessages,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getConversation(Request $request, string $thread_id)
    {
        $userId = $request->get('firebase_user_id');
        try {
            $conversation = $this->database
                ->getReference("messages/{$thread_id}")
                ->getSnapshot()
                ->getValue() ?? [];

            if (empty($conversation)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conversation not found',
                ]);
            }

            list($senderId, $receiverId) = explode('_', $thread_id);

            $users = $this->database
                ->getReference("users/{$senderId}")
                ->getSnapshot()
                ->getValue();

            $name = isset($users['fname'], $users['lname']) 
                ? $users['fname'] . ' ' . $users['lname'] 
                : 'Unknown Sender';

            uasort($conversation, function ($a, $b) {
                return ($a['timestamp'] ?? 0) <=> ($b['timestamp'] ?? 0);
            });

            return response()->json([
                'success' => true,
                'conversation' => array_values($conversation),
                'name' => $name,
                'sender' => $userId,
                'receiver' => $senderId
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSentMessages(Request $request)
    {
        $userId = $request->get('firebase_user_id');

        try {
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing firebase_user_id',
                ], 400);
            }

            $allMessageThreads = $this->database->getReference("messages")->getSnapshot()->getValue() ?? [];
            $filteredMessages = [];

            foreach ($allMessageThreads as $threadId => $messages) {
                if (strpos($threadId, '_') === false) continue;
                list($senderId, $receiverId) = explode('_', $threadId);

                if ($senderId === $userId) {
                    $lastMessage = 1;

                    foreach ($messages as $messageId => $message) {
                        if (!is_array($message)) continue;

                        if (
                            !$lastMessage ||
                            ($message['timestamp'] ?? 0) > ($lastMessage['timestamp'] ?? 0)
                        ) {
                            $lastMessage = $message;
                        }
                    }

                    $users = $this->database->getReference("users/{$senderId}")->getSnapshot()->getValue();
                    $name = $users['fname'] . " " . $users['lname'];

                    if ($lastMessage) {
                        $filteredMessages[] = [
                            'thread' => $threadId,
                            'name' => $name,
                            'last_message' => $lastMessage['message'] ?? null,
                            'timestamp' => $lastMessage['timestamp'] ?? null,
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'messages' => $filteredMessages,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSubjectTeacher(Request $request)
    {
        $userId = $request->get('firebase_user_id');
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try {
            $subjects = $this->database
                ->getReference("subjects/GR{$gradeLevel}/")
                ->getSnapshot()
                ->getValue() ?? [];

            $subject_teachers = [];

            foreach ($subjects as $subject_id => $subject) {
                if($subject['people'][$userId]){
                    foreach($subject['people'] as $people_id => $people){
                        if (
                            isset($people['role']) &&
                            $people['role'] === "teacher" &&
                            isset($people['first_name']) &&
                            isset($people['last_name'])
                        ) {
                            $name = $people['first_name'] . " " . $people['last_name'] . " ( " . $subject['title'] . " )";

                            $subject_teachers[] = [
                                'user_id' => $people_id,
                                'subject_id' => $subject_id,
                                'name' => $name,
                            ];
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'users' => $subject_teachers,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSubjectStudents(Request $request, string $subjectId)
    {
        $userId = $request->get('firebase_user_id');
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try {
            $peoples = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/people")
                ->getSnapshot()
                ->getValue() ?? [];

            $students = [];
            foreach($peoples as $user_id => $people){
                if($people['role'] !== "teacher"){
                    $name = $people['first_name'] . " " . $people['last_name'];
                    $students[] = [
                        'name' => $name,
                        'user_id' => $user_id
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'users' => $students,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSubjects(Request $request)
    {
        $userId = $request->get('firebase_user_id');
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try {
            $subjects = $this->database
                ->getReference("subjects/GR{$gradeLevel}")
                ->getSnapshot()
                ->getValue() ?? [];

            $teacher_subjets = [];
            foreach($subjects as $subject_id => $subject){
                if(isset($subject['people'][$userId])){
                    $teacher_subjets[] = [
                        'subject_id' => $subject_id,
                        'title' => $subject['title']
                    ];
                }

            }

            return response()->json([
                'success' => true,
                'teachers' => $teacher_subjets,
                'userId' => $userId
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
