<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomersInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_unique_id',
        'customer_name',
        'contact_person',
        'parents_name',
        'spouse_name',
        'address',
        'email',
        'mobile',
        'alternative_mobile',
        'identification_no',
        'profession',
        'photo_url',
        'disable_count',
        'ppp_user_id',
        'connection_date',
        'package_id',
        'status',
        'reseller_id',
        // 'auto_disabled',
        // 'expired_date',
        // 'auto_disabled_month',
        // 'extra_date',
    ];

    // In CustomersInfo model
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeUnderDisableLimit($query, $limit)
    {
        return $query->where('disable_count', '<', $limit);
    }

    public function scopeHasPPPUser($query)
    {
        return $query->whereNotNull('ppp_user_id');
    }

    public function pppUser()
    {
        return $this->belongsTo(PPPSecrets::class, 'ppp_user_id', 'id');
    }

    public function package()
    {
        return $this->belongsTo(PackageList::class, 'package_id', 'id');
    }

    public function customerAddress()
    {
        return $this->hasMany(CustomersAddress::class, 'customer_address_unique_id', 'customer_unique_id');
    }

    public function paymentSummary()
    {
        return $this->hasMany(PaymentSummary::class, 'customer_payment_unique_id', 'customer_unique_id');
    }

    public function collectionSummary()
    {
        return $this->hasMany(CollectionSummary::class, 'customer_collection_unique_id', 'customer_unique_id');
    }

    public function billing()
    {
        return $this->belongsTo(BillingInfo::class, 'customer_unique_id', 'customer_bill_unique_id');
    }

    public function official()
    {
        return $this->belongsTo(OfficialInfo::class, 'customer_unique_id', 'customer_office_unique_id');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('customer_unique_id', 'like', '%'.$search.'%')
            ->orWhere('customer_name', 'like', '%'.$search.'%')
            ->orWhere('email', 'like', '%'.$search.'%')
            ->orWhere('mobile', 'like', '%'.$search.'%')
            ->orWhereHas('pppUser', function ($q) use ($search) {
                $q->where('username', 'like', '%'.$search.'%');
            });
    }

    public function reseller()
    {
        return $this->belongsTo(Reseller::class, 'reseller_id');
    }
}
