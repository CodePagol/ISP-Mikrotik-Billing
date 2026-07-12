<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\CustomersInfo;
use App\Models\Voucher;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PortalVoucherController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Show the public voucher recharge form.
     */
    public function showRechargeForm()
    {
        return view('portal.voucher_redeem');
    }

    /**
     * Redeem a voucher to recharge the customer's account.
     */
    public function redeem(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:100',
            'code' => 'required|string|max:50',
        ]);

        // Find customer by unique ID or PPPoE username
        $customer = CustomersInfo::where('customer_unique_id', $request->username)
            ->orWhereHas('pppUser', function ($q) use ($request) {
                $q->where('username', $request->username);
            })
            ->first();

        if (!$customer) {
            return back()->withInput()->withErrors(['username' => 'Customer or PPPoE username not found.']);
        }

        // Find voucher
        $voucher = Voucher::where('code', $request->code)->first();

        if (!$voucher) {
            return back()->withInput()->withErrors(['code' => 'Invalid voucher code.']);
        }

        if (!$voucher->isUnused()) {
            return back()->withInput()->withErrors(['code' => 'This voucher has already been used.']);
        }

        if ($voucher->isExpired()) {
            return back()->withInput()->withErrors(['code' => 'This voucher has expired.']);
        }

        DB::beginTransaction();
        try {
            // If it's a package-based voucher, update customer package, billing, and PPP profile first
            if ($voucher->type === 'package_based' && $voucher->package_id) {
                $package = $voucher->package;
                if ($package) {
                    $customer->package_id = $package->id;
                    $customer->save();

                    if ($customer->billing) {
                        $customer->billing->monthly_rent = (float) $package->price;
                        $customer->billing->total_amount = (float) $package->price 
                            + (float)($customer->billing->additional_charge ?? 0) 
                            + (float)($customer->billing->vat ?? 0) 
                            - (float)($customer->billing->discount ?? 0);
                        $customer->billing->due_amount = $customer->billing->total_amount - (float)($customer->billing->paid_amount ?? 0);
                        $customer->billing->save();
                    }

                    if ($customer->pppUser) {
                        $customer->pppUser->profile = $package->package;
                        $customer->pppUser->save();
                    }
                }
            }

            // Mark voucher as used
            $voucher->update([
                'status' => 'used',
                'used_by_customer_id' => $customer->id,
                'used_at' => now(),
            ]);

            // Recharge customer using PaymentService
            $success = $this->paymentService->processSuccessPayment(
                $customer,
                (float) $voucher->value,
                'voucher',
                $voucher->code
            );

            if ($success) {
                DB::commit();
                
                // Show success page or message
                return redirect()->route('portal.voucher.recharge')
                    ->with('success', "Account successfully recharged with BDT {$voucher->value}! Your service is now active.");
            } else {
                throw new \Exception("Payment processing failed.");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Voucher redemption failed: " . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Recharge failed: ' . $e->getMessage()]);
        }
    }
}
