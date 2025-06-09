<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;

class EmergencyController extends Controller
{
    protected $database;
    protected $table = 'emergencies';


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

    public function triggerEmergency(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'name' => 'required|in:fl,ea,fi,sc,po',
        ]);

        $name = strtoupper($validated['name']); // e.g. FL, EA, etc.
        $datePart = Carbon::now()->format('Ymd'); // e.g. 20250608
        $randomSuffix = str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT); // e.g. 00 to 99

        $emergencyId = "{$name}{$datePart}{$randomSuffix}"; // e.g. FL2025060807

        // Emergency data
        $emergencyData = [
            'id' => $emergencyId,
            'name' => $name,
            'vibrate' => true,
            'people' => 'all',
            'created_in' => Carbon::now()->toDateTimeString(),
        ];

        // Announcement data
        $announcementData = [
            'id' => $emergencyId,
            'title' => 'Emergency Alert: ' . $name,
            'description' => 'A ' . $name . ' alert has been issued. Please take appropriate action.',
            'people' => 'all',
            'date' => Carbon::now()->format('Y-m-d'),
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ];

        // Store in Firebase
        $this->database->getReference("emergencies/{$emergencyId}")->set($emergencyData);
        $this->database->getReference("admin-announcements/{$emergencyId}")->set($announcementData);

        return response()->json([
            'status' => 'success',
            'message' => 'Emergency triggered successfully.',
            'emergency_id' => $emergencyId,
        ]);
    }

    public function stopVibration(Request $request)
    {
        $request->validate([
            'id' => 'required|string'
        ]);

        $emergencyId = $request->input('id');

        // Set vibrate to false and add finished_at timestamp
        $this->database->getReference("emergencies/{$emergencyId}")->update([
            'vibrate' => false,
            'finished_at' => \Carbon\Carbon::now()->toDateTimeString()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Vibration stopped and emergency marked as finished: ' . $emergencyId,
        ]);
    }


    public function getActiveEmergency()
    {
        $emergencies = $this->database->getReference('emergencies')->getValue();

        if (!$emergencies) {
            return response()->json(['active' => false]);
        }

        // Look for any emergency with vibrate == true
        foreach ($emergencies as $id => $emergency) {
            if (isset($emergency['vibrate']) && $emergency['vibrate'] === true) {
                return response()->json([
                    'active' => true,
                    'id' => $id,
                    'name' => $emergency['name']
                ]);
            }
        }

        return response()->json(['active' => false]);
    }


}
