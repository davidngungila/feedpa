<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SmsDeviceController;
use App\Http\Controllers\SmsInboxController;
use App\Http\Controllers\SmsReconciliationController;
use App\Http\Controllers\SmsDashboardController;

Route::middleware('auth')->prefix('sms-gateway')->name('sms-gateway.')->group(function () {

    Route::get('/', [SmsDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [SmsDashboardController::class, 'index'])->name('dashboard.index');

    Route::resource('devices', SmsDeviceController::class);
    Route::post('devices/{device}/generate-code', [SmsDeviceController::class, 'generateCode'])->name('devices.generate-code');
    Route::post('devices/{device}/revoke', [SmsDeviceController::class, 'revoke'])->name('devices.revoke');
    Route::post('devices/{device}/suspend', [SmsDeviceController::class, 'suspend'])->name('devices.suspend');
    Route::post('devices/{device}/activate', [SmsDeviceController::class, 'activate'])->name('devices.activate');
    Route::put('devices/{device}/config', [SmsDeviceController::class, 'updateConfig'])->name('devices.update-config');

    // SMS-only sidebar menu target
    Route::get('inbox', [SmsInboxController::class, 'index'])->name('inbox');
    Route::get('inbox/{sms}', [SmsInboxController::class, 'show'])->name('inbox.show');
    Route::get('inbox/{sms}/details', [SmsInboxController::class, 'details'])->name('inbox.details');
    Route::post('inbox/{sms}/retry', [SmsInboxController::class, 'retry'])->name('inbox.retry');
    Route::post('inbox/{sms}/recorded', [SmsInboxController::class, 'toggleRecorded'])->name('inbox.recorded');
    Route::post('inbox/{sms}/comment', [SmsInboxController::class, 'comment'])->name('inbox.comment');
    Route::get('sms', [SmsInboxController::class, 'index'])->name('sms');
    Route::get('sms/{sms}', [SmsInboxController::class, 'show'])->name('sms.show');
    Route::get('sms/{sms}/details', [SmsInboxController::class, 'details'])->name('sms.details');
    Route::post('sms/{sms}/recorded', [SmsInboxController::class, 'toggleRecorded'])->name('sms.recorded');
    Route::post('sms/{sms}/comment', [SmsInboxController::class, 'comment'])->name('sms.comment');

    Route::get('reconciliation', [SmsReconciliationController::class, 'index'])->name('reconciliation.index');
    Route::post('reconciliation/{reconciliation}/match', [SmsReconciliationController::class, 'match'])->name('reconciliation.match');
    Route::post('reconciliation/{reconciliation}/unmatch', [SmsReconciliationController::class, 'unmatch'])->name('reconciliation.unmatch');
});
