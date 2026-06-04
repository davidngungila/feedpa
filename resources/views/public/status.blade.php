<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hali ya Malipo — FeedTan</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
                    colors: {
                        primary: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        }
                    },
                    boxShadow: {
                        card: '0 25px 50px -12px rgba(6, 78, 59, 0.15)',
                        glow: '0 0 0 1px rgba(16, 185, 129, 0.08), 0 20px 40px -20px rgba(5, 150, 105, 0.35)',
                    },
                    keyframes: {
                        fadeUp: {
                            from: { opacity: 0, transform: 'translateY(12px)' },
                            to: { opacity: 1, transform: 'translateY(0)' }
                        }
                    },
                    animation: {
                        'fade-up': 'fadeUp 0.45s ease-out both'
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .mesh-bg {
            background-color: #f0fdf4;
            background-image:
                radial-gradient(at 0% 0%, rgba(16, 185, 129, 0.12) 0, transparent 50%),
                radial-gradient(at 100% 0%, rgba(5, 150, 105, 0.08) 0, transparent 45%),
                radial-gradient(at 50% 100%, rgba(6, 95, 70, 0.06) 0, transparent 50%);
        }
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.125rem 0.625rem;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 600;
        }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-yellow { background: #fef9c3; color: #854d0e; }
    </style>
</head>
<body class="mesh-bg min-h-screen text-slate-800 antialiased">

    <div class="min-h-screen flex flex-col">
        <!-- Top bar -->
        <header class="w-full border-b border-primary-100/80 bg-white/70 backdrop-blur-md sticky top-0 z-30">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-600 to-primary-800 flex items-center justify-center shadow-glow">
                        <i class="fa-solid fa-leaf text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-primary-900 leading-none">FeedTan CMG</p>
                        <p class="hidden sm:block text-[10px] text-primary-600/80 font-medium tracking-wide uppercase">Hali ya Malipo</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 text-[10px] font-semibold text-primary-700 bg-primary-50 border border-primary-100 px-3 py-1.5 rounded-full">
                    <i class="fas fa-shield-halved text-primary-600"></i>
                    <span class="hidden sm:inline">Salama</span>
                </div>
            </div>
        </header>

        <main class="flex-1 flex items-center justify-center px-4 py-4 sm:py-12">
            <div class="w-full max-w-5xl animate-fade-up">
                <div class="grid lg:grid-cols-1 gap-6 items-start">
                    <!-- Status Content -->
                    <div class="w-full max-w-4xl mx-auto">
                        @php
                            $payment = $paymentData;
                            $isSuccessful = in_array($payment['status'] ?? '', ['SUCCESS', 'SETTLED']);
                            $isFailed = in_array($payment['status'] ?? '', ['FAILED', 'CANCELLED', 'DECLINED']);

                            $statusText = $payment['status'] ?? 'UNKNOWN';
                            $statusIcon = 'fa-clock';
                            $statusColor = 'badge-yellow';

                            if ($isSuccessful) {
                                $statusText = 'Verified';
                                $statusIcon = 'fa-check-circle';
                                $statusColor = 'badge-green';
                            } elseif ($isFailed) {
                                $statusText = 'Failed';
                                $statusIcon = 'fa-times-circle';
                                $statusColor = 'badge-red';
                            }
                        @endphp

                        @if(isset($error) && $error)
                            <div class="bg-white rounded-2xl shadow-card p-6 border border-slate-200/80">
                                <div class="flex items-center gap-4 text-red-600">
                                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                                    <div>
                                        <h4 class="font-bold">Error</h4>
                                        <p class="text-xs">{{ $error }}</p>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <a href="{{ route('public.payment') }}" class="text-xs font-bold text-primary-600 hover:underline">
                                        <i class="fas fa-arrow-left me-1"></i> Rudi kwenye fomu ya malipo
                                    </a>
                                </div>
                            </div>
                        @elseif(!$payment)
                            <div class="bg-white rounded-2xl shadow-card p-6 border border-slate-200/80 text-center">
                                <i class="fas fa-search text-4xl text-primary-200 mb-4"></i>
                                <h4 class="font-bold text-primary-900">Hakuna Muamala</h4>
                                <p class="text-xs text-primary-500 mb-4">Hatukupata muamala na nambari ya reference: {{ $orderReference }}</p>
                                <a href="{{ route('public.payment') }}" class="inline-flex items-center justify-center gap-2 py-2.5 px-6 rounded-xl bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-500 hover:to-primary-600 text-white text-sm font-bold shadow-glow transition-all">
                                    Fanya Malipo Mpya
                                </a>
                            </div>
                        @else
                            <!-- Status Header Card -->
                            <div class="bg-white rounded-2xl shadow-card border border-slate-200/80 overflow-hidden">
                                <div class="p-6 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-6">
                                    <div class="flex items-center gap-6">
                                        <!-- QR Code Section -->
                                        <div class="p-3 bg-white rounded-2xl border border-primary-100 shadow-sm flex-shrink-0">
                                            @php
                                                $qrContent = "FEEDTAN DIGITAL PAYMENT SYSTEM\n" .
                                                           "Order Reference: " . ($payment['orderReference'] ?? 'N/A') . "\n" .
                                                           "Transaction ID: " . ($payment['id'] ?? $payment['transaction_id'] ?? 'N/A') . "\n" .
                                                           "Amount: " . number_format($payment['collectedAmount'] ?? $payment['amount'] ?? 0, 2) . " " . ($payment['collectedCurrency'] ?? $payment['currency'] ?? 'TZS') . "\n" .
                                                           "Status: " . ($payment['status'] ?? 'UNKNOWN') . "\n" .
                                                           "Phone: " . ($payment['paymentPhoneNumber'] ?? $payment['phone'] ?? 'N/A') . "\n" .
                                                           "Channel: " . ($payment['channel'] ?? $payment['payment_method'] ?? 'N/A') . "\n" .
                                                           "Member: " . ($payment['customer_name'] ?? $payment['customer']['customerName'] ?? $payment['payer_name'] ?? 'N/A') . "\n" .
                                                           "Payer: " . ($payment['payer_name'] ?? 'N/A') . "\n" .
                                                           "Description: " . ($payment['description'] ?? 'N/A') . "\n" .
                                                           "Date: " . (isset($payment['createdAt']) ? \Carbon\Carbon::parse($payment['createdAt'])->format('Y-m-d H:i:s') : 'N/A');
                                            @endphp
                                            {!! QrCode::size(100)->margin(1)->encoding('UTF-8')->errorCorrection('H')->generate($qrContent) !!}
                                        </div>
                                        <div>
                                            <div class="text-[10px] text-primary-500 uppercase font-extrabold tracking-widest mb-1">Order Reference</div>
                                            <div class="text-xl font-mono font-bold text-primary-900">{{ $payment['orderReference'] ?? 'N/A' }}</div>
                                            <div class="mt-2">
                                                <span class="badge {{ $statusColor }} px-4 py-1.5 text-xs">
                                                    <i class="fas {{ $statusIcon }} me-2"></i>
                                                    {{ $statusText }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center sm:text-right">
                                        <div class="text-[10px] text-primary-500 uppercase font-extrabold tracking-widest mb-1">Jumla ya Malipo</div>
                                        <div class="text-3xl font-mono font-black text-primary-600">
                                            {{ $payment['collectedCurrency'] ?? 'TZS' }} {{ number_format($payment['collectedAmount'] ?? $payment['amount'] ?? 0, 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Details Grid -->
                            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Customer Info -->
                                <div class="bg-white rounded-2xl shadow-card border border-slate-200/80 p-6 space-y-4">
                                    <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                                        <i class="fas fa-user-circle"></i> Maelezo ya Mteja
                                    </h3>
                                    <div class="space-y-3">
                                        <div>
                                            <div class="text-[10px] text-gray-400 uppercase font-bold">Jina la Mteja</div>
                                            <div class="font-bold text-primary-900">{{ $payment['customer_name'] ?? $payment['payer_name'] ?? 'Mteja' }}</div>
                                        </div>
                                        @if(isset($payment['payer_name']) && strtolower($payment['payer_name']) !== strtolower($payment['customer_name'] ?? ''))
                                            <div>
                                                <div class="text-[10px] text-gray-400 uppercase font-bold">Mwenye Malipo</div>
                                                <div class="font-semibold text-sm text-primary-700">{{ $payment['payer_name'] }}</div>
                                            </div>
                                        @endif
                                        <div class="flex gap-6">
                                            <div>
                                                <div class="text-[10px] text-gray-400 uppercase font-bold">Simu</div>
                                                <div class="font-mono text-sm text-primary-800">{{ $payment['phone'] ?? 'N/A' }}</div>
                                            </div>
                                            @if(isset($payment['email']))
                                                <div>
                                                    <div class="text-[10px] text-gray-400 uppercase font-bold">Email</div>
                                                    <div class="text-sm text-primary-800">{{ $payment['email'] }}</div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Transaction Info -->
                                <div class="bg-white rounded-2xl shadow-card border border-slate-200/80 p-6 space-y-4">
                                    <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                                        <i class="fas fa-receipt"></i> Maelezo ya Muamala
                                    </h3>
                                    <div class="space-y-3">
                                        <div class="flex justify-between border-b border-primary-50 pb-2">
                                            <span class="text-xs text-gray-400">Transaction ID</span>
                                            <span class="text-xs font-mono font-bold text-primary-900">{{ $payment['id'] ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between border-b border-primary-50 pb-2">
                                            <span class="text-xs text-gray-400">Tarehe & Saa</span>
                                            <span class="text-xs font-bold text-primary-900">
                                                {{ \Carbon\Carbon::parse($payment['createdAt'] ?? 'now')->format('M d, Y • H:i:s') }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-xs text-gray-400">Njia ya Malipo</span>
                                            <span class="text-xs font-bold text-primary-900 uppercase">
                                                {{ $payment['channel'] ?? $payment['paymentMethod'] ?? $payment['payment_method'] ?? 'USSD Push' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Description Card -->
                            <div class="mt-6 bg-white rounded-2xl shadow-card border border-slate-200/80 p-6">
                                <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 mb-3 flex items-center gap-2">
                                    <i class="fas fa-info-circle"></i> Madhumuni / Maelezo
                                </h3>
                                <div class="p-4 bg-primary-50 rounded-xl italic text-sm text-primary-800 border border-primary-100">
                                    {{ $payment['description'] ?? ($payment['message'] ?? 'Malipo ya FEEDTAN') }}
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-6 grid grid-cols-2 gap-3">
                                @if(in_array($payment['status'] ?? '', ['SUCCESS', 'SETTLED']))
                                    <a href="{{ route('payments.receipt', $payment['orderReference'] ?? '') }}" target="_blank"
                                       class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-500 hover:to-primary-600 text-white text-xs font-bold shadow-glow transition-all">
                                        <i class="fas fa-download"></i> Pakua Risiti
                                    </a>
                                @elseif(in_array($payment['status'] ?? '', ['FAILED', 'CANCELLED', 'DECLINED']))
                                    <form action="{{ route('payments.retry', $payment['orderReference'] ?? '') }}" method="POST" class="w-full">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600 text-white text-xs font-bold shadow-glow transition-all">
                                            <i class="fas fa-redo"></i> Jaribu Tena
                                        </button>
                                    </form>
                                @else
                                    <button onclick="window.location.reload()"
                                            class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-500 hover:to-primary-600 text-white text-xs font-bold shadow-glow transition-all">
                                        <i class="fas fa-sync-alt"></i> Refresh
                                    </button>
                                @endif

                                <a href="{{ route('public.payment') }}"
                                   class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-white border border-primary-100 text-primary-600 text-xs font-bold hover:bg-primary-50 transition-all">
                                    <i class="fas fa-plus"></i> Fanya malipo Mengine
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // SweetAlert Notification System
        window.showNotification = function(type, title, message, options = {}) {
            const defaultOptions = {
                timer: 5000,
                showConfirmButton: type === 'error' || type === 'warning',
                confirmButtonText: 'OK',
                confirmButtonColor: '#10b981',
                backdrop: type === 'error' || type === 'warning',
                allowOutsideClick: type !== 'error' && type !== 'warning'
            };

            const swalOptions = {
                ...defaultOptions,
                ...options,
                title: title,
                html: message,
                icon: type,
                position: 'center',
                toast: type === 'success' || type === 'info',
                customClass: {
                    popup: 'sweet-alert-popup',
                    title: 'sweet-alert-title',
                    content: 'sweet-alert-content',
                    actions: 'sweet-alert-actions',
                    confirmButton: 'sweet-alert-confirm',
                    cancelButton: 'sweet-alert-cancel',
                    container: 'sweet-alert-container'
                }
            };

            // Special handling for insufficient funds
            if (type === 'warning' && (message.includes('Insufficient') || message.includes('Hakuna'))) {
                swalOptions.html = `
                    <div class="insufficient-funds-alert">
                        <div class="alert-icon">
                            <i class="fas fa-exclamation-triangle fa-3x text-yellow-500 mb-3"></i>
                        </div>
                        <h5 class="alert-title text-yellow-800 font-bold">${title}</h5>
                        <p class="alert-message text-yellow-700">${message}</p>
                        <div class="alert-actions bg-yellow-50 p-3 rounded mb-3">
                            <h6 class="text-yellow-800 font-bold mb-2">Nini cha kufanya?</h6>
                            <ul class="text-left mb-0 text-sm text-yellow-700">
                                <li class="mb-2"><strong>Tafuta msaada</strong> - Jitolee pesa kwenye akaunti yako ya Halopesa</li>
                                <li class="mb-2"><strong>Angalia salio</strong> - Hakikisha una salio la kutosha</li>
                                <li class="mb-2"><strong>Jaribu tena</strong> - Baada ya kujisafisha, jaribu tena</li>
                            </ul>
                        </div>
                    </div>
                `;
                swalOptions.showCancelButton = true;
                swalOptions.cancelButtonText = 'Cancel';
                swalOptions.cancelButtonColor = '#6b7280';
                swalOptions.confirmButtonText = 'Sawa, nitajaribu tena';
                swalOptions.timer = null;
                swalOptions.backdrop = true;
                swalOptions.allowOutsideClick = false;
            }

            Swal.fire(swalOptions);
        };

        // Auto-show notifications from session data
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('error'))
                @if(session('warning_type') == 'insufficient_funds')
                    showNotification('warning', 'Hakuna Salio Kutosha!', `{{ session('error') }}`, {
                        showCancelButton: true,
                        cancelButtonText: 'Cancel',
                        confirmButtonText: 'Sawa, nitajaribu tena',
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#6b7280'
                    });
                @else
                    showNotification('error', 'Kosa', `{{ session('error') }}`);
                @endif
            @endif

            @if(session('success'))
                showNotification('success', 'Fanikiwa!', `{{ session('success') }}`, {
                    position: 'center',
                    showConfirmButton: true,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#10b981',
                    timer: 5000,
                    backdrop: true,
                    allowOutsideClick: false
                });
            @endif

            @if(session('info'))
                showNotification('info', 'Maelezo', `{{ session('info') }}`);
            @endif

            @if(session('warning'))
                showNotification('warning', 'Onyo', `{{ session('warning') }}`);
            @endif
        });
    </script>
</body>
</html>