<?php

namespace App\Http\Controllers;

use App\Models\BillPayNumber;
use App\Services\ClickPesaAPIService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BillController extends Controller
{
    protected ClickPesaAPIService $clickPesaService;

    public function __construct(ClickPesaAPIService $clickPesaService)
    {
        $this->clickPesaService = $clickPesaService;
    }

    public function index()
    {
        $bills = BillPayNumber::latest()->paginate(20);
        return view('bills.index', compact('bills'));
    }

    public function createOrder()
    {
        return view('bills.create-order');
    }

    public function storeOrder(Request $request)
    {
        $request->validate([
            'bill_description' => 'required|string',
            'bill_amount' => 'nullable|numeric',
            'bill_reference' => 'nullable|string',
            'bill_payment_mode' => 'required|in:ALLOW_PARTIAL_AND_OVER_PAYMENT,EXACT',
        ]);

        try {
            $response = $this->clickPesaService->createOrderControlNumber(
                $request->bill_description,
                $request->bill_amount,
                $request->bill_reference,
                $request->bill_payment_mode
            );

            if (isset($response['billPayNumber'])) {
                $bill = BillPayNumber::create([
                    'bill_pay_number' => $response['billPayNumber'],
                    'bill_description' => $request->bill_description,
                    'bill_amount' => $request->bill_amount,
                    'bill_currency' => 'TZS',
                    'bill_payment_mode' => $request->bill_payment_mode,
                    'bill_status' => 'ACTIVE',
                    'bill_type' => 'order',
                    'bill_reference' => $request->bill_reference,
                    'created_by' => Auth::check() ? Auth::id() : null,
                ]);

                return redirect()->route('bills.show', $bill->id)->with('success', 'Order Control Number created successfully! Control number: ' . $response['billPayNumber']);
            }

            return back()->with('error', 'Failed to create control number.');
        } catch (\Exception $e) {
            Log::error('Error creating order control number', ['error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }

    public function createCustomer()
    {
        return view('bills.create-customer');
    }

    public function storeCustomer(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|required_without:customer_phone',
            'customer_phone' => 'nullable|string|required_without:customer_email',
            'bill_description' => 'nullable|string|max:255',
            'bill_amount' => 'nullable|numeric|min:1',
            'bill_reference' => 'nullable|string|regex:/^[A-Za-z0-9]+$/|max:20',
            'bill_payment_mode' => 'required|in:ALLOW_PARTIAL_AND_OVER_PAYMENT,EXACT',
        ], [
            'customer_email.required_without' => 'Provide a phone number or email address (at least one is required).',
            'customer_phone.required_without' => 'Provide a phone number or email address (at least one is required).',
            'bill_reference.regex' => 'Bill reference must contain only letters and numbers (no spaces or symbols).',
            'bill_amount.min' => 'Bill amount must be at least 1 TZS.',
        ]);

        if ($request->bill_payment_mode === 'EXACT' && ! $request->filled('bill_amount')) {
            return back()
                ->withErrors(['bill_amount' => 'Amount is required when payment mode is Exact Amount Only.'])
                ->withInput();
        }

        $phone = null;
        if ($request->filled('customer_phone')) {
            $phone = $this->clickPesaService->validatePhoneNumber($request->customer_phone);
            if (! $phone) {
                return back()
                    ->withErrors(['customer_phone' => 'Use format 255712345678 (country code, no + sign).'])
                    ->withInput();
            }
        }

        $email = $request->filled('customer_email') ? trim($request->customer_email) : null;
        $billAmount = $request->filled('bill_amount') ? (float) $request->bill_amount : null;
        $billReference = $request->filled('bill_reference') ? strtoupper(trim($request->bill_reference)) : null;

        try {
            $response = $this->clickPesaService->createCustomerControlNumber(
                trim($request->customer_name),
                $email,
                $phone,
                $request->filled('bill_description') ? trim($request->bill_description) : null,
                $billAmount,
                $billReference,
                $request->bill_payment_mode
            );

            if (isset($response['billPayNumber'])) {
                $bill = BillPayNumber::create([
                    'bill_pay_number' => $response['billPayNumber'],
                    'bill_description' => $request->filled('bill_description') ? trim($request->bill_description) : null,
                    'bill_amount' => $billAmount,
                    'bill_currency' => 'TZS',
                    'bill_payment_mode' => $request->bill_payment_mode,
                    'bill_status' => 'ACTIVE',
                    'bill_type' => 'customer',
                    'customer_name' => trim($request->customer_name),
                    'customer_email' => $email,
                    'customer_phone' => $phone,
                    'bill_reference' => $billReference ?? $response['billPayNumber'],
                    'created_by' => Auth::check() ? Auth::id() : null,
                ]);

                return redirect()->route('bills.show', $bill->id)->with('success', 'Customer Control Number created successfully! Control number: ' . $response['billPayNumber']);
            }

            return back()->with('error', 'Failed to create control number.');
        } catch (\Exception $e) {
            Log::error('Error creating customer control number', ['error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        $bill = BillPayNumber::findOrFail($id);
        
        // Try to fetch latest bill details from API
        try {
            $apiBill = $this->clickPesaService->queryBillPayNumber($bill->bill_pay_number);
            
            if (isset($apiBill['billPayNumber'])) {
                // Update local bill with latest data from API
                $billType = 'order';
                if (isset($apiBill['customerName'])) {
                    $billType = 'customer';
                }
                
                $bill->update([
                    'bill_description' => $apiBill['billDescription'] ?? $bill->bill_description,
                    'bill_amount' => $apiBill['billAmount'] ?? $bill->bill_amount,
                    'bill_currency' => $apiBill['currency'] ?? $bill->bill_currency,
                    'bill_payment_mode' => $apiBill['billPaymentMode'] ?? $bill->bill_payment_mode,
                    'bill_status' => $apiBill['billStatus'] ?? $bill->bill_status,
                    'bill_type' => $billType,
                    'customer_name' => $apiBill['customerName'] ?? $bill->customer_name,
                    'customer_email' => $apiBill['customerEmail'] ?? $bill->customer_email,
                    'customer_phone' => $apiBill['customerPhone'] ?? $bill->customer_phone,
                    'bill_reference' => $apiBill['billReference'] ?? $bill->bill_reference,
                    'total_paid' => $apiBill['totalPaid'] ?? $apiBill['collectedAmount'] ?? $bill->total_paid,
                    'last_payment_at' => isset($apiBill['lastPaymentAt']) ? \Illuminate\Support\Carbon::parse($apiBill['lastPaymentAt']) : $bill->last_payment_at,
                ]);
                
                // Refresh the bill instance from DB
                $bill = $bill->fresh();
            }
        } catch (\Exception $e) {
            Log::error('Error fetching bill details from API', [
                'bill_id' => $id,
                'bill_pay_number' => $bill->bill_pay_number,
                'error' => $e->getMessage()
            ]);
        }
        
        // Get related transactions (match order_reference to bill_pay_number or bill_reference)
        $transactions = \App\Models\Transaction::where('order_reference', $bill->bill_pay_number)
            ->orWhere('order_reference', $bill->bill_reference)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Generate QR code with bill details
        $qrContent = "FEEDTAN BillPay\n" .
                   "Control Number: " . $bill->bill_pay_number . "\n" .
                   "Type: " . ucfirst($bill->bill_type) . "\n" .
                   "Description: " . $bill->bill_description . "\n" .
                   "Amount: " . number_format($bill->bill_amount, 2) . " " . $bill->bill_currency . "\n" .
                   "Status: " . $bill->bill_status . "\n" .
                   "Created: " . $bill->created_at->format('Y-m-d H:i:s');
        
        $qrCodeSvg = QrCode::format('svg')->size(150)->encoding('UTF-8')->errorCorrection('H')->generate($qrContent);
        $qrCodeImage = 'data:image/svg+xml;base64,' . base64_encode($qrCodeSvg);
        
        return view('bills.show', compact('bill', 'qrCodeImage', 'transactions'));
    }

    public function pdf($id)
    {
        $bill = BillPayNumber::findOrFail($id);
        
        // Generate QR code with bill details
        $qrContent = "FEEDTAN BillPay\n" .
                   "Control Number: " . $bill->bill_pay_number . "\n" .
                   "Type: " . ucfirst($bill->bill_type) . "\n" .
                   "Description: " . $bill->bill_description . "\n" .
                   "Amount: " . number_format($bill->bill_amount, 2) . " " . $bill->bill_currency . "\n" .
                   "Status: " . $bill->bill_status . "\n" .
                   "Created: " . $bill->created_at->format('Y-m-d H:i:s');
        
        $qrCodeSvg = QrCode::format('svg')->size(150)->encoding('UTF-8')->errorCorrection('H')->generate($qrContent);
        $qrCodeImage = 'data:image/svg+xml;base64,' . base64_encode($qrCodeSvg);
        
        $pdf = Pdf::loadView('bills.pdf', ['bill' => $bill, 'qrCodeImage' => $qrCodeImage])
            ->setPaper('a4', 'portrait')
            ->setOption('margin-bottom', 20);
        
        return $pdf->download('bill-' . $bill->bill_pay_number . '.pdf');
    }
}
