<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AppointmentController;
// use App\Http\Controllers\Admin\ClinicController as AdminClinicController;
// use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Secretary\AppointmentController as SecAppt;
use App\Http\Controllers\Secretary\DoctorController as SecDoctor;
use App\Http\Controllers\NotificationsController;

// Landing / welcome page
Route::get('/', [DashboardController::class, 'welcome'])->name('welcome');
Route::get('/welcome', [DashboardController::class, 'welcome']);

// Clinics directory (publicly accessible)
Route::get('/clinics', [ClinicController::class, 'index'])->name('clinics.index');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Auth::routes(['verify' => true]);

// Custom logout to ensure proper session cleanup
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Patient (Authenticated) Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard (landing page after login)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile management
    Route::get('/profile', [ProfileController::class,'show'])->name('profile.show');
     Route::get('/profile/edit', [ProfileController::class,'edit'])->name('profile.edit');
     Route::post('/profile', [ProfileController::class,'update'])->name('profile.update');
     
    // Appointment booking
    Route::get('/appointments/create', [AppointmentController::class, 'create'])
         ->name('appointments.create');
    Route::post('/appointments', [AppointmentController::class, 'store'])
         ->name('appointments.store');
    Route::get('/appointments', [AppointmentController::class, 'index'])
         ->name('appointments.index');
    Route::delete('appointments/{appointment}',[AppointmentController::class,'destroy'])
          ->name('appointments.destroy');

     Route::get('notifications', [NotificationsController::class,'index'])
         ->name('notifications.index');
    Route::post('notifications/mark-read', [NotificationsController::class,'markAllRead'])
         ->name('notifications.markRead');
});

     