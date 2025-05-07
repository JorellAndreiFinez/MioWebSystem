<?php

namespace App\Http\Controllers\Enrollment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Kreait\Firebase\Factory;
use Carbon\Carbon;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Exception\Auth\EmailExists;
use Kreait\Firebase\Exception\AuthException;
use Illuminate\Support\Facades\Hash;

class EnrollController extends Controller
{
    protected $database;
    protected $auth;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(base_path('storage/firebase/firebase.json'))
            ->withDatabaseUri('https://miolms-default-rtdb.firebaseio.com');

        $this->database = $factory->createDatabase();
        $this->auth = $factory->createAuth(); // Firebase Auth instance
    }

    public function signup(Request $request)
{
    $request->validate([
        'user_email' => 'required|email',
        'user_name' => 'required|string',
        'user_pass' => 'required|confirmed|min:6',
    ]);

    $email = $request->user_email;
    $password = $request->user_pass;

    try {
        // Create Firebase Auth user
        $user = $this->auth->createUserWithEmailAndPassword($email, $password);

        // Send verification email
        $this->auth->sendEmailVerificationLink($email);

        // Store name temporarily in session (optional)
        Session::put('pending_user', [
            'uid' => $user->uid,
            'username' => $request->user_name,
        ]);

        return redirect()->route('enroll-login')->with([
            'status' => 'Account created. A verification email was sent to your inbox.',
        ]);
    } catch (EmailExists $e) {
        return redirect()->back()->with(['error' => 'Email already exists.']);
    } catch (AuthException $e) {
        return redirect()->back()->with(['error' => 'Firebase Auth error: ' . $e->getMessage()]);
    }
}


    public function login(Request $request)
{
    $request->validate([
        'user_login' => 'required|string',
        'user_pass' => 'required|string',
    ]);

    $email = $request->user_login;
    $password = $request->user_pass;

    try {
        // Sign in via Firebase Auth
        $signInResult = $this->auth->signInWithEmailAndPassword($email, $password);
        $firebaseUser = $signInResult->data();

        // Check if email is verified
        $user = $this->auth->getUserByEmail($email);
        if (!$user->emailVerified) {
            return redirect()->back()->with([
                'error' => 'Please verify your email address before logging in.',
            ]);
        }

        // Get the user from Realtime Database using UID
        $uid = $user->uid;
        $userData = $this->database->getReference('enrollment/users/' . $uid)->getValue();

        if ($userData) {
            Session::put('user_data', $userData);
            return redirect()->route('dashboard');
        } else {
            return redirect()->back()->with(['error' => 'User data not found in database.']);
        }

    } catch (\Throwable $e) {
        return redirect()->back()->with(['error' => 'Login failed: ' . $e->getMessage()]);
    }
}

}