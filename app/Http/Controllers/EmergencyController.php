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
            'name' => 'required|in:flood,earthquake,fire,school_threat,power_outage',
        ]);

        $name = $validated['name'];
        $timestamp = Carbon::now()->timestamp;
        $emergencyId = strtoupper($name) . $timestamp;

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
            'title' => 'Emergency Alert: ' . ucfirst($name),
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
}
