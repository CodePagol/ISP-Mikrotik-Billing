<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotspotVoucher extends Model
{
    protected $fillable = [
        'router_name', 'code', 'profile', 'username', 'password',
        'price', 'batch_name', 'status', 'used_by', 'mac_address',
        'used_at', 'expires_at', 'comment', 'created_by',
        'validity_type', 'validity_duration', 'first_login_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
        'first_login_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeUnused($query)
    {
        return $query->where('status', 'unused');
    }

    public function scopeForRouter($query, string $router)
    {
        return $query->where('router_name', $router);
    }

    /**
     * Check if this is a realtime voucher and has expired.
     */
    public function isRealtimeExpired(): bool
    {
        if ($this->validity_type !== 'realtime' || ! $this->first_login_at || ! $this->validity_duration) {
            return false;
        }

        $expiresAt = $this->getRealtimeExpiresAt();

        return $expiresAt && $expiresAt->isPast();
    }

    /**
     * Get the absolute expiry timestamp for a realtime voucher.
     */
    public function getRealtimeExpiresAt(): ?Carbon
    {
        if (! $this->first_login_at || ! $this->validity_duration) {
            return null;
        }

        return $this->first_login_at->copy()->addSeconds(
            self::parseMikrotikDuration($this->validity_duration)
        );
    }

    /**
     * Parse MikroTik duration string (e.g. "1h", "2d", "30m", "1d12h") to seconds.
     */
    public static function parseMikrotikDuration(string $duration): int
    {
        $seconds = 0;
        // Match patterns like: 7d, 12h, 30m, 45s or combinations like 1d12h30m
        if (preg_match_all('/(\d+)\s*(w|d|h|m|s)/i', $duration, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $val = (int) $m[1];
                switch (strtolower($m[2])) {
                    case 'w': $seconds += $val * 604800; break; // weeks
                    case 'd': $seconds += $val * 86400; break;
                    case 'h': $seconds += $val * 3600; break;
                    case 'm': $seconds += $val * 60; break;
                    case 's': $seconds += $val; break;
                }
            }
        }

        return $seconds;
    }

    /**
     * Scope: realtime vouchers that have expired (first_login_at + duration < now).
     */
    public function scopeRealtimeExpired($query)
    {
        return $query->where('validity_type', 'realtime')
            ->whereNotNull('first_login_at')
            ->whereNotNull('validity_duration')
            ->where('status', '!=', 'expired');
    }
}
