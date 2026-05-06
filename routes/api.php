<?php

use App\Http\Controllers\Api\SensorDataController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth.apitoken')->group(function () {

    // Terima data dari ESP32 (dipanggil setiap 30 menit)
    Route::post('/sensor-data', [SensorDataController::class, 'store']);

    // Ambil riwayat data (untuk dashboard)
    Route::get('/sensor-data', [SensorDataController::class, 'index']);

    // Ambil data terbaru
    Route::get('/sensor-data/latest', [SensorDataController::class, 'latest']);

});