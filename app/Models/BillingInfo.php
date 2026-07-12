<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class BillingInfo extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'customer_bill_unique_id',
        'monthly_rent',
        'additional_charge',
        'discount',
        'advance',
        'vat',
        'auto_disable',
        'auto_disable_date',
        'auto_disable_month',
        'extra_date',
        'billing_type',
        'paid_amount',
        'paid_date',
        'previous_due',
        'due_amount',
        'total_due_amount',
        'total_amount',
    ];

    // In Billing model
    public function scopeAutoDisable($query)
    {
        return $query->where('auto_disable', true);
    }

    public function scopeAutoDisableDate($query, $date)
    {
        return $query->where('auto_disable_date', '<=', $date->copy()->endOfDay());
    }

    public function scopeUnpaid($query)
    {
        return $query->where('paid_amount', 0.00);
    }

    public function scopePaid($query)
    {
        return $query->where('paid_amount', '>', 0.00);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('system');
    }

    protected static function booted()
    {
        // static::created(function ($billingInfo) {
        //     \Log::info('BillingInfo created: ' . $billingInfo->customer_bill_unique_id);
        //     // other code...
        // });

        // static::updated(function ($billingInfo) {
        //     \Log::info('BillingInfo updated: ' . $billingInfo->customer_bill_unique_id);
        //     // other code...
        // });

        // static::created(function ($billingInfo) {
        //     PaymentSummary::create([
        //         'customer_payment_unique_id' => $billingInfo->customer_bill_unique_id,
        //         'ppp_username' => CustomersInfo::where('customer_unique_id', $billingInfo->customer_bill_unique_id)->first()->ppp_username,
        //         'monthly_rent' => $billingInfo->monthly_rent,
        //         'additional_charge' => $billingInfo->additional_charge,
        //         'discount' => $billingInfo->discount,
        //         'advance' => $billingInfo->advance,
        //         'vat' => $billingInfo->vat,
        //         'previous_due' => $billingInfo->previous_due,
        //         'due_amount' => $billingInfo->due_amount,
        //         'total_due_amount' => $billingInfo->total_due_amount,
        //         'paid_amount' => $billingInfo->paid_amount,
        //         'total_amount' => $billingInfo->total_amount,
        //         'payment_date' => $billingInfo->paid_date,
        //         'collected_by' => strtok(auth()->user()->email, '@'),
        //     ]);
        // });
        // static::updated(function ($billingInfo) {
        //     // Check if the 'paid_amount' attribute has changed
        //     if ($billingInfo->wasChanged('paid_amount')) {
        //         PaymentSummary::create([
        //             'customer_payment_unique_id' => $billingInfo->customer_bill_unique_id,
        //             'ppp_username' => CustomersInfo::where('customer_unique_id', $billingInfo->customer_bill_unique_id)->first()->ppp_username,
        //             'monthly_rent' => $billingInfo->monthly_rent,
        //             'additional_charge' => $billingInfo->additional_charge,
        //             'discount' => $billingInfo->discount,
        //             'advance' => $billingInfo->advance,
        //             'vat' => $billingInfo->vat,
        //             'previous_due' => $billingInfo->previous_due,
        //             'due_amount' => $billingInfo->due_amount,
        //             'total_due_amount' => $billingInfo->total_due_amount,
        //             'paid_amount' => $billingInfo->paid_amount,
        //             'total_amount' => $billingInfo->total_amount,
        //             'payment_date' => $billingInfo->paid_date,
        //             'collected_by' => strtok(auth()->user()->email, '@'),
        //         ]);
        //     }
        // });

        // static::updated(function ($billingInfo) {
        //     Log::create([
        //         'table_name' => 'BillingInfo',
        //         'action' => 'update',
        //         'record_id' => $billingInfo->customer_bill_unique_id,
        //         'old_data' => json_encode($billingInfo->getOriginal()),
        //         'new_data' => json_encode($billingInfo->getChanges()),
        //         'user_id' => auth()->id(),
        //     ]);
        // });
    }
}
