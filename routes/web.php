<?php

use App\Http\Controllers\admin\CmsController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Auth\FirebaseAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Dashboard\MessagingController;
use App\Http\Controllers\Dashboard\SettingsController;
use App\Http\Controllers\Dashboard\StudentController;
use App\Http\Controllers\Dashboard\TeacherController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmergencyController;
use App\Http\Controllers\Enrollment\EnrollController;
use App\Http\Controllers\FirebaseConnectionController;
use App\Http\Controllers\SchoolYearController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SpeechaceController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\admin\AdminController;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\EnrollAuthMiddleware;
use App\Http\Middleware\RoleBasedAccess;
use Illuminate\Support\Facades\Route;
use Kreait\Laravel\Firebase\Facades\Firebase;


Route::get('/test', [FirebaseConnectionController::class, 'index']);

// CMS
Route::prefix('')->group(function () {
    Route::get('/', function () {
        return view('landing');
    })->name('landing');

    // ADMISSION
    Route::get('/admission', function () {
        return view('layouts.enroll');
    })->name('enroll');

    Route::get('/admission/guide', function () {
        return view('layouts.assessment-guide');
    })->name('admission.assess-guide');


    Route::get('/admission/payment', function () {
        return view('layouts.payment-guide');
    })->name('admission.payment-guide');

    Route::get('/about', function () {
        return view('layouts.about');
    })->name('about');

    Route::get('/program', function () {
        return view('layouts.program');
    })->name('program');

    Route::get('/campus', function () {
        return view('layouts.campus');
    })->name('campus');

    Route::get('/news', function () {
        return view('layouts.news');
    })->name('news');

    Route::get('/events', function () {
        return view('layouts.events');
    })->name('events');
});


Route::prefix('enrollment')->group(function () {

    // Routes only accessible if NOT logged in
    Route::middleware('enroll.guest')->group(function () {
        Route::view('/login', 'enrollment-panel.enrollment-panel', ['page' => 'enroll-login'])->name('enroll-login');
        Route::post('/login', [EnrollController::class, 'login'])->name('enroll.login');
        Route::post('/signup', [EnrollController::class, 'signup'])->name('enroll.signup');
    });

    // Routes only accessible if logged in
    Route::middleware('enroll.auth')->group(function () {
        Route::get('/dashboard', [EnrollController::class, 'showDashboard'])->name('enroll-dashboard');

        Route::get('/enrollment-form', [EnrollController::class, 'showEnrollmentForm'])->name('enroll-form');
        Route::post('/enrollment-submit', [EnrollController::class, 'submitEnrollmentForm'])->name('enrollment.submit');
        Route::post('/logout', [EnrollController::class, 'logout'])->name('enroll.logout');

        Route::get('/enroll-assessment', [EnrollController::class, 'showAssessmentPage'])->name('enroll-assessment');

        Route::get('/start-assessment', [EnrollController::class, 'startAssessment'])->name('enroll.assessment.start');

        Route::get('/reading-test', [EnrollController::class, 'mainAssessment2'])->name('enroll.assessment.reading');

        Route::get('/sentence-test', [EnrollController::class, 'mainAssessment3'])->name('enroll.assessment.sentence');

        // ASSESSMENT ROUTES
        Route::prefix('assessment')->name('assessment.')->group(function () {

            Route::post('/submit', action: [SpeechaceController::class, 'submit'])->name('speechace.submit');

            Route::post('/submit-reading-test', action: [SpeechaceController::class, 'submit2'])->name('speechace.submit2');

            Route::post('/submit-sentence-test', action: [SpeechaceController::class, 'submit3'])->name('speechace.submit3');

            Route::post('/submit-written-test', action: [SpeechaceController::class, 'submit4'])->name('written.submit');
        });
    });
});




// ADMIN LOGIN

// Route::get('/mio/admin/login', function () {
//     return view('mio.admin-access.login');
// })->name('mio.admin.login');



// MIO - ADMIN PANEL
Route::prefix('mio/admin/')->middleware(
    [AuthMiddleware::class, RoleBasedAccess::class . ':admin']
)->name('mio.')->group(function () {

    Route::get('/dashboard', [FirebaseAuthController::class, 'showAdminPanel'])->name('admin-panel');

    Route::post('/verify-password', [AdminController::class, 'verifyPassword'])->name('verify-admin-password');


// ----------------  ANALYTICS

    Route::get('/data-analytics', [AdminController::class, 'showDataAnalytics'])->name('ViewDataAnalytics');


// ----------------  PID

    Route::get('/PID', [CmsController::class, 'showCMS'])->name('ViewCMS');

    Route::get('/PID/edit/{key}', [CmsController::class, 'editNav'])->name('cms.edit-nav');

    Route::POST('/PID/cms/homepage/update', [CMSController::class, 'updateCMSHomepage'])->name('cms.homepage.update');

// ----------------  ENROLLMENT
    Route::get('/enrollment', [EnrollController::class, 'showAdminEnrollment'])->name('enrollment');
     Route::get('/enrollment/{id}', [EnrollController::class, 'viewAdminEnrollee'])->name('view-enrollee');

     Route::put('/enrollment/update/{id}', [EnrollController::class, 'updateEnrolleeStatus'])->name('update-enrollee');

     Route::get('/enrollment/assessment/{type}/edit', [EnrollController::class, 'editAssessment'])->name('edit-assessment');

     Route::post('/enrollment/assessment/{type}/speech/save', [EnrollController::class, 'saveSpeechPhrases'])->name('save-speech-assessment');

    Route::delete('/enrollment/assessment/{type}/speech/delete/{id}', [EnrollController::class, 'deleteSpeechPhrase'])->name('mio.speech-assessment.destroy');

    Route::post('/enrollment/assessment/{type}/auditory/save', [EnrollController::class, 'saveAuditoryPhrases'])->name('save-auditory-assessment');

    Route::post('/enrollment/assessment/{type}/sentence/save', [EnrollController::class, 'saveSentence'])->name('save-sentence-assessment');

    Route::post('/enrollment/assessment/{type}/fnblank/save', [EnrollController::class, 'saveFillBlanks'])->name('save-fnblank-assessment');

    Route::get('/enrollment/assessment/{type}/edit', [EnrollController::class, 'editAssessment'])->name('edit-assessment2');

    Route::post('/enrollment/assessment/{type}/edit/written-question/save', [EnrollController::class, 'saveQuestion'])->name('save-question');

    Route::put('/enrollment/assessment/{type}/update/{id}', [EnrollController::class, 'updateQuestion'])->name('update-question');

    Route::delete('/enrollment/assessment/{type}/delete/{id}', [EnrollController::class, 'deleteQuestion'])->name('delete-question');




//  ---------------  TEACHERS
    Route::get('/teachers', [FirebaseAuthController::class, 'teachers'])->name('teachers');

// -- ADD TEACHER
    Route::get('/AddTeacher', [FirebaseAuthController::class, 'showAddTeacher'])->name('AddTeacher');
    Route::post('/AddTeacher', [FirebaseAuthController::class, 'addTeacher'])->name('AddTeacher');

// -- EDIT TEACHER
    Route::get('/EditTeacher/{id}', [FirebaseAuthController::class, 'showEditTeacher'])->name('EditTeacher');
    Route::put('/UpdateTeacher/{id}', [FirebaseAuthController::class, 'editTeacher'])->name('EditTeacher');

// -- DELETE TEACHER
    Route::delete('/DeleteTeacher/{id}', [FirebaseAuthController::class, 'deleteTeacher'])->name('DeleteTeacher');

// --------------- STUDENTS
    Route::get('/students', [FirebaseAuthController::class, 'students'])->name('students');

    // -- ADD STUDENT
    Route::get('/AddStudent', [FirebaseAuthController::class, 'showAddStudent'])->name('AddStudent');
    Route::post('/AddStudent', [FirebaseAuthController::class, 'addStudent'])->name('AddStudent');

    // -- EDIT STUDENT
    Route::get('/EditStudent/{id}', [FirebaseAuthController::class, 'showEditStudent'])->name('EditStudent');
    Route::put('/UpdateStudent/{id}', [FirebaseAuthController::class, 'editStudent'])->name('EditStudent');

    // -- DELETE STUDENT
    Route::delete('/DeleteStudent/{id}', [FirebaseAuthController::class, 'deleteStudent'])->name('DeleteStudent');


// ---------------  ACCOUNTS
    Route::view('/accounts', 'mio.head.admin-panel', ['page' => 'accounts'])->name('accounts');

// ---------------  ADMINS
    Route::get('/admins', [FirebaseAuthController::class, 'admins'])->name('admins');

    // -- ADD ADMIN
    Route::get('/AddAdmin', [FirebaseAuthController::class, 'showAddAdmin'])->name('AddAdmin');
    Route::post('/AddAdmin', [FirebaseAuthController::class, 'addAdmin'])->name('AddAdmin');

    Route::get('/get-teacher/{id}', [FirebaseAuthController::class, 'getTeacherData'])->name('get.teacher');

    Route::get('/get-section/{id}', [FirebaseAuthController::class, 'getSectionData'])->name('get.section');

    // -- EDIT ADMIN
    Route::get('/EditAdmin/{id}', [FirebaseAuthController::class, 'showEditAdmin'])->name('EditAdmin');
    Route::put('/UpdateAdmin/{id}', [FirebaseAuthController::class, 'editAdmin'])->name('UpdateAdmin');

    // -- DELETE ADMIN
    Route::delete('/DeleteAdmin/{id}', [FirebaseAuthController::class, 'deleteAdmin'])->name(name: 'DeleteAdmin');

// ---------------  PARENTS
     Route::get('/parents', [FirebaseAuthController::class, 'parents'])->name('parents');

    // -- ADD PARENT
     Route::get('/AddParent', [FirebaseAuthController::class, 'showAddParent'])->name('AddParent');
     Route::post('/AddParent', [FirebaseAuthController::class, 'addParent'])->name('AddParent');
     Route::get('/get-student/{id}', [FirebaseAuthController::class, 'getStudentData'])->name('get.student');

    // -- EDIT ADMIN
    Route::get('/EditParent/{id}', [FirebaseAuthController::class, 'showEditParent'])->name('EditParent');
    Route::put('/UpdateParent/{id}', [FirebaseAuthController::class, 'editParent'])->name('EditParent');

    // -- DELETE PARENT
    Route::delete('/DeleteParent/{id}', [FirebaseAuthController::class, 'deleteParent'])->name(name: 'DeleteParent');

// ---------------  SUBJECTS
    Route::get('/subjects', [SubjectController::class, 'showGradeLevels'])->name('subjects');

    Route::get('/subjects/{grade}', [SubjectController::class, 'viewSubjects'])->name('ViewSubject');

    Route::get('/subjects/{grade}/AddSubject', [SubjectController::class, 'showAddSubjectForm'])->name('AddSubject');

    Route::post('/subjects/{grade}/AddSubject', [SubjectController::class, 'addSubject'])->name('StoreSubject');

    Route::get('/subjects/{grade}/EditSubject/{subjectId}', [SubjectController::class, 'showEditSubject'])->name('EditSubject');

    Route::put('/subjects/{grade}/EditSubject/{subjectId}', [SubjectController::class, 'editSubject'])->name('UpdateSubject');

    Route::delete('/subjects/{grade}/DeleteSubject/{subjectId}', [SubjectController::class, 'deleteSubject'])->name('DeleteSubject');


// ---------------  SCHEDULES
    Route::view('/schedules', 'mio.head.admin-panel', ['page' => 'schedules'])->name('schedules');
    Route::view('/AllSchedules', 'mio.head.admin-panel', ['page' => 'view-schedule'])->name('view-schedule');
    Route::view('/AddSchedule', 'mio.head.admin-panel', ['page' => 'add-schedule'])->name('add-schedule');
    Route::view('/EditSchedule', 'mio.head.admin-panel', ['page' => 'edit-schedule'])->name('edit-schedule');

// ---------------  SCHOOL
    Route::get('/school', [AnnouncementController::class, 'school'])->name('school');

    Route::view('/Calendar', 'mio.head.admin-panel', ['page' => 'view-calendar'])->name('view-calendar');
    Route::view('/AddCalendar', 'mio.head.admin-panel', ['page' => 'add-calendar'])->name('add-calendar');
    Route::view('/EditCalendar', 'mio.head.admin-panel', ['page' => 'edit-calendar'])->name('edit-calendar');

// -- DEPARTMENT
    Route::get('/department', [DepartmentController::class, 'departments'])->name('ViewDepartment');

    Route::get('/AddDepartment', [DepartmentController::class, 'showAddDepartment'])->name('AddDepartment');
    Route::post('/AddDepartment', [DepartmentController::class, 'addDepartment'])->name('StoreDepartment');

    Route::get('/EditDepartment/{id}', [DepartmentController::class, 'showEditDepartment'])->name('EditDepartment');

    Route::put('/UpdateDepartment/{id}', [DepartmentController::class, 'editDepartment'])->name('UpdateDepartment');

    Route::delete('/DeleteDepartment/{id}', [DepartmentController::class, 'deleteDepartment'])->name('DeleteDepartment');

// -- SCHOOL ANNOUNCEMENTS
    Route::get('/announcement/{id}', [AnnouncementController::class, 'viewAnnouncement'])->name('view-announcement');

    Route::get('/AddAnnouncement', [AnnouncementController::class, 'showAddAnnouncement'])->name('AddAnnouncement');
    Route::post('/AddAnnouncement', [AnnouncementController::class, 'addAnnouncement'])->name('StoreAnnouncement');

    Route::get('/EditAnnouncement/{id}', [AnnouncementController::class, 'showEditAnnouncement'])->name('EditAnnouncement');
    Route::put('/UpdateAnnouncement/{id}', [AnnouncementController::class, 'editAnnouncement'])->name('UpdateAnnouncement');

    Route::delete('/DeleteAnnouncement/{id}', [AnnouncementController::class, 'deleteAnnouncement'])->name('DeleteAnnouncement');


// -- SCHOOL YEAR
    Route::get('/schoolyear', [SchoolYearController::class, 'viewSchoolYear'])->name('view-schoolyear');

    Route::get('/CreateSchoolYear', [SchoolYearController::class, 'showCreateSchoolYear'])->name('CreateSchoolYear');
    Route::post('/CreateSchoolYear', [SchoolYearController::class, 'addSchoolYear'])->name('StoreSchoolYear');

    Route::get('/EditSchoolYear/{id}', [SchoolYearController::class, 'showEditSchoolYear'])->name('EditSchoolYear');
    Route::put('/UpdateSchoolYear/{id}', [SchoolYearController::class, 'editSchoolYear'])->name('UpdateSchoolYear');

    Route::get('/schoolyear/{id}/totals', [SchoolYearController::class, 'getTotalsBySchoolYear']);


// ---------------  SECTIONS
    Route::get('/section', [SectionController::class, 'sections'])->name('ViewSection');

    Route::get('/AddSection', [SectionController::class, 'showAddSection'])->name('AddSection');
    Route::post('/AddSection', [SectionController::class, 'addSection'])->name('StoreSection');

    Route::get('/EditSection/{id}', [SectionController::class, 'showEditSection'])->name('EditSection');
    Route::put('/UpdateSection/{id}', [SectionController::class, 'editSection'])->name('UpdateSection');

    Route::delete('/DeleteSection/{id}', [SectionController::class, 'deleteSection'])->name('DeleteSection');

// Emergency
    Route::view('/emergency', 'mio.head.admin-panel', ['page' => 'emergency'])->name('emergency');
});

Route::post('/trigger-emergency', [EmergencyController::class, 'triggerEmergency'])->name('trigger.emergency');


Route::prefix('mio/student')->middleware([AuthMiddleware::class, RoleBasedAccess::class . ':student'])->group(function () {

    Route::get('/dashboard', [StudentController::class, 'showDashboard'])->name('mio.student-panel');

    // Announcments

    Route::get('/subject/{subjectId}/announcements', [StudentController::class, 'showSubjectAnnouncements'])->name('mio.announcements');

    Route::get('/subject/{subjectId}/announcement/{announcementId}', [StudentController::class, 'showAnnouncementDetails'])->name('mio.announcements-body');

    Route::get('/calendar', function () {
        return view('mio.head.student-panel', ['page' => 'calendar']);
    })->name('mio.calendar');

    Route::get('/messages', [MessagingController::class, 'showInbox'])->name('mio.inbox');
    Route::post('/send-message', [MessagingController::class, 'sendMessage'])->name('mio.message-send');
     Route::get('/messages/{userId}/{contactId}', [MessagingController::class, 'getMessages']);
    Route::post('/edit-message/{messageId}', [MessagingController::class, 'editMessage'])->name('mio.message-edit');
    Route::post('/delete-message/{messageId}', [MessagingController::class, 'deleteMessage']);


    Route::get('/profile', [StudentController::class, 'showProfile'])->name('mio.student.profile');

// SUBJECT
    Route::prefix('subject')->name('mio.subject.')->group(function () {

        Route::get('/{subjectId}', [StudentController::class, 'showSubject'])->name('show-subject');

        Route::get('/{subjectId}/announcements', [StudentController::class, 'showSubjectAnnouncements'])->name('announcements');

        Route::get('/{subjectId}/announcements/{announcementId}', [StudentController::class, 'showAnnouncementDetails'])->name('announcements-body');

        Route::post('/{subjectId}/announcements/{announcementId}/reply', [StudentController::class, 'storeReply'])->name('storeReply');

        Route::delete('/{subjectId}/announcement/{announcementId}/reply/{replyId}', [StudentController::class, 'deleteReply'])->name('deleteReply');

        Route::get('/{subjectId}/people', [StudentController::class, 'showPeople'])->name('people');


    // ASSIGNMENT
        Route::get('/{subjectId}/assignment', [StudentController::class, 'showAssignment'])->name('assignment');
        Route::get('/{subjectId}/assignment/{assignmentId}', [StudentController::class, 'showAssignmentDetails'])->name('assignment-body');

        Route::post('/{subjectId}/assignment/{assignmentId}/submit', [StudentController::class, 'submitAssignment'])->name('assignment-submit');


    // SCORES
        Route::get('/{subjectId}/scores', [StudentController::class, 'showScores'])->name('scores');

        Route::get('/{subjectId}/modules', [StudentController::class, 'showModules'])->name('modules');
        Route::get('/{subjectId}/modules/{moduleIndex}', [StudentController::class, 'showModuleBody'])->name('module-body');

    // QUIZZES
        Route::get('/{subjectId}/quiz', [StudentController::class, 'showQuizzes'])->name('quiz');

         Route::get('/{subjectId}/quiz/{quizId}', [StudentController::class, 'showQuizDetails'])->name('quiz-body');

         Route::post('/{subjectId}/quiz/{quizId}/submit', [StudentController::class, 'submitQuiz'])->name('quiz-submit');

         Route::post('/{subjectId}/quiz/{quizId}/save-answer/{questionId}', [StudentController::class, 'saveAnswer']) ->name('quiz-save-answer');




    });

});


// MIO - TEACHER PANEL
Route::prefix('mio/teacher')->middleware(
    [AuthMiddleware::class, RoleBasedAccess::class . ':teacher']
)->group(function () {

    Route::get('/dashboard', [TeacherController::class, 'showDashboard'])->name('mio.teacher-panel');

    // Announcments
    Route::get('/subject/{subjectId}/announcements', [TeacherController::class, 'showSubjectAnnouncements'])->name('mio.teacher-announcements');
    Route::get('/subject/{subjectId}/announcement/{announcementId}', [TeacherController::class, 'showAnnouncementDetails'])->name('mio.teacher-announcements-body');


    Route::get('/calendar', function () {
        return view('mio.head.teacher-panel', ['page' => 'calendar']);
    })->name('mio.teacher-calendar');

    // INBOX MESSAGING
       Route::get('/messages', [MessagingController::class, 'showTeacherInbox'])->name('mio.teacher-inbox');

    Route::post('/send-message', [MessagingController::class, 'sendTeacherMessage'])->name('mio.teacher-message-send');

     Route::get('/messages/{userId}/{contactId}', [MessagingController::class, 'getTeacherMessages'])->name('mio.teacher-inbox-message');

    Route::get('/profile', [TeacherController::class, 'showProfile'])->name('mio.teacher-profile');

    Route::get('/settings', [SettingsController::class, 'showSettings'])->name('mio.teacher-settings');

    Route::prefix('subject')->name('mio.subject-teacher.')->group(function () {

        Route::get('/{subjectId}', [TeacherController::class, 'showSubject'])->name('show-subject');

    // ANNOUNCEMENTS
        Route::get('/{subjectId}/announcements', [TeacherController::class, 'showSubjectAnnouncements'])->name('announcement');

        Route::get('/{subjectId}/announcements/{announcementId}', [TeacherController::class, 'showAnnouncementDetails'])->name('announcements-body');

        Route::post('/{subjectId}/announcements/{announcementId}/reply', [TeacherController::class, 'storeReply'])->name('storeReply');

        Route::delete('/{subjectId}/announcement/{announcementId}/reply/{replyId}', [TeacherController::class, 'deleteReply'])->name('deleteReply');

        Route::put('/{subjectId}/announcement/{announcementId}/edit', [TeacherController::class, 'editAnnouncement'])->name('editAnnouncement');

    // ASSIGNMENT
        Route::get('/{subjectId}/assignment', [TeacherController::class, 'showAssignment'])->name('assignment');
        Route::post('/{subjectId}/assignment/add', [TeacherController::class, 'addAssignment'])->name('addAssignment');
        Route::delete('/{subjectId}/assignment/{assignmentId}/delete', [TeacherController::class, 'deleteAssignment'])->name('deleteAssignment');


        Route::get('/{subjectId}/assignment/{assignmentId}', [TeacherController::class, 'showAssignmentDetails'])->name('assignment-body');
        Route::put('/{subjectId}/assignment/{assignmentId}/edit', [TeacherController::class, 'editAssignment'])->name('assignment.edit');
        // Save or update review for a student's assignment
        Route::post('/{subjectId}/assignment/{assignmentId}/review/{studentId}', [TeacherController::class, 'saveReview'])->name('assignment.review-save');

    // QUIZZES
        Route::get('/{subjectId}/quiz', [TeacherController::class, 'showQuizzes'])->name('quiz');

        Route::get('/{subjectId}/quiz/add-quiz', [TeacherController::class, 'addAcadsQuiz'])->name('add-acads-quiz');

        Route::post('/{subjectId}/quiz/store-quiz', [TeacherController::class, 'storeQuiz'])->name('store-acads-quiz');

        Route::delete('/{subjectId}/quiz/{quizId}/delete', [TeacherController::class, 'deleteQuiz'])->name('deleteQuiz');

        Route::get('/{subjectId}/quiz/{quizId}/edit-quiz', [TeacherController::class, 'showEditAcadsQuiz'])->name('edit-acads-quiz');

        Route::put('/{subjectId}/quiz/{quizId}/update-quiz', [TeacherController::class, 'updateAcadsQuiz'])->name('update-acads-quiz');

         Route::get('/{subjectId}/quiz/{quizId}', [TeacherController::class, 'showQuizDetails'])->name('quiz-body');

          Route::post('/{subjectId}/quiz/{quizId}/attempt/update', [TeacherController::class, 'updateEditAttempt'])->name('updateAttempt');




    // SCORES
        Route::get('/{subjectId}/scores', [TeacherController::class, 'showScores'])->name('scores');

        Route::get('/{subjectId}/scores/report', [TeacherController::class, 'generateReport'])->name('scores-pdf');




    // people

        Route::get('/{subjectId}/people', [TeacherController::class, 'showPeople'])->name('people');

        Route::get('/{subjectId}/modules', [TeacherController::class, 'showModules'])->name('modules');
        Route::get('/{subjectId}/modules/{moduleIndex}', [TeacherController::class, 'showModuleBody'])->name('module-body');

    // ATTENDANCE
        Route::get('/{subjectId}/attendance', [TeacherController::class, 'showAttendance'])->name('attendance');
        Route::post('/{subjectId}/attendance/{attendanceId}', [TeacherController::class, 'updateAttendance'])->name('attendance.update');
        Route::post('/{subjectId}/attendance', [TeacherController::class, 'storeAttendance'])->name('attendance.store');
    });

});


// LOGIN ROUTES
    Route::get('mio/login', [LoginController::class, 'loginForm'])->name('mio.login');
    Route::post('/user-login', [LoginController::class, 'login']);

    Route::get('mio/logout', [LoginController::class, 'logout'])->name('logout');

// FORGOT PASSWORD
    Route::get('/forgot-password', [LoginController::class, 'showForgotForm'])->name('forgot.form');

    Route::post('/forgot-password', [LoginController::class, 'sendResetLink'])->name('forgot.send');

    Route::get('/verify-email', function () {
        return view('mio.dashboard.verify-email');
    })->name('mio.verify-email');
    Route::get('/mio/check-verification', [LoginController::class, 'checkVerification'])->name('mio.check-verification');

    Route::post('/resend-verification', [LoginController::class, 'resendVerification'])->name('mio.resend-verification');



// MIO - PARENT PANEL
Route::prefix('mio/parent')->middleware(
    [AuthMiddleware::class, RoleBasedAccess::class . ':parent']
)->group(function () {

    Route::get('/dashboard', function () {
        return view('mio.head.parent-panel', ['page' => 'parent-dashboard']);
    })->name('mio.parent-panel');

    Route::get('/calendar', function () {
        return view('mio.head.parent-panel', ['page' => 'parent-calendar']);
    })->name('mio.parent-calendar');

    Route::get('/inbox', function () {
        return view('mio.head.parent-panel', ['page' => 'parent-inbox']);
    })->name('mio.parent-inbox');

    Route::get('/profile', function () {
        return view('mio.head.parent-panel', ['page' => 'parent-profile']);
    })->name('mio.parent-profile');

    // Route::get('/subject', function () {
    //     return view('mio.head.student-panel', ['page' => 'subject']);
    // })->name('mio.subject');

});



