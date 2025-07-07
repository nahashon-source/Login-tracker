<?php

use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;

Route::post('/import-users', [ImportController::class, 'importUsers'])->name('import.users');
Route::post('/import-sign-ins', [ImportController::class, 'importSignIns'])->name('import.sign_ins');