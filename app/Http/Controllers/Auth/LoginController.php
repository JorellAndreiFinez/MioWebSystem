<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Kreait\Firebase\Factory;
use Carbon\Carbon;

class LoginController extends Controller
{
    protected $auth;
    protected $database;

    public function __construct()
    {
        $serviceAccountPath = base_path('storage/firebase/firebase.json');

        if (!file_exists($serviceAccountPath)) {
            dd("File not found at: " . $serviceAccountPath);
        }

        $factory = (new Factory)
            ->withServiceAccount($serviceAccountPath)
            ->withDatabaseUri('https://miolms-default-rtdb.firebaseio.com');

        $this->auth = $factory->createAuth();
        $this->database = $factory->createDatabase();
    }

    public function loginForm()
    {
        return view('mio.user-access.login');
    }

    public function login(Request $request)
{
    $email = $request->input('email');
    $password = $request->input('password');

    try {
        $signInResult = $this->auth->signInWithEmailAndPassword($email, $password);
        $firebaseUser = $signInResult->data();
        $uid = $firebaseUser['localId'];

        $userData = $this->database->getReference('users/' . $uid)->getValue();

        if (!$userData || !isset($userData['role'])) {
            return redirect()->back()->with('error', 'User or role not found.');
        }

        $role = strtolower($userData['role']);

        // Set session
        Session::put('uid', $uid);
        Session::put('email', $email);
        Session::put('role', $role);

        // Update login timestamp
        $this->database->getReference('users/' . $uid)->update([
            'last_login' => Carbon::now()->toDateTimeString(),
        ]);

        // Role-based redirection
        return match ($role) {
            'admin'   => redirect()->route('mio.admin-panel'),
            'teacher' => redirect()->route('mio.teacher-panel'),
            'parent'  => redirect()->route('mio.parent-panel'),
            'student' => redirect()->route('mio.student-panel'),
            default   => redirect()->back()->with('error', 'Unrecognized role.'),
        };

    } catch (\Kreait\Firebase\Exception\Auth\InvalidPassword $e) {
        return redirect()->back()->with('error', 'Incorrect password.');
    } catch (\Kreait\Firebase\Exception\Auth\UserNotFound $e) {
        return redirect()->back()->with('error', 'Email not registered.');
    } catch (\Throwable $e) {
        return redirect()->back()->with('error', 'Login failed: ' . $e->getMessage());
    }
}


    public function logout()
    {
        Session::flush();
        return redirect()->route('login.form')->with('status', 'Logged out successfully.');
    }
}
