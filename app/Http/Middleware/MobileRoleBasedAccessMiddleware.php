<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth as FirebaseAuth; 
use Kreait\Firebase\Contract\Database;
use Symfony\Component\HttpFoundation\Response;
use Exception;

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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $requiredRole = null): Response
    {

        if($requiredRole == null){
            return response()->json(['message' => 'Access denied: No role specified for this route.'], 401);
        }

        $uid = $request->get('firebase_user');
        if (! $uid) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 401,
                    'message' => 'Unauthorized: no authenticated user found.',
                ],
            ], 401);
        }

        

        $userData = $this->database->getReference('users/' . $uid)->getValue();

        if (! $userData || ! isset($userData['role'])) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 403,
                    'message' => 'Forbidden: user role not found.',
                ],
            ], 403);
        }

        if (strtolower($userData['role']) !== strtolower($requiredRole)) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 403,
                    'message' => "Forbidden: requires role '{$requiredRole}'.",
                ],
            ], 403);
        }

        $request->attributes->set('firebase_user_role', $userData['role']);

        return $next($request);
    }
}
