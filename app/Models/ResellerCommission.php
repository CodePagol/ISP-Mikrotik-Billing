<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResellerCommission extends Model
{
    use HasFactory;

    // Commission table does not have updated_at by default in our migration
    public $timestamps = false;

    protected $fillable = [
        'reseller_id',
        'customer_id',
        'package_id',
        'amount',
        'commission_percentage',
        'created_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    /**
     * Get the reseller that received the commission.
     */
    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }

    /**
     * Get the customer that made the purchase.
     */
    public function customer()
    {
        return $this->belongsTo(CustomersInfo::class, 'customer_id');
    }

    /**
     * Get the package that generated the commission.
     */
    public function package()
    {
        return $this->belongsTo(PackageList::class, 'package_id');
    }
}
