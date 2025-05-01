<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleBasedAccess
{
    public function __construct() {}

    public function handle(Request $request, Closure $next, $role)
    {
        $firebaseUser = session('firebase_user'); // Check the updated session key

        if ($firebaseUser && $firebaseUser['role'] === $role) {
            return $next($request);
        }

        return redirect()->route('mio.login'); // Redirect if role does not match
    }
}

