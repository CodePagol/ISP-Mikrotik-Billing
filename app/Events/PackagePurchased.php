<?php

namespace App\Events;

use App\Models\CustomersInfo;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PackagePurchased
{
    use Dispatchable, SerializesModels;

    public $customer;
    public $amount;

    /**
     * Create a new event instance.
     */
    public function __construct(CustomersInfo $customer, float $amount)
    {
        $this->customer = $customer;
        $this->amount = $amount;
    }
}
