<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/mio/login', function () {
    return view('mio.user-access.login');
})->name('mio.login');

Route::get('/mio/dashboard', function () {
    return view('mio.dashboard.dashboard');
})->name('mio.dashboard');

Route::get('/mio/admin/login', function () {
    return view('mio.admin-access.login');
})->name('mio.admin.login');
