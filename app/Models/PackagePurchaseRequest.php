<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackagePurchaseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'package_name',
        'price',
        'status',
        'ip_address',
        'notes',
    ];

    public function getPreviousRequests()
    {
        return self::where('id', '<', $this->id)
            ->where(function($q) {
                $q->where('phone', $this->phone);
                if ($this->email) {
                    $q->orWhere('email', $this->email);
                }
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
