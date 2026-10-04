<?php

use App\Http\Controllers\Api\v1\Admin\AdminController;
use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\BookingController;
use App\Http\Controllers\Api\v1\CourtController;
use App\Http\Controllers\Api\v1\PaymentWebhookController;
use App\Http\Controllers\Api\v1\Pengelola\ManageCourtController;
use App\Http\Controllers\Api\v1\Pengelola\ManageVenueController;
use App\Http\Controllers\Api\v1\VenueController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes v1 - Platform Booking Lapangan Olahraga
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // --- Authentication (Public & Protected) ---
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
        });
    });

    // --- Public Discovery & Availability (Supports GET and POST with JSON Body) ---
    Route::match(['get', 'post'], 'venues', [VenueController::class, 'index']);
    Route::match(['get', 'post'], 'venues/search', [VenueController::class, 'index']);
    Route::get('venues/{id}', [VenueController::class, 'show']);
    Route::match(['get', 'post'], 'courts/{id}/availability', [CourtController::class, 'availability']);

    // --- Payment Webhook (Public Gateway Signature Validated) ---
    Route::post('payments/webhook/{provider?}', [PaymentWebhookController::class, 'handleWebhook']);
    Route::post('payments/simulate', [PaymentWebhookController::class, 'simulate']); // Sandbox testing helper

    // --- Authenticated User Operations ---
    Route::middleware('auth:sanctum')->group(function () {

        // Penyewa / Pengelola / Admin Booking Operations
        Route::get('bookings', [BookingController::class, 'index']);
        Route::get('bookings/{id}', [BookingController::class, 'show']);
        Route::post('bookings', [BookingController::class, 'store']);
        Route::post('bookings/{id}/cancel', [BookingController::class, 'cancel']);
        Route::post('bookings/{id}/pay', [BookingController::class, 'pay']);

        // --- Pengelola (Venue Owner) Management ---
        Route::prefix('manage')->middleware('role:pengelola,admin')->group(function () {
            // Venue CRUD
            Route::get('venues', [ManageVenueController::class, 'index']);
            Route::post('venues', [ManageVenueController::class, 'store']);
            Route::put('venues/{id}', [ManageVenueController::class, 'update']);
            Route::delete('venues/{id}', [ManageVenueController::class, 'destroy']);

            // Court & Schedule CRUD
            Route::post('courts', [ManageCourtController::class, 'store']);
            Route::put('courts/{id}', [ManageCourtController::class, 'update']);
            Route::delete('courts/{id}', [ManageCourtController::class, 'destroy']);

            Route::post('prices', [ManageCourtController::class, 'storePrice']);
            Route::delete('prices/{id}', [ManageCourtController::class, 'destroyPrice']);

            Route::post('blocks', [ManageCourtController::class, 'storeBlock']);
            Route::delete('blocks/{id}', [ManageCourtController::class, 'destroyBlock']);

            // Booking Operational Status (COMPLETED / NO_SHOW)
            Route::patch('bookings/{id}/status', [ManageCourtController::class, 'updateBookingStatus']);
        });

        // --- Admin Platform Management ---
        Route::prefix('admin')->middleware('role:admin')->group(function () {
            Route::get('users', [AdminController::class, 'users']);
            Route::patch('users/{id}/verify', [AdminController::class, 'verifyPengelola']);
            Route::get('transactions', [AdminController::class, 'transactions']);
            Route::get('webhook-logs', [AdminController::class, 'webhookLogs']);
        });
    });
});
