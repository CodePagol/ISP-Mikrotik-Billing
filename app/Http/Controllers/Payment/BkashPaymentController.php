<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\CustomersInfo;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BkashPaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    private function getBkashConfig()
    {
        return [
            'base_url' => siteUrlSettings('payment_bkash_base_url') ?: config('services.bkash.base_url'),
            'username' => siteUrlSettings('payment_bkash_username') ?: config('services.bkash.username'),
            'password' => siteUrlSettings('payment_bkash_password') ?: config('services.bkash.password'),
            'app_key' => siteUrlSettings('payment_bkash_app_key') ?: config('services.bkash.app_key'),
            'app_secret' => siteUrlSettings('payment_bkash_app_secret') ?: config('services.bkash.app_secret'),
        ];
    }

    private function generateToken()
    {
        $config = $this->getBkashConfig();
        $apiType = siteUrlSettings('payment_bkash_api_type') ?: 'tokenized';

        $tokenUrl = ($apiType === 'tokenized') 
            ? $config['base_url'].'/tokenized/checkout/token/grant'
            : $config['base_url'].'/checkout/token/grant';

        $response = Http::withBasicAuth($config['username'], $config['password'])
            ->post($tokenUrl, [
                'app_key' => $config['app_key'],
                'app_secret' => $config['app_secret'],
            ]);

        $body = $response->json();

        if (! isset($body['id_token'])) {
            Log::error('bKash token generation failed: '.json_encode($body));
            throw new \Exception('bKash gateway authentication failed.');
        }

        return $body['id_token'];
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
            return $this->showMockPaymentPage('bKash', $customer, $amount);
        }

        try {
            $idToken = $this->generateToken();
            $config = $this->getBkashConfig();
            $apiType = siteUrlSettings('payment_bkash_api_type') ?: 'tokenized';

            $callbackURL = route('payment.bkash.callback');

            if ($apiType === 'tokenized') {
                $createUrl = $config['base_url'].'/tokenized/checkout/create';
                $payload = [
                    'mode' => '0011',
                    'payerReference' => $customer->customer_unique_id,
                    'callbackURL' => $callbackURL,
                    'amount' => (string) round($amount, 2),
                    'merchantInvoiceNumber' => 'INV_'.uniqid(),
                    'intent' => 'sale',
                ];
            } else {
                $createUrl = $config['base_url'].'/checkout/payment/create';
                $payload = [
                    'amount' => (string) round($amount, 2),
                    'currency' => 'BDT',
                    'intent' => 'sale',
                    'merchantInvoiceNumber' => 'INV_'.uniqid(),
                    'callbackURL' => $callbackURL,
                ];
            }

            $payment = Http::withToken($idToken)
                ->withHeaders([
                    'X-App-Key' => $config['app_key'],
                ])
                ->post($createUrl, $payload);

            $res = $payment->json();

            if (isset($res['bkashURL'])) {
                session(['bkash_amount' => $amount, 'bkash_customer_id' => $customer->id]);

                return redirect()->away($res['bkashURL']);
            }

            Log::error('bKash Create Payment Failed: '.json_encode($res));

            // If API fails on local development, redirect to mock page
            if ($isLocal) {
                return $this->showMockPaymentPage('bKash', $customer, $amount, 'bKash API returned error: '.($res['errorMessage'] ?? 'Unknown error'));
            }

            return redirect()->route('filament.portal.pages.pay-bill')
                ->with('error', 'bKash payment initiation failed: '.($res['errorMessage'] ?? 'Unknown error'));

        } catch (\Exception $e) {
            Log::error('bKash initiate exception: '.$e->getMessage());

            if ($isLocal) {
                return $this->showMockPaymentPage('bKash', $customer, $amount, 'Connection failed: '.$e->getMessage());
            }

            return redirect()->route('filament.portal.pages.pay-bill')
                ->with('error', 'bKash service temporarily unavailable: '.$e->getMessage());
        }
    }

    public function callback(Request $request)
    {
        $status = $request->query('status');
        $paymentID = $request->query('paymentID');

        if ($status === 'success') {
            try {
                $idToken = $this->generateToken();
                $config = $this->getBkashConfig();
                $apiType = siteUrlSettings('payment_bkash_api_type') ?: 'tokenized';

                $executeUrl = ($apiType === 'tokenized')
                    ? $config['base_url'].'/tokenized/checkout/execute'
                    : $config['base_url'].'/checkout/payment/execute';

                $execution = Http::withToken($idToken)
                    ->withHeaders([
                        'X-App-Key' => $config['app_key'],
                    ])
                    ->post($executeUrl, [
                        'paymentID' => $paymentID,
                    ]);

                $res = $execution->json();

                if (isset($res['statusCode']) && $res['statusCode'] === '0000') {
                    $trxID = $res['trxID'] ?? 'BKASH_'.uniqid();
                    $amount = (float) $res['amount'];
                    
                    // Search customer by payerReference or session fallback
                    $customerUniqueId = $res['payerReference'] ?? null;
                    $customer = null;
                    if ($customerUniqueId) {
                        $customer = CustomersInfo::where('customer_unique_id', $customerUniqueId)->first();
                    }

                    if (! $customer) {
                        $sessionCustomerId = session('bkash_customer_id');
                        if ($sessionCustomerId) {
                            $customer = CustomersInfo::find($sessionCustomerId);
                        }
                    }

                    if ($customer) {
                        $this->paymentService->processSuccessPayment($customer, $amount, 'bkash', $trxID);

                        return redirect()->route('filament.portal.pages.dashboard')
                            ->with('success', 'Payment of BDT '.$amount.' received successfully via bKash. Your account is active.');
                    }
                }

                Log::error('bKash Execution Failed: '.json_encode($res));

                return redirect()->route('filament.portal.pages.pay-bill')
                    ->with('error', 'Payment verification failed: '.($res['statusMessage'] ?? 'Unknown error'));

            } catch (\Exception $e) {
                Log::error('bKash callback execution exception: '.$e->getMessage());

                return redirect()->route('filament.portal.pages.pay-bill')
                    ->with('error', 'Verification exception: '.$e->getMessage());
            }
        }

        return redirect()->route('filament.portal.pages.pay-bill')
            ->with('error', 'Payment process cancelled or failed. Status: '.$status);
    }

    public function mockSubmit(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers_infos,id',
            'amount' => 'required|numeric|min:1',
            'gateway' => 'required',
        ]);

        $customer = CustomersInfo::find($request->customer_id);
        $amount = (float) $request->amount;
        $gateway = strtolower($request->gateway);
        $trxID = strtoupper($gateway).'_MOCK_'.strtoupper(uniqid());

        try {
            $this->paymentService->processSuccessPayment($customer, $amount, $gateway, $trxID);

            return redirect()->route('filament.portal.pages.dashboard')
                ->with('success', 'Payment of BDT '.$amount.' simulated successfully via '.strtoupper($gateway).'. Your account is active.');
        } catch (\Exception $e) {
            return redirect()->route('filament.portal.pages.pay-bill')
                ->with('error', 'Failed to process simulated payment: '.$e->getMessage());
        }
    }

    private function showMockPaymentPage($gateway, $customer, $amount, $reason = null)
    {
        return response()->view('payment.mock_checkout', [
            'gateway' => $gateway,
            'customer' => $customer,
            'amount' => $amount,
            'reason' => $reason,
        ]);
    }
}
