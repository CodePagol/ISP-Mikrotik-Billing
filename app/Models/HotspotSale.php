<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotspotSale extends Model
{
    protected $fillable = [
        'router_name', 'voucher_code', 'profile', 'username',
        'amount', 'payment_method', 'note', 'sale_date', 'sold_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'sale_date' => 'date',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sold_by');
    }

    public function scopeForRouter($query, string $router)
    {
        return $query->where('router_name', $router);
    }

    public function scopeForPeriod($query, string $from, string $to)
    {
        return $query->whereBetween('sale_date', [$from, $to]);
    }
}
