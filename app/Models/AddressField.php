<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddressField extends Model
{
    use HasFactory;

    protected $fillable = ['label', 'input_type', 'dropdown_list', 'required', 'print_preview', 'complain_preview', 'order'];
}
