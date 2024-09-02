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
    Route::post('/update-order-status/{id}', [MapController::class, 'updateOrderStatus']);
    Route::get('/get-delivery-coordinates/{id}', [MapController::class, 'getDeliveryCoordinates']);
    Route::post('/save-address', [MapController::class, 'saveAddress']);
    Route::post('/confirm-order', [MapController::class, 'confirmOrder']);
    Route::post('/save-courier-coordinates', [MapController::class, 'saveCourierCoordinates']);
    Route::get('/user-map/{id}', [MapController::class, 'showUserMap'])->name('user.map');
    Route::get('/get-user-location-by-order/{orderId}', [MapController::class, 'getUserLocationByOrderId']);
});

require __DIR__.'/auth.php';
