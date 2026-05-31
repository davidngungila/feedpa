@extends('layouts.app')

@section('title', 'Verify Payout OTP')

@section('content')
@php
    $isSuccessful = false;
    $isFailed = false;
    $statusText = $payout->status ?? 'PENDING';
    $statusIcon = 'fa-clock';
    $statusColor = 'badge-yellow';
@endphp

<div class="max-w-4xl mx-auto space-y-6 animate-fade-in">
    @if(session('error'))
        <div class="card p-6 border-red-100 bg-red-50 dark:bg-red-900/10">
            <div class="flex items-center gap-4 text-red-600 dark:text-red-400">
                <i class="fas fa-exclamation-triangle text-2xl"></i>
                <div>
                    <h4 class="font-bold">Error</h4>
                    <p class="text-xs">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="card p-6 border-green-100 bg-green-50 dark:bg-green-900/10">
            <div class="flex items-center gap-4 text-green-600 dark:text-green-400">
                <i class="fas fa-check-circle text-2xl"></i>
                <div>
                    <h4 class="font-bold">Success</h4>
                    <p class="text-xs">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Status Header Card -->
    <div class="card overflow-hidden">
        <div class="p-6 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                <!-- QR Code Section -->
                <div class="p-3 bg-white rounded-2xl border border-primary-100 shadow-sm flex-shrink-0">
                    @php
                        $qrContent = "FEEDTAN PAYOUT OTP:\n" .
                            "Order Reference: {$payout->order_reference}\n" .
                            "Transaction ID: {$payout->id}\n" .
                            "Amount: {$payout->currency} " . number_format($payout->amount, 2) . "\n" .
                            "Status: {$statusText}\n" .
                            "Recipient: {$payout->recipient_name}\n" .
                            "Phone: {$payout->recipient_phone}\n" .
                            "Date: " . $payout->created_at->format('d/m/Y H:i:s');
                    @endphp
                    {!! QrCode::size(100)->margin(1)->encoding('UTF-8')->errorCorrection('H')->generate($qrContent) !!}
                </div>
                <div>
                    <div class="text-[10px] text-primary-500 uppercase font-extrabold tracking-widest mb-1">Order Reference</div>
                    <div class="text-xl font-mono font-bold text-primary-900 dark:text-white">{{ $payout->order_reference }}</div>
                    <div class="mt-2">
                        <span class="badge {{ $statusColor }} px-4 py-1.5 text-xs">
                            <i class="fas {{ $statusIcon }} me-2"></i>
                            {{ $statusText }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="text-center sm:text-right">
                <div class="text-[10px] text-primary-500 uppercase font-extrabold tracking-widest mb-1">Total Amount</div>
                <div class="text-3xl font-mono font-black text-primary-600 dark:text-primary-400">
                    {{ $payout->currency }} {{ number_format($payout->amount, 2) }}
                </div>
            </div>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Beneficiary Info -->
        <div class="card p-6 space-y-4">
            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                <i class="fas fa-user-circle"></i> Beneficiary Information
            </h3>
            <div class="space-y-3">
                <div>
                    <div class="text-[10px] text-gray-400 uppercase font-bold">Recipient Name</div>
                    <div class="font-bold text-primary-900 dark:text-white">{{ $payout->recipient_name }}</div>
                </div>
                <div class="flex gap-6">
                    <div>
                        <div class="text-[10px] text-gray-400 uppercase font-bold">Phone</div>
                        <div class="font-mono text-sm text-primary-800 dark:text-primary-200">{{ $payout->recipient_phone ?? $payout->beneficiary_mobile }}</div>
                    </div>
                </div>
                @if($payout->payout_type !== 'MOBILE_MONEY' && $payout->bank_account_number)
                    <div class="pt-2 border-t border-primary-100 dark:border-dark-border">
                        <div class="text-[10px] text-gray-400 uppercase font-bold">Account Number</div>
                        <div class="font-mono text-sm text-primary-800 dark:text-primary-200">{{ $payout->bank_account_number }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Transaction Info -->
        <div class="card p-6 space-y-4">
            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                <i class="fas fa-receipt"></i> Payout Details
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                    <span class="text-xs text-gray-400">Payout ID</span>
                    <span class="text-xs font-mono font-bold text-primary-900 dark:text-white">{{ $payout->id }}</span>
                </div>
                <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                    <span class="text-xs text-gray-400">Date & Time</span>
                    <span class="text-xs font-bold text-primary-900 dark:text-white">
                        {{ $payout->created_at->format('M d, Y • H:i:s') }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-gray-400">Payout Type</span>
                    <span class="text-xs font-bold text-primary-900 dark:text-white uppercase">
                        {{ $payout->payout_type === 'MOBILE_MONEY' ? 'Mobile Money' : 'Bank Transfer' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Description Card -->
    <div class="card p-6">
        <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 mb-3 flex items-center gap-2">
            <i class="fas fa-info-circle"></i> Purpose / Description
        </h3>
        <div class="p-4 bg-primary-50 dark:bg-dark-900 rounded-xl italic text-sm text-primary-800 dark:text-primary-300 border border-primary-100 dark:border-dark-border">
            {{ $payout->description ?? 'Payout from FEEDTAN' }}
        </div>
    </div>

    <!-- OTP Form -->
    <div class="card p-6">
        <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 mb-4 flex items-center gap-2">
            <i class="fas fa-lock"></i> Verify Payout OTP
        </h3>

        <form action="{{ route('payouts.verify', $payout->order_reference) }}" method="POST">
            @csrf
            <div class="mb-6">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">
                    Enter OTP (sent to {{ auth()->user()->phone ?? 'your phone' }}
                </label>
                <input type="text" name="otp" maxlength="6" placeholder="000000" required
                       class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-4 py-4 text-3xl font-mono text-center tracking-[0.5em] font-black text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <button type="submit" class="px-4 py-3 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold shadow-lg shadow-primary-900/20 transition-all">
                    <i class="fas fa-check-circle me-2"></i> Verify Payout
                </button>
                <button type="button" id="resendOtpBtn"
                        class="flex items-center justify-center px-4 py-3 rounded-xl bg-white dark:bg-dark-card border border-primary-100 dark:border-dark-border text-primary-600 dark:text-primary-400 text-xs font-bold hover:bg-primary-50 dark:hover:bg-dark-800 transition-all">
                    <i class="fas fa-redo me-2"></i> Resend OTP
                </button>
            </div>
        </form>
    </div>

</div>

@push('scripts')
<script>
document.getElementById('resendOtpBtn').addEventListener('click', async function() {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Sending...';
    
    try {
        const response = await fetch('{{ route('payouts.resend-otp', $payout->order_reference) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            redirect: 'follow'
        });
        
        if (response.ok) {
            // Reload the page to show success message
            window.location.reload();
        } else {
            throw new Error('Failed to resend OTP');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to resend OTP. Please try again.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-redo me-2"></i> Resend OTP';
    }
});
</script>
@endpush
@endsection
