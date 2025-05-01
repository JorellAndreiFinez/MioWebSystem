<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Factory;

class SubjectController extends Controller
{
    protected $database;
    protected $gradeLevelsTable = 'gradelevel'; // Firebase collection for grade levels

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

     // Fetch grade levels from Firebase and display them in the view
     public function showGradeLevels()
     {
        $gradeLevels = $this->database->getReference($this->gradeLevelsTable)->getSnapshot()->getValue();

        // Sort the grade levels from GR7 to GR10
        uksort($gradeLevels, function ($a, $b) {
            return (int) filter_var($a, FILTER_SANITIZE_NUMBER_INT) - (int) filter_var($b, FILTER_SANITIZE_NUMBER_INT);
        });

         return view('mio.head.admin-panel', ['page' => 'subjects'],compact('gradeLevels'));
     }

     public function viewSubject($grade)
{
    // Fetch the subjects for the specific grade from Firebase
    $subjects = $this->database->getReference('subjects/' . $grade)->getSnapshot()->getValue();

    return view('mio.head.admin-panel', ['page' => 'view-subject'], compact('subjects'));
}

}
