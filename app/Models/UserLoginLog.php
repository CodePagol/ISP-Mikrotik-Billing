<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLoginLog extends Model
{
    protected $fillable = [
        'authenticatable_id',
        'authenticatable_type',
        'username',
        'ip_address',
        'user_agent',
        'action',
    ];

    public function authenticatable()
    {
        return $this->morphTo();
    }
}
