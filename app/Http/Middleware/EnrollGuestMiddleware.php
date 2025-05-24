<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class EnrollGuestMiddleware
{
     public function handle($request, Closure $next)
    {
        if (Session::has('enrollment_user')) {
            return redirect()->route('enroll-dashboard');
        }

        return $next($request);
    }
}
