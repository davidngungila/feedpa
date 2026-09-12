@extends('layouts.app')

@section('title', 'Register Device')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 animate-fade-in">
    <!-- Header Card -->
    <div class="card overflow-hidden">
        <div class="p-6 sm:p-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                <div class="p-3 bg-white rounded-2xl border border-primary-100 shadow-sm flex-shrink-0 hidden sm:flex">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center">
                        <i class="fa-solid fa-mobile-screen-button text-3xl text-primary-700"></i>
                    </div>
                </div>
                <div>
                    <div class="text-[10px] text-primary-500 uppercase font-extrabold tracking-widest mb-1">SMS Gateway • Register Device</div>
                    <div class="text-xl font-bold text-primary-900">New Gateway Device</div>
                    <p class="text-xs text-primary-500 mt-1 max-w-lg">Add a physical phone (MOSHI-01, TABORA-01...). System generates a 6-digit activation code for the Flutter gateway.</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="px-3 py-1 text-[10px] font-bold rounded-full bg-primary-100 text-primary-700"><i class="fa-solid fa-shield-halved me-1"></i>Activation required</span>
                        <span class="px-3 py-1 text-[10px] font-bold rounded-full bg-amber-100 text-amber-800"><i class="fa-solid fa-qrcode me-1"></i>Code valid 60 min</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('sms-gateway.devices.index') }}" class="px-4 py-2.5 rounded-xl border border-primary-200 text-xs font-bold text-primary-700 hover:bg-primary-50 transition-all flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Back to Devices
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="card p-6 sm:p-8 space-y-6">
        <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
            <i class="fa-solid fa-circle-info"></i> Device Information
        </h3>

        <form method="POST" action="{{ route('sms-gateway.devices.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Device Code -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Device Code *</label>
                    <input type="text" name="device_code" value="{{ old('device_code') }}" placeholder="MOSHI-01" required
                           class="w-full bg-primary-50 border border-primary-100 rounded-xl px-3 py-2.5 text-xs font-mono font-bold uppercase tracking-widest text-primary-900 placeholder:text-primary-300 outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <p class="mt-1 text-[10px] text-primary-400">Unique, e.g. MOSHI-01, TABORA-02. Uppercase recommended.</p>
                    @error('device_code')<p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>@enderror
                </div>

                <!-- Device Name -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Device Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Moshi Main Phone" required
                           class="w-full bg-primary-50 border border-primary-100 rounded-xl px-3 py-2.5 text-xs text-primary-900 outline-none focus:ring-2 focus:ring-primary-500">
                    @error('name')<p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>@enderror
                </div>

                <!-- Location -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Location</label>
                    <select name="location_id" class="w-full bg-primary-50 border border-primary-100 rounded-xl px-3 py-2.5 text-xs text-primary-900 outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">— Select Location —</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" @selected(old('location_id')==$loc->id)>{{ $loc->name }} ({{ $loc->code }})</option>
                        @endforeach
                    </select>
                    @error('location_id')<p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>@enderror
                </div>

                <!-- Sim Operator -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">SIM Operator</label>
                    <select name="sim_operator" class="w-full bg-primary-50 border border-primary-100 rounded-xl px-3 py-2.5 text-xs text-primary-900 outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">— Select Operator —</option>
                        <option value="Vodacom" @selected(old('sim_operator')=='Vodacom')>Vodacom (M-Pesa)</option>
                        <option value="Airtel" @selected(old('sim_operator')=='Airtel')>Airtel</option>
                        <option value="Yas" @selected(old('sim_operator')=='Yas')>Yas (Mixx)</option>
                        <option value="Halotel" @selected(old('sim_operator')=='Halotel')>Halotel (HaloPesa)</option>
                        <option value="Tigo" @selected(old('sim_operator')=='Tigo')>Tigo</option>
                    </select>
                    <input type="hidden" id="sim_operator_free" name="sim_operator_free" value="">
                    @error('sim_operator')<p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>@enderror
                </div>

                <!-- Phone -->
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Phone Number (SIM)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-primary-400">+255</span>
                        <input type="text" name="phone_number" value="{{ old('phone_number') }}" placeholder="74 12 345 678" inputmode="numeric"
                               class="w-full bg-primary-50 border border-primary-100 rounded-xl pl-12 pr-3 py-2.5 text-xs font-mono text-primary-900 outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <p class="mt-1 text-[10px] text-primary-400">Optional but helps identify SIM. Stored as entered.</p>
                    @error('phone_number')<p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 flex gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-400 flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-lightbulb text-white text-xs"></i></div>
                <div>
                    <p class="text-xs font-bold text-amber-800">What happens next?</p>
                    <ol class="text-xs text-amber-700 list-decimal ml-4 mt-1 space-y-1 leading-relaxed">
                        <li>Tap <strong>Register</strong> → system creates device with status <code class="px-1 py-0.5 bg-amber-100 rounded">PENDING</code> and shows a 6-digit code.</li>
                        <li>Install Flutter Gateway on the physical phone, enter the code → device becomes <span class="font-bold text-green-700">ACTIVE</span>.</li>
                        <li>Grant SMS permission → gateway starts syncing. Accountants see SMS at <code class="px-1 py-0.5 bg-white rounded border">/sms-gateway/sms</code>.</li>
                    </ol>
                </div>
            </div>

            <div class="pt-6 flex flex-wrap gap-3 justify-end border-t border-primary-100">
                <a href="{{ route('sms-gateway.devices.index') }}" class="px-5 py-2.5 rounded-xl border border-primary-200 text-xs font-bold text-primary-600 hover:bg-primary-50 transition-all">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold shadow-lg shadow-primary-900/20 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Register & Generate Activation Code
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
