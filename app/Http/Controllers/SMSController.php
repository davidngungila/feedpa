<?php

namespace App\Http\Controllers;

use App\Services\MessagingServiceAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SMSController extends Controller
{
    protected MessagingServiceAPI $messaging;

    public function __construct(MessagingServiceAPI $messaging)
    {
        $this->messaging = $messaging;
    }

    /**
     * Show SMS testing form
     */
    public function index()
    {
        return view('sms-test.index');
    }

    /**
     * Send test SMS
     */
    public function sendSMS(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'message' => 'required|string|max:160'
        ]);

        try {
            $phoneNumber = $request->input('phone_number');
            $message = $request->input('message');

            Log::info('Testing SMS send', [
                'phone' => $phoneNumber,
                'message' => $message
            ]);

            $result = $this->messaging->sendSMS($phoneNumber, $message);

            return back()->with('success', 'SMS sent successfully! Result: ' . json_encode($result));

        } catch (\Exception $e) {
            Log::error('SMS test failed: ' . $e->getMessage());
            return back()->with('error', 'SMS failed: ' . $e->getMessage());
        }
    }

    /**
     * Test bill notification
     */
    public function testBillNotification(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string'
        ]);

        try {
            $phoneNumber = $request->input('phone_number');

            $billData = [
                'reference' => 'FEEDTAN1234',
                'description' => 'Test Bill Payment',
                'amount' => 25000,
                'currency' => 'TZS',
                'due_date' => 'ASAP',
                'customer_name' => 'Test Customer'
            ];

            $result = $this->messaging->sendBillNotification($phoneNumber, $billData);

            return back()->with('success', 'Bill notification sent! Result: ' . json_encode($result));

        } catch (\Exception $e) {
            Log::error('Bill notification test failed: ' . $e->getMessage());
            return back()->with('error', 'Bill notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Test payment notification
     */
    public function testPaymentNotification(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string'
        ]);

        try {
            $phoneNumber = $request->input('phone_number');

            $paymentData = [
                'transaction_id' => 'TXN' . time(),
                'reference' => 'FEEDTAN1234',
                'amount' => 25000,
                'currency' => 'TZS',
                'payment_method' => 'Mobile Money'
            ];

            $result = $this->messaging->sendPaymentConfirmation($phoneNumber, $paymentData);

            return back()->with('success', 'Payment notification sent! Result: ' . json_encode($result));

        } catch (\Exception $e) {
            Log::error('Payment notification test failed: ' . $e->getMessage());
            return back()->with('error', 'Payment notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Test insufficient funds notification
     */
    public function testInsufficientFunds(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string'
        ]);

        try {
            $phoneNumber = $request->input('phone_number');

            $paymentData = [
                'reference' => 'FEEDTAN1234',
                'amount' => 25000,
                'currency' => 'TZS',
                'phone_number' => $phoneNumber
            ];

            $result = $this->messaging->sendInsufficientFundsNotification($phoneNumber, $paymentData);

            return back()->with('success', 'Insufficient funds notification sent! Result: ' . json_encode($result));

        } catch (\Exception $e) {
            Log::error('Insufficient funds notification test failed: ' . $e->getMessage());
            return back()->with('error', 'Insufficient funds notification failed: ' . $e->getMessage());
        }
    }
}
