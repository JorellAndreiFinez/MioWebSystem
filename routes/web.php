<?php

use Illuminate\Support\Facades\Route;

// CMS
Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::get('/enroll', function () {
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

// ADMIN ACCESS

Route::get('/mio/admin/login', function () {
    return view('mio.admin-access.login');
})->name('mio.admin.login');

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
