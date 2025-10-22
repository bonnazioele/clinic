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
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Secretary\AppointmentController as SecAppt;
use App\Http\Controllers\Secretary\DoctorController as SecDoctor;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\Admin\SecretaryController as AdminSecretaryController;
use App\Http\Controllers\Auth\SecretaryRegisterController;
use App\Http\Controllers\Owner\ApplicationController as OwnerApplicationController;


//Public Routes

Route::get('/', [DashboardController::class, 'welcome'])->name('welcome');
Route::get('/welcome', [DashboardController::class, 'welcome']);

Route::get('/clinics', [ClinicController::class, 'index'])->name('clinics.index');
Route::get('/clinics/{clinic}', [ClinicController::class, 'show'])->name('clinics.show');
Route::get('/services/search', [\App\Http\Controllers\PublicServiceController::class, 'search'])->name('services.search');

Route::middleware('guest')->group(function(){
     Route::get('/apply/clinic', [OwnerApplicationController::class, 'create'])->name('owner.apply');
     Route::post('/apply/clinic', [OwnerApplicationController::class, 'store'])->name('owner.apply.store');
     Route::get('/apply/clinic/thanks', function(){
         return view('owner.apply_thanks');
     })->name('owner.apply.thanks');
});


Auth::routes(['verify' => true]);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');


//Patient Routes

Route::middleware(['auth'])
     ->group(function () {

     Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class,'show'])->name('profile.show');
     Route::get('/profile/edit', [ProfileController::class,'edit'])->name('profile.edit');
     Route::post('/profile', [ProfileController::class,'update'])->name('profile.update');

    Route::get('/appointments/create', [AppointmentController::class, 'create'])
         ->name('appointments.create');
    Route::get('/appointments/availability', [AppointmentController::class, 'availability'])
         ->name('appointments.availability');
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

    Route::get('/queue/status', [QueueController::class, 'status'])->name('queue.status');
    Route::get('/queue/status/{entry}', [QueueController::class, 'status'])->name('queue.status.entry');
    Route::post('/queue/join/{clinic}', [QueueController::class, 'join'])->name('queue.join');
     Route::post('/queue/leave/{entry}', [QueueController::class, 'leave'])->name('queue.leave');
});


//Admin Routes

Route::prefix('admin')
     ->name('admin.')
     ->group(function () {
         Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

         Route::resource('clinics', AdminClinicController::class);
         Route::get('clinics/{clinic}/application', [AdminClinicController::class, 'showApplication'])->name('clinics.application');
         Route::post('clinics/{clinic}/approve', [AdminClinicController::class, 'approve'])->name('clinics.approve');
         Route::post('clinics/{clinic}/decline', [AdminClinicController::class, 'decline'])->name('clinics.decline');

         Route::resource('services', AdminServiceController::class)
              ->except('show');

         Route::get('secretaries', [AdminSecretaryController::class, 'index'])->name('secretaries.index');

         Route::get('doctors', [AdminDoctorController::class, 'index'])->name('doctors.index');
         Route::get('doctors/{doctor}', [AdminDoctorController::class, 'show'])->name('doctors.show');

         Route::get('users', [AdminUserController::class, 'index'])->name('users.index');

           Route::get('reports', [ReportsController::class, 'index'])->name('reports.index');

     });

Route::prefix('secretary')
     ->middleware(['auth', 'force.password.change', \App\Http\Middleware\SecretaryMiddleware::class, \App\Http\Middleware\EnsureSelectedClinic::class])
     ->name('secretary.')
     ->group(function(){
         Route::get('/auth/force-password', [\App\Http\Controllers\Auth\ForcedPasswordChangeController::class, 'show'])
              ->name('auth.password.force.show');
         Route::put('/auth/force-password', [\App\Http\Controllers\Auth\ForcedPasswordChangeController::class, 'update'])
              ->name('auth.password.force.update');

         Route::get('/dashboard', [\App\Http\Controllers\Secretary\DashboardController::class,'index'])->name('dashboard');
         Route::post('/active-clinic', [\App\Http\Controllers\Secretary\ActiveClinicController::class,'update'])->name('active-clinic.update');

         Route::get('/queues', [\App\Http\Controllers\Secretary\QueueController::class,'overview'])->name('queue.overview');

         Route::resource('appointments', SecAppt::class)
              ->only(['index','edit','update','destroy']);

         Route::get('/appointments/create', [SecAppt::class, 'create'])->name('appointments.create');
         Route::post('/appointments', [SecAppt::class, 'store'])->name('appointments.store');

         Route::get('/clinics/{clinic}/queue', [\App\Http\Controllers\Secretary\QueueController::class,'queue'])->name('queue.index');
        Route::post('/{clinic}/{entry}/call', [\App\Http\Controllers\Secretary\QueueController::class, 'call'])->name('queue.call');
        Route::post('/{clinic}/{entry}/reschedule', [\App\Http\Controllers\Secretary\QueueController::class, 'reschedule'])->name('queue.reschedule');
         Route::post('/{clinic}/{entry}/cancel', [\App\Http\Controllers\Secretary\QueueController::class, 'cancel'])->name('queue.cancel');
         Route::post('/{clinic}/{entry}/no-show', [\App\Http\Controllers\Secretary\QueueController::class, 'noShow'])->name('queue.no_show');

         Route::resource('doctors', SecDoctor::class);

         Route::get('services', [\App\Http\Controllers\Secretary\ClinicServiceController::class,'index'])->name('services.index');
         Route::get('services/create', [\App\Http\Controllers\Secretary\ClinicServiceController::class,'create'])->name('services.create');
         Route::post('services', [\App\Http\Controllers\Secretary\ClinicServiceController::class,'store'])->name('services.store');
         Route::post('clinics/{clinic}/services/attach', [\App\Http\Controllers\Secretary\ClinicServiceController::class,'attach'])->name('services.attach');
         Route::delete('clinics/{clinic}/services/{service}', [\App\Http\Controllers\Secretary\ClinicServiceController::class,'detach'])->name('services.detach');
         Route::delete('services/{service}', [\App\Http\Controllers\Secretary\ClinicServiceController::class,'destroy'])->name('services.destroy');

         Route::get('clinics/{clinic}/edit', [\App\Http\Controllers\Secretary\ClinicProfileController::class,'edit'])->name('clinic.edit');
         Route::put('clinics/{clinic}', [\App\Http\Controllers\Secretary\ClinicProfileController::class,'update'])->name('clinic.update');
     });


//Doctor Routes

Route::prefix('doctor')
      ->name('doctor.')
      ->group(function(){
           Route::get('/dashboard', [\App\Http\Controllers\Doctor\DashboardController::class,'index'])->name('dashboard');
           Route::get('/queue', [\App\Http\Controllers\Doctor\QueueController::class,'index'])->name('queue.index');
           Route::post('/queue/{entry}/serve', [\App\Http\Controllers\Doctor\QueueController::class,'serve'])->name('queue.serve');

           Route::get('/schedules', [\App\Http\Controllers\Doctor\ScheduleController::class,'index'])->name('schedules.index');
           Route::post('/schedules', [\App\Http\Controllers\Doctor\ScheduleController::class,'store'])->name('schedules.store');
           Route::put('/schedules/{schedule}', [\App\Http\Controllers\Doctor\ScheduleController::class,'update'])->name('schedules.update');
           Route::delete('/schedules/{schedule}', [\App\Http\Controllers\Doctor\ScheduleController::class,'destroy'])->name('schedules.destroy');
      });


Broadcast::routes();
