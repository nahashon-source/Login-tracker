<?php

use App\Http\Controllers\ImportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/import-users', [ImportController::class, 'importUsers'])->name('import.users');
Route::post('/import-sign-ins', [ImportController::class, 'importSignIns'])->name('import.sign_ins');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');