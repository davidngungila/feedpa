<?php

namespace App\Http\Controllers;

use App\Models\SmsMessage;
use App\Models\SmsDevice;
use App\Models\SmsProvider;
use Illuminate\Http\Request;

class SmsInboxController extends Controller
{
    public function index(Request $request)
    {
        $q = SmsMessage::with(['device','provider','smsTransaction','recordedBy','commentBy'])->latest('sms_timestamp');

        if ($request->filled('device_id')) $q->where('device_id',$request->device_id);
        if ($request->filled('provider_id')) $q->where('provider_id',$request->provider_id);
        if ($request->filled('status')) $q->where('processing_status',$request->status);
        if ($request->filled('recorded')) {
            $q->where('is_recorded', $request->recorded === '1' || $request->recorded === 'recorded');
        }
        if ($request->filled('search')) {
            $s=$request->search;
            $q->where(fn($qq)=>$qq->where('sender','like',"%$s%")->orWhere('body','like',"%$s%")->orWhere('hash','like',"%$s%")->orWhere('admin_comment','like',"%$s%"));
        }
        if ($request->filled('date_from')) $q->whereDate('sms_timestamp','>=',$request->date_from);
        if ($request->filled('date_to')) $q->whereDate('sms_timestamp','<=',$request->date_to);
        // Quick filters
        if ($request->filled('filter') ){
            if ($request->filter==='today') $q->whereDate('sms_timestamp', today());
            if ($request->filter==='recorded') $q->where('is_recorded', true);
            if ($request->filter==='not_recorded') $q->where('is_recorded', false);
        }

        $messages = $q->paginate(20)->withQueryString();
        $devices = SmsDevice::select('id','device_code','name')->get();
        $providers = SmsProvider::all();
        $stats = [
            'today' => SmsMessage::whereDate('sms_timestamp', today())->count(),
            'recorded' => SmsMessage::where('is_recorded', true)->count(),
            'not_recorded' => SmsMessage::where('is_recorded', false)->count(),
            'pending' => SmsMessage::where('sync_status','PENDING')->count(),
        ];
        return view('sms-gateway.inbox.index', compact('messages','devices','providers','stats'));
    }

    public function show(SmsMessage $sms)
    {
        $sms->load(['device.location','provider','smsTransaction','recordedBy','commentBy']);
        return view('sms-gateway.inbox.show', compact('sms'));
    }

    public function retry(Request $request, SmsMessage $sms)
    {
        $sms->update(['processing_status'=>'UNPROCESSED','sync_status'=>'PENDING']);
        return back()->with('success','Message queued for reprocessing.');
    }

    public function toggleRecorded(Request $request, SmsMessage $sms)
    {
        $request->validate(['is_recorded' => 'required|boolean']);
        $sms->update([
            'is_recorded' => (bool) $request->is_recorded,
            'recorded_at' => $request->is_recorded ? now() : null,
            'recorded_by' => $request->is_recorded ? auth()->id() : null,
        ]);
        // If AJAX drawer request, return JSON
        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['success' => true, 'is_recorded' => $sms->is_recorded, 'message' => $sms->is_recorded ? 'Marked as recorded.' : 'Marked as not recorded.']);
        }
        return back()->with('success', $sms->is_recorded ? 'Marked as recorded.' : 'Marked as not recorded.');
    }

    public function comment(Request $request, SmsMessage $sms)
    {
        $request->validate(['admin_comment' => 'required|string|max:2000']);
        $sms->update([
            'admin_comment' => $request->admin_comment,
            'comment_by' => auth()->id(),
            'commented_at' => now(),
        ]);
        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['success' => true, 'comment' => $sms->admin_comment, 'message' => 'Comment saved.']);
        }
        return back()->with('success','Comment saved.');
    }

    // AJAX endpoint for drawer: return JSON details
    public function details(SmsMessage $sms)
    {
        $sms->load(['device.location','provider','smsTransaction','recordedBy','commentBy']);
        return response()->json([
            'id' => $sms->id,
            'uuid' => $sms->uuid,
            'sender' => $sms->sender,
            'body' => $sms->body,
            'hash' => $sms->hash,
            'sms_timestamp' => $sms->sms_timestamp?->format('Y-m-d H:i:s'),
            'received_at' => $sms->received_at?->format('Y-m-d H:i:s'),
            'device' => $sms->device ? ['code' => $sms->device->device_code, 'name' => $sms->device->name, 'location' => $sms->device->location?->name] : null,
            'provider' => $sms->provider ? ['name' => $sms->provider->name, 'code' => $sms->provider->code] : ['code' => $sms->parsed_data['provider_code'] ?? null],
            'transaction' => $sms->smsTransaction ? [
                'amount' => $sms->smsTransaction->amount,
                'currency' => $sms->smsTransaction->currency,
                'reference' => $sms->smsTransaction->reference,
                'counterparty' => $sms->smsTransaction->counterparty,
                'counterparty_name' => $sms->smsTransaction->counterparty_name,
                'type' => $sms->smsTransaction->transaction_type,
                'balance' => $sms->smsTransaction->balance,
            ] : null,
            'parsed_data' => $sms->parsed_data,
            'is_recorded' => (bool) $sms->is_recorded,
            'recorded_at' => $sms->recorded_at?->format('Y-m-d H:i:s'),
            'recorded_by' => $sms->recordedBy?->name,
            'admin_comment' => $sms->admin_comment,
            'comment_by' => $sms->commentBy?->name,
            'commented_at' => $sms->commented_at?->format('Y-m-d H:i:s'),
            'sync_status' => $sms->sync_status,
            'processing_status' => $sms->processing_status,
        ]);
    }

    public function reparse(Request $request, SmsMessage $sms)
    {
        $parsed = \App\Services\SmsParserService::parse($sms->sender, $sms->body, $sms->sms_timestamp->toDateTimeString());
        // update message parsed_data and provider if detected
        $sms->update([
            'provider_id' => $parsed['provider_id'] ?? $sms->provider_id,
            'parsed_data' => $parsed,
        ]);
        if ($sms->smsTransaction) {
            $sms->smsTransaction->update([
                'provider_id' => $parsed['provider_id'] ?? $sms->smsTransaction->provider_id,
                'provider_code' => $parsed['provider_code'] ?? $sms->smsTransaction->provider_code,
                'transaction_type' => $parsed['transaction_type'],
                'amount' => $parsed['amount'],
                'currency' => $parsed['currency'],
                'reference' => $parsed['reference'],
                'counterparty' => $parsed['counterparty'],
                'counterparty_name' => $parsed['counterparty_name'] ?? null,
                'balance' => $parsed['balance'],
                'transaction_at' => $parsed['transaction_at'],
                'raw_extracted' => $parsed['raw_extracted'],
            ]);
        } else {
            \App\Models\SmsTransaction::create([
                'sms_message_id' => $sms->id,
                'device_id' => $sms->device_id,
                'provider_id' => $parsed['provider_id'],
                'provider_code' => $parsed['provider_code'],
                'transaction_type' => $parsed['transaction_type'],
                'amount' => $parsed['amount'],
                'currency' => $parsed['currency'],
                'reference' => $parsed['reference'],
                'counterparty' => $parsed['counterparty'],
                'counterparty_name' => $parsed['counterparty_name'] ?? null,
                'balance' => $parsed['balance'],
                'transaction_at' => $parsed['transaction_at'],
                'raw_extracted' => $parsed['raw_extracted'],
            ]);
        }
        return back()->with('success', 'Re-parsed successfully: Amount '.($parsed['amount'] ?? '—').' Ref '.($parsed['reference'] ?? '—'));
    }

    public function reparseAll()
    {
        abort_unless(auth()->user()->is_admin, 403);
        $count=0;
        foreach (\App\Models\SmsMessage::with('smsTransaction')->cursor() as $sms) {
            $parsed = \App\Services\SmsParserService::parse($sms->sender, $sms->body, $sms->sms_timestamp->toDateTimeString());
            $sms->update(['provider_id'=>$parsed['provider_id'] ?? $sms->provider_id, 'parsed_data'=>$parsed]);
            if($sms->smsTransaction){
                $sms->smsTransaction->update([
                    'provider_id'=>$parsed['provider_id'] ?? $sms->smsTransaction->provider_id,
                    'provider_code'=>$parsed['provider_code'],
                    'transaction_type'=>$parsed['transaction_type'],
                    'amount'=>$parsed['amount'],
                    'currency'=>$parsed['currency'],
                    'reference'=>$parsed['reference'],
                    'counterparty'=>$parsed['counterparty'],
                    'counterparty_name'=>$parsed['counterparty_name'] ?? null,
                    'balance'=>$parsed['balance'],
                    'transaction_at'=>$parsed['transaction_at'],
                    'raw_extracted'=>$parsed['raw_extracted'],
                ]);
            }
            $count++;
        }
        return back()->with('success', "Re-parsed $count messages with new Swahili/English parser.");
    }
}
