<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MikrotikLog extends Model
{
    protected $fillable = [
        'router_name',
        'log_id',
        'time',
        'topics',
        'message',
        'buffer',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Return a Tailwind/Filament color for a topic string.
     */
    public static function topicColor(string $topics): string
    {
        $t = strtolower($topics);
        if (str_contains($t, 'error') || str_contains($t, 'critical')) {
            return 'danger';
        }
        if (str_contains($t, 'warning')) {
            return 'warning';
        }
        if (str_contains($t, 'firewall')) {
            return 'warning';
        }
        if (str_contains($t, 'info')) {
            return 'info';
        }
        if (str_contains($t, 'account') || str_contains($t, 'ppp')) {
            return 'success';
        }

        return 'gray';
    }

    public function scopeForRouter(Builder $query, string $router): Builder
    {
        return $query->where('router_name', $router);
    }

    public function scopeWithTopic(Builder $query, string $topic): Builder
    {
        return $query->where('topics', 'like', "%{$topic}%");
    }

    public function scopeWithBuffer(Builder $query, string $buffer): Builder
    {
        return $query->where('buffer', $buffer);
    }
}
