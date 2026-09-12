<?php

namespace App\Http\Controllers;

use App\Models\SmsDevice;
use App\Models\SmsMessage;
use App\Models\SmsTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SmsDashboardController extends Controller
{
    public function __construct(){ $this->middleware('auth'); }

    public function index(Request $request)
    {
        $today = today();
        $devicesOnline = SmsDevice::where('last_heartbeat_at','>', now()->subMinutes(3))->count();
        $devicesOffline = SmsDevice::where(fn($q)=>$q->whereNull('last_heartbeat_at')->orWhere('last_heartbeat_at','<=', now()->subMinutes(3)))->count();
        $smsToday = SmsMessage::whereDate('sms_timestamp', $today)->count();
        $syncedToday = SmsMessage::whereDate('sms_timestamp', $today)->where('sync_status','SENT')->count();
        $pending = SmsMessage::where('sync_status','PENDING')->count();
        $failed = SmsMessage::where('sync_status','FAILED')->count();
        $paymentsToday = SmsTransaction::whereDate('transaction_at', $today)->where('transaction_type','PAYMENT')->sum('amount');
        $recentSms = SmsMessage::with(['device','provider'])->latest()->limit(10)->get();
        $deviceHealth = SmsDevice::with('location')->get()->map(fn($d)=>[
            'code'=>$d->device_code,
            'name'=>$d->name,
            'online'=>$d->isOnline(),
            'battery'=>$d->battery_level,
            'network'=>$d->network_type,
            'last_heartbeat'=>$d->last_heartbeat_at,
        ]);

        // Provider breakdown today
        $byProvider = SmsMessage::select('provider_id', DB::raw('count(*) as cnt'))->whereDate('sms_timestamp',$today)->groupBy('provider_id')->with('provider')->get();

        return view('sms-gateway.dashboard', compact('devicesOnline','devicesOffline','smsToday','syncedToday','pending','failed','paymentsToday','recentSms','deviceHealth','byProvider'));
    }
}
