<?php

namespace App\Http\Controllers;

use App\Models\SmsMessage;
use App\Models\SmsDevice;
use App\Models\SmsProvider;
use Illuminate\Http\Request;

class SmsInboxController extends Controller
{
    public function __construct(){ $this->middleware('auth'); }

    public function index(Request $request)
    {
        $q = SmsMessage::with(['device','provider','smsTransaction','reconciliation'])->latest('sms_timestamp');

        if ($request->filled('device_id')) $q->where('device_id',$request->device_id);
        if ($request->filled('provider_id')) $q->where('provider_id',$request->provider_id);
        if ($request->filled('status')) $q->where('processing_status',$request->status);
        if ($request->filled('reconciliation_status')) $q->where('reconciliation_status',$request->reconciliation_status);
        if ($request->filled('search')) {
            $s=$request->search;
            $q->where(fn($qq)=>$qq->where('sender','like',"%$s%")->orWhere('body','like',"%$s%")->orWhere('hash','like',"%$s%"));
        }
        if ($request->filled('date_from')) $q->whereDate('sms_timestamp','>=',$request->date_from);
        if ($request->filled('date_to')) $q->whereDate('sms_timestamp','<=',$request->date_to);
        // Quick filters
        if ($request->filled('filter') ){
            if ($request->filter==='today') $q->whereDate('sms_timestamp', today());
            if ($request->filter==='unreconciled') $q->where('reconciliation_status','UNRECONCILED');
        }

        $messages = $q->paginate(20)->withQueryString();
        $devices = SmsDevice::select('id','device_code','name')->get();
        $providers = SmsProvider::all();
        $stats = [
            'today' => SmsMessage::whereDate('sms_timestamp', today())->count(),
            'unreconciled' => SmsMessage::where('reconciliation_status','UNRECONCILED')->count(),
            'pending' => SmsMessage::where('sync_status','PENDING')->count(),
        ];
        return view('sms-gateway.inbox.index', compact('messages','devices','providers','stats'));
    }

    public function show(SmsMessage $sms)
    {
        $sms->load(['device.location','provider','smsTransaction','reconciliation']);
        return view('sms-gateway.inbox.show', compact('sms'));
    }

    public function retry(Request $request, SmsMessage $sms)
    {
        $sms->update(['processing_status'=>'UNPROCESSED','sync_status'=>'PENDING']);
        return back()->with('success','Message queued for reprocessing.');
    }
}
