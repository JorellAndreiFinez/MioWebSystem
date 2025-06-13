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
    Route::get('/subject/{subjectId}/attempts/{activityType}/{activityId}', [SpecializedSpeechApi::class, 'checkActiveActivity']);

    // speech 
    Route::get('/subject/{subjectId}/attempts/speech/{activityType}/{activityId}/{attemptId}', [SpecializedSpeechApi::class, 'continueActivity']);
    Route::post('/subject/{subjectId}/speech/{activityType}/{difficulty}/{activityId}', [SpecializedSpeechApi::class, 'startFlashcardActivity']);
    Route::post('/subject/{subjectId}/speech/{activityType}/{activityId}/{attemptId}/{flashcardId}', [SpecializedSpeechApi::class, 'submitFlashcardAnswer']);
    Route::patch('/subject/{subjectId}/speech/{activityType}/{difficulty}/{activityId}/{attemptId}', [SpecializedSpeechApi::class, 'finalizeFlashcardAttempt']);

    // auditory
    Route::get('/subject/{subjectId}/attempts/auditory/{activityType}/{activityId}/{attemptId}', [SpecializedAuditoryApi::class, 'continueBingoActivity']);
    Route::post('/subject/{subjectId}/auditory/bingo/{difficulty}/{activityId}', [SpecializedAuditoryApi::class, 'startBingoActivity']);
    Route::post('/subject/{subjectId}/auditory/matching/{difficulty}/{activityId}', [SpecializedAuditoryApi::class, 'startMatchingActivity']);
    Route::post('/subject/{subjectId}/auditory/bingo/{difficulty}/{activityId}/{attemptId}', [SpecializedAuditoryApi::class, 'finalizeBingoAttempt']);
    Route::put('/subject/{subjectId}/auditory/matching/{difficulty}/{activityId}/{attemptId}', [SpecializedAuditoryApi::class, 'finalizeMatchingAttempt']);

    //language
    Route::get('/subject/{subjectId}/attempts/language/{activityType}/{activityId}/{attemptId}', [SpecializedLanguageApi::class, 'continueLanguageActivity']);
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
    Route::post('/subject/{subjectId}/announcement/{announcementId}', [TeacherApiController::class, 'editSubjectAnnouncementApi']);
    Route::delete('/subject/{subjectId}/announcement/{announcementId}', [TeacherApiController::class, 'deleteSubjectAnnouncementApi']);
    
    Route::post('/subject/{subjectId}/assignment', [TeacherApiController::class, 'createAssignmentApi']);
    Route::put('/subject/{subjectId}/assignment/{assignmentId}', [TeacherApiController::class, 'editSubjectAssignmentApi']);
    Route::delete('/subject/{subjectId}/assignment/{assignmentId}', [TeacherApiController::class, 'deleteSubjectAssignmentApi']);

    Route::post('/subject/{subjectId}/quiz', [TeacherApiController::class, 'createSubjectQuizzesApi']);

    //people
    Route::get('/subject/{subjectId}/peoples', [TeacherApiController::class, 'getStudents']);

    // scores
    Route::get('/subject/{subjectId}/scores', [TeacherApiController::class, 'getScores']);
    Route::get('/subject/{subjectId}/scores/{activityType}/{activityId}/{userId}', [TeacherApiController::class, 'getStudentAttempts']);
    Route::get('/subject/{subjectId}/scores/{activityType}/{activityId}/{userId}/{attemptId}', [TeacherApiController::class, 'getStudentActivity']);

    //speech
    Route::get('/subject/{subjectId}/speech/{activityType}/{difficulty}/{activityId}', [SpecializedSpeechApi::class, 'getActivityPictureById']);
    Route::post('/subject/{subjectId}/specialized/speech', [SpecializedSpeechApi::class, 'createSpeechActivity']);
    Route::post('/subject/{subjectId}/specialized/speech/picture', [SpecializedSpeechApi::class, 'createSpeechPictureActivity']);
    Route::put('/subject/{subjectId}/specialized/speech/{activityType}/{difficulty}/{activityId}', [SpecializedSpeechApi::class, 'editSpeechActivity']);
    Route::post('/subject/{subjectId}/specialized/speech/picture/{difficulty}/{activityId}', [SpecializedSpeechApi::class, 'editSpeechPictureActivity']);

    // auditory
    Route::get('/subject/{subjectId}/auditory/bingo/{difficulty}/{activityId}', [SpecializedAuditoryApi::class, 'getBingoById']);
    Route::get('/subject/{subjectId}/auditory/matching/{difficulty}/{activityId}', [SpecializedAuditoryApi::class, 'getMatchingById']);
    Route::post('/subject/{subjectId}/specialized/auditory/bingo', [SpecializedAuditoryApi::class, 'createBingoActivity']);
    Route::post('/subject/{subjectId}/specialized/auditory/bingo/{difficulty}/{activityId}', [SpecializedAuditoryApi::class, 'editBingoActivity']);
    Route::post('/subject/{subjectId}/specialized/auditory/matching/{difficulty}/{activityId}', [SpecializedAuditoryApi::class, 'editMatchingActivity']);
    Route::post('/subject/{subjectId}/specialized/auditory/matching', [SpecializedAuditoryApi::class, 'createMatchingCardsActivity']);

    // language
    Route::get('/subject/{subjectId}/language/fill/{difficulty}/{activityId}', [SpecializedLanguageApi::class, 'getFillActivity']);
    Route::get('/subject/{subjectId}/language/homonyms/{difficulty}/{activityId}', [SpecializedLanguageApi::class, 'getHomonymsActivity']);
    Route::post('/subject/{subjectId}/specialized/language/homonyms', [SpecializedLanguageApi::class, 'createHomonymsActivity']);
    Route::post('/subject/{subjectId}/specialized/language/fill', [SpecializedLanguageApi::class, 'createFillActivity']);
    Route::post('/subject/{subjectId}/specialized/language/homonyms/{difficulty}/{activityId}', [SpecializedLanguageApi::class, 'editHomonymsActivity']);
    Route::post('/subject/{subjectId}/specialized/language/fill/{difficulty}/{activityId}', [SpecializedLanguageApi::class, 'editFillActivity']);
});
