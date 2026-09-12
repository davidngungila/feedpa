@extends('layouts.app')
@section('title', 'Register Device')

@section('content')
<div class="max-w-xl mx-auto card p-6 space-y-4">
    <h1 class="text-lg font-bold text-primary-900">Register New Gateway Device</h1>
    <p class="text-xs text-primary-500">Administrator creates MOSHI-01 etc. Phone will activate with 6-digit code.</p>

    <form method="POST" action="{{ route('sms-gateway.devices.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="text-xs font-bold text-primary-700">Device Code *</label>
            <input type="text" name="device_code" value="{{ old('device_code') }}" placeholder="MOSHI-01" required class="mt-1 w-full px-3 py-2 rounded-lg border border-primary-200 text-sm uppercase">
            @error('device_code')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="text-xs font-bold text-primary-700">Device Name *</label>
            <input type="text" name="name" value="{{ old('name') }}" placeholder="Moshi Main Phone" required class="mt-1 w-full px-3 py-2 rounded-lg border border-primary-200 text-sm">
        </div>
        <div>
            <label class="text-xs font-bold text-primary-700">Location</label>
            <select name="location_id" class="mt-1 w-full px-3 py-2 rounded-lg border border-primary-200 text-sm">
                <option value="">— Select Location —</option>
                @foreach($locations as $loc)
                <option value="{{ $loc->id }}" @selected(old('location_id')==$loc->id)>{{ $loc->name }} ({{ $loc->code }})</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-xs font-bold text-primary-700">Phone Number (SIM)</label>
                <input type="text" name="phone_number" value="{{ old('phone_number') }}" placeholder="2557..." class="mt-1 w-full px-3 py-2 rounded-lg border border-primary-200 text-sm">
            </div>
            <div>
                <label class="text-xs font-bold text-primary-700">Operator</label>
                <input type="text" name="sim_operator" value="{{ old('sim_operator') }}" placeholder="Vodacom / Airtel / Yas / Halotel" class="mt-1 w-full px-3 py-2 rounded-lg border border-primary-200 text-sm">
            </div>
        </div>

        <button class="w-full py-3 rounded-lg bg-primary-600 text-white text-sm font-bold hover:bg-primary-700">Register &amp; Generate Activation Code</button>
    </form>

    <div class="p-3 rounded-lg bg-amber-50 border border-amber-200">
        <p class="text-xs font-bold text-amber-800">Next steps:</p>
        <ol class="text-xs text-amber-700 list-decimal ml-4 mt-1 space-y-1">
            <li>Install Flutter Gateway on the phone (SIM inside).</li>
            <li>Open app → Enter activation code shown after creation.</li>
            <li>Grant SMS permission → Gateway becomes 🟢 ACTIVE.</li>
        </ol>
    </div>
</div>
@endsection
