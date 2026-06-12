<?php

use App\Http\Controllers\Api\AuthapiController;
use App\Http\Controllers\Api\BookingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::post('/register', [AuthapiController::class, 'register']);
Route::post('/login', [AuthapiController::class, 'login']);
Route::get('/workers/{category}', [AuthapiController::class, 'getWorkersByCategory']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthapiController::class, 'logout']);
    Route::post('/worker/update-profile', [AuthapiController::class, 'updateWorkerProfile']);
    
    // Booking Routes
    Route::post('/bookings', [BookingController::class, 'createBooking']);
    Route::get('/my-bookings', [BookingController::class, 'getMyBookings']);
    Route::put('/bookings/{id}/status', [BookingController::class, 'updateStatus']); // 🚀 IDINAGDAG: Endpoint para sa 7-Step workflow tracking!
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});