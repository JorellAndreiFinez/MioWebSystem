<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Database;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SettingsController extends Controller
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

    public function showSettings(Request $request)
    {
        // Retrieve Firebase user from session
        $firebaseUser = session('firebase_user');

        // Optional: Redirect if not logged in
        if (!$firebaseUser) {
            return redirect()->route('login')->with('error', 'You must be logged in to access settings.');
        }

        $hasUnreadMessages = false;
        $loggedInTeacherId = session('firebase_user')['uid'] ?? null;

        $messagesRef = $this->database->getReference('messages');
        $messages = $messagesRef->getValue() ?? [];

        foreach ($messages as $threadKey => $thread) {
            foreach ($thread as $messageId => $message) {
                if (
                     isset($message['receiver_id'], $message['read']) &&
                                    $message['receiver_id'] === $loggedInTeacherId &&
                                    $message['read'] === false
                ) {
                    $hasUnreadMessages = true;
                    break 2; // Stop checking once found
                }
            }
        }

        return view('mio.head.teacher-panel',[
            'page' => 'teacher-settings',
            'firebaseUser' => $firebaseUser,
            'hasUnreadMessages' => $hasUnreadMessages,

        ]);
    }


}
