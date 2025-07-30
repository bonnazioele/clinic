<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ClinicController as AdminClinicController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\DashboardController;

// All routes in this file are protected by 'auth' and 'can:access-admin-panel' via RouteServiceProvider


// Admin dashboard route for system admin users
Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

Route::resource('clinics', AdminClinicController::class);
// Route for Add Clinic button
Route::get('clinics/create', [AdminClinicController::class, 'create'])->name('clinics.create');
// Route for Add Service button
Route::get('services/create', [AdminServiceController::class, 'create'])->name('services.create');
Route::resource('services', AdminServiceController::class)->except('show');
