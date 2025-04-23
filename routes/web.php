<?php

use Illuminate\Support\Facades\Route;

// CMS
Route::prefix('')->group(function () {

    Route::get('/', function () {
        return view('landing');
    })->name('landing');

    Route::get('/admission', function () {
        return view('layouts.enroll');
    })->name('enroll');

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

// ADMIN LOGIN

// Route::get('/mio/admin/login', function () {
//     return view('mio.admin-access.login');
// })->name('mio.admin.login');



// MIO - ADMIN PANEL
Route::prefix('mio/admin1')->name('mio.')->group(function () {

    Route::view('/dashboard', 'mio.head.admin-panel', ['page' => 'dashboard'])->name('admin-panel');

    // TEACHERS
    Route::view('/teachers', 'mio.head.admin-panel', ['page' => 'teachers'])->name('teachers');
    Route::view('/AddTeacher', 'mio.head.admin-panel', ['page' => 'add-teacher'])->name('add-teacher');
    Route::view('/EditTeacher', 'mio.head.admin-panel', ['page' => 'edit-teacher'])->name('edit-teacher');

    // STUDENTS
    Route::view('/students', 'mio.head.admin-panel', ['page' => 'students'])->name('students');
    Route::view('/AddStudent', 'mio.head.admin-panel', ['page' => 'add-student'])->name('add-student');
    Route::view('/EditStudent', 'mio.head.admin-panel', ['page' => 'edit-student'])->name('edit-student');

    // ACCOUNTS
    Route::view('/accounts', 'mio.head.admin-panel', ['page' => 'accounts'])->name('accounts');

    // ADMINS
    Route::view('/admins', 'mio.head.admin-panel', ['page' => 'admin'])->name('admin');
    Route::view('/AddAdmin', 'mio.head.admin-panel', ['page' => 'add-admin'])->name('add-admin');
    Route::view('/EditAdmin', 'mio.head.admin-panel', ['page' => 'edit-admin'])->name('edit-admin');

    // PARENTS
    Route::view('/parents', 'mio.head.admin-panel', ['page' => 'parent'])->name('parent');
    Route::view('/AddParent', 'mio.head.admin-panel', ['page' => 'add-parent'])->name('add-parent');
    Route::view('/EditParent', 'mio.head.admin-panel', ['page' => 'edit-parent'])->name('edit-parent');

    // SUBJECTS
    Route::view('/subjects', 'mio.head.admin-panel', ['page' => 'subjects'])->name('subjects');
    Route::view('/AllSubjects', 'mio.head.admin-panel', ['page' => 'view-subject'])->name('view-subject');
    Route::view('/AddSubjects', 'mio.head.admin-panel', ['page' => 'add-subject'])->name('add-subject');
    Route::view('/EditSubjects', 'mio.head.admin-panel', ['page' => 'edit-subject'])->name('edit-subject');

    // SCHEDULES
    Route::view('/schedules', 'mio.head.admin-panel', ['page' => 'schedules'])->name('schedules');
    Route::view('/AllSchedules', 'mio.head.admin-panel', ['page' => 'view-schedule'])->name('view-schedule');
    Route::view('/AddSchedule', 'mio.head.admin-panel', ['page' => 'add-schedule'])->name('add-schedule');
    Route::view('/EditSchedule', 'mio.head.admin-panel', ['page' => 'edit-schedule'])->name('edit-schedule');

    // SCHOOL
    Route::view('/school', 'mio.head.admin-panel', ['page' => 'school'])->name('school');
    Route::view('/AllCalendar', 'mio.head.admin-panel', ['page' => 'view-calendar'])->name('view-calendar');
    Route::view('/AddCalendar', 'mio.head.admin-panel', ['page' => 'add-calendar'])->name('add-calendar');
    Route::view('/EditCalendar', 'mio.head.admin-panel', ['page' => 'edit-calendar'])->name('edit-calendar');

    Route::view('/AllDepartment', 'mio.head.admin-panel', ['page' => 'view-department'])->name('view-department');
    Route::view('/AddDepartment', 'mio.head.admin-panel', ['page' => 'add-department'])->name('add-department');
    Route::view('/EditDepartment', 'mio.head.admin-panel', ['page' => 'edit-department'])->name('edit-department');

    Route::view('/ViewAnnouncement', 'mio.head.admin-panel', ['page' => 'view-announcement'])->name('view-announcement');
    Route::view('/AddAnnouncement', 'mio.head.admin-panel', ['page' => 'add-announcement'])->name('add-announcement');
    Route::view('/EditAnnouncement', 'mio.head.admin-panel', ['page' => 'edit-announcement'])->name('edit-announcement');

    // Emergency
    Route::view('/emergency', 'mio.head.admin-panel', ['page' => 'emergency'])->name('emergency');
});

// MIO - STUDENT PANEL
Route::prefix('mio/student1')->group(function () {

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

    Route::get('/login', function () {
        return view('mio.user-access.login');
    })->name('mio.login');
});

Route::get('mio/login', function () {
    return view('mio.user-access.login');
})->name('mio.login');

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
Route::prefix('mio/parent1')->group(function () {

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
Route::prefix('mio/teacher1')->group(function () {

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
