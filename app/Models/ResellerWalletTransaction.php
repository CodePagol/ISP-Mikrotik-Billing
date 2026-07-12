<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResellerWalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reseller_id',
        'type',
        'amount',
        'description',
        'reference_type',
        'reference_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the reseller that owns the transaction.
     */
    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }
}
