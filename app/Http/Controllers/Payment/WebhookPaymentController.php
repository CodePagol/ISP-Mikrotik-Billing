<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\CustomersInfo;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookPaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Handle incoming bKash Merchant IPN webhook.
     */
    public function handleBkashIpn(Request $request)
    {
        // Log the raw incoming payload for auditing
        Log::info('bKash Merchant IPN Webhook received: ' . json_encode($request->all()));

        $trxId = $request->input('trxID') ?? $request->input('transactionId') ?? $request->input('trxId');
        $amount = $request->input('amount');
        $payerReference = $request->input('payerReference') ?? $request->input('reference') ?? $request->input('payerRef');
        $status = strtolower($request->input('transactionStatus') ?? $request->input('status') ?? 'completed');

        if (!$trxId || !$amount || !$payerReference) {
            Log::warning('bKash IPN missing required fields: trxID, amount, or payerReference.');
            return response()->json(['status' => 'error', 'message' => 'Missing required fields'], 400);
        }

        // Verify status is success/completed
        if ($status !== 'completed' && $status !== 'successful' && $status !== 'success') {
            Log::info("bKash IPN transaction ignored. Status is '{$status}' (expected Completed).");
            return response()->json(['status' => 'ignored', 'message' => 'Non-success transaction status'], 200);
        }

        // Search for customer matching reference (unique ID)
        $customer = CustomersInfo::where('customer_unique_id', $payerReference)->first();

        if (!$customer) {
            Log::warning("bKash IPN customer not found for payerReference: {$payerReference}");
            return response()->json(['status' => 'error', 'message' => 'Customer not found'], 404);
        }

        try {
            $this->paymentService->processSuccessPayment($customer, (float)$amount, 'bkash', $trxId);
            return response()->json(['status' => 'success', 'message' => 'Payment processed successfully']);
        } catch (\Exception $e) {
            Log::error("bKash IPN processing failed: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle MFS SMS Forwarder webhooks.
     */
    public function handleSmsReceiver(Request $request)
    {
        // Log the SMS payload (Sanctum auth handles token verification)
        Log::info('MFS SMS Forwarder Webhook received payload: ' . json_encode($request->all()));

        $sender = $request->input('sender') ?? $request->input('from') ?? $request->input('sender_name') ?? '';
        $message = $request->input('message') ?? $request->input('text') ?? $request->input('body') ?? $request->input('msg') ?? '';

        if (empty($message)) {
            return response()->json(['status' => 'ignored', 'message' => 'Empty message content'], 200);
        }

        // 2. Parse SMS parameters
        $parsed = $this->parseSms($sender, $message);
        $amount = $parsed['amount'];
        $trxId = $parsed['trxId'];
        $ref = $parsed['ref'];
        $senderMobile = $parsed['senderMobile'];
        $gateway = $parsed['gateway'];

        if (!$amount || !$trxId) {
            Log::warning("Could not extract amount or transaction ID from MFS SMS. Parsed Amount: '{$amount}', TrxID: '{$trxId}'");
            return response()->json(['status' => 'error', 'message' => 'Failed to parse SMS content'], 422);
        }

        $customer = null;

        // Step 1: Match by reference in SMS (customer unique ID)
        if ($ref) {
            $customer = CustomersInfo::where('customer_unique_id', $ref)->first();
        }

        // Step 2: Match by reference in SMS (PPP secret username)
        if (!$customer && $ref) {
            $customer = CustomersInfo::whereHas('pppUser', function ($query) use ($ref) {
                $query->where('username', $ref);
            })->first();
        }

        // Step 3: Match by sender's mobile number
        if (!$customer && $senderMobile) {
            // Clean up 88/+88 prefixes for matching
            $cleanMobile = $senderMobile;
            if (str_starts_with($cleanMobile, '88')) {
                $cleanMobile = substr($cleanMobile, 2);
            } elseif (str_starts_with($cleanMobile, '+88')) {
                $cleanMobile = substr($cleanMobile, 3);
            }

            $customer = CustomersInfo::where('mobile', $cleanMobile)
                ->orWhere('mobile', '88' . $cleanMobile)
                ->orWhere('mobile', '+88' . $cleanMobile)
                ->first();
        }

        if (!$customer) {
            Log::warning("No matching customer found for MFS SMS. Ref: '{$ref}', SenderMobile: '{$senderMobile}', TrxID: '{$trxId}'");
            return response()->json(['status' => 'error', 'message' => 'No matching customer found'], 404);
        }

        try {
            $this->paymentService->processSuccessPayment($customer, (float)$amount, $gateway, $trxId);
            return response()->json([
                'status' => 'success',
                'message' => 'Payment processed successfully',
                'customer' => $customer->customer_unique_id,
                'amount' => $amount,
                'gateway' => $gateway,
                'transaction_id' => $trxId
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to process MFS SMS payment: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Payment process exception: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper method to parse MFS notification SMS texts using regex patterns.
     */
    private function parseSms($sender, $message)
    {
        $senderLower = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $sender));
        $amount = null;
        $trxId = null;
        $ref = null;
        $senderMobile = null;
        $gateway = 'mfs';

        if (str_contains($senderLower, 'bkash')) {
            $gateway = 'bkash';
            // Parse Amount
            if (preg_match('/(?:Received BDT|Cash In BDT|Payment received BDT)\s*(\d+(?:\.\d+)?)/i', $message, $matches)) {
                $amount = (float)$matches[1];
            }
            // Parse Sender Mobile
            if (preg_match('/from\s*(\d{11})/i', $message, $matches)) {
                $senderMobile = $matches[1];
            }
            // Parse TrxID
            if (preg_match('/TrxID\s*([A-Z0-9]+)/i', $message, $matches)) {
                $trxId = $matches[1];
            }
            // Parse Ref
            if (preg_match('/Ref\s*([a-zA-Z0-9_-]+)/i', $message, $matches)) {
                $ref = $matches[1];
            }
        } elseif (str_contains($senderLower, 'nagad')) {
            $gateway = 'nagad';
            // Parse Amount
            if (preg_match('/(?:Received Amount:\s*Tk\s*|Received BDT\s*)(\d+(?:\.\d+)?)/i', $message, $matches)) {
                $amount = (float)$matches[1];
            }
            // Parse Sender Mobile
            if (preg_match('/From:\s*(\d{11})/i', $message, $matches)) {
                $senderMobile = $matches[1];
            }
            // Parse TxnID
            if (preg_match('/TxnID:\s*([A-Z0-9]+)/i', $message, $matches)) {
                $trxId = $matches[1];
            }
            // Parse Ref
            if (preg_match('/Ref:\s*([a-zA-Z0-9_-]+)/i', $message, $matches)) {
                $ref = $matches[1];
            }
        } elseif (str_contains($senderLower, 'rocket') || str_contains($senderLower, 'dbbl') || $senderLower === '16216') {
            $gateway = 'rocket';
            // Parse Amount
            if (preg_match('/Tk\.?\s*(\d+(?:\.\d+)?)/i', $message, $matches)) {
                $amount = (float)$matches[1];
            }
            // Parse Sender Mobile
            if (preg_match('/from\s*(\d{11,12})/i', $message, $matches)) {
                $senderMobile = $matches[1];
                if (strlen($senderMobile) === 12) {
                    $senderMobile = substr($senderMobile, 0, 11);
                }
            }
            // Parse TxnID
            if (preg_match('/(?:TxnID|TrxID):\s*([A-Z0-9]+)/i', $message, $matches)) {
                $trxId = $matches[1];
            }
            // Parse Ref
            if (preg_match('/Ref:\s*([a-zA-Z0-9_-]+)/i', $message, $matches)) {
                $ref = $matches[1];
            }
        } else {
            // General Fallback
            if (preg_match('/(?:BDT|Tk|TK)\.?\s*(\d+(?:\.\d+)?)/i', $message, $matches)) {
                $amount = (float)$matches[1];
            } elseif (preg_match('/(\d+(?:\.\d+)?)\s*(?:BDT|Tk|TK)/i', $message, $matches)) {
                $amount = (float)$matches[1];
            }

            if (preg_match('/(?:TrxID|TxnID|TxID)\s*:?\s*([A-Z0-9]+)/i', $message, $matches)) {
                $trxId = $matches[1];
            }

            if (preg_match('/from\s*(\d{11})/i', $message, $matches)) {
                $senderMobile = $matches[1];
            }

            if (preg_match('/Ref\s*:?\s*([a-zA-Z0-9_-]+)/i', $message, $matches)) {
                $ref = $matches[1];
            }
        }

        return [
            'amount' => $amount,
            'trxId' => $trxId,
            'ref' => $ref,
            'senderMobile' => $senderMobile,
            'gateway' => $gateway
        ];
    }
}
