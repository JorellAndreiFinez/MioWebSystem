<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Kreait\Firebase\Messaging\CloudMessage;


class DataAnalytics extends Controller
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

    public function getDashboard(Reqeuest $request){
        $userId = $request->get('firebase_user_id');
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try{
            $active_user = [];
            $subjects = $this->database->getReference("subjects/GR{$gradeLevel}")->getSnapshot()->getValue();

            foreach($subjects as $subject_id => $subject){
                
            }


        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
