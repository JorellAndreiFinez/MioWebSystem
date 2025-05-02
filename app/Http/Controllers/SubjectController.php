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

     // View subjects under a grade
    public function viewSubjects($grade)
    {
        $subjects = $this->database->getReference("subjects/{$grade}")->getSnapshot()->getValue() ?? [];

        return view('mio.head.admin-panel', ['page' => 'view-subject'], compact('subjects', 'grade'));
    }

    // Show add subject form
    public function showAddSubjectForm($grade)
    {
        return view('mio.head.admin-panel',['page' => 'add-subjects'], compact('grade'));
    }

    // Handle form submission
    public function addSubject(Request $request, $grade)
    {
        $data = $request->only(['subject_id', 'code', 'title', 'teacher_id', 'section_id']);

        $this->database->getReference("subjects/{$grade}/{$data['subject_id']}")->set($data);

        return redirect()->route('mio.ViewSubject', ['grade' => $grade])->with('success', 'Subject added successfully.');

    }

    // Edit subject form
    public function editSubject($grade, $subjectId)
    {
        $subject = $this->database->getReference("subjects/{$grade}/{$subjectId}")->getSnapshot()->getValue();

        return view('mio.head.subjects.edit-subject', compact('subject', 'grade', 'subjectId'));
    }

    // Update subject
    public function updateSubject(Request $request, $grade, $subjectId)
    {
        $data = $request->only(['code', 'title', 'teacher_id', 'section_id']);

        $this->database->getReference("subjects/{$grade}/{$subjectId}")->update($data);

        return redirect()->route('mio.view-subject', ['grade' => $grade])->with('success', 'Subject updated.');
    }

    // Delete subject
    public function deleteSubject($grade, $subjectId)
    {
        $this->database->getReference("subjects/{$grade}/{$subjectId}")->remove();

        return response()->json(['message' => 'Subject deleted successfully']);
    }

}
