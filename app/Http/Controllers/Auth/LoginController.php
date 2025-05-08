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
    // Check if the user is already authenticated
    if (session()->has('firebase_user')) {
        $role = session('firebase_user')['role'];

        // Redirect to the appropriate dashboard based on the user's role
        return match ($role) {
            'admin'   => redirect()->route('mio.admin-panel'),
            'teacher' => redirect()->route('mio.teacher-panel'),
            'parent'  => redirect()->route('mio.parent-panel'),
            'student' => redirect()->route('mio.student-panel'),
            default   => redirect()->route('mio.login')->with('error', 'Unrecognized role.'),
        };
    }

    // If the user is not logged in, show the login form
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

        // Retrieve name safely (set default if not found)
        $name = $userData['fname'] ?? 'User'; // Default to 'User' if name is not set
        $role = strtolower($userData['role']);

        // Set the entire firebase_user in session with name, role, etc.
        Session::put('firebase_user', [
            'uid' => $uid,
            'email' => $email,
            'role' => $role,
            'name' => $name, // Store name in session
            'category' => $userData['category'] ?? null, // <-- Add this
        ]);

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

    // } catch (\Kreait\Firebase\Exception\Auth\InvalidPassword $e) {
    //     return redirect()->back()->with('error', 'Incorrect password.');
    // } catch (\Kreait\Firebase\Exception\Auth\UserNotFound $e) {
    //     return redirect()->back()->with('error', 'Email not registered.');
    } catch (\Kreait\Firebase\Exception\AuthException $e) {
        return response()->json(['error' => 'Invalid Credentials.'], 401);
    } catch (\Throwable $e) {
        return redirect()->back()->with('error', 'Login failed: ' . $e->getMessage());
    }
}

public function logout()
{
    // Clear the entire session
    Session::flush();

    // Optionally regenerate the session ID
    Session::regenerate();

    // Redirect back to login page with a success message
    return redirect()->route('mio.login')->with('status', 'Logged out successfully.');
}



// Mobile Login

    public function mobileLogin(Request $request)
        {
            $email = $request->input('email');
            $password = $request->input('password');

        try {
            $signInResult = $this->auth->signInWithEmailAndPassword($email, $password);
            $firebaseUser = $signInResult->data();
            $uid = $firebaseUser['localId'];

            $userData = $this->database->getReference('users/' . $uid)->getValue();

                if (!$userData || !isset($userData['role'])) {
                    return response()->json(['error' => 'User or role not found.'], 404);
                }

            // Retrieve name safely (set default if not found)
            $name = $userData['fname'] ?? 'User';
            $role = strtolower($userData['role']);

            // Update login timestamp
            $this->database->getReference('users/' . $uid)->update([
                'last_login' => Carbon::now()->toDateTimeString(),
            ]);

            // Return success response with user data
            return response()->json([
                'success' => true,
                'message' => 'Login successful.',
                'user' => [
                    'uid' => $uid,
                    'email' => $email,
                    'role' => $role,
                    'name' => $name,
                    'category' => $userData['category'] ?? null,
                ],
            ], 200);

        } catch (\Kreait\Firebase\Exception\Auth\InvalidPassword $e) {
            return response()->json(['error' => 'Incorrect password.'], 401);
        } catch (\Kreait\Firebase\Exception\Auth\UserNotFound $e) {
            return response()->json(['error' => 'Email not registered.'], 404);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Login failed: ' . $e->getMessage()], 500);
        }
    }

    public function mobileLogout()
    {
        Session::flush();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ], 200);
    }

}
