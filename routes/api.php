<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AgentTraceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\DisputeController;
use App\Http\Controllers\Api\FollowUpController;
use App\Http\Controllers\Api\PricingController;
use App\Http\Controllers\Api\ProviderController;
use App\Http\Controllers\Api\ServiceRequestController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

Route::middleware(['auth:sanctum', 'throttle:60,1'])->prefix('v1')->group(function () {

    Route::middleware('throttle:10,1')->group(function () {
        Route::post('service-requests', [ServiceRequestController::class, 'store']);
    });

    Route::get('service-requests', [ServiceRequestController::class, 'index']);
    Route::get('service-requests/{id}', [ServiceRequestController::class, 'show']);

    Route::get('providers', [ProviderController::class, 'nearby']);
    Route::get('providers/{provider}', [ProviderController::class, 'show']);
    Route::get('providers/{provider}/availability', [ProviderController::class, 'availability']);

    Route::post('pricing/quote', [PricingController::class, 'quote']);
    Route::get('pricing/quote/{pricingQuote}', [PricingController::class, 'show']);

    Route::get('bookings', [BookingController::class, 'index']);
    Route::post('bookings', [BookingController::class, 'store']);
    Route::get('bookings/{booking}', [BookingController::class, 'show']);
    Route::delete('bookings/{booking}', [BookingController::class, 'destroy']);
    Route::put('bookings/{booking}/confirm', [BookingController::class, 'confirm']);
    Route::put('bookings/{booking}/status', [FollowUpController::class, 'updateStatus']);
    Route::post('bookings/{booking}/followup', [FollowUpController::class, 'schedule']);
    Route::post('bookings/{booking}/feedback', [FollowUpController::class, 'feedback']);
    Route::get('bookings/{booking}/receipt', [BookingController::class, 'receipt']);

    Route::get('disputes', [DisputeController::class, 'index']);
    Route::post('disputes', [DisputeController::class, 'store']);
    Route::get('disputes/{dispute}', [DisputeController::class, 'show']);
    Route::put('disputes/{dispute}/resolve', [DisputeController::class, 'resolve']);

    Route::get('agent-trace/{runId}', [AgentTraceController::class, 'show']);
    Route::get('agent-trace/{runId}/export', [AgentTraceController::class, 'export']);

    Route::prefix('admin')->group(function () {
        Route::get('providers', [AdminController::class, 'providers']);
        Route::put('providers/{provider}', [AdminController::class, 'updateProvider']);
        Route::put('providers/{provider}/status', [AdminController::class, 'updateProviderStatus']);
        Route::get('bookings', [AdminController::class, 'bookings']);
    });
});
