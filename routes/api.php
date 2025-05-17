<?php

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\MobileAuthMiddleware;
use App\Http\Middleware\MobileRoleBasedAccessMiddleware;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SubjectController;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Session\Middleware\StartSession;

Route::middleware([
    EncryptCookies::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
])->post('/user-login', [LoginController::class, 'mobileLogin']);

Route::post('/logout',   [LoginController::class, 'mobileLogout'])->middleware(['firebase.auth']);
    

Route::get('/validate/token', [LoginController::class, 'mobileValidateToken'])
     ->middleware(['firebase.auth']);

// Student Routes
Route::middleware([
    StartSession::class,
    'firebase.auth',
    'firebase.role:student'
])->group(function() {
    Route::get('/subjects', [SubjectController::class, 'viewSubjectsApi']);
    Route::get('/subject/{subjectId}/modules', [SubjectController::class, 'getSubjectModulesApi']);
    Route::get('/subject/{subjectId}/announcements', [SubjectController::class, 'getSubjectAnnouncementsApi']);
    Route::get('/subject/{subjectId}/assignments', [SubjectController::class, 'getSubjectAssignmentsApi']);
    Route::get('/subject/{subjectId}/scores', [SubjectController::class, 'getSubjectScoresApi']);
});
