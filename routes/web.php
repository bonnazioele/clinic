<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Frontend\DashboardController;
use App\Http\Controllers\Frontend\ClinicController;
use App\Http\Controllers\Patient\AppointmentController;
use App\Http\Controllers\Patient\ProfileController;
use App\Http\Controllers\Patient\QueueController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\Admin\ClinicController as AdminClinicController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\DoctorController as AdminDoctorController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Doctor\ClinicSelectionController as DocClinicSelectionController;
use App\Http\Controllers\Secretary\AppointmentController as SecAppt;
use App\Http\Controllers\Secretary\DoctorController as SecDoctor;
use App\Http\Controllers\Secretary\PatientController as SecPatient;
use App\Http\Controllers\Secretary\ClinicSelectionController;
use App\Http\Controllers\Secretary\QueueController as SecretaryQueueController;
use App\Http\Controllers\Secretary\WalkInPatientDirectoryController;
use App\Http\Controllers\Patient\NotificationsController;
use App\Http\Controllers\Admin\SecretaryController as AdminSecretaryController;
use App\Http\Controllers\Auth\SecretaryRegisterController;
use App\Http\Controllers\Owner\ApplicationController as OwnerApplicationController;
use App\Http\Controllers\Secretary\WalkInRegistrationController;
use App\Http\Controllers\Frontend\PublicServiceController;
use App\Http\Middleware\SecretaryMiddleware;
use App\Http\Middleware\EnsureSelectedClinic;


//Public Routes

Route::get('/', [DashboardController::class, 'welcome'])->name('welcome');
Route::get('/welcome', [DashboardController::class, 'welcome']);

Route::get('/clinics', [ClinicController::class, 'index'])->name('clinics.index');
Route::get('/clinics/{clinic}', [ClinicController::class, 'show'])->name('clinics.show');
Route::get('/services/search', [PublicServiceController::class, 'search'])->name('services.search');

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
     Route::get('/reports', [\App\Http\Controllers\UserReportController::class, 'index'])->name('reports.index');
    Route::post('/queue/join/{clinic}', [QueueController::class, 'join'])->name('queue.join');
     Route::post('/queue/leave/{entry}', [QueueController::class, 'leave'])->name('queue.leave');

     Route::post('/profile/history', [ProfileController::class, 'storeHistory'])->name('profile.history.store');

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
                     Route::get('/patients', [WalkInPatientDirectoryController::class, 'index'])->name('patients');

         Route::resource('services', AdminServiceController::class)
              ->except('show');

         Route::get('secretaries', [AdminSecretaryController::class, 'index'])->name('secretaries.index');

         Route::get('doctors', [AdminDoctorController::class, 'index'])->name('doctors.index');
         Route::get('doctors/{doctor}', [AdminDoctorController::class, 'show'])->name('doctors.show');

         Route::get('users', [AdminUserController::class, 'index'])->name('users.index');

           Route::get('reports', [ReportsController::class, 'index'])->name('reports.index');

     });

     //Secretary Routes
// Secretary clinic selection (multi-clinic secretaries)
Route::middleware(['auth', 'force.password.change', SecretaryMiddleware::class])->group(function () {
     Route::get('/choose-clinic', [ClinicSelectionController::class, 'index'])->name('secretary.choose-clinic');
     Route::post('/choose-clinic/select', [ClinicSelectionController::class, 'select'])->name('secretary.choose-clinic.select');
});

        Route::prefix('secretary')
     ->middleware(['auth', 'force.password.change', \App\Http\Middleware\SecretaryMiddleware::class, \App\Http\Middleware\EnsureSelectedClinic::class])
     ->name('secretary.')
     ->group(function(){
         Route::get('/auth/force-password', [\App\Http\Controllers\Auth\ForcedPasswordChangeController::class, 'show'])
              ->name('auth.password.force.show');
         Route::put('/auth/force-password', [\App\Http\Controllers\Auth\ForcedPasswordChangeController::class, 'update'])
              ->name('auth.password.force.update');

        Route::prefix('secretary')
           ->name('secretary.')
           ->group(function () {
               Route::post('/queue/{entry}/call', [SecretaryQueueController::class, 'call'])->name('queue.call');
           });

         Route::get('/dashboard', [\App\Http\Controllers\Secretary\DashboardController::class,'index'])->name('dashboard');
         Route::post('/active-clinic', [\App\Http\Controllers\Secretary\ActiveClinicController::class,'update'])->name('active-clinic.update');

         Route::get('/queues', [\App\Http\Controllers\Secretary\QueueController::class,'overview'])->name('queue.overview');

         Route::resource('appointments', SecAppt::class)
              ->only(['index','edit','update','destroy']);

         Route::get('/appointments/create', [SecAppt::class, 'create'])->name('appointments.create');
         Route::post('/appointments', [SecAppt::class, 'store'])->name('appointments.store');

         Route::get('/clinics/{clinic}/queue', [\App\Http\Controllers\Secretary\QueueController::class,'queue'])->name('queue.index');
        Route::post('/{clinic}/{entry}/call', [\App\Http\Controllers\Secretary\QueueController::class, 'call'])->name('queue.call');
     Route::post('/{clinic}/{entry}/done-next', [\App\Http\Controllers\Secretary\QueueController::class, 'doneNext'])->name('queue.done_next');
        Route::post('/{clinic}/{entry}/reschedule', [\App\Http\Controllers\Secretary\QueueController::class, 'reschedule'])->name('queue.reschedule');
         Route::post('/{clinic}/{entry}/cancel', [\App\Http\Controllers\Secretary\QueueController::class, 'cancel'])->name('queue.cancel');
         Route::post('/{clinic}/{entry}/no-show', [\App\Http\Controllers\Secretary\QueueController::class, 'noShow'])->name('queue.no_show');

         Route::resource('doctors', SecDoctor::class);

         Route::get('patients', [SecPatient::class, 'index'])->name('patients.index');
         Route::get('patients/create', [SecPatient::class, 'create'])->name('patients.create');
         Route::post('patients', [SecPatient::class, 'store'])->name('patients.store');
         Route::get('patients/{patient}/edit', [SecPatient::class, 'edit'])->name('patients.edit');
         Route::put('patients/{patient}', [SecPatient::class, 'update'])->name('patients.update');
         Route::delete('patients/{patient}', [SecPatient::class, 'destroy'])->name('patients.destroy');

           Route::prefix('walkin')
                ->name('walkin.')
                ->group(function () {
                     Route::get('/', [WalkInRegistrationController::class, 'index'])->name('index');
                     Route::get('/search', [WalkInRegistrationController::class, 'searchPatient'])->name('search');
                     Route::post('/register', [WalkInRegistrationController::class, 'store'])->name('store');
                     Route::get('/confirmation/{visit}', [WalkInRegistrationController::class, 'confirmation'])->name('confirmation');
                     Route::get('/print/{visit}', [WalkInRegistrationController::class, 'printSlip'])->name('print');
                });

         Route::get('services', [\App\Http\Controllers\Secretary\ClinicServiceController::class,'index'])->name('services.index');
         Route::get('services/search', [\App\Http\Controllers\Secretary\ClinicServiceController::class,'search'])->name('services.search');
         Route::post('clinics/{clinic}/services/attach', [\App\Http\Controllers\Secretary\ClinicServiceController::class,'attach'])->name('services.attach');
         Route::delete('clinics/{clinic}/services/{service}', [\App\Http\Controllers\Secretary\ClinicServiceController::class,'detach'])->name('services.detach');

         Route::get('clinics/{clinic}/edit', [\App\Http\Controllers\Secretary\ClinicProfileController::class,'edit'])->name('clinic.edit');
         Route::put('clinics/{clinic}', [\App\Http\Controllers\Secretary\ClinicProfileController::class,'update'])->name('clinic.update');
     });


//Doctor Routes

Route::middleware(['auth'])->group(function () {
      Route::get('/doctor/choose-clinic', [DocClinicSelectionController::class, 'index'])
           ->name('doctor.choose-clinic');

      Route::post('/doctor/choose-clinic/select', [DocClinicSelectionController::class, 'select'])
           ->name('doctor.choose-clinic.select');
});

Route::prefix('doctor')
      ->name('doctor.')
      ->group(function(){
           Route::get('/dashboard', [\App\Http\Controllers\Doctor\DashboardController::class,'index'])->name('dashboard');
          Route::get('/appointments', [\App\Http\Controllers\Doctor\AppointmentController::class,'index'])->name('appointments.index');
           Route::get('/queue', [\App\Http\Controllers\Doctor\QueueController::class,'index'])->name('queue.index');
           Route::post('/queue/{entry}/serve', [\App\Http\Controllers\Doctor\QueueController::class,'serve'])->name('queue.serve');

           Route::get('/schedules', [\App\Http\Controllers\Doctor\ScheduleController::class,'index'])->name('schedules.index');
           Route::get('/schedules/feed', [\App\Http\Controllers\Doctor\ScheduleController::class,'feed'])->name('schedules.feed');
           Route::post('/schedules', [\App\Http\Controllers\Doctor\ScheduleController::class,'store'])->name('schedules.store');
           Route::put('/schedules/{schedule}', [\App\Http\Controllers\Doctor\ScheduleController::class,'update'])->name('schedules.update');
           Route::delete('/schedules/{schedule}', [\App\Http\Controllers\Doctor\ScheduleController::class,'destroy'])->name('schedules.destroy');
      });


Broadcast::routes();
