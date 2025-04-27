<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;

class FirebaseConnectionController extends Controller
{
    public function index()
    {
        $path = base_path('storage/firebase/firebase.json');

        if(!file_exists($path)) {

            die("This File Path .{$path}. is not exists.");
        }

        try {

            $factory = (new Factory)
                ->withServiceAccount($path)
                ->withDatabaseUri('https://miolms-default-rtdb.firebaseio.com');

            $database = $factory->createDatabase();
            $reference = $database->getReference('users');
            $reference->set(['connection' => true]);
            $snapshot = $reference->getSnapshot();
            $value = $snapshot->getValue();

            return response()->json([
                'message' => true,
                'data' => $value,
            ]);
        } catch(Exception $e) {
            return response([
                'message' => $e->getMessage(),
                'status' => 'False',
            ]);


        }
    }
}
