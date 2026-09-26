<?php

use App\Http\Controllers\Api\TelemetryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — NUTRIX Telemetry & Farm Actions
|--------------------------------------------------------------------------
| Semua route di bawah membutuhkan session auth (bukan token Sanctum).
| Ownership per-taman divalidasi di dalam TelemetryController.
// Endpoint Publik Khusus ESP32 (Wireless IoT)
Route::post('/iot/telemetry', [TelemetryController::class, 'ingestDeviceTelemetry']);

Route::middleware('auth')->group(function () {

    // Ambil data sensor terbaru + 20 riwayat
    Route::get('/taman/{taman}/telemetry', [TelemetryController::class, 'index']);
    Route::get('/taman/{taman}/telemetry/latest', [TelemetryController::class, 'latest']);

    // Pairing sensor fisik ketika perangkat sudah tersedia.
    Route::post('/taman/{taman}/sensor/connect', [TelemetryController::class, 'connectSensor']);
    Route::delete('/taman/{taman}/sensor', [TelemetryController::class, 'disconnectSensor']);

    // Generate rekam sensor baru (sync) dan catat di farm_activities
    Route::post('/taman/{taman}/sync', [TelemetryController::class, 'sync']);

    // Aksi remote: siram
    Route::post('/taman/{taman}/actions/water', [TelemetryController::class, 'water']);

    // Aksi remote: pupuk
    Route::post('/taman/{taman}/actions/fertilize', [TelemetryController::class, 'fertilize']);

    // Riwayat aktivitas taman (50 terakhir)
    Route::get('/taman/{taman}/activities', [TelemetryController::class, 'activities']);

    // Export telemetry sebagai CSV
    Route::get('/taman/{taman}/export.csv', [TelemetryController::class, 'exportCsv']);
});
