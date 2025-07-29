<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ClinicController as AdminClinicController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;

// All routes in this file are protected by 'auth' and 'can:access-admin-panel' via RouteServiceProvider

// Admin dashboard route for system admin users
Route::get('/', [AdminClinicController::class, 'index'])->name('admin.dashboard');

Route::resource('clinics', AdminClinicController::class);
Route::resource('services', AdminServiceController::class)->except('show');
