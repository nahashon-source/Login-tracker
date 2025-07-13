<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityReportController;
use App\Http\Controllers\DashboardController;

// Redirect root URL to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard route (use the index method)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Logged-in users list
Route::get('/users/logged-in', [UserController::class, 'loggedInUsers'])->name('users.logged-in');

// Not logged-in users list
Route::get('/users/not-logged-in', [UserController::class, 'notLoggedInUsers'])->name('users.not-logged-in');

// User Activity Report
Route::get('/activity-report', [ActivityReportController::class, 'userActivityReport'])->name('activity.report');

// Imports view page
Route::get('/imports', function () {
    return view('imports.index');
})->name('imports.index');

// CSV import endpoints
Route::post('/import/users', [ImportController::class, 'importUsers'])->name('import.users');
Route::post('/import/sign-ins', [ImportController::class, 'importSignIns'])->name('import.sign_ins');
Route::post('/import/applications', [ImportController::class, 'importApplications'])->name('import.applications');

// User management resource routes (show, create, edit, delete, etc.)
Route::resource('users', UserController::class);
// routes/web.php
