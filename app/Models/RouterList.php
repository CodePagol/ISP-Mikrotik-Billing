<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouterList extends Model
{
    use HasFactory;

    protected $fillable = ['router_name', 'ip_address', 'username', 'password', 'action', 'ssh_port', 'api_port'];

    /**
     * Helper to encrypt value if it's plaintext.
     */
    private function encryptValue($value)
    {
        if ($value === null || $value === '') {
            return $value;
        }

        try {
            decrypt($value);
            // Already encrypted
            return $value;
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // Plaintext
            return encrypt($value);
        }
    }

    /**
     * Helper to decrypt value if it's encrypted.
     */
    private function decryptValue($value)
    {
        if ($value === null || $value === '') {
            return $value;
        }

        try {
            return decrypt($value);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // Legacy plaintext
            return $value;
        }
    }

    // --- Getters ---

    public function getIpAddressAttribute($value)
    {
        return $this->decryptValue($value);
    }

    public function getUsernameAttribute($value)
    {
        return $this->decryptValue($value);
    }

    public function getPasswordAttribute($value)
    {
        return $this->decryptValue($value);
    }

    public function getSshPortAttribute($value)
    {
        $decrypted = $this->decryptValue($value);
        return ($decrypted !== null) ? (int)$decrypted : null;
    }

    public function getApiPortAttribute($value)
    {
        $decrypted = $this->decryptValue($value);
        return ($decrypted !== null) ? (int)$decrypted : null;
    }

    // --- Setters ---

    public function setIpAddressAttribute($value)
    {
        $this->attributes['ip_address'] = $this->encryptValue($value);
    }

    public function setUsernameAttribute($value)
    {
        $this->attributes['username'] = $this->encryptValue($value);
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = $this->encryptValue($value);
    }

    public function setSshPortAttribute($value)
    {
        $value = $value === '' ? null : $value;
        $this->attributes['ssh_port'] = ($value !== null) ? $this->encryptValue($value) : null;
    }

    public function setApiPortAttribute($value)
    {
        $value = $value === '' ? null : $value;
        $this->attributes['api_port'] = ($value !== null) ? $this->encryptValue($value) : null;
    }
}
