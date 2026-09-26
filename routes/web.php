<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\ConcertController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminProfileController;
use App\Http\Controllers\AdminTransactionController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\Api\Admin\ConcertApiController;
use App\Http\Controllers\Api\Admin\DashboardApiController;
use App\Http\Controllers\Api\Admin\UserApiController;
use App\Http\Controllers\Api\Admin\VenueApiController;
use App\Http\Controllers\Api\ConcertBookingApiController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VenueController;
use App\Models\Concert;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Concerts listing and detail pages
Route::get('/concerts', [HomeController::class, 'concerts'])->name('concerts.index');

// More specific routes BEFORE generic ones
Route::middleware(['auth', 'user'])->group(function () {
    Route::get('/concerts/{concert}/book', [BookingController::class, 'create'])->name('bookings.create')->where('concert', '[0-9]+');
    Route::post('/concerts/{concert}/book', [BookingController::class, 'store'])->name('bookings.store')->where('concert', '[0-9]+');
    Route::match(['get', 'post'], '/concerts/{concert}/review', [BookingController::class, 'review'])->name('bookings.review')->where('concert', '[0-9]+');
    Route::match(['get', 'post'], '/concerts/{concert}/checkout', [BookingController::class, 'checkout'])->name('bookings.checkout')->where('concert', '[0-9]+');
    Route::post('/concerts/{concert}/confirm-payment', [BookingController::class, 'confirmPayment'])->name('bookings.confirm-payment')->where('concert', '[0-9]+');
    Route::get('/bookings/{booking}/tickets', [BookingController::class, 'tickets'])->name('bookings.tickets')->where('booking', '[0-9]+');
    Route::get('/concerts/{concert}/seats', [BookingController::class, 'getSeats'])->name('bookings.seats')->where('concert', '[0-9]+');

    Route::prefix('api')->group(function () {
        Route::get('/concerts/{concert}/ticket-options', [ConcertBookingApiController::class, 'ticketOptions'])->name('api.concerts.ticket-options')->where('concert', '[0-9]+');
        Route::get('/concerts/{concert}/seats', [ConcertBookingApiController::class, 'seats'])->name('api.concerts.seats')->where('concert', '[0-9]+');
        Route::post('/concerts/{concert}/bookings', [ConcertBookingApiController::class, 'store'])->name('api.concerts.bookings.store')->where('concert', '[0-9]+');
    });
});

// Generic concert show route
Route::get('/concerts/{concert}', [HomeController::class, 'show'])->name('concerts.show')->where('concert', '[0-9]+');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'user'])->name('dashboard');

Route::middleware(['auth', 'user'])->group(function () {
    Route::resource('bookings', BookingController::class, ['only' => ['index', 'show']]);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [AdminProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::get('/analytics', [AdminDashboardController::class, 'analytics'])->name('analytics');
    Route::get('/activity-logs', [AdminDashboardController::class, 'activityLogs'])->name('activity-logs');
    Route::get('/ticket-management', [AdminDashboardController::class, 'ticketManagement'])->name('ticket-management');
    Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{transaction}', [AdminTransactionController::class, 'show'])->name('transactions.show');

    Route::resource('concerts', ConcertController::class);
    Route::resource('venues', VenueController::class);
    Route::resource('users', AdminUserController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    // Add more admin routes

    // API endpoints for dashboard charts (accessible via session auth)
    Route::prefix('api')->group(function () {
        Route::get('/metrics', [DashboardApiController::class, 'metrics'])->name('api.metrics');
        Route::get('/analytics', [DashboardApiController::class, 'analytics'])->name('api.analytics');
        Route::get('/activity-logs', [DashboardApiController::class, 'activityLogs'])->name('api.activity-logs');
    });
});

require __DIR__.'/auth.php';
