<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class NotificationController extends Controller
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

    public function getNotifications(Request $request){
        $userId = $request->get('firebase_user_id');

        try{
            $notifications = $this->database->getReference("notifications")->getSnapshot()->getValue();

            $notification_list = [];
            foreach($notifications as $notification_id => $notification){
                if (isset($notification['student_ids'][$userId])) {
                    $notification_list[] = [
                        'title' => $notification['title'],
                        'body' => $notification['body'],
                        'date' => $notification['date'],
                        'subject_id' => $notification['subject_id'],
                        'announcement_id' => $notification['announcement_id'],
                        'type' => $notification['type'],
                        'notification_id' => $notification_id
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'notifications' => $notification_list,
                'user'=> $userId
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Failed to create announcement: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function dismissNotification(Request $request, string $notificationId)
    {
        $userId = $request->get('firebase_user_id');

        $ref = $this->database->getReference("notifications/{$notificationId}");
        $notification = $ref->getSnapshot()->getValue();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found.'
            ], 404);
        }

        $ref->getChild("dismissed_ids/{$userId}")->set(true);

        $total = count($notification['student_ids'] ?? []);
        $dismissed = count(($notification['dismissed_ids'] ?? []) + [$userId => true]);

        if ($total > 0 && $dismissed >= $total) {
            $ref->remove();
        }

        return response()->json(['success' => true, 'message' => 'Notification dismissed.']);
    }
}
