<?php

namespace App\Http\Controllers;

use App\Services\ClickPesaAPIService;
use App\Services\MessagingServiceAPI;
use App\Models\BillPayNumber;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BillPayController extends Controller
{
    protected ClickPesaAPIService $api;
    protected MessagingServiceAPI $messaging;

    public function __construct(ClickPesaAPIService $api, MessagingServiceAPI $messaging)
    {
        $this->api = $api;
        $this->messaging = $messaging;
    }

    /**
     * Show create BillPay form
     */
    public function create()
    {
        return view('billpay.create');
    }

    /**
     * Store BillPay control number
     */
    public function store(Request $request)
    {
        // Debug logging
        Log::info('BillPay store method called', [
            'request_data' => $request->all(),
            'request_method' => $request->method(),
            'request_url' => $request->fullUrl()
        ]);

        $validated = $request->validate([
            'bill_type' => 'required|in:order,customer,bulk_order,bulk_customer',
            'bill_description' => 'nullable|string|max:255',
            'bill_amount' => 'required|numeric|min:100',
            'bill_reference' => 'nullable|string|max:20',
            'bill_payment_mode' => 'nullable|in:ALLOW_PARTIAL_AND_OVER_PAYMENT,EXACT',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|regex:/^255[67]\d{8}$/',
            'bulk_data' => 'nullable|json'
        ]);

        try {
            $billPayData = null;
            $billPaymentMode = $validated['bill_payment_mode'] ?? 'ALLOW_PARTIAL_AND_OVER_PAYMENT';

            if ($validated['bill_type'] === 'order') {
                $billDescription = $validated['bill_description'] ?? 'Order Payment';
                $billAmount = $this->api->formatAmount($validated['bill_amount']);
                $billReference = $validated['bill_reference'] ?? $this->api->generateFeedtanPayControlNumber();

                $result = $this->api->createOrderControlNumber($billDescription, $billAmount, $billReference, $billPaymentMode);
                
                // Save to database
                $billPayNumber = BillPayNumber::createFromApiResponse($result, [
                    'bill_type' => 'order',
                    'bill_reference' => $billReference,
                    'bill_description' => $billDescription,
                    'bill_amount' => $billAmount,
                    'bill_currency' => 'TZS',
                    'created_by' => auth()->id(),
                ]);

                // Send SMS notification for bill generation
                $this->sendBillNotification($billPayNumber);
                
                return redirect()->route('billpay.show', $billPayNumber->bill_pay_number)
                    ->with('success', 'Order Control Number created successfully!');
            }

            if ($validated['bill_type'] === 'customer') {
                $customerName = $validated['customer_name'];
                $customerEmail = $validated['customer_email'] ?? null;
                $customerPhone = !empty($validated['customer_phone']) ? $this->api->validatePhoneNumber($validated['customer_phone']) : null;
                $billDescription = $validated['bill_description'] ?? 'Customer Payment';
                $billAmount = $this->api->formatAmount($validated['bill_amount']);
                $billReference = $validated['bill_reference'] ?? $this->api->generateFeedtanPayControlNumber();

                // Debug logging
                Log::info('BillPay Customer Control Number Creation', [
                    'customer_name' => $customerName,
                    'original_amount' => $validated['bill_amount'],
                    'formatted_amount' => $billAmount,
                    'bill_reference' => $billReference
                ]);

                $result = $this->api->createCustomerControlNumber($customerName, $customerEmail, $customerPhone, $billDescription, $billAmount, $billReference, $billPaymentMode);
                
                // Save to database
                $billPayNumber = BillPayNumber::createFromApiResponse($result, [
                    'bill_type' => 'customer',
                    'customer_name' => $customerName,
                    'customer_email' => $customerEmail,
                    'customer_phone' => $customerPhone,
                    'bill_reference' => $billReference,
                    'bill_description' => $billDescription,
                    'bill_amount' => $billAmount,
                    'bill_currency' => 'TZS',
                    'created_by' => auth()->id(),
                ]);

                // Send SMS notification for bill generation
                $this->sendBillNotification($billPayNumber);
                
                return redirect()->route('billpay.show', $billPayNumber->bill_pay_number)
                    ->with('success', 'Customer Control Number created successfully!');
            }

            if ($validated['bill_type'] === 'bulk_order') {
                $bulkData = json_decode($validated['bulk_data'], true);
                
                if (!$bulkData || !is_array($bulkData)) {
                    return back()->with('error', 'Invalid bulk data format');
                }

                $controlNumbers = [];
                foreach ($bulkData as $item) {
                    $controlItem = ['billDescription' => $item['billDescription'] ?? 'Bulk Bill'];
                    
                    if (isset($item['billAmount']) && $item['billAmount'] > 0) {
                        $controlItem['billAmount'] = $this->api->formatAmount($item['billAmount']);
                    }
                    
                    $controlItem['billReference'] = $item['billReference'] ?? $this->api->generateFeedtanPayControlNumber();
                    $controlNumbers[] = $controlItem;
                }

                $result = $this->api->bulkCreateOrderControlNumbers($controlNumbers);
                $billPayData = $result;
                
                return redirect()->route('billpay.index')
                    ->with('success', 'Bulk Order Control Numbers created successfully! Created: ' . ($result['created'] ?? 0));
            }

            if ($validated['bill_type'] === 'bulk_customer') {
                $bulkData = json_decode($validated['bulk_data'], true);
                
                if (!$bulkData || !is_array($bulkData)) {
                    return back()->with('error', 'Invalid bulk data format');
                }

                $controlNumbers = [];
                foreach ($bulkData as $item) {
                    if (empty($item['customerName'])) {
                        continue;
                    }

                    $controlItem = ['customerName' => $item['customerName']];
                    
                    if (!empty($item['customerEmail'])) {
                        $controlItem['customerEmail'] = $item['customerEmail'];
                    }
                    if (!empty($item['customerPhone'])) {
                        $controlItem['customerPhone'] = $this->api->validatePhoneNumber($item['customerPhone']);
                    }
                    if (!empty($item['billDescription'])) {
                        $controlItem['billDescription'] = $item['billDescription'];
                    }
                    if (isset($item['billAmount']) && $item['billAmount'] > 0) {
                        $controlItem['billAmount'] = $this->api->formatAmount($item['billAmount']);
                    }
                    
                    $controlItem['billReference'] = $item['billReference'] ?? $this->api->generateFeedtanPayControlNumber();
                    $controlNumbers[] = $controlItem;
                }

                $result = $this->api->bulkCreateCustomerControlNumbers($controlNumbers);
                $billPayData = $result;
                
                return redirect()->route('billpay.index')
                    ->with('success', 'Bulk Customer Control Numbers created successfully! Created: ' . ($result['created'] ?? 0));
            }
        } catch (Exception $e) {
            Log::error('BillPay creation failed: ' . $e->getMessage());
            
            // Handle insufficient funds error specifically
            if (stripos($e->getMessage(), 'Insufficient Funds') !== false) {
                return back()
                    ->with('error', $e->getMessage())
                    ->with('warning_type', 'insufficient_funds')
                    ->withInput();
            }
            
            return back()->with('error', $e->getMessage())->withInput();
        }

        return back()->with('error', 'Invalid bill type selected');
    }

    /**
     * Send SMS notification for bill generation
     */
    private function sendBillNotification(BillPayNumber $billPayNumber)
    {
        try {
            if (!config('messaging.enabled') || !config('messaging.notifications.bill_generation')) {
                Log::info('SMS notifications disabled, skipping bill notification');
                return;
            }

            $phoneNumber = null;
            
            // Get phone number from customer data or bill reference
            if ($billPayNumber->customer_phone) {
                $phoneNumber = $billPayNumber->customer_phone;
            } elseif ($billPayNumber->bill_reference) {
                // Try to extract phone number from bill reference if it's a phone number
                $phoneNumber = $billPayNumber->bill_reference;
            }

            if (!$phoneNumber) {
                Log::warning('No phone number available for bill notification', [
                    'bill_id' => $billPayNumber->id,
                    'bill_reference' => $billPayNumber->bill_reference
                ]);
                return;
            }

            $billData = [
                'reference' => $billPayNumber->bill_reference,
                'description' => $billPayNumber->bill_description,
                'amount' => $billPayNumber->bill_amount,
                'currency' => $billPayNumber->bill_currency,
                'due_date' => 'ASAP', // Can be customized based on business logic
                'customer_name' => $billPayNumber->customer_name,
            ];

            $result = $this->messaging->sendBillNotification($phoneNumber, $billData);
            
            Log::info('Bill notification sent successfully', [
                'bill_id' => $billPayNumber->id,
                'phone_number' => $phoneNumber,
                'result' => $result
            ]);

        } catch (Exception $e) {
            Log::error('Failed to send bill notification', [
                'bill_id' => $billPayNumber->id,
                'phone_number' => $billPayNumber->customer_phone ?? 'N/A',
                'error' => $e->getMessage()
            ]);
            
            // Don't throw exception - SMS failure shouldn't break bill creation
        }
    }

    /**
     * List all BillPay control numbers
     */
    public function index(Request $request)
    {
        $error = null;
        $search = $request->get('search');
        $status = $request->get('status');

        try {
            // Query database for BillPay numbers with filters
            $billPayNumbersQuery = BillPayNumber::query();
            
            // Apply search filter
            if ($search) {
                $billPayNumbersQuery->search($search);
            }
            
            // Apply status filter
            if ($status && $status !== 'all') {
                $billPayNumbersQuery->status($status);
            }
            
            // Get paginated results
            $billPayNumbers = $billPayNumbersQuery->orderBy('created_at', 'desc')
                ->paginate(50);
            
        } catch (Exception $e) {
            $error = 'Failed to retrieve BillPay numbers: ' . $e->getMessage();
            Log::error($error);
            $billPayNumbers = collect();
        }

        return view('billpay.index', compact('billPayNumbers', 'error'));
    }

    /**
     * Show specific BillPay control number details
     */
    public function show(string $billPayNumber)
    {
        $billPayData = null;
        $error = null;

        Log::info('BillPay show requested', ['billPayNumber' => $billPayNumber]);

        try {
            // First try to get from database
            $billPayRecord = BillPayNumber::where('bill_pay_number', $billPayNumber)->first();
            
            if ($billPayRecord) {
                Log::info('BillPay found in database', [
                    'billPayNumber' => $billPayNumber,
                    'bill_amount' => $billPayRecord->bill_amount,
                    'bill_currency' => $billPayRecord->bill_currency,
                    'customer_name' => $billPayRecord->customer_name
                ]);
                
                $billPayData = [
                    'billPayNumber' => $billPayRecord->bill_pay_number,
                    'billDescription' => $billPayRecord->bill_description,
                    'billAmount' => (float) $billPayRecord->bill_amount,
                    'billCurrency' => $billPayRecord->bill_currency,
                    'billPaymentMode' => $billPayRecord->bill_payment_mode,
                    'billStatus' => $billPayRecord->bill_status,
                    'billType' => $billPayRecord->bill_type,
                    'billCustomerName' => $billPayRecord->customer_name,
                    'billReference' => $billPayRecord->bill_reference,
                    'customerEmail' => $billPayRecord->customer_email,
                    'customerPhone' => $billPayRecord->customer_phone,
                    'notes' => $billPayRecord->notes,
                    'createdBy' => $billPayRecord->created_by,
                    'createdAt' => $billPayRecord->created_at->toISOString(),
                    'updatedAt' => $billPayRecord->updated_at->toISOString(),
                    'totalPaid' => (float) $billPayRecord->total_paid,
                    'lastPaymentAt' => $billPayRecord->last_payment_at?->toISOString(),
                ];
            } else {
                // Fall back to API if not in database
                Log::info('BillPay not found in database, trying API', ['billPayNumber' => $billPayNumber]);
                $billPayData = $this->api->queryBillPayNumber($billPayNumber);
                Log::info('API response received', ['data' => $billPayData]);
                
                if (isset($billPayData['billPayNumber'])) {
                    // Save to database for future reference
                    BillPayNumber::createFromApiResponse($billPayData, [
                        'created_by' => auth()->id(),
                    ]);
                }
            }
            
            if (!isset($billPayData['billPayNumber'])) {
                if (strpos($billPayNumber, 'FEEDTANPAY') === 0) {
                    // Create a sample record for FEEDTANPAY numbers
                    $billPayRecord = BillPayNumber::firstOrCreate(
                        ['bill_pay_number' => $billPayNumber],
                        [
                            'bill_description' => 'Sample BillPay Control Number',
                            'bill_amount' => 0,
                            'bill_currency' => 'TZS',
                            'bill_payment_mode' => 'ALLOW_PARTIAL_AND_OVER_PAYMENT',
                            'bill_status' => 'ACTIVE',
                            'bill_type' => 'order',
                            'created_by' => auth()->id(),
                        ]
                    );
                    
                    // Use the created record for display
                    $billPayData = [
                        'billPayNumber' => $billPayRecord->bill_pay_number,
                        'billDescription' => $billPayRecord->bill_description,
                        'billAmount' => (float) $billPayRecord->bill_amount,
                        'billCurrency' => $billPayRecord->bill_currency,
                        'billPaymentMode' => $billPayRecord->bill_payment_mode,
                        'billStatus' => $billPayRecord->bill_status,
                        'billType' => $billPayRecord->bill_type,
                        'billCustomerName' => $billPayRecord->customer_name,
                        'billReference' => $billPayRecord->bill_reference,
                        'createdAt' => $billPayRecord->created_at->toISOString(),
                        'updatedAt' => $billPayRecord->updated_at->toISOString(),
                        'totalPaid' => (float) $billPayRecord->total_paid,
                        'lastPaymentAt' => $billPayRecord->last_payment_at?->toISOString(),
                    ];
                    
                    Log::info('Sample FEEDTANPAY number created in database', ['billPayNumber' => $billPayNumber]);
                } else {
                    $error = 'No BillPay number found with provided number: ' . $billPayNumber;
                    Log::warning('No BillPay data found', ['billPayNumber' => $billPayNumber]);
                }
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            Log::error('BillPay show error: ' . $error, [
                'billPayNumber' => $billPayNumber,
                'exception' => $e->getTraceAsString()
            ]);
        }

        return view('billpay.show', compact('billPayData', 'error', 'billPayNumber'));
    }

    /**
     * Update BillPay control number
     */
    public function update(Request $request, string $billPayNumber)
    {
        $validated = $request->validate([
            'bill_description' => 'nullable|string|max:255',
            'bill_amount' => 'nullable|numeric|min:0',
            'bill_payment_mode' => 'nullable|in:ALLOW_PARTIAL_AND_OVER_PAYMENT,EXACT',
            'bill_status' => 'nullable|in:ACTIVE,INACTIVE'
        ]);

        try {
            $billAmount = isset($validated['bill_amount']) ? $this->api->formatAmount($validated['bill_amount']) : null;
            $billDescription = $validated['bill_description'] ?? null;
            $billPaymentMode = $validated['bill_payment_mode'] ?? null;
            $billStatus = $validated['bill_status'] ?? null;

            $result = $this->api->updateBillPayReference($billPayNumber, $billAmount, $billDescription, $billPaymentMode, $billStatus);
            
            return redirect()->route('billpay.show', $billPayNumber)
                ->with('success', 'BillPay number updated successfully!');
        } catch (Exception $e) {
            Log::error('BillPay update failed: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }
}
