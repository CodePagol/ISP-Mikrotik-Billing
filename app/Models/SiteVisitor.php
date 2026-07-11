<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteVisitor extends Model
{
    use HasFactory;

    protected $fillable = ['ip_address', 'visited_date'];
}
