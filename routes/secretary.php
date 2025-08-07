<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Secretary\AppointmentController as SecAppt;
use App\Http\Controllers\Secretary\DoctorController as SecDoctor;
use App\Http\Controllers\Secretary\DashboardController as SecDashboard;
use App\Http\Controllers\Secretary\ServiceController as SecService;

// All routes in this file are protected by 'auth' and 'can:access-secretary-panel' via RouteServiceProvider

// Secretary dashboard
Route::get('/dashboard', [SecDashboard::class, 'index'])->name('dashboard');

// Secretary appointments management
Route::resource('appointments', SecAppt::class)
     ->only(['index','edit','update','destroy']);

// Secretary doctors management  
Route::resource('doctors', SecDoctor::class)
     ->except(['show']);

// Secretary services management  
Route::resource('services', SecService::class)
     ->only(['index', 'create', 'store', 'destroy']);
