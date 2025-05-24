<?php

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\MobileAuthMiddleware;
use App\Http\Middleware\MobileRoleBasedAccessMiddleware;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\api\StudentApiController;
use App\Http\Controllers\api\TeacherApiController;
use App\Http\Controllers\api\SpecializedActivityApi;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Session\Middleware\StartSession;

Route::post('login', [LoginController::class, 'mobileLogin']);

Route::middleware([
    StartSession::class,
    'firebase.auth',
    'firebase.role:student-teacher'
])->group(function() {
    Route::get('/subjects', [StudentApiController::class, 'viewSubjectsApi']);
    Route::get('/subject/{subjectId}/modules', [StudentApiController::class, 'getSubjectModulesApi']);
    Route::get('/subject/{subjectId}/announcements', [StudentApiController::class, 'getSubjectAnnouncementsApi']);
    Route::get('/subject/{subjectId}/announcement/{announcementId}', [StudentApiController::class, 'getSubjectAnnouncementByIdApi']);
    Route::get('/subject/{subjectId}/assignments', [StudentApiController::class, 'getSubjectAssignmentsApi']);
    Route::get('/subject/{subjectId}/assignment/{assignmentId}', [StudentApiController::class, 'getSubjectAssignmentByIdApi']);
    Route::get('/subject/{subjectId}/scores', [StudentApiController::class, 'getSubjectScoresApi']);
    Route::get('/subject/{subjectId}/quizzes', [StudentApiController::class, 'getSubjectQuizzesApi']);
    Route::get('/subject/{subjectId}/quiz/{quizId}', [StudentApiController::class, 'getSubjectQuizByIdApi']);
    Route::get('/subject/{subjectId}/specialized/{activityType}/{difficulty}', [SpecializedActivityApi::class, 'getSpeechActivities']);
    Route::get('/subject/{subjectId}/specialized/{activityType}/{difficulty}/{activityId}', [SpecializedActivityApi::class, 'getSpeechActivityById']);
});

Route::middleware([
    StartSession::class,
    'firebase.auth',
    'firebase.role:teacher'
])
->group(function () {
    Route::post('/subject/{subjectId}/announcement', [TeacherApiController::class, 'createSubjectAnnouncementApi']);
    Route::put('/subject/{subjectId}/announcement/{announcementId}', [TeacherApiController::class, 'editSubjectAnnouncementApi']);
    Route::delete('/subject/{subjectId}/announcement/{announcementId}', [TeacherApiController::class, 'deleteSubjectAnnouncementApi']);
    
    Route::post('/subject/{subjectId}/assignment', [TeacherApiController::class, 'createAssignmentApi']);
    Route::put('/subject/{subjectId}/assignment/{assignmentId}', [TeacherApiController::class, 'editSubjectAssignmentApi']);
    Route::delete('/subject/{subjectId}/assignment/{assignmentId}', [TeacherApiController::class, 'deleteSubjectAssignmentApi']);

    Route::post('/subject/{subjectId}/quiz', [TeacherApiController::class, 'createSubjectQuizzesApi']);

    Route::post('/subject/{subjectId}/specialized', [SpecializedActivityApi::class, 'createSpeechActivity']);
});
