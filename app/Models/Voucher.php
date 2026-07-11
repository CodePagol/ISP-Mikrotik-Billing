<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'value',
        'type',
        'package_id',
        'status',
        'expiry_date',
        'reseller_id',
        'commission_paid_upfront',
        'used_by_customer_id',
        'used_at',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'expiry_date' => 'date',
        'used_at' => 'datetime',
        'commission_paid_upfront' => 'boolean',
    ];

    /**
     * Get the reseller that generated the voucher.
     */
    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }

    /**
     * Get the package associated with the voucher (if package_based).
     */
    public function package()
    {
        return $this->belongsTo(PackageList::class, 'package_id');
    }

    /**
     * Get the customer that redeemed this voucher.
     */
    public function usedBy()
    {
        return $this->belongsTo(CustomersInfo::class, 'used_by_customer_id');
    }

    /**
     * Check if the voucher has expired.
     */
    public function isExpired(): bool
    {
        return $this->expiry_date->isPast();
    }

    /**
     * Check if the voucher is unused.
     */
    public function isUnused(): bool
    {
        return $this->status === 'unused';
    }
}
