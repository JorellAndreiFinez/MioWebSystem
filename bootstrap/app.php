<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RoleBasedAccess;
use App\Http\Middleware\EnrollAuthMiddleware;
use App\Http\Middleware\EnrollGuestMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
        'firebase.auth' => \App\Http\Middleware\MobileAuthMiddleware::class,
        'firebase.role' => \App\Http\Middleware\MobileRoleBasedAccessMiddleware::class,
        'enroll.auth' => EnrollAuthMiddleware::class,
            'enroll.guest' => EnrollGuestMiddleware::class,
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle exceptions
    })
    ->create();

$app->middleware([
    // Add your middleware here, like the RoleBasedAccess
    RoleBasedAccess::class,
]);

$app->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'enroll.auth' => \App\Http\Middleware\EnrollAuthMiddleware::class,
        'enroll.guest' => \App\Http\Middleware\EnrollGuestMiddleware::class,
    ]);
});


