<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsappAppController;

// All WhatsApp App web routes - auth required, prefix whatsapp-app (display name WhatsApp App per spec)
Route::middleware('auth')->prefix('whatsapp-app')->name('whatsapp-app.')->group(function () {
    Route::get('/', [WhatsappAppController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [WhatsappAppController::class, 'dashboard'])->name('dashboard.index');

    Route::get('/messages/create', [WhatsappAppController::class, 'create'])->name('create');
    Route::post('/messages', [WhatsappAppController::class, 'store'])->name('store');
    Route::get('/messages/{whatsappMessage}', [WhatsappAppController::class, 'show'])->name('show');
    Route::post('/messages/{whatsappMessage}/cancel', [WhatsappAppController::class, 'cancel'])->name('cancel');
    Route::get('/messages/{whatsappMessage}/logs', [WhatsappAppController::class, 'logs'])->name('logs');

    Route::get('/outbox', [WhatsappAppController::class, 'outbox'])->name('outbox');
    Route::get('/sent', function(\Illuminate\Http\Request $r){ $r->merge(['status'=>'SENT']); return app(WhatsappAppController::class)->outbox($r); })->name('sent');
    Route::get('/pending', function(\Illuminate\Http\Request $r){ $r->merge(['status'=>'PENDING']); return app(WhatsappAppController::class)->outbox($r); })->name('pending');
    Route::get('/failed', function(\Illuminate\Http\Request $r){ $r->merge(['status'=>'FAILED']); return app(WhatsappAppController::class)->outbox($r); })->name('failed');

    Route::get('/devices', [WhatsappAppController::class, 'devices'])->name('devices');
    Route::get('/templates', [WhatsappAppController::class, 'templates'])->name('templates');
    Route::post('/templates', [WhatsappAppController::class, 'storeTemplate'])->name('templates.store');

    // Backwards compat aliases for spec naming
    Route::get('/inbox', [WhatsappAppController::class, 'outbox'])->name('inbox');
});

// Keep old /whatsapp/gateway routes for spec compatibility but redirect to whatsapp-app
Route::middleware('auth')->prefix('whatsapp')->name('whatsapp.gateway.')->group(function(){
    Route::get('/dashboard', fn()=> redirect()->route('whatsapp-app.dashboard'))->name('dashboard');
});
