<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\PasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ConfirmPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\LanguageController;

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

// Simple Home route with login form
Route::get('/', function () {
    return view('auth.login');
});

// Authentication Routes - explicit definition with middleware
Route::group(['middleware' => 'web'], function () {
    // Login Routes
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    // Registration Routes
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);

    // Password Reset Routes
    Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

    // Password Confirmation Routes
    Route::get('password/confirm', [ConfirmPasswordController::class, 'showConfirmForm'])->name('password.confirm');
    Route::post('password/confirm', [ConfirmPasswordController::class, 'confirm']);

    // Email Verification Routes
    Route::get('email/verify', [VerificationController::class, 'show'])
        ->middleware(['auth'])
        ->name('verification.notice');
        
    Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware(['auth', 'signed'])
        ->name('verification.verify');
        
    Route::post('email/resend', [VerificationController::class, 'resend'])
        ->middleware(['auth', 'throttle:6,1'])
        ->name('verification.resend');
        
    // Language change route
    Route::post('change-language', [LanguageController::class, 'changeLanguage'])->name('change.language');
});

// Admin routes with auth middleware
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/profile', [ProfileController::class, 'index'])->name('admin.profile');
    Route::get('/password', [PasswordController::class, 'index'])->name('admin.password');
    
    // Language management routes
    Route::get('/language', [App\Http\Controllers\Admin\LanguageController::class, 'index'])->name('admin.language.index');
    Route::get('/language/edit', [App\Http\Controllers\Admin\LanguageController::class, 'edit'])->name('admin.language.edit');
    Route::post('/language/update', [App\Http\Controllers\Admin\LanguageController::class, 'update'])->name('admin.language.update');
    Route::post('/language/auto-translate', [App\Http\Controllers\Admin\LanguageController::class, 'autoTranslate'])->name('admin.language.auto-translate');
    Route::get('/language/use', [App\Http\Controllers\Admin\LanguageController::class, 'changeLanguage'])->name('admin.language.use');
});

// Redirect default home route to admin dashboard
Route::get('/home', function() {
    return redirect()->route('admin.dashboard');
})->middleware('auth');
