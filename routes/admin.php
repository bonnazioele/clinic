<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ClinicController as AdminClinicController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\ClinicTypeController as AdminClinicTypeController;
use App\Http\Controllers\Admin\DashboardController;


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
Route::get('services', [AdminServiceController::class, 'index'])->name('services.index');
Route::resource('services', AdminServiceController::class)->names([
    'create' => 'services.create',
    'store' => 'services.store',
    'edit' => 'services.edit',
    'update' => 'services.update',
    'destroy' => 'services.destroy'
])->except(['show', 'index']);
Route::post('services/{service}/toggle-status', [AdminServiceController::class, 'toggleStatus'])->name('services.toggle-status');


// Clinic Type management routes
Route::resource('clinic-types', AdminClinicTypeController::class);
