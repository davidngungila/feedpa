<?php

namespace App\Http\Controllers;

use App\Services\ClickPesaAPIService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayoutController extends Controller
{
    protected ClickPesaAPIService $api;

    public function __construct(ClickPesaAPIService $api)
    {
        $this->api = $api;
    }

    /**
     * Show initiate payout form
     */
    public function create()
    {
        $banksList = [];
        
        try {
            $banksResponse = $this->api->getBanksList();
            if (isset($banksResponse['data'])) {
                $banksList = $banksResponse['data'];
            }
        } catch (Exception $e) {
            Log::warning('Failed to fetch banks list: ' . $e->getMessage());
        }

        return view('payouts.create', compact('banksList'));
    }

    /**
     * Initiate payout
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'payout_type' => 'required|in:mobile_money,bank',
            'amount' => 'required|numeric|min:100|max:10000000',
            'currency' => 'required|in:TZS,USD',
            'narrative' => 'nullable|string|max:255'
        ]);

        try {
            $amount = $this->api->formatAmount($validated['amount']);
            $reference = $this->api->generateOrderReference('PY');

            if ($validated['payout_type'] === 'mobile_money') {
                $mobileValidated = $request->validate([
                    'phone_number' => 'required|string|regex:/^255[67]\d{8}$/'
                ]);

                $phoneNumber = $this->api->validatePhoneNumber($mobileValidated['phone_number']);
                
                if (!$phoneNumber) {
                    return back()->with('error', 'Invalid phone number format. Please use Tanzania format (255xxxxxxxxx).');
                }

                // Create mobile money payout
                $payout = $this->api->createMobileMoneyPayout($amount, $phoneNumber, $validated['currency'], $reference);
                
                return redirect()->route('payouts.status', ['reference' => $reference])
                    ->with('success', 'Mobile Money payout initiated successfully!');
            } 
            
            if ($validated['payout_type'] === 'bank') {
                $bankValidated = $request->validate([
                    'account_number' => 'required|string',
                    'account_name' => 'required|string',
                    'bic' => 'required|string',
                    'transfer_type' => 'required|in:ACH,RTGS'
                ]);

                // Preview bank payout
                $preview = $this->api->previewBankPayout(
                    $amount, 
                    $validated['currency'], 
                    $bankValidated['account_number'], 
                    $bankValidated['account_name'], 
                    $bankValidated['bic'], 
                    $bankValidated['transfer_type'],
                    $reference
                );

                if (!isset($preview['amount']) && !isset($preview['balance'])) {
                    throw new Exception('Bank payout preview failed: ' . ($preview['message'] ?? 'Unknown error'));
                }

                // Create bank payout
                $payout = $this->api->createBankPayout(
                    $amount, 
                    $validated['currency'], 
                    $bankValidated['account_number'], 
                    $bankValidated['account_name'], 
                    $bankValidated['bic'], 
                    $bankValidated['transfer_type'],
                    $reference
                );
                
                return redirect()->route('payouts.status', ['reference' => $reference])
                    ->with('success', 'Bank payout initiated successfully!');
            }
        } catch (Exception $e) {
            Log::error('Payout initiation failed: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }

        return back()->with('error', 'Invalid payout type selected');
    }

    /**
     * Check payout status
     */
    public function status(Request $request)
    {
        $payoutData = null;
        $error = null;
        $payoutReference = $request->get('reference');

        if ($payoutReference) {
            try {
                $payoutData = $this->api->queryPayoutStatus($payoutReference);
                
                if (empty($payoutData) || !isset($payoutData['id'])) {
                    $error = 'No payout found with this reference';
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
                Log::error('Payout status check failed: ' . $error);
            }
        }

        return view('payouts.status', compact('payoutData', 'error', 'payoutReference'));
    }

    /**
     * Payout history with filters
     */
    public function history(Request $request)
    {
        $params = [
            'limit' => $request->get('limit', 20),
            'skip' => ($request->get('page', 1) - 1) * 20,
            'orderBy' => 'DESC',
            'sortBy' => 'createdAt'
        ];

        // Apply filters
        if ($request->filled('status')) {
            $params['status'] = $request->status;
        }
        if ($request->filled('currency')) {
            $params['currency'] = $request->currency;
        }
        if ($request->filled('payout_type')) {
            $params['channel'] = $request->payout_type;
        }
        if ($request->filled('reference')) {
            $params['orderReference'] = $request->reference;
        }
        if ($request->filled('start_date')) {
            $params['startDate'] = $request->start_date;
        }
        if ($request->filled('end_date')) {
            $params['endDate'] = $request->end_date;
        }

        $payouts = [];
        $totalCount = 0;
        $error = null;

        try {
            $response = $this->api->queryAllPayouts($params);
            
            if (isset($response['data'])) {
                $payouts = $response['data'];
                $totalCount = $response['totalCount'] ?? 0;
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            Log::error('Payout history fetch failed: ' . $error);
        }

        return view('payouts.history', compact('payouts', 'totalCount', 'error'));
    }
}
