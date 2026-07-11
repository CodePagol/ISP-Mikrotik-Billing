<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerReview extends Model
{
    protected $fillable = [
        'ppp_user_id',
        'rating',
        'comment',
        'edit_count',
        'show_on_site',
    ];

    public function pppUser()
    {
        return $this->belongsTo(PPPSecrets::class, 'ppp_user_id', 'id');
    }
}
