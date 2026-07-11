<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLogs extends Model
{
    protected $fillable = [
        'title',
        'message',
        'status',
        'type',
        'read_by',
        'read_at',
    ];
}
