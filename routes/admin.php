<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ClinicController as AdminClinicController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\ClinicTypeController as AdminClinicTypeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Livewire\Admin\Services\Index as ServicesIndex;

// All routes in this file are protected by 'auth' and 'can:access-admin-panel' via RouteServiceProvider


// Admin dashboard route for system admin users
Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

// Clinic approval/rejection routes
Route::post('clinics/{id}/approve', [AdminClinicController::class, 'approve'])->name('clinics.approve');
Route::post('clinics/{id}/reject', [DashboardController::class, 'rejectClinic'])->name('clinics.reject');
Route::post('add-admin', [DashboardController::class, 'addAdmin'])->name('add-admin');

// Clinic management routes
Route::resource('clinics', AdminClinicController::class);

// Service management routes
Route::get('services', ServicesIndex::class)->name('services.index');
Route::resource('services', AdminServiceController::class)->except(['show', 'index']);

// Clinic Type management routes
Route::resource('clinic-types', AdminClinicTypeController::class);
