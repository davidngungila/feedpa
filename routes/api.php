<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EcommercePaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// E-commerce Payment API Routes
Route::prefix('ecommerce')->group(function () {
    Route::post('/payments/initiate', [EcommercePaymentController::class, 'initiatePayment']);
    Route::get('/payments/status/{orderReference}', [EcommercePaymentController::class, 'checkStatus']);
    Route::get('/payments/history', [EcommercePaymentController::class, 'transactionHistory']);
});
