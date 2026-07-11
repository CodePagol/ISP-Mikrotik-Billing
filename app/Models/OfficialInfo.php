<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficialInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_office_unique_id',
        'service_charge',
        'security_deposit',
        'customer_type',
        'connection_type',
        'connectivity_type',
        'distribution_location',
        'client_type',
        'billing_type',
        'bill_create',
        'bill_print',
        'bill_email',
        'bill_sms',
        'bill_fax',
        'continue_bill',
        'description',
        'note',
        'connected_by',
        // 'status',
    ];
}
