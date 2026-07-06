<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Malipo ya Wanachama — FeedTan CMG</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <meta name="description" content="Fanya malipo ya wanachama wa FeedTan Community Microfinance Group kwa urahisi kupitia Tigo Pesa, M-Pesa, Airtel Money na Halopesa.">
    <meta property="og:title" content="FeedTan CMG — Malipo ya Wanachama">
    <meta property="og:description" content="Malipo salama na ya haraka kwa wanachama wa FeedTan kupitia mobile money.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
                    colors: {
                        brand: {
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
        .input-field {
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }
        .input-field:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.12);
            background: #fff;
        }
        .chip.active {
            background: #059669;
            color: #fff;
            border-color: #059669;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-up { animation: fadeUp 0.45s ease-out both; }
        .modal-backdrop { 
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
        }
    </style>
</head>
<body class="mesh-bg min-h-screen text-slate-800 antialiased">

    <div id="globalNotice"
         class="fixed top-4 left-1/2 -translate-x-1/2 z-50 hidden w-[min(92vw,680px)] rounded-2xl border px-4 py-3 shadow-2xl backdrop-blur bg-white/95">
        <div class="flex items-start gap-3">
            <div id="globalNoticeIcon" class="mt-0.5 text-sm"></div>
            <div class="min-w-0 flex-1">
                <p id="globalNoticeTitle" class="text-sm font-bold"></p>
                <p id="globalNoticeMessage" class="text-sm text-slate-600"></p>
            </div>
            <button type="button" id="globalNoticeClose" class="text-slate-400 hover:text-slate-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <div class="min-h-screen flex flex-col">
        <!-- Top bar -->
        <header class="w-full border-b border-brand-100/80 bg-white/70 backdrop-blur-md sticky top-0 z-30">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-600 to-brand-800 flex items-center justify-center shadow-glow">
                        <i class="fa-solid fa-leaf text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-brand-900 leading-none">FeedTan CMG</p>
                        <p class="hidden sm:block text-[10px] text-brand-600/80 font-medium tracking-wide uppercase">Member Payments</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 text-[10px] font-semibold text-brand-700 bg-brand-50 border border-brand-100 px-3 py-1.5 rounded-full">
                    <i class="fas fa-shield-halved text-brand-600"></i>
                    <span class="hidden sm:inline">Malipo Salama</span>
                    <span class="sm:hidden">Salama</span>
                </div>
            </div>
        </header>

        <main class="flex-1 flex items-center justify-center px-4 py-4 sm:py-12">
            <div class="w-full max-w-5xl animate-fade-up">
                <div class="grid lg:grid-cols-5 gap-6 lg:gap-8 items-start">

                    <!-- Info panel (desktop only) -->
                    <aside class="hidden lg:block lg:col-span-2 space-y-4">
                        <div class="rounded-2xl bg-gradient-to-br from-brand-800 via-brand-700 to-brand-900 text-white p-6 sm:p-8 shadow-card relative overflow-hidden">
                            <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/5"></div>
                            <div class="absolute -left-4 bottom-0 w-24 h-24 rounded-full bg-white/5"></div>
                            <div class="relative">
                                <p class="text-brand-200 text-[10px] font-bold uppercase tracking-widest mb-2">Malipo ya Wanachama</p>
                                <h1 class="text-2xl sm:text-3xl font-extrabold leading-tight mb-3">Lipa kwa urahisi<br><span class="text-brand-200">kupitia simu yako</span></h1>
                                <p class="text-sm text-brand-100/90 leading-relaxed mb-6">
                                    Jaza fomu, thibitisha USSD kwenye simu yako, na upokee uthibitisho kwa SMS mara malipo yanapokamilika.
                                </p>
                                <ul class="space-y-3 text-sm">
                                    <li class="flex items-start gap-3">
                                        <span class="w-6 h-6 rounded-lg bg-white/15 flex items-center justify-center shrink-0 mt-0.5"><i class="fas fa-bolt text-xs"></i></span>
                                        <span>Haraka — USSD inatumwa moja kwa moja</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <span class="w-6 h-6 rounded-lg bg-white/15 flex items-center justify-center shrink-0 mt-0.5"><i class="fas fa-lock text-xs"></i></span>
                                        <span>Salama — ClickPesa & mobile money</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <span class="w-6 h-6 rounded-lg bg-white/15 flex items-center justify-center shrink-0 mt-0.5"><i class="fas fa-sms text-xs"></i></span>
                                        <span>UThibitisho kwa SMS baada ya malipo</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-sm">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-3">Tunakubali</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach(['M-Pesa', 'Tigo Pesa', 'Airtel Money', 'Halopesa'] as $network)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-100 text-xs font-semibold text-slate-600">
                                        <i class="fas fa-mobile-screen text-brand-600 text-[10px]"></i>
                                        {{ $network }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <p class="text-center text-xs text-slate-400 font-medium">Let's Grow Together</p>
                    </aside>

                    <!-- Payment form -->
                    <div class="lg:col-span-3 w-full max-w-lg mx-auto lg:max-w-none">
                        <div class="rounded-2xl bg-white border border-slate-200/80 shadow-card overflow-hidden">
                            <div class="px-5 sm:px-8 pt-5 sm:pt-7 pb-4 sm:pb-5 border-b border-slate-100">
                                <h2 class="text-lg font-bold text-slate-900">Malipo ya Wanachama</h2>
                                <p class="hidden sm:block text-xs text-slate-500 mt-1">Sehemu zote zenye alama <span class="text-red-500">*</span> ni lazima</p>
                            </div>

                            <form action="{{ route('payments.store') }}" method="POST" id="paymentForm" class="px-5 sm:px-8 py-5 sm:py-6 space-y-4 sm:space-y-5">
                                @csrf

                                <!-- Member name -->
                                <div>
                                    <label for="payer_name" class="block text-xs font-semibold text-slate-600 mb-1.5">
                                        Jina la Mwanachama <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"><i class="fas fa-user text-sm"></i></span>
                                        <input type="text" id="payer_name" name="payer_name"
                                               class="input-field w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50/80 text-sm font-medium text-slate-900 placeholder:text-slate-400"
                                               placeholder="Mfano: Jane Mwanza" maxlength="100" required autocomplete="name">
                                    </div>
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label for="phone_number" class="block text-xs font-semibold text-slate-600 mb-1.5">
                                        Namba ya Simu <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"><i class="fas fa-phone text-sm"></i></span>
                                        <input type="tel" id="phone_number" name="phone_number"
                                               class="input-field w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50/80 text-sm font-medium text-slate-900 placeholder:text-slate-400 font-mono"
                                               placeholder="255712345678" maxlength="12" required inputmode="numeric" autocomplete="tel">
                                    </div>
                                </div>

                                <!-- Amount -->
                                <div>
                                    <label for="amount" class="block text-xs font-semibold text-slate-600 mb-1.5">
                                        Kiasi (TZS) <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[10px] font-bold text-brand-600">TZS</span>
                                        <input type="number" id="amount" name="amount"
                                               class="input-field w-full pl-14 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50/80 text-sm font-bold text-slate-900 placeholder:text-slate-400 placeholder:font-normal"
                                               placeholder="5,000" min="500" max="5000000" required inputmode="numeric">
                                    </div>
                                    <p class="hidden sm:block mt-1.5 text-[11px] text-slate-400">Kiwango: TZS 500 — 5,000,000</p>
                                </div>

                                <!-- Purpose -->
                                <div>
                                    <label for="description" class="block text-xs font-semibold text-slate-600 mb-1.5">
                                        Malipo Kwaajili Ya <span class="text-red-500">*</span>
                                    </label>
                                    <div class="flex flex-wrap gap-2 mb-2.5" id="purposeChips">
                                        @foreach(['Akiba', 'Uwekezaji', 'Malipo ya mkopo', 'Ada ya Uanachama', 'Hisa', 'SWF Contribution', 'Malipo ya Bidhaa'] as $purpose)
                                            <button type="button" data-purpose="{{ $purpose }}"
                                                    class="purpose-chip px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-600 hover:border-brand-300 hover:text-brand-700 hover:bg-brand-50 transition-colors">
                                                {{ $purpose }}
                                            </button>
                                        @endforeach
                                    </div>
                                    <input type="text" id="description" name="description" required readonly
                                              class="input-field w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-100/80 text-sm text-slate-900 cursor-not-allowed"
                                              placeholder="Chagua malipo kwaajili ya…">
                                </div>

                                <!-- Hidden inputs for types -->
                                <input type="hidden" id="akiba_type" name="akiba_type">
                                <input type="hidden" id="uwekezaji_type" name="uwekezaji_type">
                                <input type="hidden" id="hisa_type" name="hisa_type">

                                <!-- Summary + submit -->
                                <div class="rounded-xl bg-brand-50 border border-brand-100 p-4 flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-brand-600/80">Jumla ya Malipo</p>
                                        <p class="text-2xl font-extrabold text-brand-800 tabular-nums">
                                            TZS <span id="btnAmount">0</span>
                                        </p>
                                    </div>
                                    <div class="hidden sm:flex w-12 h-12 rounded-xl bg-brand-600/10 items-center justify-center">
                                        <i class="fas fa-wallet text-brand-600 text-lg"></i>
                                    </div>
                                </div>

                                <button type="submit" id="submitBtn"
                                        class="w-full flex items-center justify-center gap-2 py-3.5 px-6 rounded-xl bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 text-white text-sm font-bold shadow-glow transition-all hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:shadow-none">
                                    <i class="fas fa-lock text-xs"></i>
                                    <span>Lipa Sasa</span>
                                </button>

                                <p class="hidden sm:block text-center text-[10px] text-slate-400 leading-relaxed">
                                    Kwa kubofya <strong>Lipa Sasa</strong>, USSD itatumwa kwenye namba uliyoingiza. Thibitisha na PIN yako.
                                </p>
                            </form>
                        </div>

                        <!-- Sub-Option Selection Modal -->
                        <div id="subOptionModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 modal-backdrop bg-slate-900">
                            <div class="w-full max-w-md bg-white rounded-2xl shadow-card p-6 animate-fade-up">
                                <div class="text-center mb-4">
                                    <h3 id="subOptionModalTitle" class="text-lg font-bold text-slate-900"></h3>
                                </div>
                                <div id="subOptionModalContent" class="flex flex-wrap gap-2 justify-center mb-4"></div>
                                <button type="button" id="subOptionModalClose" class="w-full py-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-bold transition-colors">
                                    Funga
                                </button>
                            </div>
                        </div>

                        <p class="hidden sm:flex text-center text-[10px] text-slate-400 mt-4 items-center justify-center gap-1.5">
                            <i class="fas fa-lock text-brand-500"></i>
                            Powered by FeedTan Team · FeedTan CMG
                        </p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modals mount -->
    <div id="modalRoot"></div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const noticeEl = document.getElementById('globalNotice');
        const noticeTitleEl = document.getElementById('globalNoticeTitle');
        const noticeMessageEl = document.getElementById('globalNoticeMessage');
        const noticeIconEl = document.getElementById('globalNoticeIcon');
        const noticeCloseEl = document.getElementById('globalNoticeClose');

        function showGlobalNotice(type, title, message) {
            const styles = {
                warning: {
                    box: 'border-amber-200 bg-amber-50/95',
                    icon: '<i class="fas fa-triangle-exclamation text-amber-600"></i>',
                    title: 'text-amber-900',
                },
                error: {
                    box: 'border-red-200 bg-red-50/95',
                    icon: '<i class="fas fa-circle-xmark text-red-600"></i>',
                    title: 'text-red-900',
                },
                info: {
                    box: 'border-sky-200 bg-sky-50/95',
                    icon: '<i class="fas fa-circle-info text-sky-600"></i>',
                    title: 'text-sky-900',
                },
                success: {
                    box: 'border-emerald-200 bg-emerald-50/95',
                    icon: '<i class="fas fa-circle-check text-emerald-600"></i>',
                    title: 'text-emerald-900',
                }
            };

            const selected = styles[type] || styles.info;

            noticeEl.className = `fixed top-4 left-1/2 -translate-x-1/2 z-50 w-[min(92vw,680px)] rounded-2xl border px-4 py-3 shadow-2xl backdrop-blur ${selected.box}`;
            noticeEl.classList.remove('hidden');
            noticeTitleEl.className = `text-sm font-bold ${selected.title}`;
            noticeTitleEl.textContent = title;
            noticeMessageEl.textContent = message;
            noticeIconEl.innerHTML = selected.icon;

            window.clearTimeout(window.__globalNoticeTimeout);
            window.__globalNoticeTimeout = window.setTimeout(() => {
                noticeEl.classList.add('hidden');
            }, 5000);
        }

        noticeCloseEl.addEventListener('click', function () {
            noticeEl.classList.add('hidden');
        });

        @if(session('warning'))
            showGlobalNotice('warning', 'Not Found', @json(session('warning')));
        @elseif(session('error'))
            showGlobalNotice('error', 'Notice', @json(session('error')));
        @elseif(session('info'))
            showGlobalNotice('info', 'Information', @json(session('info')));
        @elseif(session('success'))
            showGlobalNotice('success', 'Success', @json(session('success')));
        @endif

        const form = document.getElementById('paymentForm');
        const submitBtn = document.getElementById('submitBtn');
        const amountInput = document.getElementById('amount');
        const btnAmount = document.getElementById('btnAmount');
        const descriptionInput = document.getElementById('description');
        const phoneInput = document.getElementById('phone_number');
        const modalRoot = document.getElementById('modalRoot');
        const akibaTypeInput = document.getElementById('akiba_type');
        const uwekezajiTypeInput = document.getElementById('uwekezaji_type');
        const hisaTypeInput = document.getElementById('hisa_type');
        const subOptionModal = document.getElementById('subOptionModal');
        const subOptionModalTitle = document.getElementById('subOptionModalTitle');
        const subOptionModalContent = document.getElementById('subOptionModalContent');
        const subOptionModalClose = document.getElementById('subOptionModalClose');

        // Define sub-options for each purpose
        const subOptions = {
            'Akiba': {
                title: 'Chagua Aina ya Akiba',
                options: ['RDA', 'FLEX', 'EMERGENCE'],
                inputField: akibaTypeInput,
                descriptionPrefix: 'Akiba'
            },
            'Uwekezaji': {
                title: 'Chagua Aina ya Uwekezaji',
                options: ['2Year FIA', '4Years FIA', '6 Years FIA'],
                inputField: uwekezajiTypeInput,
                descriptionPrefix: 'Uwekezaji'
            },
            'Hisa': {
                title: 'Chagua Aina ya Hisa',
                options: ['Hisa za duka', 'Hisa za Feedtan CMG'],
                inputField: hisaTypeInput,
                descriptionPrefix: 'Hisa'
            }
        };

        let currentPurpose = null;
        let currentSubOptionConfig = null;

        // Polling variables
        let pollingInterval = null;
        let pollingStartTime = null;
        const POLLING_DURATION = 60000; // 1 minute
        const POLLING_INTERVAL = 3000; // 3 seconds per poll

        function formatAmountDisplay(value) {
            const n = Number(value) || 0;
            return n.toLocaleString('en-TZ');
        }

        amountInput.addEventListener('input', function () {
            btnAmount.textContent = formatAmountDisplay(this.value);
        });

        // Function to open sub-option modal
        function openSubOptionModal(purpose) {
            currentPurpose = purpose;
            currentSubOptionConfig = subOptions[purpose];
            
            subOptionModalTitle.textContent = currentSubOptionConfig.title;
            subOptionModalContent.innerHTML = '';
            
            currentSubOptionConfig.options.forEach(option => {
                const button = document.createElement('button');
                button.type = 'button';
                button.textContent = option;
                button.className = 'px-4 py-2 rounded-lg border border-slate-200 bg-white text-sm font-semibold text-slate-600 hover:border-brand-300 hover:text-brand-700 hover:bg-brand-50 transition-colors';
                button.addEventListener('click', () => selectSubOption(option));
                subOptionModalContent.appendChild(button);
            });
            
            subOptionModal.classList.remove('hidden');
        }

        // Function to close sub-option modal
        function closeSubOptionModal() {
            subOptionModal.classList.add('hidden');
            currentPurpose = null;
            currentSubOptionConfig = null;
        }

        // Function to handle sub-option selection
        function selectSubOption(option) {
            if (!currentSubOptionConfig) return;
            
            currentSubOptionConfig.inputField.value = option;
            descriptionInput.value = `${currentSubOptionConfig.descriptionPrefix} - ${option}`;
            
            closeSubOptionModal();
        }

        // Purpose chip click handler
        document.querySelectorAll('.purpose-chip').forEach(function (chip) {
            chip.addEventListener('click', function () {
                const purpose = this.dataset.purpose;
                
                // Reset all type inputs
                akibaTypeInput.value = '';
                uwekezajiTypeInput.value = '';
                hisaTypeInput.value = '';
                
                // Update active purpose chip
                document.querySelectorAll('.purpose-chip').forEach(c => c.classList.remove('active', 'ring-2', 'ring-brand-500', 'border-brand-500', 'bg-brand-50', 'text-brand-700'));
                this.classList.add('active', 'ring-2', 'ring-brand-500', 'border-brand-500', 'bg-brand-50', 'text-brand-700');
                
                // Check if this purpose has sub-options
                if (subOptions[purpose]) {
                    openSubOptionModal(purpose);
                } else {
                    // No sub-options, just set description
                    descriptionInput.value = purpose;
                }
            });
        });

        // Modal close button click handler
        subOptionModalClose.addEventListener('click', closeSubOptionModal);

        // Close modal when clicking on backdrop
        subOptionModal.addEventListener('click', function (e) {
            if (e.target === subOptionModal) {
                closeSubOptionModal();
            }
        });

        phoneInput.addEventListener('blur', function () {
            let value = this.value.replace(/\D/g, '');
            if (value.length === 10 && value.startsWith('0')) {
                this.value = '255' + value.substring(1);
            }
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(form);
            const data = {
                amount: formData.get('amount'),
                phone_number: String(formData.get('phone_number') || '').replace(/\D/g, ''),
                payer_name: String(formData.get('payer_name') || '').trim(),
                description: String(formData.get('description') || '').trim(),
                akiba_type: String(formData.get('akiba_type') || '').trim(),
                uwekezaji_type: String(formData.get('uwekezaji_type') || '').trim(),
                hisa_type: String(formData.get('hisa_type') || '').trim()
            };

            if (!data.payer_name) {
                showAlert('error', 'Tafadhali ingiza jina lako kamili.');
                return;
            }
            if (!data.description) {
                showAlert('error', 'Tafadhali chagua malipo kwaajili ya.');
                return;
            }
            // Validate akiba type if purpose is Akiba
            if (data.description === 'Akiba' && !data.akiba_type) {
                showAlert('error', 'Tafadhali chagua aina ya Akiba (RDA, FLEX, au EMERGENCE).');
                return;
            }
            // Validate uwekezaji type if purpose is Uwekezaji
            if (data.description === 'Uwekezaji' && !data.uwekezaji_type) {
                showAlert('error', 'Tafadhali chagua aina ya Uwekezaji (2Year FIA, 4Years FIA, au 6 Years FIA).');
                return;
            }
            // Validate hisa type if purpose is Hisa
            if (data.description.startsWith('Hisa') && !data.hisa_type) {
                showAlert('error', 'Tafadhali chagua aina ya Hisa (Hisa za duka au Hisa za Feedtan CMG).');
                return;
            }
            if (!data.phone_number.match(/^255[67]\d{8}$/)) {
                showAlert('error', 'Namba ya simu si sahihi. Mfano: 255712345678');
                return;
            }
            if (!data.amount || data.amount < 500) {
                showAlert('error', 'Kiasi cha chini ni TZS 500.');
                return;
            }
            if (data.amount > 5000000) {
                showAlert('error', 'Kiasi cha juu ni TZS 5,000,000.');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Inatuma…</span>';
            showProcessingModal();

            fetch('/payments/store', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(result => {
                closeModal('processingModal');
                if (result.success) {
                    showUSSDNotification(result);
                } else {
                    showAlert('error', result.message || 'Imeshindikwa kutuma malipo. Jaribu tena.');
                    resetButton();
                }
            })
            .catch(() => {
                closeModal('processingModal');
                showAlert('warning', 'Tatizo la mtandao. Tafadhali jaribu tena.');
                resetButton();
            });
        });

        function resetButton() {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-lock text-xs"></i><span>Lipa Sasa</span>';
        }

        function closeModal(id) {
            const el = document.getElementById(id);
            if (el) el.remove();
            modalRoot.innerHTML = '';
        }

        function showProcessingModal() {
            modalRoot.innerHTML = `
                <div id="processingModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-backdrop bg-slate-900">
                    <div class="w-full max-w-md bg-white rounded-2xl shadow-card p-6 sm:p-8 text-center animate-fade-up">
                        <div class="w-14 h-14 mx-auto rounded-2xl bg-brand-50 flex items-center justify-center mb-4">
                            <i class="fas fa-spinner fa-spin text-2xl text-brand-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">Tunaandaa Malipo Yako</h3>
                        <p class="text-sm text-slate-500 mt-2">USSD inatumwa kwenye simu yako. Subiri kidogo…</p>
                        <ol class="mt-5 text-left text-sm text-slate-600 space-y-2 bg-slate-50 rounded-xl p-4 border border-slate-100">
                            <li class="flex gap-2"><span class="text-brand-600 font-bold">1.</span> Angalia simu yako</li>
                            <li class="flex gap-2"><span class="text-brand-600 font-bold">2.</span> Thibitisha USSD PUSH</li>
                            <li class="flex gap-2"><span class="text-brand-600 font-bold">3.</span> Weka PIN na thibitisha</li>
                        </ol>
                    </div>
                </div>`;
        }

        function showUSSDNotification(data) {
            const phone = data.phone_number || data.phone || '—';
            const amount = formatAmountDisplay(data.amount);
            const orderReference = data.order_reference;
            modalRoot.innerHTML = `
                <div id="ussdNotification" class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-backdrop bg-slate-900">
                    <div class="w-full max-w-md bg-white rounded-2xl shadow-card overflow-hidden animate-fade-up">
                        <div class="bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-5 text-white text-center">
                            <div class="w-12 h-12 mx-auto rounded-full bg-white/20 flex items-center justify-center mb-3">
                                <i class="fas fa-check text-xl"></i>
                            </div>
                            <h3 class="text-lg font-bold">Malipo Yameanzishwa</h3>
                            <p class="text-sm text-brand-100 mt-1">USSD imetumwa kwenye simu yako</p>
                        </div>
                        <div class="p-6 space-y-3">
                            <div class="flex justify-between items-center py-2.5 px-4 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-xs text-slate-500 font-medium">Simu</span>
                                <span class="text-sm font-mono font-bold text-slate-800">${phone}</span>
                            </div>
                            <div class="flex justify-between items-center py-2.5 px-4 rounded-xl bg-brand-50 border border-brand-100">
                                <span class="text-xs text-brand-600 font-medium">Kiasi</span>
                                <span class="text-sm font-bold text-brand-800">TZS ${amount}</span>
                            </div>
                            <div class="flex justify-between items-center py-2.5 px-4 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-xs text-slate-500 font-medium">Reference</span>
                                <span class="text-sm font-mono font-bold text-slate-800">${orderReference}</span>
                            </div>
                            <p class="text-xs text-slate-500 text-center pt-2">Thibitisha na PIN. Tunaangalia hali ya malipo yako mara kwa mara…</p>
                            <div id="statusIndicator" class="mt-3 text-center">
                                <i class="fas fa-spinner fa-spin text-brand-600 mr-1"></i>
                                <span class="text-sm text-slate-600">Inaangalia hali ya malipo…</span>
                            </div>
                            <div id="pollingProgress" class="mt-3">
                                <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                                    <div id="progressBar" class="h-full bg-brand-500 transition-all duration-300" style="width: 0%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
            
            // Start polling
            startPolling(orderReference);
        }

        window.closeUSSDNotification = function () {
            stopPolling();
            closeModal('ussdNotification');
            form.reset();
            btnAmount.textContent = '0';
            resetButton();
        };

        function startPolling(orderReference) {
            pollingStartTime = Date.now();
            updateProgressBar();
            
            pollingInterval = setInterval(() => {
                const elapsed = Date.now() - pollingStartTime;
                updateProgressBar();
                
                if (elapsed >= POLLING_DURATION) {
                    stopPolling();
                    showPollingTimeout(orderReference);
                    return;
                }
                
                checkPaymentStatus(orderReference);
            }, POLLING_INTERVAL);
            
            // Initial check
            checkPaymentStatus(orderReference);
        }

        function stopPolling() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
        }

        function updateProgressBar() {
            const progressBar = document.getElementById('progressBar');
            const statusIndicator = document.getElementById('statusIndicator');
            
            if (!progressBar || !statusIndicator) return;
            
            const elapsed = Date.now() - pollingStartTime;
            const percentage = Math.min((elapsed / POLLING_DURATION) * 100, 100);
            progressBar.style.width = `${percentage}%`;
        }

        async function checkPaymentStatus(orderReference) {
            try {
                const response = await fetch('/payments/api/status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ order_reference: orderReference })
                });
                
                const result = await response.json();
                
                if (!result.success) {
                    console.error('Status check failed', result);
                    return;
                }
                
                const data = result.data;
                const transaction = result.transaction;
                let status = null;
                
                if (transaction && transaction.status) {
                    status = transaction.status;
                } else if (data && data.status) {
                    status = data.status;
                } else if (Array.isArray(data) && data[0] && data[0].status) {
                    status = data[0].status;
                }
                
                if (!status) {
                    return;
                }
                
                const isSuccessful = ['SUCCESS', 'SETTLED', 'COMPLETED'].includes(status);
                const isFailed = ['FAILED', 'DECLINED', 'CANCELLED', 'ERROR', 'REVERSED'].includes(status);
                
                if (isSuccessful) {
                    stopPolling();
                    showSuccessModal(orderReference, data || transaction);
                } else if (isFailed) {
                    stopPolling();
                    showFailureModal(status);
                }
                
            } catch (error) {
                console.error('Error checking payment status', error);
            }
        }

        function showSuccessModal(orderReference, paymentData) {
            const amount = formatAmountDisplay(paymentData.amount || paymentData.collectedAmount || 0);
            
            modalRoot.innerHTML = `
                <div id="successModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-backdrop bg-slate-900">
                    <div class="w-full max-w-md bg-white rounded-2xl shadow-card overflow-hidden animate-fade-up">
                        <div class="bg-gradient-to-r from-green-600 to-emerald-700 px-6 py-5 text-white text-center">
                            <div class="w-14 h-14 mx-auto rounded-full bg-white/20 flex items-center justify-center mb-3">
                                <i class="fas fa-check-circle text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-bold">Hongera! Malipo Yamekamilika</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <p class="text-sm text-slate-600 text-center">Asante! Malipo yako yamekamilika.</p>
                            <div class="flex justify-between items-center py-2.5 px-4 rounded-xl bg-green-50 border border-green-100">
                                <span class="text-xs text-green-700 font-medium">Kiasi</span>
                                <span class="text-sm font-bold text-green-800">TZS ${amount}</span>
                            </div>
                            <div class="flex justify-between items-center py-2.5 px-4 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-xs text-slate-500 font-medium">Reference</span>
                                <span class="text-sm font-mono font-bold text-slate-800">${orderReference}</span>
                            </div>
                            <div class="grid grid-cols-1 gap-3">
                                <a href="/payments/receipt/${orderReference}" target="_blank"
                                   class="flex items-center justify-center gap-2 py-3 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-sm font-bold transition-colors">
                                    <i class="fas fa-download"></i>
                                    Pakua Rcpt
                                </a>
                            </div>
                            <button type="button" onclick="closeSuccessModal()"
                                    class="w-full py-3 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-bold transition-colors">
                                Funga
                            </button>
                        </div>
                    </div>
                </div>`;
        }

        window.closeSuccessModal = function () {
            closeModal('successModal');
            form.reset();
            btnAmount.textContent = '0';
            resetButton();
        };

        function showFailureModal(status) {
            modalRoot.innerHTML = `
                <div id="failureModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-backdrop bg-slate-900">
                    <div class="w-full max-w-md bg-white rounded-2xl shadow-card overflow-hidden animate-fade-up">
                        <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-5 text-white text-center">
                            <div class="w-14 h-14 mx-auto rounded-full bg-white/20 flex items-center justify-center mb-3">
                                <i class="fas fa-times-circle text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-bold">Malipo Haujaweza</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <p class="text-sm text-slate-600 text-center">Malipo haujaweza kukamilika. Tafadhali jaribu tena.</p>
                            <div class="flex justify-between items-center py-2.5 px-4 rounded-xl bg-red-50 border border-red-100">
                                <span class="text-xs text-red-700 font-medium">Hali</span>
                                <span class="text-sm font-bold text-red-800">${status}</span>
                            </div>
                            <button type="button" onclick="closeFailureModal()"
                                    class="w-full py-3 rounded-xl bg-red-600 hover:bg-red-500 text-white text-sm font-bold transition-colors">
                                Jaribu Tena
                            </button>
                        </div>
                    </div>
                </div>`;
        }

        window.closeFailureModal = function () {
            closeModal('failureModal');
            form.reset();
            btnAmount.textContent = '0';
            resetButton();
        };

        function showPollingTimeout(orderReference) {
            modalRoot.innerHTML = `
                <div id="timeoutModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-backdrop bg-slate-900">
                    <div class="w-full max-w-md bg-white rounded-2xl shadow-card overflow-hidden animate-fade-up">
                        <div class="bg-gradient-to-r from-amber-600 to-amber-700 px-6 py-5 text-white text-center">
                            <div class="w-14 h-14 mx-auto rounded-full bg-white/20 flex items-center justify-center mb-3">
                                <i class="fas fa-clock text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-bold">Tulikuwa Tunasubiri Sana</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <p class="text-sm text-slate-600 text-center">Hakuna majibu kuhusiana na hali ya malipo. Unaweza kuangalia hali ya malipo baadaye.</p>
                            <a href="/payments/status?reference=${orderReference}"
                               class="flex items-center justify-center gap-2 py-3 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-sm font-bold transition-colors">
                                <i class="fas fa-eye"></i>
                                Angalia Hali
                            </a>
                            <button type="button" onclick="closeTimeoutModal()"
                                    class="w-full py-3 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-bold transition-colors">
                                Funga
                            </button>
                        </div>
                    </div>
                </div>`;
        }

        window.closeTimeoutModal = function () {
            closeModal('timeoutModal');
            form.reset();
            btnAmount.textContent = '0';
            resetButton();
        };

        function showAlert(type, message) {
            const styles = {
                success: { icon: 'check-circle', ring: 'ring-green-200', bg: 'bg-green-50', text: 'text-green-800', btn: 'bg-green-700 hover:bg-green-600' },
                error:   { icon: 'times-circle', ring: 'ring-red-200', bg: 'bg-red-50', text: 'text-red-800', btn: 'bg-red-700 hover:bg-red-600' },
                warning: { icon: 'exclamation-triangle', ring: 'ring-amber-200', bg: 'bg-amber-50', text: 'text-amber-800', btn: 'bg-amber-700 hover:bg-amber-600' },
            };
            const s = styles[type] || styles.warning;
            const overlay = document.createElement('div');
            overlay.className = 'fixed inset-0 z-[60] flex items-center justify-center p-4 modal-backdrop bg-slate-900';
            overlay.innerHTML = `
                <div class="w-full max-w-sm bg-white rounded-2xl shadow-card p-6 text-center ring-4 ${s.ring} animate-fade-up">
                    <div class="w-12 h-12 mx-auto rounded-full ${s.bg} flex items-center justify-center mb-3">
                        <i class="fas fa-${s.icon} text-xl ${s.text}"></i>
                    </div>
                    <p class="text-sm ${s.text} font-medium leading-relaxed">${message}</p>
                    <button type="button" class="mt-5 w-full py-2.5 rounded-xl text-white text-sm font-bold ${s.btn} transition-colors">
                        Sawa
                    </button>
                </div>`;
            overlay.querySelector('button').onclick = () => overlay.remove();
            overlay.addEventListener('click', e => { if (e.target === overlay) overlay.remove(); });
            document.body.appendChild(overlay);
        }
    });
    </script>
</body>
</html>
