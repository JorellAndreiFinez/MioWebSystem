<?php

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\MobileAuthMiddleware;
use App\Http\Middleware\MobileRoleBasedAccessMiddleware;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\Api;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Session\Middleware\StartSession;

Route::post('login', [LoginController::class, 'mobileLogin']);

Route::middleware([
    StartSession::class,
    'firebase.auth',
    'firebase.role:student-teacher'
])->group(function() {
    Route::get('/subjects', [SubjectController::class, 'viewSubjectsApi']);
    Route::get('/subject/{subjectId}/modules', [SubjectController::class, 'getSubjectModulesApi']);
    Route::get('/subject/{subjectId}/announcements', [SubjectController::class, 'getSubjectAnnouncementsApi']);
    Route::get('/subject/{subjectId}/announcement/{announcementId}', [SubjectController::class, 'getSubjectAnnouncementByIdApi']);
    Route::get('/subject/{subjectId}/assignments', [SubjectController::class, 'getSubjectAssignmentsApi']);
    Route::get('/subject/{subjectId}/assignment/{assignmentId}', [SubjectController::class, 'getSubjectAssignmentByIdApi']);
    Route::get('/subject/{subjectId}/scores', [SubjectController::class, 'getSubjectScoresApi']);
    Route::get('/subject/{subjectId}/quizzes', [SubjectController::class, 'getSubjectQuizzesApi']);
    Route::get('/subject/{subjectId}/quiz/{quizId}', [SubjectController::class, 'getSubjectQuizByIdApi']);
});

Route::middleware([
    StartSession::class,
    'firebase.auth',
    'firebase.role:teacher'
])
->group(function () {
    Route::post('/subject/{subjectId}/announcement', [SubjectController::class, 'createSubjectAnnouncementApi']);
    Route::put('/subject/{subjectId}/announcement/{announcementId}', [SubjectController::class, 'editSubjectAnnouncementApi']);
    Route::delete('/subject/{subjectId}/announcement/{announcementId}', [SubjectController::class, 'deleteSubjectAnnouncementApi']);
    
    Route::post('/subject/{subjectId}/assignment', [SubjectController::class, 'createAssignmentApi']);
    Route::put('/subject/{subjectId}/assignment/{assignmentId}', [SubjectController::class, 'editSubjectAssignmentApi']);
    Route::delete('/subject/{subjectId}/assignment/{assignmentId}', [SubjectController::class, 'deleteSubjectAssignmentApi']);

    Route::post('/subject/{subjectId}/quizzes', [SubjectController::class, 'createSubjectQuizzesApi']);
});
