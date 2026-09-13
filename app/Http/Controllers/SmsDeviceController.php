<?php

namespace App\Http\Controllers;

use App\Models\SmsDevice;
use App\Models\SmsLocation;
use App\Models\SmsProvider;
use App\Models\SmsMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmsDeviceController extends Controller
{
    public function index(Request $request)
    {
        $q = SmsDevice::with(['location'])->withCount('smsMessages')->latest();
        if ($request->filled('status')) $q->where('status', $request->status);
        if ($request->filled('location_id')) $q->where('location_id', $request->location_id);
        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(fn($qq) => $qq->where('device_code','like',"%$s%")->orWhere('name','like',"%$s%")->orWhere('phone_number','like',"%$s%"));
        }
        $devices = $q->paginate(15)->withQueryString();
        $locations = SmsLocation::all();
        $stats = [
            'total' => SmsDevice::count(),
            'active' => SmsDevice::where('status','ACTIVE')->count(),
            'online' => SmsDevice::where('last_heartbeat_at','>', now()->subMinutes(3))->count(),
            'pending' => SmsDevice::where('status','PENDING')->count(),
        ];
        return view('sms-gateway.devices.index', compact('devices','locations','stats'));
    }

    public function create()
    {
        $locations = SmsLocation::all();
        return view('sms-gateway.devices.create', compact('locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'device_code' => 'required|string|max:30|unique:sms_devices,device_code',
            'name' => 'required|string|max:100',
            'location_id' => 'nullable|exists:sms_locations,id',
            'phone_number' => 'nullable|string|max:20',
            'sim_operator' => 'nullable|string|max:30',
        ]);
        $device = SmsDevice::create([
            'device_code' => strtoupper(trim($request->device_code)),
            'name' => $request->name,
            'location_id' => $request->location_id,
            'phone_number' => $request->phone_number,
            'sim_operator' => $request->sim_operator,
            'status' => 'PENDING',
            'created_by' => Auth::id(),
        ]);
        $code = $device->generateActivationCode(60);
        return redirect()->route('sms-gateway.devices.show', $device)->with('success', "Device created. Activation code: {$code} (valid 60 min)");
    }

    public function show(SmsDevice $device)
    {
        $device->load(['location','tokens','heartbeats' => fn($q)=>$q->latest()->limit(20)]);
        $recentSms = SmsMessage::where('device_id',$device->id)->latest()->limit(10)->get();
        $isOnline = $device->isOnline();
        return view('sms-gateway.devices.show', compact('device','recentSms','isOnline'));
    }

    public function details(SmsDevice $device)
    {
        $device->load([
            'location',
            'creator',
            'tokens',
            'heartbeats' => fn($q) => $q->latest()->limit(1),
        ]);
        $activeToken = $device->activeToken();
        $isOnline = $device->isOnline();
        return response()->json([
            'id' => $device->id,
            'device_code' => $device->device_code,
            'name' => $device->name,
            'status' => $device->status,
            'is_online' => $isOnline,
            'location' => $device->location?->name,
            'phone_number' => $device->phone_number,
            'sim_slot' => $device->sim_slot,
            'sim_operator' => $device->sim_operator,
            'android_version' => $device->android_version,
            'app_version' => $device->app_version,
            'battery_level' => $device->battery_level,
            'network_type' => $device->network_type,
            'signal_strength' => $device->signal_strength,
            'last_heartbeat_at' => optional($device->last_heartbeat_at)->toDateTimeString(),
            'last_sync_at' => optional($device->last_sync_at)->toDateTimeString(),
            'last_sms_at' => optional($device->last_sms_at)->toDateTimeString(),
            'created_at' => optional($device->created_at)->toDateTimeString(),
            'created_by' => $device->creator?->name,
            'sms_count' => $device->smsMessages()->count(),
            'whatsapp_count' => $device->whatsappMessages()->count(),
            'active_token' => $activeToken ? [
                'plain_hint' => $activeToken->plain_hint,
                'created_at' => optional($activeToken->created_at)->toDateTimeString(),
                'expires_at' => optional($activeToken->expires_at)->toDateTimeString(),
                'last_used_at' => optional($activeToken->last_used_at)->toDateTimeString(),
            ] : null,
            'config' => $device->config,
        ]);
    }

    public function generateCode(SmsDevice $device)
    {
        abort_unless(auth()->user()->is_admin, 403, 'Admin only.');
        $code = $device->generateActivationCode(60);
        return back()->with('success', "New activation code: {$code} (valid 60 min)");
    }

    public function revoke(SmsDevice $device)
    {
        abort_unless(auth()->user()->is_admin, 403, 'Admin only.');
        $device->update(['status'=>'REVOKED']);
        $device->tokens()->update(['is_revoked'=>true]);
        return back()->with('success', 'Device revoked. Tokens invalidated.');
    }

    public function suspend(SmsDevice $device)
    {
        abort_unless(auth()->user()->is_admin, 403, 'Admin only.');
        $device->update(['status'=>'SUSPENDED']);
        return back()->with('success', 'Device suspended.');
    }

    public function activate(SmsDevice $device)
    {
        abort_unless(auth()->user()->is_admin, 403, 'Admin only.');
        $device->update(['status'=>'ACTIVE']);
        return back()->with('success', 'Device activated.');
    }

    public function destroy(SmsDevice $device)
    {
        abort_unless(auth()->user()->is_admin, 403, 'Admin only.');
        $device->delete();
        return redirect()->route('sms-gateway.devices.index')->with('success','Device deleted.');
    }

    public function updateConfig(Request $request, SmsDevice $device)
    {
        $request->validate(['config'=>'required|array']);
        $device->update(['config'=>array_merge($device->config ?? [], $request->config)]);
        return back()->with('success','Config updated.');
    }
}
