<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\AdminMiddleware;


// 1) Landing page at `/` & `/welcome`
Route::get('/', [HomeController::class,'index']);
Route::get('/welcome', [HomeController::class,'index'])->name('welcome');

// 2) Auth routes (register/login/logout), sending users to `/home`
Auth::routes(['verify' => true]);

// 3) Make sure `/home` exists and points to the same controller
Route::get('/home', [HomeController::class,'index'])->name('home');

// 4) Clinics directory stays at `/clinics`
Route::get('clinics', [ClinicController::class,'index'])->name('clinics.index');

// 5) Protected profile & booking routes
Route::middleware(['auth','verified'])->group(function(){
    Route::get('profile',   [ProfileController::class,'edit'])->name('profile.edit');
    Route::post('profile',  [ProfileController::class,'update'])->name('profile.update');

    Route::get('appointments/create',[AppointmentController::class,'create'])
         ->name('appointments.create');
    Route::post('appointments',[AppointmentController::class,'store'])
         ->name('appointments.store');
    Route::get('appointments',[AppointmentController::class,'index'])
         ->name('appointments.index');
});

// routes/web.php

use App\Http\Controllers\Admin\ClinicController as AdminClinic;
use App\Http\Controllers\Admin\ServiceController as AdminService;

// … your existing patient‐facing routes …

// Admin area – only for auth + admin users
Route::middleware(['auth', AdminMiddleware::class])
     ->prefix('admin')
     ->name('admin.')
     ->group(function(){
         Route::resource('clinics',  AdminClinic::class);
         Route::resource('services', AdminService::class)->except('show');
     });


