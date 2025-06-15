<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;


class EmergencyApi extends Controller
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

    public function sendEmergencyEarthquake(Request $request)
    {
        try {
            $users = $this->database->getReference("users")->getSnapshot()->getValue();

            $tokens = [];
            foreach ($users as $user) {
                if (!empty($user['fcm_token'])) {
                    $tokens[] = $user['fcm_token'];
                }
            }

            $title = 'Emergency Alert!';
            $body = 'Drop, cover, and hold! Stay safe and follow emergency instructions promptly.';

            foreach($tokens as $token){
                $message = CloudMessage::withTarget('token', $token)
                    ->withNotification(['title' => $title, 'body' => $body])
                    ->withData([
                        'type' => 'earthquake',
                        'screen' => 'EmergencyScreen',
                    ]);
                
                $this->messaging->send($message);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification sent successfully',  
            ]);
        } catch (\Throwable $e) {
            Log::error('FCM Send Failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateFCMToken(Request $request, string $student_id)
    {
        $validated = $request->validate([
            'token' => 'required|string|min:1',
        ]);

        try {
            $ref = $this->database->getReference("users/{$student_id}");
            $snapshot = $ref->getSnapshot();

            if (!$snapshot->exists()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Student not found.'
                ], 404);
            }

            $ref->update([
                'fcm_token' => $validated['token']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FCM token updated successfully.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function removeFCMToken(Request $request, string $student_id)
    {
        try {
            $ref = $this->database->getReference("users/{$student_id}");
            $snapshot = $ref->getSnapshot();

            if (!$snapshot->exists()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Student not found.'
                ], 404);
            }

            $ref->update([
                'fcm_token' => ""
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FCM token removed successfully.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
