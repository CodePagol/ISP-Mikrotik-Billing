<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermittedUrl extends Model
{
    protected $fillable = ['router_id', 'url_or_ip', 'type', 'comment'];

    public function router()
    {
        return $this->belongsTo(RouterList::class, 'router_id');
    }
}
