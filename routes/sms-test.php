<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SMSController;

// SMS Testing Routes (temporary for testing)
Route::prefix('sms-test')->name('sms-test.')->group(function () {
    Route::get('/', [SMSController::class, 'index'])->name('index');
    Route::post('/send', [SMSController::class, 'sendSMS'])->name('send');
    Route::post('/test-bill', [SMSController::class, 'testBillNotification'])->name('test-bill');
    Route::post('/test-payment', [SMSController::class, 'testPaymentNotification'])->name('test-payment');
    Route::post('/test-insufficient', [SMSController::class, 'testInsufficientFunds'])->name('test-insufficient');
});
