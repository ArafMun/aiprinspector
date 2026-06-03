<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.reset.update');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/', function () {
    return view('landing');
});

// Static page routes
Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

Route::get('/terms', function () {
    return view('terms');
})->name('terms');

Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

// Health check routes
Route::get('/health', [HealthController::class, 'index'])->name('health');
Route::get('/health/detailed', [HealthController::class, 'detailed'])->name('health.detailed');

// Admin routes (protected by admin authentication)
Route::middleware(['auth', 'admin.auth'])->prefix('admin')->name('admin.')->group(callback: function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // Review management
    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::get('/reviews/{review}', [ReviewController::class, 'show'])->name('reviews.show');
    Route::post('/reviews/{review}/retry', [ReviewController::class, 'retry'])->name('reviews.retry');

    // User management
    Route::middleware('permission:admin.users')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Role management
    Route::middleware('permission:admin.users')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/{role}', [RoleController::class, 'show'])->name('roles.show');
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
        Route::get('/roles/{role}/users', [RoleController::class, 'users'])->name('roles.users');
        Route::post('/roles/{role}/toggle-status', [RoleController::class, 'toggleStatus'])->name('roles.toggle-status');
    });

    // Log monitoring
    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::get('/logs/download', [LogController::class, 'download'])->name('logs.download');
    Route::post('/logs/clear', [LogController::class, 'clear'])->name('logs.clear');
    Route::get('/logs/tail', [LogController::class, 'tail'])->name('logs.tail');

    // Statistics
    Route::get('/stats', [AdminController::class, 'statistics'])->name('statistics');
    Route::get('/stats/api', [AdminController::class, 'statisticsApi'])->name('statistics.api');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
    Route::post('/settings/password', [ProfileController::class, 'updatePassword'])->name('settings.password');
});
