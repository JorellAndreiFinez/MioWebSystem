<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth as FirebaseAuth;
use Symfony\Component\HttpFoundation\Response;
use Exception;

class MobileAuthMiddleware
{
    protected FirebaseAuth $auth;

    /**
     * Constructor to inject FirebaseAuth.
     */
    public function __construct()
    {
        $path = base_path('storage/firebase/firebase.json');

        if (!file_exists($path)) {
            die("This File Path .{$path}. does not exist.");
        }

        $this->auth = (new Factory)
            ->withServiceAccount($path)
            ->createAuth();
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthorized: Bearer token is missing or invalid.',], 401);
        }

        try {
            $verifiedIdToken = $this->auth->verifyIdToken($token);
            $request->merge(['firebase_user' => $verifiedIdToken->claims()->get('sub')]);
        } catch (Exception $e) {
            return response()->json(['error_message' => 'Invalid Token'], 401);
        }

        return $next($request);
    }
}