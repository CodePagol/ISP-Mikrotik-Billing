<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\PackagePurchaseRequest;
use App\Rules\ValidPhoneDigits;

class PackagePurchaseForm extends Component
{
    public $name = '';
    public $phone = '';
    public $email = '';
    public $address = '';
    public $notes = '';
    public $packageName = '';
    public $price = 0;

    public $showModal = false;

    #[On('open-purchase-modal')]
    public function openModal($packageName, $price)
    {
        $this->packageName = $packageName;
        $this->price = $price;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['name', 'phone', 'email', 'address', 'notes']);
    }

    public function submitRequest()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', new ValidPhoneDigits],
            'email' => 'nullable|email|max:255',
            'address' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        $isRejected = PackagePurchaseRequest::where('status', 'rejected')
            ->where(function ($q) {
                $q->where('phone', $this->phone);
                if ($this->email) {
                    $q->orWhere('email', $this->email);
                }
            })
            ->exists();

        if (!$isRejected) {
            PackagePurchaseRequest::create([
                'name' => $this->name,
                'phone' => $this->phone,
                'email' => $this->email,
                'address' => $this->address,
                'package_name' => $this->packageName,
                'price' => $this->price,
                'status' => 'pending',
                'ip_address' => request()->ip(),
                'notes' => $this->notes,
            ]);
        }

        $this->closeModal();

        sweetalert()->success('Thanks for choosing us. Your Application submitted successfully! Our representative will contact you soon.');
    }

    public function render()
    {
        return view('livewire.package-purchase-form');
    }
}
