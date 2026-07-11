<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NagadPaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    private function getNagadConfig()
    {
        return [
            'base_url' => siteUrlSettings('payment_nagad_base_url') ?: config('services.nagad.base_url') ?: 'http://sandbox.nagad.com.bd:10080/remote-payment-gateway-1.0/api/dfs',
            'merchant_id' => siteUrlSettings('payment_nagad_merchant_id') ?: config('services.nagad.merchant_id') ?: '683002007104225',
            'public_key' => siteUrlSettings('payment_nagad_public_key'),
            'private_key' => siteUrlSettings('payment_nagad_private_key'),
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
                'gateway' => 'Nagad',
                'customer' => $customer,
                'amount' => $amount,
                'reason' => null,
            ]);
        }

        $config = $this->getNagadConfig();

        // Check if Nagad private/public keys are defined. If not, fallback to Simulator/Mock for sandbox testing.
        if (empty($config['private_key']) || empty($config['public_key'])) {
            if ($isLocal) {
                return response()->view('payment.mock_checkout', [
                    'gateway' => 'Nagad',
                    'customer' => $customer,
                    'amount' => $amount,
                    'reason' => 'Nagad public/private key pair is not configured in settings. Simulating sandbox payment.',
                ]);
            }

            return redirect()->route('filament.portal.pages.pay-bill')
                ->with('error', 'Nagad is not properly configured (keys are missing).');
        }

        try {
            $merchantId = $config['merchant_id'];
            $orderId = 'NGD_'.uniqid();
            $dateTime = now()->format('YmdHis');

            // 1. Initial Step: Post to payment-initializer (plaintext parameters)
            $postData = [
                'accountNumber' => $customer->mobile ?: '01700000000',
                'datetime' => $dateTime,
                'orderId' => $orderId,
                'merchantId' => $merchantId,
            ];

            $response = Http::withHeaders([
                'Client-Type' => 'MERCHANT',
                'X-KM-Api-Version' => 'v1.0.0',
                'X-KM-IP-Address' => request()->ip() ?: '127.0.0.1',
            ])->post($config['base_url']."/payment-initializer/merchantId/{$merchantId}/{$orderId}", $postData);

            $res = $response->json();

            if (isset($res['sensitiveData']) && isset($res['signature'])) {
                // Decrypt the sensitiveData to retrieve paymentReferenceId and challenge
                $decryptedData = $this->decrypt($res['sensitiveData'], $config['private_key']);
                if (!$decryptedData) {
                    throw new \Exception('Failed to decrypt payment initialization sensitiveData.');
                }

                $decryptedJson = json_decode($decryptedData, true);
                $paymentRefId = $decryptedJson['paymentReferenceId'] ?? null;
                $challenge = $decryptedJson['challenge'] ?? null;

                if (!$paymentRefId || !$challenge) {
                    throw new \Exception('paymentReferenceId or challenge missing in decrypted initialization data.');
                }

                // 2. Complete Step: Prepare sensitive payload for quick-pay-initializer
                $sensitivePayload = [
                    'merchantId' => $merchantId,
                    'orderId' => $orderId,
                    'amount' => (string) round($amount, 2),
                    'currencyCode' => '050',
                    'challenge' => $challenge,
                    'paymentRefId' => $paymentRefId,
                    'productDetails' => 'Internet Bill',
                    'clientMobileNo' => $customer->mobile ?: '01700000000',
                    'ip' => request()->ip() ?: '127.0.0.1',
                ];

                $sensitiveJson = json_encode($sensitivePayload);
                
                // Encrypt payload with Nagad's Public Key
                $encryptedPayload = $this->encrypt($sensitiveJson, $config['public_key']);
                
                // Sign plaintext JSON with Merchant's Private Key
                $signature = $this->sign($sensitiveJson, $config['private_key']);

                if (!$encryptedPayload || !$signature) {
                    throw new \Exception('Failed to encrypt or sign sensitive complete payload.');
                }

                $completePayload = [
                    'sensitiveData' => $encryptedPayload,
                    'signature' => $signature,
                ];

                // POST to quick-pay-initializer to generate redirect checkout URL
                $completeResponse = Http::withHeaders([
                    'Client-Type' => 'MERCHANT',
                    'X-KM-Api-Version' => 'v1.0.0',
                    'X-KM-IP-Address' => request()->ip() ?: '127.0.0.1',
                ])->post($config['base_url']."/quick-pay-initializer/merchantId/{$merchantId}/{$orderId}", $completePayload);

                $completeRes = $completeResponse->json();

                if (isset($completeRes['sensitiveData'])) {
                    // Decrypt the complete step response to get the callBackUrl
                    $decryptedComplete = $this->decrypt($completeRes['sensitiveData'], $config['private_key']);
                    $decryptedCompleteJson = json_decode($decryptedComplete, true);

                    if (isset($decryptedCompleteJson['callBackUrl'])) {
                        return redirect()->away($decryptedCompleteJson['callBackUrl']);
                    }
                    
                    throw new \Exception('callBackUrl not found in decrypted complete response: ' . $decryptedComplete);
                }

                $errorMessage = $completeRes['message'] ?? 'Failed to initialize quick pay session.';
                throw new \Exception($errorMessage);
            }

            Log::error('Nagad session initiation failed: '.json_encode($res));

            if ($isLocal) {
                return response()->view('payment.mock_checkout', [
                    'gateway' => 'Nagad',
                    'customer' => $customer,
                    'amount' => $amount,
                    'reason' => 'Nagad API error: '.($res['message'] ?? 'Failed to initialize session.'),
                ]);
            }

            return redirect()->route('filament.portal.pages.pay-bill')
                ->with('error', 'Nagad session creation failed: '.($res['message'] ?? 'Unknown error'));

        } catch (\Exception $e) {
            Log::error('Nagad initiate exception: '.$e->getMessage());

            if ($isLocal) {
                return response()->view('payment.mock_checkout', [
                    'gateway' => 'Nagad',
                    'customer' => $customer,
                    'amount' => $amount,
                    'reason' => 'Connection failed: '.$e->getMessage(),
                ]);
            }

            return redirect()->route('filament.portal.pages.pay-bill')
                ->with('error', 'Nagad service currently unavailable: '.$e->getMessage());
        }
    }

    public function callback(Request $request)
    {
        // Nagad callback parameters
        $status = $request->query('status');
        $orderId = $request->query('order_id');
        $paymentRefId = $request->query('payment_ref_id');

        if ($status === 'success') {
            try {
                $config = $this->getNagadConfig();
                $verify = Http::withHeaders([
                    'Client-Type' => 'MERCHANT',
                    'X-KM-Api-Version' => 'v1.0.0',
                    'X-KM-IP-Address' => request()->ip() ?: '127.0.0.1',
                ])->get($config['base_url']."/payment/status/{$paymentRefId}");
                
                $res = $verify->json();

                if (isset($res['sensitiveData'])) {
                    $decryptedVerify = $this->decrypt($res['sensitiveData'], $config['private_key']);
                    if ($decryptedVerify) {
                        $decryptedVerifyJson = json_decode($decryptedVerify, true);

                        if (isset($decryptedVerifyJson['status']) && $decryptedVerifyJson['status'] === 'Success') {
                            $amount = (float) $decryptedVerifyJson['amount'];
                            $customer = auth()->user() ? auth()->user()->customer : null;

                            if ($customer) {
                                $this->paymentService->processSuccessPayment($customer, $amount, 'nagad', $paymentRefId);

                                return redirect()->route('filament.portal.pages.dashboard')
                                    ->with('success', 'Payment of BDT '.$amount.' received successfully via Nagad. Your account is active.');
                            }
                        }
                    }
                }

                Log::error('Nagad validation failed: '.json_encode($res));

                return redirect()->route('filament.portal.pages.pay-bill')
                    ->with('error', 'Nagad verification failed.');

            } catch (\Exception $e) {
                Log::error('Nagad callback verification exception: '.$e->getMessage());

                return redirect()->route('filament.portal.pages.pay-bill')
                    ->with('error', 'Verification exception: '.$e->getMessage());
            }
        }

        return redirect()->route('filament.portal.pages.pay-bill')
            ->with('error', 'Payment failed or cancelled. Status: '.$status);
    }

    private function cleanPrivateKey($key)
    {
        $key = trim($key);
        if (str_contains($key, '-----BEGIN PRIVATE KEY-----') || str_contains($key, '-----BEGIN RSA PRIVATE KEY-----')) {
            return $key;
        }
        return "-----BEGIN PRIVATE KEY-----\n" . chunk_split($key, 64, "\n") . "-----END PRIVATE KEY-----";
    }

    private function cleanPublicKey($key)
    {
        $key = trim($key);
        if (str_contains($key, '-----BEGIN PUBLIC KEY-----')) {
            return $key;
        }
        return "-----BEGIN PUBLIC KEY-----\n" . chunk_split($key, 64, "\n") . "-----END PUBLIC KEY-----";
    }

    private function encrypt($data, $publicKey)
    {
        $publicKey = $this->cleanPublicKey($publicKey);
        $encrypted = '';
        if (openssl_public_encrypt($data, $encrypted, $publicKey)) {
            return base64_encode($encrypted);
        }
        Log::error('Nagad encryption failed: ' . openssl_error_string());
        return null;
    }

    private function decrypt($data, $privateKey)
    {
        $privateKey = $this->cleanPrivateKey($privateKey);
        $decrypted = '';
        if (openssl_private_decrypt(base64_decode($data), $decrypted, $privateKey)) {
            return $decrypted;
        }
        Log::error('Nagad decryption failed: ' . openssl_error_string());
        return null;
    }

    private function sign($data, $privateKey)
    {
        $privateKey = $this->cleanPrivateKey($privateKey);
        $signature = '';
        if (openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            return base64_encode($signature);
        }
        Log::error('Nagad signing failed: ' . openssl_error_string());
        return null;
    }
}
