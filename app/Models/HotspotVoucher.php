<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotspotVoucher extends Model
{
    protected $fillable = [
        'router_name', 'code', 'profile', 'username', 'password',
        'price', 'batch_name', 'status', 'used_by', 'mac_address',
        'used_at', 'expires_at', 'comment', 'created_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeUnused($query)
    {
        return $query->where('status', 'unused');
    }

    public function scopeForRouter($query, string $router)
    {
        return $query->where('router_name', $router);
    }
}
