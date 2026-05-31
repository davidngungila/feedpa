<?php

namespace App\Http\Controllers;

use App\Models\Payout;
use App\Services\ClickPesaAPIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PayoutController extends Controller
{
    protected ClickPesaAPIService $api;

    public function __construct(ClickPesaAPIService $api)
    {
        $this->api = $api;
    }

    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $query = Payout::orderBy('created_at', 'desc');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $payouts = $query->paginate(20);

        return view('payouts.index', compact('payouts', 'status'));
    }

    public function create()
    {
        return view('payouts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:100',
            'currency' => 'required|in:TZS,USD',
            'payout_type' => 'required|in:MOBILE_MONEY,BANK',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required_if:payout_type,MOBILE_MONEY|nullable|string',
            'bank_account_number' => 'required_if:payout_type,BANK|nullable|string',
            'bank_name' => 'required_if:payout_type,BANK|nullable|string',
            'bic' => 'required_if:payout_type,BANK|nullable|string',
            'description' => 'nullable|string|max:500'
        ]);

        try {
            $orderReference = $this->api->generateOrderReference('FEEDTANPAY');
            $payout = Payout::create([
                'order_reference' => $orderReference,
                'status' => 'PENDING',
                'amount' => $validated['amount'],
                'currency' => $validated['currency'],
                'payout_type' => $validated['payout_type'],
                'recipient_name' => $validated['recipient_name'],
                'recipient_phone' => $validated['recipient_phone'] ?? null,
                'bank_account_number' => $validated['bank_account_number'] ?? null,
                'bank_name' => $validated['bank_name'] ?? null,
                'bic' => $validated['bic'] ?? null,
                'description' => $validated['description'] ?? null,
                'user_id' => auth()->id()
            ]);

            // Initiate payout via ClickPesa
            if ($validated['payout_type'] === 'MOBILE_MONEY') {
                $apiResponse = $this->api->createMobileMoneyPayout(
                    $validated['amount'],
                    $validated['recipient_phone'],
                    $validated['currency'],
                    $orderReference
                );
            } else {
                $apiResponse = $this->api->createBankPayout(
                    $validated['amount'],
                    $validated['currency'],
                    $validated['bank_account_number'],
                    $validated['recipient_name'],
                    $validated['bic'],
                    'ACH',
                    $orderReference
                );
            }

            // Update payout with transaction ID if available
            if (isset($apiResponse['id'])) {
                $payout->update(['transaction_id' => $apiResponse['id']]);
            }

            return redirect()->route('payouts.status', $orderReference)
                            ->with('success', 'Payout initiated successfully!');

        } catch (\Exception $e) {
            Log::error('Payout initiation failed', ['error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(string $orderReference)
    {
        $payout = Payout::where('order_reference', $orderReference)->firstOrFail();

        // Refresh status from API if not in final state
        if (!in_array($payout->status, ['SUCCESS', 'FAILED', 'SETTLED'])) {
            try {
                $apiResponse = $this->api->queryPayoutStatus($orderReference);
                $this->updatePayoutFromApi($payout, $apiResponse);
            } catch (\Exception $e) {
                Log::error('Failed to refresh payout status', ['error' => $e->getMessage()]);
            }
        }

        return view('payouts.show', compact('payout'));
    }

    public function refreshStatus(string $orderReference)
    {
        $payout = Payout::where('order_reference', $orderReference)->firstOrFail();

        try {
            $apiResponse = $this->api->queryPayoutStatus($orderReference);
            $this->updatePayoutFromApi($payout, $apiResponse);
            return back()->with('success', 'Payout status refreshed!');
        } catch (\Exception $e) {
            Log::error('Failed to refresh payout status', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to refresh status: ' . $e->getMessage());
        }
    }

    protected function updatePayoutFromApi(Payout $payout, array $apiData)
    {
        $updateData = [
            'status' => $apiData['status'] ?? $payout->status,
            'transaction_id' => $apiData['id'] ?? $apiData['transaction_id'] ?? $payout->transaction_id,
            'callback_data' => $apiData
        ];
        $payout->update($updateData);
    }
}
