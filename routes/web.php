<?php

use Illuminate\Support\Facades\Route;


Route::get('/home', function () {
    return view('landing');
})->name('landing');

Route::get('/enroll', function () {
    return view('enroll');
})->name('enroll');


Route::get('/about', function () {
    return view('about');
})->name('about');
