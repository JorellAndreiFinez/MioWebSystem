<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    //FOR CHECKING AND VERIFYING

    public function showLoginForm()
    {
        return view('mio.user-access.login'); // Blade file path
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Role-based redirection
            if ($user->role === 'admin') {
                return redirect()->route('mio.admin-panel');
            } elseif ($user->role === 'student') {
                return redirect()->route('mio.dashboard');
            } else {
                Auth::logout();
                return redirect()->back()->withErrors(['email' => 'Unauthorized role.']);
            }
        }

        return redirect()->back()->withErrors(['email' => 'Invalid credentials.']);
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
