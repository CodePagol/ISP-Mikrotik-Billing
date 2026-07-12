<?php

namespace App\Livewire\Reseller;

use App\Models\PackageList;
use App\Models\Voucher;
use App\Services\ResellerWalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class ResellerVoucherManagement extends Component
{
    use WithPagination;

    public $count = 5;

    public $type = 'fixed_amount';

    public $value;

    public $package_id;

    public $expiry_date;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->expiry_date = now()->addMonths(3)->format('Y-m-d');
        $reseller = auth()->user()->reseller;
        if ($reseller && !$reseller->getSetting('allow_fixed_voucher', true)) {
            $this->type = 'package_based';
        }
    }

    public function rules()
    {
        $reseller = auth()->user()->reseller;
        $allowFixedAmount = $reseller ? (bool)$reseller->getSetting('allow_fixed_voucher', true) : true;
        $typeRule = $allowFixedAmount ? 'required|in:fixed_amount,package_based' : 'required|in:package_based';

        return [
            'count' => 'required|integer|min:1|max:100',
            'type' => $typeRule,
            'value' => 'required_if:type,fixed_amount|nullable|numeric|min:1',
            'package_id' => 'required_if:type,package_based|nullable|exists:package_lists,id',
            'expiry_date' => 'required|date|after:today',
        ];
    }

    public function generate()
    {
        $this->validate();

        $reseller = auth()->user()->reseller;
        if (! $reseller) {
            flash()->error('Unauthorized.');

            return;
        }

        $allowFixedAmount = (bool)$reseller->getSetting('allow_fixed_voucher', true);
        if ($this->type === 'fixed_amount' && !$allowFixedAmount) {
            flash()->error('Fixed amount vouchers are disabled for your account.');

            return;
        }

        $voucherValue = 0.00;
        $packageId = null;

        if ($this->type === 'package_based') {
            $package = PackageList::findOrFail($this->package_id);

            // Verify package belongs to reseller
            $isAssigned = $reseller->assignedPackages->contains($package->id);
            $isCustom = $package->reseller_id === $reseller->id;
            if (! $isAssigned && ! $isCustom) {
                $this->addError('package_id', 'Selected package is not assigned to you.');

                return;
            }

            $voucherValue = $package->price;
            $packageId = $package->id;
        } else {
            $voucherValue = (float) $this->value;
        }

        $payDiscountedUpfront = (bool)$reseller->getSetting('pay_discounted_upfront', false);
        $commissionPercentage = (float)$reseller->commission_percentage;

        if ($payDiscountedUpfront) {
            $costPerVoucher = $voucherValue * (1 - $commissionPercentage / 100);
        } else {
            $costPerVoucher = $voucherValue;
        }

        $totalCost = (float)$this->count * $costPerVoucher;
        if ($reseller->balance < $totalCost) {
            flash()->error('Insufficient wallet balance. You need BDT '.number_format($totalCost, 2).' but only have BDT '.number_format($reseller->balance, 2));

            return;
        }

        DB::beginTransaction();
        try {
            $walletService = app(ResellerWalletService::class);
            $walletService->debit(
                $reseller,
                $totalCost,
                "Generated {$this->count} vouchers valued at BDT ".number_format($voucherValue, 2).' each.',
                'voucher_generation'
            );

            $generatedCount = 0;

            for ($i = 0; $i < $this->count; $i++) {
                do {
                    $code = 'VCH-'.strtoupper(Str::random(6)).'-'.strtoupper(Str::random(6));
                } while (Voucher::where('code', $code)->exists());

                Voucher::create([
                    'code' => $code,
                    'value' => $voucherValue,
                    'type' => $this->type,
                    'package_id' => $packageId,
                    'status' => 'unused',
                    'expiry_date' => $this->expiry_date,
                    'reseller_id' => $reseller->id,
                    'commission_paid_upfront' => $payDiscountedUpfront,
                ]);

                $generatedCount++;
            }

            DB::commit();

            activity()
                ->performedOn($reseller)
                ->causedBy(auth()->user())
                ->log("Generated {$generatedCount} vouchers valued at BDT ".number_format($voucherValue, 2).' each. Total cost BDT '.number_format($totalCost, 2).' debited from wallet.');

            flash()->success("Successfully generated {$generatedCount} vouchers. BDT ".number_format($totalCost, 2).' was debited from your wallet.');
        } catch (\Exception $e) {
            DB::rollBack();
            flash()->error('Failed to generate vouchers: '.$e->getMessage());

            return;
        }

        $this->count = 5;
        $this->value = null;
        $this->package_id = null;
        $this->expiry_date = now()->addMonths(3)->format('Y-m-d');

        $this->resetPage();
    }

    public function cancelVoucher(int $id)
    {
        $reseller = auth()->user()->reseller;
        if (! $reseller) {
            flash()->error('Unauthorized.');

            return;
        }

        $voucher = Voucher::where('id', $id)
            ->where('reseller_id', $reseller->id)
            ->firstOrFail();

        if ($voucher->status !== 'unused') {
            flash()->error('Only unused vouchers can be cancelled.');

            return;
        }

        if ($voucher->isExpired()) {
            flash()->error('Expired vouchers cannot be cancelled.');

            return;
        }

        DB::beginTransaction();
        try {
            $voucher->update([
                'status' => 'cancelled',
            ]);

            $walletService = app(ResellerWalletService::class);
            $walletService->credit(
                $reseller,
                (float) $voucher->value,
                'Refunded cancelled voucher: '.$voucher->code,
                'voucher_cancellation',
                $voucher->id
            );

            DB::commit();

            activity()
                ->performedOn($voucher)
                ->causedBy(auth()->user())
                ->log("Cancelled reseller voucher {$voucher->code} valued at BDT ".number_format($voucher->value, 2).'. BDT '.number_format($voucher->value, 2).' refunded to wallet.');

            flash()->success("Successfully cancelled voucher {$voucher->code}. BDT ".number_format($voucher->value, 2).' has been refunded to your wallet.');
        } catch (\Exception $e) {
            DB::rollBack();
            flash()->error('Failed to cancel voucher: '.$e->getMessage());
        }
    }

    public function render()
    {
        $reseller = auth()->user()->reseller;
        if (! $reseller) {
            abort(403);
        }

        $vouchers = Voucher::where('reseller_id', $reseller->id)
            ->with(['package', 'usedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $packages = PackageList::where(function ($q) use ($reseller) {
            $q->whereIn('id', $reseller->assignedPackages->pluck('id'))
                ->orWhere('reseller_id', $reseller->id);
        })
            ->get();

        return view('livewire.reseller.voucher-management', [
            'vouchers' => $vouchers,
            'packages' => $packages,
        ])->layout('layouts.app');
    }
}
