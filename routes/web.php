<?php

use Illuminate\Support\Facades\Route;

// CMS
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

// DASHBOARD

Route::get('/mio/login', function () {
    return view('mio.user-access.login');
})->name('mio.login');

Route::get('/mio/dashboard', function () {
    return view('mio.head.dashboard');
})->name('mio.dashboard');

Route::get('/mio/sample', function () {
    return view('mio.head.subject');
})->name('mio.subject');

Route::get('/mio/calendar', function () {
    return view('mio.head.calendar');
})->name('mio.calendar');

Route::get('/mio/inbox', function () {
    return view('mio.head.inbox');
})->name('mio.inbox');

Route::get('/mio/profile', function () {
    return view('mio.head.profile');
})->name('mio.profile');

// ADMIN ACCESS

Route::get('/mio/admin/login', function () {
    return view('mio.admin-access.login');
})->name('mio.admin.login');

Route::get('/mio/admin/dashboard', function () {
    return view('mio.head.admin-panel', ['page' => 'dashboard']);
})->name('mio.admin-panel');

// TEACHERS

Route::get('/mio/admin/teachers', function () {
    return view('mio.head.admin-panel', ['page' => 'teachers']);
})->name('mio.teachers');

Route::get('/mio/admin/AddTeacher', function () {
    return view('mio.head.admin-panel', ['page' => 'add-teacher']);
})->name('mio.add-teacher');

Route::get('/mio/admin/EditTeacher', function () {
    return view('mio.head.admin-panel', ['page' => 'edit-teacher']);
})->name('mio.edit-teacher');

// STUDENTS

Route::get('/mio/admin/students', function () {
    return view('mio.head.admin-panel', ['page' => 'students']);
})->name('mio.students');

Route::get('/mio/admin/AddStudent', function () {
    return view('mio.head.admin-panel', ['page' => 'add-student']);
})->name('mio.add-student');

Route::get('/mio/admin/EditStudent', function () {
    return view('mio.head.admin-panel', ['page' => 'edit-student']);
})->name('mio.edit-student');

// ACCOUNT

Route::get('/mio/admin/accounts', function () {
    return view('mio.head.admin-panel', ['page' => 'accounts']);
})->name('mio.accounts');

// ADMINS

Route::get('/mio/admin/admins', function () {
    return view('mio.head.admin-panel', ['page' => 'admin']);
})->name('mio.admin');

Route::get('/mio/admin/AddAdmin', function () {
    return view('mio.head.admin-panel', ['page' => 'add-admin']);
})->name('mio.add-admin');

Route::get('/mio/admin/EditAdmin', function () {
    return view('mio.head.admin-panel', ['page' => 'edit-admin']);
})->name('mio.edit-admin');

// PARENTS

Route::get('/mio/admin/parents', function () {
    return view('mio.head.admin-panel', ['page' => 'parent']);
})->name('mio.parent');

Route::get('/mio/admin/AddParent', function () {
    return view('mio.head.admin-panel', ['page' => 'add-parent']);
})->name('mio.add-parent');

Route::get('/mio/admin/EditParent', function () {
    return view('mio.head.admin-panel', ['page' => 'edit-parent']);
})->name('mio.edit-parent');

// SUBJECTS

Route::get('/mio/admin/subjects', function () {
    return view('mio.head.admin-panel', ['page' => 'subjects']);
})->name('mio.subjects');

Route::get('/mio/admin/AllSubjects', function () {
    return view('mio.head.admin-panel', ['page' => 'view-subject']);
})->name('mio.view-subject');

Route::get('/mio/admin/AddSubjects', function () {
    return view('mio.head.admin-panel', ['page' => 'add-subject']);
})->name('mio.add-subject');

Route::get('/mio/admin/EditSubjects', function () {
    return view('mio.head.admin-panel', ['page' => 'edit-subject']);
})->name('mio.edit-subject');

// SCHEDULES

Route::get('/mio/admin/schedules', function () {
    return view('mio.head.admin-panel', ['page' => 'schedules']);
})->name('mio.schedules');

Route::get('/mio/admin/AllSchedules', function () {
    return view('mio.head.admin-panel', ['page' => 'view-schedule']);
})->name('mio.view-schedule');

Route::get('/mio/admin/AddSchedule', function () {
    return view('mio.head.admin-panel', ['page' => 'add-schedule']);
})->name('mio.add-schedule');

Route::get('/mio/admin/EditSchedule', function () {
    return view('mio.head.admin-panel', ['page' => 'edit-schedule']);
})->name('mio.edit-schedule');

// SCHOOL

Route::get('/mio/admin/school', function () {
    return view('mio.head.admin-panel', ['page' => 'school']);
})->name('mio.school');

//
Route::get('/mio/admin/AllCalendar', function () {
    return view('mio.head.admin-panel', ['page' => 'view-calendar']);
})->name('mio.view-calendar');

Route::get('/mio/admin/AddCalendar', function () {
    return view('mio.head.admin-panel', ['page' => 'add-calendar']);
})->name('mio.add-calendar');

Route::get('/mio/admin/EditCalendar', function () {
    return view('mio.head.admin-panel', ['page' => 'edit-calendar']);
})->name('mio.edit-calendar');

//

Route::get('/mio/admin/AllDepartment', function () {
    return view('mio.head.admin-panel', ['page' => 'view-department']);
})->name('mio.view-department');

Route::get('/mio/admin/AddDepartment', function () {
    return view('mio.head.admin-panel', ['page' => 'add-department']);
})->name('mio.add-department');

Route::get('/mio/admin/EditDepartment', function () {
    return view('mio.head.admin-panel', ['page' => 'edit-department']);
})->name('mio.edit-department');

//

Route::get('/mio/admin/ViewAnnouncement', function () {
    return view('mio.head.admin-panel', ['page' => 'view-announcement']);
})->name('mio.view-announcement');

Route::get('/mio/admin/AddAnnouncement', function () {
    return view('mio.head.admin-panel', ['page' => 'add-announcement']);
})->name('mio.add-announcement');

Route::get('/mio/admin/EditAnnouncement', function () {
    return view('mio.head.admin-panel', ['page' => 'edit-announcement']);
})->name('mio.edit-announcement');

// Emergency

Route::get('/mio/admin/emergency', function () {
    return view('mio.head.admin-panel', ['page' => 'emergency']);
})->name('mio.emergency');

// SUBJECT

Route::get('/mio/sample/announcement', function () {
    return view('mio.head.announcement');
})->name('mio.subject.announcement');

Route::get('/mio/sample/announcement/sample1', function () {
    return view('mio.head.announcement-content');
})->name('mio.subject.announcement-content');

Route::get('/mio/sample/assignment', function () {
    return view('mio.head.assignment');
})->name('mio.subject.assignment');

Route::get('/mio/sample/assignment/sample1', function () {
    return view('mio.head.assignment-content');
})->name('mio.assignment.assignment-content');

Route::get('/mio/sample/scores', function () {
    return view('mio.head.scores');
})->name('mio.subject.scores');

Route::get('/mio/sample/module', function () {
    return view('mio.head.module');
})->name('mio.subject.module');

Route::get('/mio/sample/module/sample1', function () {
    return view('mio.head.module-content');
})->name('mio.subject.module-content');

// ADMISSION - CMS
Route::get('/admission/enrollment', function () {
    return view('layouts.enrollment');
})->name('admission.enrollment');

Route::get('/admission/assessment-guide', function () {
    return view('layouts.assessment-guide');
})->name('admission.assess-guide');

Route::get('/admission/payment-guide', function () {
    return view('layouts.payment-guide');
})->name('admission.payment-guide');
