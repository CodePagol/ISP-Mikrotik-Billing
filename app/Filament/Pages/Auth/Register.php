<?php

namespace App\Filament\Pages\Auth;

use App\Models\BillingInfo;
use App\Models\CustomersInfo;
use App\Models\OfficialInfo;
use App\Models\PPPSecrets;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SensitiveParameter;

class Register extends BaseRegister
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('username')
                    ->label('Username / PPPoE Secret')
                    ->required()
                    ->unique(PPPSecrets::class, 'username')
                    ->maxLength(25),
                TextInput::make('customer_name')
                    ->label('Full Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('mobile')
                    ->label('Mobile Number')
                    ->required()
                    ->tel()
                    ->length(11)
                    ->rules([
                        function () {
                            return function (string $attribute, $value, \Closure $fail) {
                                $formatted = '88'.ltrim($value, '8');
                                if (CustomersInfo::where('mobile', $formatted)->exists()) {
                                    $fail('The mobile number has already been taken.');
                                }
                            };
                        },
                    ])
                    ->validationMessages([
                        'length' => 'The mobile number must be exactly 11 digits.',
                    ]),
                TextInput::make('email')
                    ->label('Email Address')
                    ->email()
                    ->maxLength(255),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('filament-panels::auth/pages/register.form.password.label'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->same('passwordConfirmation')
            ->validationAttribute(__('filament-panels::auth/pages/register.form.password.validation_attribute'));
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label(__('filament-panels::auth/pages/register.form.password_confirmation.label'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->dehydrated(false);
    }

    protected function handleRegistration(#[SensitiveParameter] array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // 1. Create PPPSecrets
            $pppUser = new PPPSecrets;
            $pppUser->username = $data['username'];
            $pppUser->password = $data['password']; // plain text
            $pppUser->status = 'inactive';
            $pppUser->service = 'pppoe';
            $pppUser->save();

            // 2. Generate customer_unique_id
            $prefix = siteUrlSettings('customer_id_prefix') ?: 'FCNET';
            $lastCustomer = CustomersInfo::orderBy('id', 'desc')->value('customer_unique_id');
            if ($lastCustomer) {
                if (str_starts_with($lastCustomer, $prefix)) {
                    $lastId = (int) substr($lastCustomer, strlen($prefix));
                } else {
                    if (preg_match('/(\d+)$/', $lastCustomer, $matches)) {
                        $lastId = (int) $matches[1];
                    } else {
                        $lastId = 99;
                    }
                }
                $newId = $prefix . ($lastId + 1);
            } else {
                $newId = $prefix . '100';
            }

            // 3. Create CustomersInfo
            $customer = new CustomersInfo;
            $customer->customer_unique_id = $newId;
            $customer->customer_name = $data['customer_name'];
            $customer->email = $data['email'] ?? null;
            $customer->mobile = '88'.ltrim($data['mobile'], '8'); // Format to 88...
            $customer->status = 'pending';
            $customer->ppp_user_id = $pppUser->id;
            $customer->connection_date = now()->toDateString();
            $customer->save();

            // 4. Create BillingInfo
            $billing = new BillingInfo;
            $billing->customer_bill_unique_id = $newId;
            $billing->billing_type = 'prepaid';
            $billing->monthly_rent = 0;
            $billing->due_amount = 0;
            $billing->additional_charge = 0;
            $billing->discount = 0;
            $billing->advance = 0;
            $billing->vat = 0;
            $billing->auto_disable = 1;
            $billing->auto_disable_date = now()->addDays(30)->toDateString();
            $billing->total_amount = 0;
            $billing->due_amount = 0;
            $billing->save();

            // 5. Create OfficialInfo
            $official = new OfficialInfo;
            $official->customer_office_unique_id = $newId;
            $official->billing_type = 'prepaid';
            $official->connection_type = 'fiber';
            $official->connectivity_type = 'shared';
            $official->save();

            return $pppUser;
        });
    }
}
