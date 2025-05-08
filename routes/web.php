<?php

use App\Http\Controllers\admin\CmsController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Auth\FirebaseAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\Enrollment\EnrollController;
use App\Http\Controllers\FirebaseConnectionController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SubjectController;
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
    Route::view('/login', 'enrollment-panel.enrollment-panel', ['page' => 'enroll-login'])->name('enroll-login');

    Route::view('/send-otp', 'enrollment-panel.enrollment-panel', ['page' => 'send-otp'])->name('send-otp');

    Route::post('/signup', [EnrollController::class, 'signup'])->name('enroll.signup');
    Route::post('/login', [EnrollController::class, 'login'])->name('enroll.login');

    Route::middleware([EnrollAuthMiddleware::class])->group(function () {
        Route::view('/dashboard', 'enrollment-panel.enrollment-panel', ['page' => 'enroll-dashboard'])->name('enroll-dashboard');
        Route::post('/logout', [EnrollController::class, 'logout'])->name('enroll.logout');
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

// ----------------  PID

    Route::get('/PID', [CmsController::class, 'showCMS'])->name('ViewCMS');
    Route::get('/admin/cms/{id}', [CmsController::class, 'show'])->name('cms.show');





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

// MIO - STUDENT PANEL
Route::prefix('mio/student')->middleware(
    [AuthMiddleware::class, RoleBasedAccess::class . ':student']
)->group(function () {

    Route::get('/dashboard', function () {
        return view('mio.head.student-panel', ['page' => 'dashboard']);
    })->name('mio.student-panel');

    Route::get('/calendar', function () {
        return view('mio.head.student-panel', ['page' => 'calendar']);
    })->name('mio.calendar');

    Route::get('/inbox', function () {
        return view('mio.head.student-panel', ['page' => 'inbox']);
    })->name('mio.inbox');

    Route::get('/profile', function () {
        return view('mio.head.student-panel', ['page' => 'profile']);
    })->name('mio.profile');

    Route::get('/subject', function () {
        return view('mio.head.student-panel', ['page' => 'subject']);
    })->name('mio.subject');

});

// LOGIN ROUTES
    Route::get('mio/login', [LoginController::class, 'loginForm'])->name('mio.login');
    Route::post('/user-login', [LoginController::class, 'login']);

    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

// FORGOT PASSWORD
    Route::get('/forgot-password', [LoginController::class, 'showForgotForm'])->name('forgot.form');

    Route::post('/forgot-password', [LoginController::class, 'sendResetLink'])->name('forgot.send');

    Route::get('/verify-email', function () {
        return view('mio.dashboard.verify-email');
    })->name('mio.verify-email');
    Route::get('/mio/check-verification', [LoginController::class, 'checkVerification'])->name('mio.check-verification');

    Route::post('/resend-verification', [LoginController::class, 'resendVerification'])->name('mio.resend-verification');





// SUBJECT

Route::prefix('mio/sample1')->name('mio.subject.')->group(function () {

    Route::view('/announcement', 'mio.head.student-panel', ['page' => 'announcement'])->name('announcement');
    Route::view('/announcement/sample1', 'mio.head.student-panel', ['page' => 'announcement-body'])->name('announcement-body');

    Route::view('/assignment', 'mio.head.student-panel', ['page' => 'assignment'])->name('assignment');

    Route::view('/assignment/sample1', 'mio.head.student-panel', ['page' => 'assignment-body'])->name('assignment-body');

    Route::view('/scores', 'mio.head.student-panel', ['page' => 'scores'])->name('scores');

    Route::view('/module', 'mio.head.student-panel', ['page' => 'module'])->name('module');

    Route::view('/module/sample1', 'mio.head.student-panel', ['page' => 'module-body'])->name('module-body');
});


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


// MIO - TEACHER PANEL
Route::prefix('mio/teacher')->middleware(
    [AuthMiddleware::class, RoleBasedAccess::class . ':teacher']
)->group(function () {

    Route::get('/dashboard', function () {
        return view('mio.head.teacher-panel', ['page' => 'teacher-dashboard']);
    })->name('mio.teacher-panel');

    Route::get('/calendar', function () {
        return view('mio.head.teacher-panel', ['page' => 'calendar']);
    })->name('mio.teacher-calendar');

    Route::get('/inbox', function () {
        return view('mio.head.teacher-panel', ['page' => 'inbox']);
    })->name('mio.teacher-inbox');

    Route::get('/profile', function () {
        return view('mio.head.teacher-panel', ['page' => 'profile']);
    })->name('mio.teacher-profile');

    Route::get('/subject', function () {
        return view('mio.head.teacher-panel', ['page' => 'subject']);
    })->name('mio.subject');

});

require __DIR__.'/mobile.php';
