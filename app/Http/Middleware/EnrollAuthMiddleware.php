<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class EnrollAuthMiddleware
{
    public function handle($request, Closure $next)
    {
        if (!Session::has('enrollment_user')) {
            return redirect()->route('enroll-login')->with([
                'error' => 'Please log in to access the enrollment dashboard.',
                'show_login_tab' => true
            ]);
        }

        return $next($request);
    }
}
