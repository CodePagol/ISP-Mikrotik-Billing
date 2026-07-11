<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomersAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_address_unique_id',
        'label_name',
        'input_type_text',
        'input_type_dropdown',
        'input_type_textarea',
        'latitude',
        'longitude',
    ];

    // public function addressLabel(){
    //     return $this->belongsTo(AddressField::class, 'label');
    // }
}
