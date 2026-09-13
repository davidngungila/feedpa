<?php

namespace App\Http\Controllers;

use App\Models\SmsReconciliation;
use App\Models\SmsMessage;
use App\Models\Transaction;
use App\Models\Payout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmsReconciliationController extends Controller
{
    public function index(Request $request)
    {
        $q = SmsReconciliation::with(['smsTransaction','smsMessage.device','smsMessage.provider'])->latest();
        if ($request->filled('status')) $q->where('status',$request->status);
        if ($request->filled('search')) {
            $s=$request->search;
            $q->whereHas('smsTransaction', fn($qq)=>$qq->where('reference','like',"%$s%")->orWhere('counterparty','like',"%$s%"));
        }
        $reconciliations = $q->paginate(20)->withQueryString();
        $stats = [
            'unreconciled' => SmsReconciliation::where('status','UNRECONCILED')->count(),
            'matched' => SmsReconciliation::where('status','MATCHED')->count(),
            'reconciled' => SmsReconciliation::where('status','RECONCILED')->count(),
        ];
        $transactions = Transaction::orderByDesc('created_at')->limit(300)->get(['id','order_reference','transaction_id','amount','status','description']);
        $payouts = Payout::orderByDesc('created_at')->limit(300)->get(['id','order_reference','amount','status','recipient_name','description']);
        return view('sms-gateway.reconciliation.index', compact('reconciliations','stats','transactions','payouts'));
    }

    public function details(SmsReconciliation $reconciliation)
    {
        $reconciliation->load([
            'smsTransaction.device', 'smsTransaction.provider',
            'smsMessage.device', 'smsMessage.provider', 'smsMessage.smsTransaction',
        ]);
        $matched = null;
        if ($reconciliation->matched_transaction_id) {
            $matched = Transaction::with('notes')->find($reconciliation->matched_transaction_id);
        } elseif ($reconciliation->matched_payout_id) {
            $matched = Payout::find($reconciliation->matched_payout_id);
        }
        return response()->json([
            'id' => $reconciliation->id,
            'status' => $reconciliation->status,
            'notes' => $reconciliation->notes,
            'reconciled_by' => optional($reconciliation->reconciledBy)->name,
            'reconciled_at' => optional($reconciliation->reconciled_at)?->toDateTimeString(),
            'match_meta' => $reconciliation->match_meta,
            'sms' => $reconciliation->smsMessage ? [
                'id' => $reconciliation->smsMessage->id,
                'uuid' => $reconciliation->smsMessage->uuid,
                'sender' => $reconciliation->smsMessage->sender,
                'body' => $reconciliation->smsMessage->body,
                'sms_timestamp' => optional($reconciliation->smsMessage->sms_timestamp)->toDateTimeString(),
                'received_at' => optional($reconciliation->smsMessage->received_at)->toDateTimeString(),
                'sync_status' => $reconciliation->smsMessage->sync_status,
                'processing_status' => $reconciliation->smsMessage->processing_status,
                'reconciliation_status' => $reconciliation->smsMessage->reconciliation_status,
                'is_recorded' => $reconciliation->smsMessage->is_recorded,
                'admin_comment' => $reconciliation->smsMessage->admin_comment,
                'device' => $reconciliation->smsMessage->device ? [
                    'code' => $reconciliation->smsMessage->device->device_code,
                    'name' => $reconciliation->smsMessage->device->name,
                    'location' => $reconciliation->smsMessage->device->location->name ?? null,
                ] : null,
                'provider' => $reconciliation->smsMessage->provider ? [
                    'code' => $reconciliation->smsMessage->provider->code,
                    'name' => $reconciliation->smsMessage->provider->name,
                ] : null,
                'transaction' => $reconciliation->smsMessage->smsTransaction ? [
                    'amount' => $reconciliation->smsMessage->smsTransaction->amount,
                    'reference' => $reconciliation->smsMessage->smsTransaction->reference,
                    'counterparty' => $reconciliation->smsMessage->smsTransaction->counterparty,
                    'counterparty_name' => $reconciliation->smsMessage->smsTransaction->counterparty_name,
                    'balance' => $reconciliation->smsMessage->smsTransaction->balance,
                    'type' => $reconciliation->smsMessage->smsTransaction->transaction_type,
                    'currency' => $reconciliation->smsMessage->smsTransaction->currency,
                    'transaction_at' => optional($reconciliation->smsMessage->smsTransaction->transaction_at)->toDateTimeString(),
                ] : null,
            ] : null,
            'matched' => $matched ? [
                'kind' => $reconciliation->matched_transaction_id ? 'transaction' : 'payout',
                'id' => $matched->id,
                'reference' => $matched->order_reference ?? $matched->transaction_id ?? $matched->id,
                'status' => $matched->status ?? null,
                'amount' => $matched->amount ?? null,
                'description' => $matched->description ?? null,
            ] : null,
        ]);
    }

    public function match(Request $request, SmsReconciliation $reconciliation)
    {
        $request->validate([
            'matched_transaction_id' => 'nullable|exists:transactions,id',
            'matched_payout_id' => 'nullable|exists:payouts,id',
            'notes' => 'nullable|string|max:500',
        ]);
        if (!$request->matched_transaction_id && !$request->matched_payout_id) {
            return back()->withErrors(['matched_transaction_id'=>'Select transaction or payout to match.']);
        }
        $reconciliation->update([
            'matched_transaction_id' => $request->matched_transaction_id,
            'matched_payout_id' => $request->matched_payout_id,
            'status' => 'RECONCILED',
            'reconciled_by' => Auth::id(),
            'reconciled_at' => now(),
            'notes' => $request->notes,
            'match_meta' => array_merge($reconciliation->match_meta ?? [], ['manual'=>true, 'matched_at'=>now()->toIso8601String()]),
        ]);
        $reconciliation->smsMessage()->update(['reconciliation_status'=>'RECONCILED']);
        return back()->with('success','Reconciled successfully.');
    }

    public function unmatch(SmsReconciliation $reconciliation)
    {
        $reconciliation->update(['status'=>'UNRECONCILED','reconciled_by'=>null,'reconciled_at'=>null]);
        $reconciliation->smsMessage()->update(['reconciliation_status'=>'UNRECONCILED']);
        return back()->with('success','Reconciliation removed.');
    }
}
