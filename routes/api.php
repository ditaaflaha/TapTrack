<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// Memanggil Controller baru yang barusan kamu buat
use App\Http\Controllers\Api\TapTrackController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Gerbang masuk data dari perangkat ESP32 kamu
Route::post('/tap-track', [TapTrackController::class, 'handleTap']);