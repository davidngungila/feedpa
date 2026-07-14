<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\CallbackController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\AppNotificationController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\BeneficiaryController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AiChatController;

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
        Route::post('/clear-cache', function () {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            return back()->with('success', 'Cache cleared successfully!');
        })->name('clear-cache');
        Route::get('/export/pdf', [DashboardController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/ai-chat', [AiChatController::class, 'index'])->name('ai-chat.index');
        Route::post('/ai-chat', [AiChatController::class, 'chat'])->name('ai-chat');
    });

    // Payment Routes (Authenticated)
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/create', [PaymentController::class, 'create'])->name('create');
        Route::get('/history', [PaymentController::class, 'history'])->name('history');
        Route::get('/export/pdf', [PaymentController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/export/excel', [PaymentController::class, 'exportExcel'])->name('export.excel');
        Route::post('/resend-ussd', [PaymentController::class, 'resendUssd'])->name('resend-ussd');
        Route::post('/{orderReference}/notes', [PaymentController::class, 'addNote'])->name('notes.add');
        Route::post('/{orderReference}/send-sms', [PaymentController::class, 'sendManualSMS'])->name('send-sms');
        Route::post('/{orderReference}/send-email', [PaymentController::class, 'sendManualEmail'])->name('send-email');
        Route::post('/{orderReference}/retry', [PaymentController::class, 'retryPayment'])->name('retry');
    });

    // Payout Routes (Authenticated)
    Route::prefix('payouts')->name('payouts.')->group(function () {
        Route::get('/', [PayoutController::class, 'index'])->name('index');
        Route::get('/create', [PayoutController::class, 'create'])->name('create');
        Route::post('/preview', [PayoutController::class, 'previewPayout'])->name('preview');
        Route::post('/detect-provider', [PayoutController::class, 'detectProvider'])->name('detect-provider');
        Route::post('/lookup-account-name', [PayoutController::class, 'lookupAccountName'])->name('lookup-account-name');
        Route::post('/', [PayoutController::class, 'store'])->name('store');
        Route::get('/{orderReference}/verify', [PayoutController::class, 'showVerifyOtp'])->name('verify-otp');
        Route::post('/{orderReference}/verify', [PayoutController::class, 'verifyOtp'])->name('verify');
        Route::post('/{orderReference}/resend-otp', [PayoutController::class, 'resendOtp'])->name('resend-otp');
        Route::post('/{orderReference}/approve', [PayoutController::class, 'approve'])->name('approve');
        Route::post('/{orderReference}/cancel', [PayoutController::class, 'cancel'])->name('cancel');
        Route::post('/{orderReference}/reject', [PayoutController::class, 'reject'])->name('reject');
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
        Route::post('/transaction/fetch', [AccountController::class, 'fetchSingleTransaction'])->name('transaction.fetch');
        Route::post('/transaction/sync', [AccountController::class, 'syncSingleTransaction'])->name('transaction.sync');
        Route::post('/payout/fetch', [AccountController::class, 'fetchSinglePayout'])->name('payout.fetch');
        Route::post('/payout/sync', [AccountController::class, 'syncSinglePayout'])->name('payout.sync');
    });

    // Bill Management Routes
    Route::prefix('bills')->name('bills.')->group(function () {
        Route::get('/', [BillController::class, 'index'])->name('index');
        Route::get('/create-order', [BillController::class, 'createOrder'])->name('create-order');
        Route::post('/store-order', [BillController::class, 'storeOrder'])->name('store-order');
        Route::get('/create-customer', [BillController::class, 'createCustomer'])->name('create-customer');
        Route::post('/store-customer', [BillController::class, 'storeCustomer'])->name('store-customer');
        Route::get('/{id}/edit', [BillController::class, 'edit'])->name('edit');
        Route::put('/{id}', [BillController::class, 'update'])->name('update');
        Route::get('/{id}', [BillController::class, 'show'])->name('show');
        Route::get('/{id}/pdf', [BillController::class, 'pdf'])->name('pdf');
    });

    // Beneficiary Management Routes
    Route::resource('beneficiaries', BeneficiaryController::class);
    
    // User Management Routes
    Route::resource('users', UserController::class);
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    
    // Audit Log Routes
    Route::get('audits', [AuditController::class, 'index'])->name('audits.index');
    Route::get('audits/export/pdf', [AuditController::class, 'exportPdf'])->name('audits.export.pdf');
    Route::delete('audits/{audit}', [AuditController::class, 'destroy'])->name('audits.destroy');
    Route::delete('audits/bulk/destroy', [AuditController::class, 'bulkDestroy'])->name('audits.bulk-destroy');

    // In-app notification routes
    Route::get('notifications', [AppNotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/mark-all-read', [AppNotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::get('notifications/{notification}', [AppNotificationController::class, 'open'])->name('notifications.open');
    
    // Financial Reports Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/trial-balance', [ReportController::class, 'trialBalance'])->name('trial-balance');
        Route::get('/trial-balance/export/pdf', [ReportController::class, 'exportTrialBalance'])->name('trial-balance.export.pdf');
        Route::get('/balance-sheet', [ReportController::class, 'balanceSheet'])->name('balance-sheet');
        Route::get('/balance-sheet/export/pdf', [ReportController::class, 'exportBalanceSheet'])->name('balance-sheet.export.pdf');
        Route::get('/profit-loss', [ReportController::class, 'profitLoss'])->name('profit-loss');
        Route::get('/profit-loss/export/pdf', [ReportController::class, 'exportProfitLoss'])->name('profit-loss.export.pdf');
        Route::get('/customer-report', [ReportController::class, 'customerReport'])->name('customer-report');
        Route::get('/customer-report/export/pdf', [ReportController::class, 'exportCustomerReportPdf'])->name('customer-report.export.pdf');
        Route::get('/customer-report/export/excel', [ReportController::class, 'exportCustomerReportExcel'])->name('customer-report.export.excel');
    });

    // Profile Routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [UserController::class, 'profile'])->name('index');
        Route::get('/edit', [UserController::class, 'editProfile'])->name('edit');
        Route::put('/update', [UserController::class, 'updateProfile'])->name('update');
        Route::put('/change-password', [UserController::class, 'updatePassword'])->name('password');
        Route::get('/sessions', [UserController::class, 'getActiveSessions'])->name('sessions');
        Route::post('/sessions/logout/{sessionId}', [UserController::class, 'logoutSession'])->name('sessions.logout');
        Route::post('/sessions/logout-others', [UserController::class, 'logoutOtherSessions'])->name('sessions.logout-others');
        
        // 2FA Routes
        Route::get('/two-factor', [UserController::class, 'showTwoFactorSetup'])->name('two-factor.setup');
        Route::post('/two-factor/enable', [UserController::class, 'enableTwoFactor'])->name('two-factor.enable');
        Route::get('/two-factor/disable', [UserController::class, 'showDisableTwoFactor'])->name('two-factor.disable.show');
        Route::post('/two-factor/disable', [UserController::class, 'disableTwoFactor'])->name('two-factor.disable');
        Route::post('/two-factor/recovery-codes/regenerate', [UserController::class, 'regenerateRecoveryCodes'])->name('two-factor.recovery-codes.regenerate');
        Route::get('/two-factor/recovery-codes', [UserController::class, 'showRecoveryCodes'])->name('two-factor.recovery-codes');
        Route::get('/two-factor/recovery-codes/pdf', [UserController::class, 'downloadRecoveryCodesPdf'])->name('two-factor.recovery-codes.pdf');
    });
    
    // Settings Routes (Admin Only)
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/sms', [SettingsController::class, 'sms'])->name('sms');
        Route::post('/sms/update', [SettingsController::class, 'updateSms'])->name('sms.update');
        Route::post('/sms/test', [SettingsController::class, 'testSms'])->name('sms.test');
        Route::get('/email', [SettingsController::class, 'email'])->name('email');
        Route::post('/email/update', [SettingsController::class, 'updateEmail'])->name('email.update');
        Route::get('/general', [SettingsController::class, 'general'])->name('general');
        Route::post('/general/update', [SettingsController::class, 'updateGeneral'])->name('general.update');
        Route::post('/users/{user}/toggle-lock', [SettingsController::class, 'toggleUserLock'])->name('users.toggle-lock');
        Route::delete('/users/{user}', [SettingsController::class, 'deleteUser'])->name('users.delete');
        Route::get('/ai', [SettingsController::class, 'ai'])->name('ai');
        Route::post('/ai/update', [SettingsController::class, 'updateAi'])->name('ai.update');
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
    Route::get('/', function() {
        return redirect('/payment')->with('info', 'Please use the payment form to submit payments.');
    }); // Handle direct access to /payments
    Route::get('/store', function() {
        return redirect('/payment')->with('info', 'Please use the payment form to submit payments.');
    }); // Handle direct access to /store
    Route::get('/status', [PaymentController::class, 'status'])->name('status');
    Route::get('/receipt/{orderReference}', [PaymentController::class, 'receipt'])->name('receipt');
    Route::post('/api/status', [PaymentController::class, 'apiStatus'])->name('api.status');
    Route::post('/{orderReference}/retry', [PaymentController::class, 'retryPayment'])->name('retry');
});

// Callback/Webhook Routes
Route::prefix('webhooks')->name('webhooks.')->group(function () {
    Route::post('/clickpesa', [CallbackController::class, 'handle'])->name('clickpesa')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    Route::post('/clickpesa/test', [CallbackController::class, 'test'])->name('clickpesa.test')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
});

// Forgot Password Routes
Route::prefix('password')->name('password.')->group(function () {
    Route::get('/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('request');
    Route::post('/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('email');
    Route::get('/otp', [ForgotPasswordController::class, 'showOtpForm'])->name('otp');
    Route::post('/otp', [ForgotPasswordController::class, 'verifyOtp'])->name('verify');
    Route::get('/reset-password', [ForgotPasswordController::class, 'showResetPasswordForm'])->name('reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('update');
});

// Secure Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/entry', [LoginController::class, 'issueEntry'])->name('login');
    Route::match(['get', 'post'], '/login', fn () => redirect('/'));
    Route::match(['get', 'post'], '/register', fn () => redirect('/'));
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Two-Factor Authentication Routes
Route::get('/two-factor', [LoginController::class, 'showTwoFactorLoginForm'])->name('two-factor.login');
Route::post('/two-factor', [LoginController::class, 'verifyTwoFactor'])->name('two-factor.verify');

Route::middleware('guest')->group(function () {
    Route::get('/{entryToken}', [LoginController::class, 'showLoginForm'])
        ->where('entryToken', '[A-Za-z0-9\-_]{40,}')
        ->name('login.form');
    Route::post('/{entryToken}', [LoginController::class, 'login'])
        ->where('entryToken', '[A-Za-z0-9\-_]{40,}')
        ->name('login.attempt');
});
