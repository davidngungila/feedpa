<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SmsGatewayController;

Route::prefix('v1')->group(function () {
    Route::post('/auth/device/activate', [SmsGatewayController::class, 'activate']);
    Route::post('/auth/device/login', [SmsGatewayController::class, 'login']);

    Route::middleware('device.auth')->group(function () {
        Route::get('/device/config', [SmsGatewayController::class, 'config']);
        Route::post('/device/heartbeat', [SmsGatewayController::class, 'heartbeat']);
        Route::get('/device/status', [SmsGatewayController::class, 'status']);

        Route::post('/sms', [SmsGatewayController::class, 'single']);
        Route::post('/sms/batch', [SmsGatewayController::class, 'batch']);
        Route::get('/sms/{id}/status', [SmsGatewayController::class, 'smsStatus']);
    });
});
