<?php

use App\Http\Controllers\Web\AdminWebController;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\ProviderWebController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;

// Root redirect
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route(auth()->user()->role === 'admin' ? 'admin.dashboard' : (auth()->user()->role === 'provider' ? 'provider.dashboard' : 'user.dashboard'))
        : redirect()->route('login');
});

// Auth routes (guests only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthWebController::class, 'login']);
    Route::get('/register', [AuthWebController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthWebController::class, 'register']);
});

Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout')->middleware('auth');

// User routes
Route::middleware(['auth'])->prefix('dashboard')->name('user.')->group(function () {
    Route::get('/', [UserController::class, 'dashboard'])->name('dashboard');
    Route::get('/request', [UserController::class, 'showRequest'])->name('request');
    Route::post('/request', [UserController::class, 'storeRequest'])->name('request.store');
    Route::post('/quote', [UserController::class, 'getQuote'])->name('quote');
    Route::get('/bookings', [UserController::class, 'bookings'])->name('bookings');
    Route::post('/bookings', [UserController::class, 'storeBooking'])->name('bookings.store');
    Route::get('/bookings/{booking}', [UserController::class, 'showBooking'])->name('bookings.show');
    Route::put('/bookings/{booking}/confirm', [UserController::class, 'confirmBooking'])->name('bookings.confirm');
    Route::delete('/bookings/{booking}', [UserController::class, 'cancelBooking'])->name('bookings.cancel');
    Route::post('/bookings/{booking}/feedback', [UserController::class, 'submitFeedback'])->name('bookings.feedback');
    Route::get('/disputes', [UserController::class, 'disputes'])->name('disputes');
    Route::get('/disputes/create', [UserController::class, 'createDispute'])->name('disputes.create');
    Route::post('/disputes', [UserController::class, 'storeDispute'])->name('disputes.store');
});

// Provider routes
Route::middleware(['auth'])->prefix('provider')->name('provider.')->group(function () {
    Route::get('/', [ProviderWebController::class, 'dashboard'])->name('dashboard');
    Route::get('/bookings', [ProviderWebController::class, 'bookings'])->name('bookings');
    Route::put('/bookings/{booking}/status', [ProviderWebController::class, 'updateBookingStatus'])->name('bookings.status');
    Route::get('/profile', [ProviderWebController::class, 'profile'])->name('profile');
});

// Admin routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminWebController::class, 'dashboard'])->name('dashboard');
    Route::get('/providers', [AdminWebController::class, 'providers'])->name('providers');
    Route::put('/providers/{provider}/status', [AdminWebController::class, 'updateProviderStatus'])->name('providers.status');
    Route::get('/bookings', [AdminWebController::class, 'bookings'])->name('bookings');
    Route::get('/disputes', [AdminWebController::class, 'disputes'])->name('disputes');
});
