<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\CallbackController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\ReportController;

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
    return view('public.swahili-payment');
});

// Protected Routes (Require Authentication)
Route::middleware(['auth'])->group(function () {
    // Dashboard Routes
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/account-balance', [DashboardController::class, 'accountBalance'])->name('account-balance');
        Route::get('/advanced', [DashboardController::class, 'advanced'])->name('advanced');
        Route::get('/live-status', [DashboardController::class, 'liveStatus'])->name('live-status');
        Route::post('/send-manual-sms', [DashboardController::class, 'sendManualSMS'])->name('send.manual.sms');
        Route::post('/sync-transactions', [DashboardController::class, 'syncTransactions'])->name('sync-transactions');
        Route::post('/sync-bills', [DashboardController::class, 'syncBills'])->name('sync-bills');
    });

    // Payment Routes (Authenticated)
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/create', [PaymentController::class, 'create'])->name('create');
        Route::get('/history', [PaymentController::class, 'history'])->name('history');
        Route::get('/export/pdf', [PaymentController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/export/excel', [PaymentController::class, 'exportExcel'])->name('export.excel');
        Route::post('/resend-ussd', [PaymentController::class, 'resendUssd'])->name('resend-ussd');
        Route::post('/{orderReference}/notes', [PaymentController::class, 'addNote'])->name('notes.add');
    });

    // Payout Routes (Authenticated)
    Route::prefix('payouts')->name('payouts.')->group(function () {
        Route::get('/', [PayoutController::class, 'index'])->name('index');
        Route::get('/create', [PayoutController::class, 'create'])->name('create');
        Route::post('/', [PayoutController::class, 'store'])->name('store');
        Route::get('/{orderReference}/verify', [PayoutController::class, 'showVerifyOtp'])->name('verify-otp');
        Route::post('/{orderReference}/verify', [PayoutController::class, 'verifyOtp'])->name('verify');
        Route::post('/{orderReference}/resend-otp', [PayoutController::class, 'resendOtp'])->name('resend-otp');
        Route::get('/{orderReference}', [PayoutController::class, 'show'])->name('status');
        Route::post('/{orderReference}/refresh', [PayoutController::class, 'refreshStatus'])->name('refresh');
        Route::post('/{orderReference}/notes', [PayoutController::class, 'addNote'])->name('notes.add');
        Route::post('/sync', [PayoutController::class, 'syncFromApi'])->name('sync');
        Route::get('/export/pdf', [PayoutController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/export/excel', [PayoutController::class, 'exportExcel'])->name('export.excel');
        Route::get('/receipt/{orderReference}', [PayoutController::class, 'receipt'])->name('receipt');
    });

    // Account Routes
    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/', [AccountController::class, 'index'])->name('index');
        Route::get('/balance', [AccountController::class, 'balance'])->name('balance');
        Route::get('/statement', [AccountController::class, 'statement'])->name('statement');
        
        // API Endpoints
        Route::get('/balance/api', [AccountController::class, 'balanceApi'])->name('balance.api');
    });

    // Bill Management Routes
    Route::prefix('bills')->name('bills.')->group(function () {
        Route::get('/', [BillController::class, 'index'])->name('index');
        Route::get('/create-order', [BillController::class, 'createOrder'])->name('create-order');
        Route::post('/store-order', [BillController::class, 'storeOrder'])->name('store-order');
        Route::get('/create-customer', [BillController::class, 'createCustomer'])->name('create-customer');
        Route::post('/store-customer', [BillController::class, 'storeCustomer'])->name('store-customer');
        Route::get('/{id}', [BillController::class, 'show'])->name('show');
        Route::get('/{id}/pdf', [BillController::class, 'pdf'])->name('pdf');
    });

    // User Management Routes
    Route::resource('users', UserController::class);
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    
    // Audit Log Routes
    Route::get('audits', [AuditController::class, 'index'])->name('audits.index');
    
    // Financial Reports Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/trial-balance', [ReportController::class, 'trialBalance'])->name('trial-balance');
        Route::get('/trial-balance/export/pdf', [ReportController::class, 'exportTrialBalance'])->name('trial-balance.export.pdf');
        Route::get('/balance-sheet', [ReportController::class, 'balanceSheet'])->name('balance-sheet');
        Route::get('/balance-sheet/export/pdf', [ReportController::class, 'exportBalanceSheet'])->name('balance-sheet.export.pdf');
        Route::get('/profit-loss', [ReportController::class, 'profitLoss'])->name('profit-loss');
        Route::get('/profit-loss/export/pdf', [ReportController::class, 'exportProfitLoss'])->name('profit-loss.export.pdf');
    });

    // Profile Routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [UserController::class, 'profile'])->name('index');
        Route::get('/edit', [UserController::class, 'editProfile'])->name('edit');
        Route::put('/update', [UserController::class, 'updateProfile'])->name('update');
        Route::put('/change-password', [UserController::class, 'updatePassword'])->name('password');
    });
    
    // Sync Trigger Routes
    Route::post('/api-sync', function () {
        Artisan::call('app:sync-transactions-from-api');
        return back()->with('success', 'Transactions synced successfully!');
    })->name('api-sync');
    
    Route::post('/api-sync-bills', function () {
        Artisan::call('app:sync-bills-from-api');
        return back()->with('success', 'Bills synced successfully!');
    })->name('api-sync-bills');
});

// Public Payment Page
Route::get('/payment', function () {
    return view('public.swahili-payment');
})->name('public.payment');

// Public Payment Routes (No authentication required)
Route::prefix('payments')->name('payments.')->group(function () {
    Route::post('/', [PaymentController::class, 'store'])->name('store');
    Route::post('/store', [PaymentController::class, 'store'])->name('store.alt'); // Add explicit /store route
    Route::get('/store', function() {
        return redirect('/payment')->with('info', 'Please use the payment form to submit payments.');
    }); // Handle direct access to /store
    Route::get('/status', [PaymentController::class, 'status'])->name('status');
    Route::get('/receipt/{orderReference}', [PaymentController::class, 'receipt'])->name('receipt');
    Route::post('/api/status', [PaymentController::class, 'apiStatus'])->name('api.status');
});

// Callback/Webhook Routes
Route::prefix('webhooks')->name('webhooks.')->group(function () {
    Route::post('/clickpesa', [CallbackController::class, 'handle'])->name('clickpesa')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    Route::post('/clickpesa/test', [CallbackController::class, 'test'])->name('clickpesa.test')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
});

// Authentication Routes (if needed)
Auth::routes();
