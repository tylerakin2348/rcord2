<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
});

Route::get('/recordings', function () {
    return view('app');
});

Route::get('/profile', function () {
    return view('app');
});

Route::get('/system-info', function () {
    return view('app');
});

Route::get('/system-info/storage', function () {
    return view('app');
});

Route::get('/system-info/activity', function () {
    return view('app');
});

Route::get('/system-info/sessions', function () {
    return view('app');
});
