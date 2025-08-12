<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public routes
Route::get('/', [DashboardController::class, 'welcome'])->name('welcome');

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/clinics', [ClinicController::class, 'index'])->name('clinics.index');
});

// Auth routes
require __DIR__.'/auth.php';

// Protected routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Clinics
    Route::get('/clinics', [ClinicController::class, 'index'])->name('clinics.index');

    // Appointments
    Route::resource('appointments', AppointmentController::class);

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    // Queue
    Route::get('/queue/status', [App\Http\Controllers\QueueController::class, 'status'])->name('queue.status');
});

// Admin routes
Route::middleware(['auth', 'can:access-admin-panel'])->prefix('admin')->name('admin.')->group(function () {
    require __DIR__.'/admin.php';
});

// Secretary routes
Route::middleware(['auth', 'can:access-secretary-panel'])->prefix('secretary')->name('secretary.')->group(function () {
    require __DIR__.'/secretary.php';
});
