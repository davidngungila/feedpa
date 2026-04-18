<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\BillPayController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\CallbackController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('dashboard.index');
});

// Dashboard Routes
Route::prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/advanced', [DashboardController::class, 'advanced'])->name('advanced');
    Route::get('/live-status', [DashboardController::class, 'liveStatus'])->name('live-status');
});

// Payment Routes
Route::prefix('payments')->name('payments.')->group(function () {
    Route::get('/create', [PaymentController::class, 'create'])->name('create');
    Route::post('/', [PaymentController::class, 'store'])->name('store');
    Route::post('/store', [PaymentController::class, 'store'])->name('store.alt'); // Add explicit /store route
    Route::get('/store', function() {
        return redirect('/payment')->with('info', 'Please use the payment form to submit payments.');
    }); // Handle direct access to /store
    Route::get('/status', [PaymentController::class, 'status'])->name('status');
    Route::get('/history', [PaymentController::class, 'history'])->name('history');
    Route::get('/export/pdf', [PaymentController::class, 'exportPdf'])->name('export.pdf');
    Route::get('/export/excel', [PaymentController::class, 'exportExcel'])->name('export.excel');
    Route::get('/receipt/{orderReference}', [PaymentController::class, 'receipt'])->name('receipt');
    Route::post('/api/status', [PaymentController::class, 'apiStatus'])->name('api.status');
    Route::post('/resend-ussd', [PaymentController::class, 'resendUssd'])->name('resend-ussd');
});

// Public Payment Page
Route::get('/payment', function () {
    return view('public.swahili-payment');
})->name('public.payment');

// Payout Routes
Route::prefix('payouts')->name('payouts.')->group(function () {
    Route::get('/create', [PayoutController::class, 'create'])->name('create');
    Route::post('/', [PayoutController::class, 'store'])->name('store');
    Route::get('/status', [PayoutController::class, 'status'])->name('status');
    Route::get('/history', [PayoutController::class, 'history'])->name('history');
});

// BillPay Routes
Route::prefix('billpay')->name('billpay.')->group(function () {
    Route::get('/', [BillPayController::class, 'index'])->name('index');
    Route::get('/create', [BillPayController::class, 'create'])->name('create');
    Route::post('/', [BillPayController::class, 'store'])->name('store');
    Route::get('/{billPayNumber}', [BillPayController::class, 'show'])->name('show');
    Route::patch('/{billPayNumber}', [BillPayController::class, 'update'])->name('update');
});

// Account Routes
Route::prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'index'])->name('index');
    Route::get('/balance', [AccountController::class, 'balance'])->name('balance');
    Route::get('/statement', [AccountController::class, 'statement'])->name('statement');
    
    // API Endpoints
    Route::get('/balance/api', [AccountController::class, 'balanceApi'])->name('balance.api');
});

// Callback/Webhook Routes
Route::prefix('webhooks')->name('webhooks.')->group(function () {
    Route::post('/clickpesa', [CallbackController::class, 'handle'])->name('clickpesa');
    Route::post('/clickpesa/test', [CallbackController::class, 'test'])->name('clickpesa.test');
});

// Authentication Routes (if needed)
Auth::routes();
