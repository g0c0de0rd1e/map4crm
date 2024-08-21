<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MapController;

Route::get('/map', [MapController::class, 'showMap']);
Route::get('/tracker', [MapController::class, 'showTracker']);
Route::post('/save-address', [MapController::class, 'saveAddress']);
Route::post('/confirm-order', [MapController::class, 'confirmOrder']);
Route::get('/delivery-location/{id}', [MapController::class, 'getDeliveryLocation']);
Route::get('/get-user-location', [MapController::class, 'getUserLocation']);
