<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $fillable = [
        'ticket_no',
        'customer_unique_id',
        'ppp_username',
        'subject',
        'description',
        'priority',
        'status',
        'category',
        'admin_reply',
        'replied_at',
        'replied_by',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(CustomersInfo::class, 'customer_unique_id', 'customer_unique_id');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'open' => 'amber',
            'in_progress' => 'blue',
            'resolved' => 'emerald',
            'closed' => 'gray',
            default => 'gray',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'high' => 'rose',
            'medium' => 'amber',
            'low' => 'emerald',
            default => 'gray',
        };
    }

    /**
     * Generate a unique ticket number.
     */
    public static function generateTicketNo(): string
    {
        do {
            $no = 'TKT-'.strtoupper(substr(uniqid(), -6));
        } while (static::where('ticket_no', $no)->exists());

        return $no;
    }
}
