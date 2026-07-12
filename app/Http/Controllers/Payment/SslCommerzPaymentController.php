<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\CustomersInfo;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SslCommerzPaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    private function getSslConfig()
    {
        $sandbox = siteUrlSettings('payment_sslcommerz_sandbox') ?? true;
        $baseUrl = $sandbox
            ? 'https://sandbox.sslcommerz.com'
            : 'https://header.sslcommerz.com';

        return [
            'base_url' => $baseUrl,
            'store_id' => siteUrlSettings('payment_sslcommerz_store_id'),
            'store_passwd' => siteUrlSettings('payment_sslcommerz_store_password'),
            'sandbox' => $sandbox,
        ];
    }

    public function initiate(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $amount = $request->amount;
        $user = auth()->user();

        if (! $user || ! $user->customer) {
            return redirect()->back()->with('error', 'Unauthorized customer access.');
        }

        $customer = $user->customer;

        // Auto-detect local development to offer sandbox mock helper
        $host = request()->getHost();
        $isLocal = str_ends_with($host, '.test') || str_ends_with($host, '.local') || $host === 'localhost' || $host === '127.0.0.1';

        if ($isLocal && $request->has('mock')) {
            return response()->view('payment.mock_checkout', [
                'gateway' => 'SSLCommerz',
                'customer' => $customer,
                'amount' => $amount,
                'reason' => null,
            ]);
        }

        try {
            $config = $this->getSslConfig();

            if (empty($config['store_id']) || empty($config['store_passwd'])) {
                if ($isLocal) {
                    return response()->view('payment.mock_checkout', [
                        'gateway' => 'SSLCommerz',
                        'customer' => $customer,
                        'amount' => $amount,
                        'reason' => 'SSLCommerz store ID or password is not configured.',
                    ]);
                }

                return redirect()->route('filament.portal.pages.pay-bill')
                    ->with('error', 'SSLCommerz is not properly configured.');
            }

            $post_data = [
                'store_id' => $config['store_id'],
                'store_passwd' => $config['store_passwd'],
                'total_amount' => (string) round($amount, 2),
                'currency' => 'BDT',
                'tran_id' => 'SSL_'.uniqid(),
                'success_url' => route('payment.sslcommerz.callback'),
                'fail_url' => route('payment.sslcommerz.callback'),
                'cancel_url' => route('payment.sslcommerz.callback'),

                // Customer Information
                'cus_name' => $customer->customer_name ?: 'ISP Customer',
                'cus_email' => $customer->email ?: 'customer@example.com',
                'cus_add1' => $customer->address ?: 'Dhaka',
                'cus_city' => 'Dhaka',
                'cus_country' => 'Bangladesh',
                'cus_phone' => $customer->mobile ?: '01700000000',

                // Non-physical goods
                'shipping_method' => 'NO',
                'product_name' => 'Internet Bill',
                'product_category' => 'Internet',
                'product_profile' => 'non-physical-goods',
            ];

            $response = Http::asForm()->post($config['base_url'].'/gwprocess/v4/api.php', $post_data);
            $res = $response->json();

            if (isset($res['status']) && $res['status'] === 'SUCCESS' && isset($res['GatewayPageURL'])) {
                session(['ssl_amount' => $amount, 'ssl_customer_id' => $customer->id]);

                return redirect()->away($res['GatewayPageURL']);
            }

            Log::error('SSLCommerz Session Init Failed: '.json_encode($res));

            if ($isLocal) {
                return response()->view('payment.mock_checkout', [
                    'gateway' => 'SSLCommerz',
                    'customer' => $customer,
                    'amount' => $amount,
                    'reason' => 'SSLCommerz API error: '.($res['failedreason'] ?? 'Session creation failed.'),
                ]);
            }

            return redirect()->route('filament.portal.pages.pay-bill')
                ->with('error', 'SSLCommerz session creation failed: '.($res['failedreason'] ?? 'Unknown error'));

        } catch (\Exception $e) {
            Log::error('SSLCommerz initiate exception: '.$e->getMessage());

            if ($isLocal) {
                return response()->view('payment.mock_checkout', [
                    'gateway' => 'SSLCommerz',
                    'customer' => $customer,
                    'amount' => $amount,
                    'reason' => 'Connection exception: '.$e->getMessage(),
                ]);
            }

            return redirect()->route('filament.portal.pages.pay-bill')
                ->with('error', 'SSLCommerz service currently offline: '.$e->getMessage());
        }
    }

    public function callback(Request $request)
    {
        $status = $request->input('status');
        $tranId = $request->input('tran_id');
        $amount = (float) $request->input('amount');
        $valId = $request->input('val_id');

        if ($status === 'VALID' || $status === 'VALIDATED') {
            try {
                $config = $this->getSslConfig();

                // Double check validity using SSLCommerz transaction validator API
                $validator = Http::get($config['base_url'].'/validator/api/valid.php', [
                    'val_id' => $valId,
                    'store_id' => $config['store_id'],
                    'store_passwd' => $config['store_passwd'],
                    'v' => 1,
                    'format' => 'json',
                ]);

                $res = $validator->json();

                if (isset($res['status']) && ($res['status'] === 'VALID' || $res['status'] === 'VALIDATED')) {
                    $trxID = $res['bank_trx_id'] ?: $tranId;

                    // We can locate the customer by matching the user session or retrieving their identifier
                    $customer = auth()->user() ? auth()->user()->customer : null;

                    if (! $customer && session()->has('ssl_customer_id')) {
                        $customer = CustomersInfo::find(session('ssl_customer_id'));
                    }

                    if ($customer) {
                        $this->paymentService->processSuccessPayment($customer, $amount, 'sslcommerz', $trxID);

                        return redirect()->route('filament.portal.pages.dashboard')
                            ->with('success', 'Payment of BDT '.$amount.' received successfully via SSLCommerz. Your account is active.');
                    }
                }

                Log::error('SSLCommerz Validation Failed: '.json_encode($res));

                return redirect()->route('filament.portal.pages.pay-bill')
                    ->with('error', 'Transaction validation failed.');

            } catch (\Exception $e) {
                Log::error('SSLCommerz callback verification exception: '.$e->getMessage());

                return redirect()->route('filament.portal.pages.pay-bill')
                    ->with('error', 'Verification exception: '.$e->getMessage());
            }
        }

        return redirect()->route('filament.portal.pages.pay-bill')
            ->with('error', 'Payment failed or cancelled. Status: '.$status);
    }
}
