<?php

namespace App\Http\Controllers;

use App\Models\SmsReconciliation;
use App\Models\SmsMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmsReconciliationController extends Controller
{
    public function __construct(){ $this->middleware('auth'); }

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
        return view('sms-gateway.reconciliation.index', compact('reconciliations','stats'));
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
