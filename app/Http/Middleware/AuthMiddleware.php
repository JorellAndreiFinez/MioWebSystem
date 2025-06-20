<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Session;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the firebase_user exists in the session
        if (!session()->has('firebase_user')) {
            return redirect()->route('mio.login'); // Redirect to login if not authenticated
        }

        return $next($request);
    }
}


