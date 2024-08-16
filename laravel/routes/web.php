<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;

Route::post('/save-location', [LocationController::class, 'saveLocation']);
Route::get('/tracker', function () {
    return view('tracker');
});