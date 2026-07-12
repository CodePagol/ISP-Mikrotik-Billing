<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentList extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'comment',
        'ip_address',
    ];
}
