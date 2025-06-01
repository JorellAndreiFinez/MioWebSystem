<?php

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\MobileAuthMiddleware;
use App\Http\Middleware\MobileRoleBasedAccessMiddleware;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\api\StudentApiController;
use App\Http\Controllers\api\TeacherApiController;
use App\Http\Controllers\api\SpecializedSpeechApi;
use App\Http\Controllers\api\SpecializedAuditoryApi;
use App\Http\Controllers\api\SpecializedLanguageApi;
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
    Route::get('/subject/{subjectId}/specialized/{activityType}/{difficulty}', [SpecializedSpeechApi::class, 'getSpeechActivities']);
});


Route::middleware([
    StartSession::class,
    'firebase.auth',
    'firebase.role:student'
])->group(function() {
    // speech 

    Route::post('/subject/{subjectId}/speech/{activityType}/{difficulty}/{activityId}', [SpecializedSpeechApi::class, 'startFlashcardActivity']);
    Route::post('/subject/{subjectId}/speech/{activityType}/{activityId}/{attemptId}/{flashcardId}', [SpecializedSpeechApi::class, 'submitFlashcardAnswer']);
    Route::patch('/subject/{subjectId}/speech/{activityType}/{difficulty}/{activityId}/{attemptId}', [SpecializedSpeechApi::class, 'finalizeFlashcardAttempt']);

    // auditory
    Route::post('/subject/{subjectId}/auditory/bingo/{difficulty}/{activityId}', [SpecializedAuditoryApi::class, 'startBingoActivity']);
    Route::post('/subject/{subjectId}/auditory/matching/{difficulty}/{activityId}', [SpecializedAuditoryApi::class, 'startMatchingActivity']);
    Route::patch('/subject/{subjectId}/auditory/bingo/{difficulty}/{activityId}/{attemptId}', [SpecializedAuditoryApi::class, 'finalizeBingoAttempt']);
    Route::patch('/subject/{subjectId}/auditory/matching/{difficulty}/{activityId}/{attemptId}', [SpecializedAuditoryApi::class, 'finalizeMatchingAttempt']);

    Route::post('/subject/{subjectId}/language/homonyms/{difficulty}/{activityId}', [SpecializedLanguageApi::class, 'takeHomonymActivity']);
    Route::patch('/subject/{subjectId}/language/homonyms/{difficulty}/{activityId}/{attemptId}', [SpecializedLanguageApi::class, 'finalizeHomonymsAttempt']);

    Route::post('/subject/{subjectId}/language/fill/{difficulty}/{activityId}', [SpecializedLanguageApi::class, 'takeFillActivity']);
    Route::patch('/subject/{subjectId}/language/fill/{difficulty}/{activityId}/{attemptId}', [SpecializedLanguageApi::class, 'finalizeFillAttempt']);
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

    Route::post('/subject/{subjectId}/specialized/speech', [SpecializedSpeechApi::class, 'createSpeechActivity']);
    Route::post('/subject/{subjectId}/specialized/auditory/bingo', [SpecializedAuditoryApi::class, 'createBingoActivity']);
    Route::post('/subject/{subjectId}/specialized/auditory/matching', [SpecializedAuditoryApi::class, 'createMatchingCardsActivity']);


    Route::post('/subject/{subjectId}/specialized/language/homonyms', [SpecializedLanguageApi::class, 'createHomonymsActivity']);
    Route::post('/subject/{subjectId}/specialized/language/fill', [SpecializedLanguageApi::class, 'createFillActivity']);
    // Route::post('/subject/{subjectId}/specialized/language/talk2Me', [SpecializedLanguageApi::class, 'createTalk2MeActivity']);
});
