<?php

use Illuminate\Support\Facades\Route;

// CMS
Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::get('/enroll', function () {
    return view('enroll');
})->name('enroll');


Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/program', function () {
    return view('program');
})->name('program');

Route::get('/campus', function () {
    return view('campus');
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
