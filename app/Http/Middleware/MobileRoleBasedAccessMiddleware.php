<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Contract\Database;
use Symfony\Component\HttpFoundation\Response;

class MobileRoleBasedAccessMiddleware
{
    protected FirebaseAuth $auth;
    protected Database     $database;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(base_path('storage/firebase/firebase.json'))
            ->withDatabaseUri('https://miolms-default-rtdb.firebaseio.com');

        $this->auth     = $factory->createAuth();
        $this->database = $factory->createDatabase();
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $requiredRole
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ?string $requiredRole = null): Response
    {
        if (! $requiredRole) {
            return response()->json(['message' => 'Forbidden: no role specified'], 403);
        }

        $uid = $request->attributes->get('firebase_user_id');
        if (! $uid) {
            return response()->json(['message' => 'Unauthorized: user not authenticated'], 401);
        }

        $userData = $this->database
            ->getReference("users/{$uid}")
            ->getValue();

        $role = $userData['role'] ?? null;
        if (! $role) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: user role not found'
            ], 403);
        }

        $allowedRoles = array_map('strtolower', explode('-', $requiredRole));
        if (! in_array(strtolower($role), $allowedRoles)) {
            return response()->json([
                'message' => "Forbidden: requires one of the roles [" . implode(', ', $allowedRoles) . "]"
            ], 403);
        }

        $gradeLevel = $userData['grade_level'] ?? null;
        if (! $gradeLevel) {
            return response()->json([
                'success' => false,
                'error'   => 'User grade level is missing.',
            ], 400);
        }

        $request->attributes->set('firebase_user_role', $role);
        $request->attributes->set('firebase_user_gradeLevel', $gradeLevel);

        return $next($request);
    }
}
