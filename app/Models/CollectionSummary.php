<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CollectionSummary extends Model
{
    use LogsActivity;

    protected static $recordEvents = ['deleted', 'updated'];

    protected $fillable = [
        'customer_collection_unique_id',
        'collection_date',
        'collection_amount',
        'collected_by',
        'payment_type',
        'payment_method',
        'transaction_id',
        'payment_status',
        'bill_month',
        'invoice_no',
    ];

    public function customer()
    {
        return $this->belongsTo(CustomersInfo::class, 'customer_collection_unique_id', 'customer_unique_id');
    }

    public function pppUser()
    {
        return $this->customer()->pppUser();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['customer_collection_unique_id', 'collection_date', 'collection_amount', 'collected_by', 'payment_type', 'payment_method', 'transaction_id', 'payment_status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Override the log name dynamically.
     */
    public function getLogNameToUse(string $eventName = ''): string
    {
        return "{$this->customer_collection_unique_id} - {$this->collection_date}";
    }
}
