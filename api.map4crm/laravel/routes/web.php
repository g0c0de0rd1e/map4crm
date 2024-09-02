<?php

use App\Http\Controllers\MapController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/map', [MapController::class, 'showMap']);
    Route::get('/worker', [MapController::class, 'showWorker']);
    Route::get('/get-orders', [MapController::class, 'streamOrders']);
    Route::get('/user-addresses', [MapController::class, 'getUserAddresses']);
    Route::post('/update-order-status/{id}', [MapController::class, 'updateOrderStatus']);
    Route::get('/get-delivery-coordinates/{id}', [MapController::class, 'getDeliveryCoordinates']);
    // Route::get('/tracker', [MapController::class, 'showTracker']);
    Route::post('/save-address', [MapController::class, 'saveAddress']);
    Route::post('/confirm-order', [MapController::class, 'confirmOrder']);
    Route::post('/save-courier-coordinates', [MapController::class, 'saveCourierCoordinates']);
    Route::get('/user-map/{id}', [MapController::class, 'showUserMap'])->name('user.map');
    Route::get('/delivery-location/{id}', [MapController::class, 'getDeliveryLocation']);
    Route::get('/get-user-location', [MapController::class, 'getUserLocation']);
});

require __DIR__.'/auth.php';
