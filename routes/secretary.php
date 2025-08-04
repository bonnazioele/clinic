<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Secretary\AppointmentController as SecAppt;
use App\Http\Controllers\Secretary\DoctorController as SecDoctor;

// All routes in this file are protected by 'auth' and 'can:access-secretary-panel' via RouteServiceProvider

// Secretary appointments management
Route::resource('appointments', SecAppt::class)
     ->only(['index','edit','update','destroy']);

// Secretary doctors management  
Route::resource('doctors', SecDoctor::class)
     ->except(['show']);
