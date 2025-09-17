<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QueueController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\Admin\ClinicController as AdminClinicController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\DoctorController as AdminDoctorController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Secretary\AppointmentController as SecAppt;
use App\Http\Controllers\Secretary\DoctorController as SecDoctor;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\Admin\SecretaryController as AdminSecretaryController;
use App\Http\Controllers\Auth\SecretaryRegisterController;


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

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
    Route::get('/appointments/{appointment}/edit', [AppointmentController::class, 'edit'])
         ->name('appointments.edit');
    Route::put('/appointments/{appointment}', [AppointmentController::class, 'update'])
         ->name('appointments.update');
    Route::delete('appointments/{appointment}',[AppointmentController::class,'destroy'])
          ->name('appointments.destroy');

     Route::get('notifications', [NotificationsController::class,'index'])
         ->name('notifications.index');
    Route::post('notifications/mark-read', [NotificationsController::class,'markAllRead'])
         ->name('notifications.markRead');

    // Queue management
    Route::get('/queue/status', [QueueController::class, 'status'])->name('queue.status');
    Route::get('/queue/status/{entry}', [QueueController::class, 'status'])->name('queue.status.entry');
    Route::post('/queue/join/{clinic}', [QueueController::class, 'join'])->name('queue.join');
    Route::post('/queue/leave/{entry}', [QueueController::class, 'leave'])->name('queue.leave');
});

/*
|--------------------------------------------------------------------------
| Admin (Authenticated + is_admin) Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
     ->middleware(['auth', AdminMiddleware::class])
     ->name('admin.')
     ->group(function () {
         // Admin dashboard
         Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

         // Manage clinics
         Route::resource('clinics', AdminClinicController::class);
         Route::post('clinics/{clinic}/approve', [AdminClinicController::class, 'approve'])->name('clinics.approve');
         Route::post('clinics/{clinic}/decline', [AdminClinicController::class, 'decline'])->name('clinics.decline');

         // Manage services (no show route needed)
         Route::resource('services', AdminServiceController::class)
              ->except('show');

         // Secretaries overview (read-only)
         Route::get('secretaries', [AdminSecretaryController::class, 'index'])->name('secretaries.index');

         // Doctors overview (read-only) + profile
         Route::get('doctors', [AdminDoctorController::class, 'index'])->name('doctors.index');
         Route::get('doctors/{doctor}', [AdminDoctorController::class, 'show'])->name('doctors.show');

         // Users overview (read-only)
         Route::get('users', [AdminUserController::class, 'index'])->name('users.index');

     });

Route::prefix('secretary')
     ->middleware(['auth', \App\Http\Middleware\SecretaryMiddleware::class])
     ->name('secretary.')
     ->group(function(){
         // Secretary dashboard
         Route::get('/dashboard', [\App\Http\Controllers\Secretary\DashboardController::class,'index'])->name('dashboard');
         // Queue overview
         Route::get('/queues', [\App\Http\Controllers\Secretary\QueueController::class,'overview'])->name('queue.overview');
         // existing appointments…
         Route::resource('appointments', SecAppt::class)
              ->only(['index','edit','update','destroy']);

         // Create appointments for patients
         Route::get('/appointments/create', [SecAppt::class, 'create'])->name('appointments.create');
         Route::post('/appointments', [SecAppt::class, 'store'])->name('appointments.store');

         // Queue management
         Route::get('/clinics/{clinic}/queue', [\App\Http\Controllers\Secretary\QueueController::class,'queue'])->name('queue.index');
         Route::post('/clinics/{clinic}/queue/{entry}/serve', [\App\Http\Controllers\Secretary\QueueController::class,'serve'])->name('queue.serve');
         Route::post('/clinics/{clinic}/queue/{entry}/cancel', [\App\Http\Controllers\Secretary\QueueController::class,'cancel'])->name('queue.cancel');

         // ↓ new doctors resource ↓
         Route::resource('doctors', SecDoctor::class); // include show for detailed doctor profile

         // Secretary clinic-service management (attach/detach existing master services)
         Route::get('services', [\App\Http\Controllers\Secretary\ClinicServiceController::class,'index'])->name('services.index');
         Route::get('services/create', [\App\Http\Controllers\Secretary\ClinicServiceController::class,'create'])->name('services.create');
         Route::post('services', [\App\Http\Controllers\Secretary\ClinicServiceController::class,'store'])->name('services.store');
         Route::post('clinics/{clinic}/services/attach', [\App\Http\Controllers\Secretary\ClinicServiceController::class,'attach'])->name('services.attach');
         Route::delete('clinics/{clinic}/services/{service}', [\App\Http\Controllers\Secretary\ClinicServiceController::class,'detach'])->name('services.detach');
         Route::delete('services/{service}', [\App\Http\Controllers\Secretary\ClinicServiceController::class,'destroy'])->name('services.destroy');

         // Secretary clinic profile (edit assigned clinics)
         Route::get('clinics/{clinic}/edit', [\App\Http\Controllers\Secretary\ClinicProfileController::class,'edit'])->name('clinic.edit');
         Route::put('clinics/{clinic}', [\App\Http\Controllers\Secretary\ClinicProfileController::class,'update'])->name('clinic.update');
     });

/*
|--------------------------------------------------------------------------
| Doctor Routes
|--------------------------------------------------------------------------
*/
Route::prefix('doctor')
      ->middleware(['auth', \App\Http\Middleware\DoctorMiddleware::class])
      ->name('doctor.')
      ->group(function(){
           Route::get('/dashboard', [\App\Http\Controllers\Doctor\DashboardController::class,'index'])->name('dashboard');
           Route::get('/queue', [\App\Http\Controllers\Doctor\QueueController::class,'index'])->name('queue.index');
           Route::post('/queue/{entry}/serve', [\App\Http\Controllers\Doctor\QueueController::class,'serve'])->name('queue.serve');

           // Schedules
           Route::get('/schedules', [\App\Http\Controllers\Doctor\ScheduleController::class,'index'])->name('schedules.index');
           Route::post('/schedules', [\App\Http\Controllers\Doctor\ScheduleController::class,'store'])->name('schedules.store');
           Route::put('/schedules/{schedule}', [\App\Http\Controllers\Doctor\ScheduleController::class,'update'])->name('schedules.update');
           Route::delete('/schedules/{schedule}', [\App\Http\Controllers\Doctor\ScheduleController::class,'destroy'])->name('schedules.destroy');
      });

