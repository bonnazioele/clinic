<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Controllers
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\SecretaryRegisterController;

use App\Http\Controllers\Frontend\DashboardController;
use App\Http\Controllers\Frontend\ClinicController;
use App\Http\Controllers\Frontend\PublicServiceController;

use App\Http\Controllers\Owner\ApplicationController as OwnerApplicationController;

use App\Http\Controllers\Patient\AppointmentController;
use App\Http\Controllers\Patient\ProfileController;
use App\Http\Controllers\Patient\QueueController;

use App\Http\Controllers\Admin\ClinicController as AdminClinicController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DoctorController as AdminDoctorController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\SecretaryController as AdminSecretaryController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\UserController as AdminUserController;

use App\Http\Controllers\Secretary\AppointmentController as SecretaryAppointmentController;
use App\Http\Controllers\Secretary\ClinicSelectionController as SecretaryClinicSelectionController;
use App\Http\Controllers\Secretary\ClinicServiceController;
use App\Http\Controllers\Secretary\DoctorController as SecretaryDoctorController;
use App\Http\Controllers\Secretary\PatientController as SecretaryPatientController;
use App\Http\Controllers\Secretary\QueueController as SecretaryQueueController;
use App\Http\Controllers\Secretary\WalkInPatientDirectoryController;
use App\Http\Controllers\Secretary\WalkInRegistrationController;

use App\Http\Controllers\Doctor\ClinicSelectionController as DoctorClinicSelectionController;

/*
|--------------------------------------------------------------------------
| Middleware
|--------------------------------------------------------------------------
*/

use App\Http\Middleware\EnsureSelectedClinic;
use App\Http\Middleware\SecretaryMiddleware;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [DashboardController::class, 'welcome'])->name('welcome');
Route::get('/welcome', [DashboardController::class, 'welcome']);

Route::get('/clinics', [ClinicController::class, 'index'])->name('clinics.index');
Route::get('/clinics/{clinic}', [ClinicController::class, 'show'])->name('clinics.show');

Route::get('/services/search', [PublicServiceController::class, 'search'])->name('services.search');

/*
|--------------------------------------------------------------------------
| Clinic Owner Application Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/apply/clinic', [OwnerApplicationController::class, 'create'])
        ->name('owner.apply');

    Route::post('/apply/clinic', [OwnerApplicationController::class, 'store'])
        ->name('owner.apply.store');

    Route::get('/apply/clinic/thanks', function () {
        return view('owner.apply_thanks');
    })->name('owner.apply.thanks');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Auth::routes(['verify' => true]);

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Shared Auth Routes
|--------------------------------------------------------------------------
| Dropdown-only notifications.
| No notification page is needed.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::post('/notifications/mark-read', function () {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
        ]);
    })->name('notifications.markRead');
});

/*
|--------------------------------------------------------------------------
| Patient Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Patient Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'show'])
        ->name('profile.show');

    Route::get('/profile/edit', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::post('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::post('/profile/history', [ProfileController::class, 'storeHistory'])
        ->name('profile.history.store');

    /*
    |--------------------------------------------------------------------------
    | Patient Appointments
    |--------------------------------------------------------------------------
    */

    Route::get('/appointments', [AppointmentController::class, 'index'])
        ->name('appointments.index');

    Route::get('/appointments/create', [AppointmentController::class, 'create'])
        ->name('appointments.create');

    Route::get('/appointments/availability', [AppointmentController::class, 'availability'])
        ->name('appointments.availability');

    Route::post('/appointments', [AppointmentController::class, 'store'])
        ->name('appointments.store');

    Route::get('/appointments/{appointment}/edit', [AppointmentController::class, 'edit'])
        ->name('appointments.edit');

    Route::put('/appointments/{appointment}', [AppointmentController::class, 'update'])
        ->name('appointments.update');

    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])
        ->name('appointments.destroy');

    /*
    |--------------------------------------------------------------------------
    | Patient Queue
    |--------------------------------------------------------------------------
    */

    Route::get('/queue/status', [QueueController::class, 'status'])
        ->name('queue.status');

    Route::get('/queue/status/{entry}', [QueueController::class, 'status'])
        ->name('queue.status.entry');

    Route::post('/queue/join/{clinic}', [QueueController::class, 'join'])
        ->name('queue.join');

    Route::post('/queue/leave/{entry}', [QueueController::class, 'leave'])
        ->name('queue.leave');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Admin Clinics
        |--------------------------------------------------------------------------
        */

        Route::resource('clinics', AdminClinicController::class);

        Route::get('clinics/{clinic}/application', [AdminClinicController::class, 'showApplication'])
            ->name('clinics.application');

        Route::post('clinics/{clinic}/approve', [AdminClinicController::class, 'approve'])
            ->name('clinics.approve');

        Route::post('clinics/{clinic}/decline', [AdminClinicController::class, 'decline'])
            ->name('clinics.decline');

        /*
        |--------------------------------------------------------------------------
        | Admin Services
        |--------------------------------------------------------------------------
        */

        Route::resource('services', AdminServiceController::class)
            ->except('show');

        /*
        |--------------------------------------------------------------------------
        | Admin Doctors
        |--------------------------------------------------------------------------
        */

        Route::get('doctors', [AdminDoctorController::class, 'index'])
            ->name('doctors.index');

        Route::get('doctors/{doctor}', [AdminDoctorController::class, 'show'])
            ->name('doctors.show');

        /*
        |--------------------------------------------------------------------------
        | Admin Secretaries / Users / Patients / Reports
        |--------------------------------------------------------------------------
        */

        Route::get('secretaries', [AdminSecretaryController::class, 'index'])
            ->name('secretaries.index');

        Route::get('users', [AdminUserController::class, 'index'])
            ->name('users.index');

        Route::get('patients', [WalkInPatientDirectoryController::class, 'index'])
            ->name('patients');

        Route::get('reports', [ReportsController::class, 'index'])
            ->name('reports.index');
    });

/*
|--------------------------------------------------------------------------
| Secretary Clinic Selection Routes
|--------------------------------------------------------------------------
| These routes are outside EnsureSelectedClinic because this is where the
| secretary chooses the active clinic.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'force.password.change', SecretaryMiddleware::class])
    ->group(function () {
        Route::get('/choose-clinic', [SecretaryClinicSelectionController::class, 'index'])
            ->name('secretary.choose-clinic');

        Route::post('/choose-clinic/select', [SecretaryClinicSelectionController::class, 'select'])
            ->name('secretary.choose-clinic.select');
    });

/*
|--------------------------------------------------------------------------
| Secretary Routes
|--------------------------------------------------------------------------
*/

Route::prefix('secretary')
    ->name('secretary.')
    ->middleware([
        'auth',
        'force.password.change',
        SecretaryMiddleware::class,
        EnsureSelectedClinic::class,
    ])
    ->group(function () {
        /*
        |--------------------------------------------------------------------------
        | Secretary Password / Active Clinic / Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/auth/force-password', [\App\Http\Controllers\Auth\ForcedPasswordChangeController::class, 'show'])
            ->name('auth.password.force.show');

        Route::put('/auth/force-password', [\App\Http\Controllers\Auth\ForcedPasswordChangeController::class, 'update'])
            ->name('auth.password.force.update');

        Route::get('/dashboard', [\App\Http\Controllers\Secretary\DashboardController::class, 'index'])
            ->name('dashboard');

        Route::post('/active-clinic', [\App\Http\Controllers\Secretary\ActiveClinicController::class, 'update'])
            ->name('active-clinic.update');

        /*
        |--------------------------------------------------------------------------
        | Secretary Appointments
        |--------------------------------------------------------------------------
        */

        Route::get('/appointments', [SecretaryAppointmentController::class, 'index'])
            ->name('appointments.index');

        Route::get('/appointments/create', [SecretaryAppointmentController::class, 'create'])
            ->name('appointments.create');

        Route::post('/appointments', [SecretaryAppointmentController::class, 'store'])
            ->name('appointments.store');

        Route::get('/appointments/{appointment}/edit', [SecretaryAppointmentController::class, 'edit'])
            ->name('appointments.edit');

        Route::put('/appointments/{appointment}', [SecretaryAppointmentController::class, 'update'])
            ->name('appointments.update');

        Route::delete('/appointments/{appointment}', [SecretaryAppointmentController::class, 'destroy'])
            ->name('appointments.destroy');

        /*
        |--------------------------------------------------------------------------
        | Secretary Queue
        |--------------------------------------------------------------------------
        */

        Route::get('/queues', [SecretaryQueueController::class, 'overview'])
            ->name('queue.overview');

        Route::get('/clinics/{clinic}/queue', [SecretaryQueueController::class, 'queue'])
            ->name('queue.index');

        Route::post('/clinics/{clinic}/queue/{entry}/call', [SecretaryQueueController::class, 'call'])
            ->name('queue.call');

        Route::post('/clinics/{clinic}/queue/{entry}/done-next', [SecretaryQueueController::class, 'doneNext'])
            ->name('queue.done_next');

        Route::post('/clinics/{clinic}/queue/{entry}/reschedule', [SecretaryQueueController::class, 'reschedule'])
            ->name('queue.reschedule');

        Route::post('/clinics/{clinic}/queue/{entry}/cancel', [SecretaryQueueController::class, 'cancel'])
            ->name('queue.cancel');

        Route::post('/clinics/{clinic}/queue/{entry}/no-show', [SecretaryQueueController::class, 'noShow'])
            ->name('queue.no_show');

        /*
        |--------------------------------------------------------------------------
        | Secretary Doctors
        |--------------------------------------------------------------------------
        */

        Route::resource('doctors', SecretaryDoctorController::class);

        /*
        |--------------------------------------------------------------------------
        | Secretary Patients
        |--------------------------------------------------------------------------
        */

        Route::get('/patients', [SecretaryPatientController::class, 'index'])
            ->name('patients.index');

        Route::get('/patients/create', [SecretaryPatientController::class, 'create'])
            ->name('patients.create');

        Route::post('/patients', [SecretaryPatientController::class, 'store'])
            ->name('patients.store');

        Route::get('/patients/{patient}/edit', [SecretaryPatientController::class, 'edit'])
            ->name('patients.edit');

        Route::put('/patients/{patient}', [SecretaryPatientController::class, 'update'])
            ->name('patients.update');

        Route::delete('/patients/{patient}', [SecretaryPatientController::class, 'destroy'])
            ->name('patients.destroy');

        /*
        |--------------------------------------------------------------------------
        | Secretary Walk-In
        |--------------------------------------------------------------------------
        */

        Route::prefix('walkin')
            ->name('walkin.')
            ->group(function () {
                Route::get('/', [WalkInRegistrationController::class, 'index'])
                    ->name('index');

                Route::get('/search', [WalkInRegistrationController::class, 'searchPatient'])
                    ->name('search');

                Route::post('/register', [WalkInRegistrationController::class, 'store'])
                    ->name('store');

                Route::get('/confirmation/{visit}', [WalkInRegistrationController::class, 'confirmation'])
                    ->name('confirmation');

                Route::get('/print/{visit}', [WalkInRegistrationController::class, 'printSlip'])
                    ->name('print');
            });

        /*
        |--------------------------------------------------------------------------
        | Secretary Clinic-Scoped Services
        |--------------------------------------------------------------------------
        | These are clinic-scoped on purpose:
        | /secretary/clinics/{clinic}/services
        |--------------------------------------------------------------------------
        */

        Route::get('/clinics/{clinic}/services', [ClinicServiceController::class, 'index'])
            ->name('services.index');

        Route::get('/clinics/{clinic}/services/create', [ClinicServiceController::class, 'create'])
            ->name('services.create');

        Route::post('/clinics/{clinic}/services', [ClinicServiceController::class, 'store'])
            ->name('services.store');

        Route::get('/clinics/{clinic}/services/search', [ClinicServiceController::class, 'search'])
            ->name('services.search');

        Route::post('/clinics/{clinic}/services/attach', [ClinicServiceController::class, 'attach'])
            ->name('services.attach');

        Route::get('/clinics/{clinic}/services/{service}/edit', [ClinicServiceController::class, 'edit'])
            ->name('services.edit');

        Route::put('/clinics/{clinic}/services/{service}', [ClinicServiceController::class, 'update'])
            ->name('services.update');

        Route::delete('/clinics/{clinic}/services/{service}', [ClinicServiceController::class, 'destroy'])
            ->name('services.destroy');

        Route::delete('/clinics/{clinic}/services/{service}/detach', [ClinicServiceController::class, 'detach'])
            ->name('services.detach');

        /*
        |--------------------------------------------------------------------------
        | Secretary Clinic Profile
        |--------------------------------------------------------------------------
        */

        Route::get('/clinics/{clinic}/edit', [\App\Http\Controllers\Secretary\ClinicProfileController::class, 'edit'])
            ->name('clinic.edit');

        Route::put('/clinics/{clinic}', [\App\Http\Controllers\Secretary\ClinicProfileController::class, 'update'])
            ->name('clinic.update');
    });

/*
|--------------------------------------------------------------------------
| Doctor Clinic Selection Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/doctor/choose-clinic', [DoctorClinicSelectionController::class, 'index'])
        ->name('doctor.choose-clinic');

    Route::post('/doctor/choose-clinic/select', [DoctorClinicSelectionController::class, 'select'])
        ->name('doctor.choose-clinic.select');
});

/*
|--------------------------------------------------------------------------
| Doctor Routes
|--------------------------------------------------------------------------
*/

Route::prefix('doctor')
    ->name('doctor.')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Doctor\DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/queue', [\App\Http\Controllers\Doctor\QueueController::class, 'index'])
            ->name('queue.index');

        Route::post('/queue/{entry}/serve', [\App\Http\Controllers\Doctor\QueueController::class, 'serve'])
            ->name('queue.serve');

        Route::get('/schedules', [\App\Http\Controllers\Doctor\ScheduleController::class, 'index'])
            ->name('schedules.index');

        Route::get('/schedules/feed', [\App\Http\Controllers\Doctor\ScheduleController::class, 'feed'])
            ->name('schedules.feed');

        Route::post('/schedules', [\App\Http\Controllers\Doctor\ScheduleController::class, 'store'])
            ->name('schedules.store');

        Route::put('/schedules/{schedule}', [\App\Http\Controllers\Doctor\ScheduleController::class, 'update'])
            ->name('schedules.update');

        Route::delete('/schedules/{schedule}', [\App\Http\Controllers\Doctor\ScheduleController::class, 'destroy'])
            ->name('schedules.destroy');
    });

/*
|--------------------------------------------------------------------------
| Broadcasting
|--------------------------------------------------------------------------
*/

Broadcast::routes();