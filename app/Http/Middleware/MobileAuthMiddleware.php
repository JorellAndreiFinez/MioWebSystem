<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\ExpiredIdToken;
use Kreait\Firebase\Exception\Auth\InvalidIdToken;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class MobileAuthMiddleware
{
    protected FirebaseAuth $auth;

    /**
     * Constructor to initialize FirebaseAuth.
     */
    public function __construct()
    {
        $path = base_path('storage/firebase/firebase.json');

        if (! file_exists($path)) {
            abort(500, "Missing Firebase credentials at {$path}");
        }

        $this->auth = (new Factory)
            ->withServiceAccount($path)
            ->createAuth();
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json([
                'message' => 'Unauthorized: Bearer token is missing.'
            ], 401);
        }

        try {
            $verifiedIdToken = $this->auth->verifyIdToken($token);
        } catch (ExpiredIdToken $e) {
            return response()->json([
                'message' => 'Unauthorized: Token has expired.'
            ], 401);
        } catch (InvalidIdToken $e) {
            return response()->json([
                'message' => 'Unauthorized: Token is invalid.'
            ], 401);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Unauthorized: Token verification failed.',
            ], 401);
        }

        $uid = $verifiedIdToken->claims()->get('sub');
        $request->attributes->set('firebase_user_id', $uid);

        return $next($request);
    }
}
