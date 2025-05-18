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

Route::middleware([
    StartSession::class,
    'firebase.auth',
    'firebase.role:teacher'
])->group(function() {
    Route::get('/subjects', [SubjectController::class, 'viewSubjectsApi']);
    Route::get('/subject/{subjectId}/modules', [SubjectController::class, 'getSubjectModulesApi']);
    Route::get('/subject/{subjectId}/announcements', [SubjectController::class, 'getSubjectAnnouncementsApi']);
    Route::post('/subject/{subjectId}/announcements/', [SubjectController::class, 'createSubjectAnnouncementsApi']);
    Route::post('/subject/{subjectId}/announcements/{announcementId}', [SubjectController::class, 'editSubjectAnnouncementApi']);
    Route::get('/subject/{subjectId}/assignments', [SubjectController::class, 'getSubjectAssignmentsApi']);
    Route::get('/subject/{subjectId}/scores', [SubjectController::class, 'getSubjectScoresApi']);
});
