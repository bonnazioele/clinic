<?php

use App\Http\Controllers\Auth\ConfirmPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| Here are the routes for authentication functionality including login,
| registration, password reset, and email verification.
|
*/

// Guest routes (only accessible when not authenticated)
Route::middleware('guest')->group(function () {
    // Login routes
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);

    // Registration routes
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);

    // Password reset routes
    Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Authenticated routes (only accessible when authenticated)
Route::middleware('auth')->group(function () {
    // Logout route
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    // Email verification routes
    Route::get('email/verify', [VerificationController::class, 'show'])->name('verification.notice');
    Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::post('email/verification-notification', [VerificationController::class, 'resend'])->name('verification.resend');

    // Password confirmation route
    Route::get('confirm-password', [ConfirmPasswordController::class, 'showConfirmForm'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmPasswordController::class, 'confirm']);
});
