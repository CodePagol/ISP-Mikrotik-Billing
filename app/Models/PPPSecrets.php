<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class PPPSecrets extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasFactory;

    protected $fillable = [
        'router_name',
        'username',
        'password',
        'service',
        'profile',
        'caller_id',
        'comment',
        'ppp_remote_ip',
        'bandwidth',
        'uptime',
        'downtime',
        'last_logged_out',
        'last_caller_id',
        'last_disconnect_reason',
        'routes',
        'ipv6_routes',
        'status',
        'package_name',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function customer()
    {
        return $this->hasOne(CustomersInfo::class, 'ppp_user_id', 'id');
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Determine if the user can access the Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true; // You can add logic here to restrict access if needed
    }

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    /**
     * Get the unique identifier for the user.
     */
    public function getAuthIdentifier()
    {
        return $this->id;
    }

    /**
     * Get the user's name for Filament.
     */
    public function getNameAttribute()
    {
        return $this->username;
    }

    /**
     * Get the decrypted password attribute.
     *
     * @param  string|null  $value
     * @return string|null
     */
    public function getPasswordAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        try {
            return decrypt($value);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // Fallback for plaintext or legacy bcrypt hashes
            return $value;
        }
    }

    /**
     * Set the encrypted password attribute.
     *
     * @param  string|null  $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['password'] = $value;
            return;
        }

        // If it looks like a bcrypt hash (standard Laravel hashing) or already encrypted, save it directly
        if (str_starts_with($value, '$2y$') || str_starts_with($value, '$2a$')) {
            $this->attributes['password'] = $value;
            return;
        }

        try {
            decrypt($value);
            // It's already encrypted
            $this->attributes['password'] = $value;
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // Plaintext - encrypt it
            $this->attributes['password'] = encrypt($value);
        }
    }

    /**
     * Get the user's avatar URL for Filament.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        $customer = $this->customer;
        if ($customer && $customer->photo_url) {
            return asset($customer->photo_url);
        }

        return null;
    }
}

