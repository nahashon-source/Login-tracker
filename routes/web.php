<?php 


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityReportController;

// Dashboard + Core
Route::get('/dashboard', [UserController::class, 'index'])->name('dashboard');

// Logged-in / Not logged-in user lists
Route::get('/users/logged-in', [UserController::class, 'loggedInUsers'])->name('users.logged-in');
Route::get('/users/not-logged-in', [UserController::class, 'notLoggedInUsers'])->name('users.not-logged-in');

// Users Resource (must come after specific routes)
Route::resource('users', UserController::class);

// Activity Report
Route::get('/activity-report', [ActivityReportController::class, 'userActivityReport'])->name('activity.report');

// Imports View
Route::get('/imports', function () {
    return view('imports.index');
})->name('imports.index');

// CSV Import Endpoints
Route::post('/import-users', [ImportController::class, 'importUsers'])->name('import.users');
Route::post('/import-sign-ins', [ImportController::class, 'importSignIns'])->name('import.sign_ins');
