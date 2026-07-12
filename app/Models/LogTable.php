<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogTable extends Model
{
    // use HasFactory;

    protected $fillable = [
        'table_name', 'action', 'record_id', 'old_data', 'new_data', 'user_id',
    ];
}
