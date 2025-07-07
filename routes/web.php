<?php 


use App\Http\Controllers\ImportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/import-users', [ImportController::class, 'importUsers'])->name('import.users');
Route::post('/import-sign-ins', [ImportController::class, 'importSignIns'])->name('import.sign_ins');

Route::get('/dashboard', [UserController::class, 'index'])->name('dashboard');
Route::resource('users', UserController::class);

Route::get('/imports', function () {
    return view('imports.index');
})->name('imports.index');
