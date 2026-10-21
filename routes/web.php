<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BookingController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DocsController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\VenueController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Platform Booking Lapangan Olahraga
|--------------------------------------------------------------------------
*/

// Public Discovery
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/venues/{id}', [VenueController::class, 'show'])->name('venues.show');
Route::redirect('/docs', '/')->name('docs');
Route::redirect('/docs/postman', '/')->name('docs.postman');

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Customer / Authenticated Bookings
Route::middleware('auth')->group(function () {
    Route::post('/venues/book', [VenueController::class, 'book'])->name('venues.book');
    Route::get('/booking/{id}', [BookingController::class, 'checkout'])->name('booking.checkout');
    Route::get('/my-bookings', [BookingController::class, 'myBookings'])->name('my.bookings');
    Route::post('/booking/{id}/cancel', [BookingController::class, 'cancel'])->name('booking.cancel');

    // Pengelola Dashboard
    Route::prefix('dashboard/pengelola')->middleware('role:pengelola,admin')->group(function () {
        Route::get('/', [DashboardController::class, 'pengelola'])->name('pengelola.dashboard');
        Route::patch('/bookings/{id}/status', [DashboardController::class, 'updateBookingStatus'])->name('pengelola.booking.status');
        Route::post('/blocks', [DashboardController::class, 'storeBlock'])->name('pengelola.block.store');
        Route::post('/courts', [DashboardController::class, 'storeCourt'])->name('pengelola.court.store');
        Route::put('/courts/{id}', [DashboardController::class, 'updateCourt'])->name('pengelola.court.update');
        Route::delete('/courts/{id}', [DashboardController::class, 'destroyCourt'])->name('pengelola.court.destroy');
        Route::post('/prices', [DashboardController::class, 'storePrice'])->name('pengelola.price.store');
        Route::delete('/prices/{id}', [DashboardController::class, 'destroyPrice'])->name('pengelola.price.destroy');
    });

    // Admin Dashboard
    Route::prefix('dashboard/admin')->middleware('role:admin')->group(function () {
        Route::get('/', [DashboardController::class, 'admin'])->name('admin.dashboard');
        Route::post('/pengelola/{id}/verify', [DashboardController::class, 'verifyPengelola'])->name('admin.pengelola.verify');
    });
});
