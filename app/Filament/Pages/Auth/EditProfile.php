<?php

namespace App\Filament\Pages\Auth;

use App\Models\CustomersInfo;
use Filament\Actions\Action;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;

class EditProfile extends BaseEditProfile
{
    public static function isSimple(): bool
    {
        return false;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $customerInfo = CustomersInfo::where('ppp_user_id', auth()->id())->first();

        if ($customerInfo) {
            $customerAddresses = [];
            foreach ($customerInfo->customerAddress as $address) {
                $customerAddresses[] = $address->label_name.' : '.($address->input_type_text ?? $address->input_type_dropdown ?? $address->input_type_textarea);
            }
            $full_address = implode(', ', $customerAddresses);

            $data['user_id'] = $customerInfo->customer_unique_id;
            $data['pppoe_secret'] = auth()->user()->username;
            $data['customer_name'] = $customerInfo->customer_name;
            $data['contact_person'] = $customerInfo->contact_person;
            $data['parents_name'] = $customerInfo->parents_name;
            $data['spouse_name'] = $customerInfo->spouse_name;
            $data['email'] = $customerInfo->email;

            // Remove 880 prefix for the form if present
            $data['mobile'] = preg_replace('/^880/', '', $customerInfo->mobile);
            $data['alternative_mobile'] = preg_replace('/^880/', '', $customerInfo->alternative_mobile);

            $data['profession'] = $customerInfo->profession;
            $data['identification_no'] = $customerInfo->identification_no;
            $data['photo_url'] = $customerInfo->photo_url;
            $data['full_address'] = $full_address;
        }

        return $data;
    }

    public function form(Schema $schema): Schema
    {
        $customerInfo = CustomersInfo::where('ppp_user_id', auth()->id())->first();
        $hasProfile = $customerInfo !== null;

        return $schema
            ->components([
                Section::make('Customer Information')
                    ->headerActions([
                        Action::make('status')
                            ->label(fn () => auth()->user()->status)
                            ->color(fn () => (auth()->user()->status === 'active') ? 'success' : ((auth()->user()->status === 'free') ? 'info' : 'warning'))
                            ->icon(fn () => auth()->user()->status === 'active' ? 'heroicon-m-check-circle' : ((auth()->user()->status === 'free') ? 'heroicon-m-clock' : 'heroicon-m-x-circle'))
                            ->disabled(),
                    ])
                    ->schema([
                        Placeholder::make('warning')
                            ->hiddenLabel()
                            ->content(new HtmlString('
                                <div class="cp-p-4 cp-bg-amber-500/10 cp-border cp-border-amber-500/20 cp-text-amber-600 dark:cp-text-amber-400 cp-rounded-2xl cp-text-sm cp-flex cp-items-start cp-gap-3">
                                    <svg style="width: 24px; height: 24px; min-width: 24px; min-height: 24px;" class="cp-mt-0.5 cp-text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <div>
                                        <span class="cp-font-bold cp-block">Customer Profile Not Found</span>
                                        Your PPPoE account is active, but it is not linked to a customer profile. Please contact support to complete your profile.
                                    </div>
                                </div>
                            '))
                            ->hidden($hasProfile)
                            ->columnSpanFull(),
                        TextInput::make('user_id')
                            ->label('User ID')
                            ->disabled()
                            ->dehydrated()
                            ->hidden(! $hasProfile),
                        TextInput::make('pppoe_secret')
                            ->label('PPPOE Secret')
                            ->disabled()
                            ->dehydrated(),
                        TextInput::make('customer_name')
                            ->label('Customer Name')
                            ->required($hasProfile)
                            ->maxLength(255)
                            ->hidden(! $hasProfile),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('mobile')
                            ->label('Mobile')
                            ->disabled()
                            ->prefix('880')
                            ->numeric()
                            ->length(10)
                            ->validationMessages([
                                'length' => 'The mobile number must be exactly 10 digits after 880.',
                            ])
                            ->hidden(! $hasProfile),
                        TextInput::make('alternative_mobile')
                            ->label('Alternative Mobile')
                            ->prefix('880')
                            ->numeric()
                            ->length(10)
                            ->validationMessages([
                                'length' => 'The alternative mobile number must be exactly 10 digits after 880.',
                            ])
                            ->hidden(! $hasProfile),
                    ])->columns(2),
                Section::make('Contact & Identification')
                    ->schema([
                        TextInput::make('contact_person')
                            ->label('Contact Person')
                            ->required($hasProfile)
                            ->maxLength(255),
                        TextInput::make('parents_name')
                            ->label('Parents Name')
                            ->maxLength(255),
                        TextInput::make('spouse_name')
                            ->label('Spouse Name')
                            ->maxLength(255),
                        TextInput::make('profession')
                            ->label('Profession')
                            ->required($hasProfile)
                            ->maxLength(255),
                        TextInput::make('identification_no')
                            ->label('Identification No')
                            ->required($hasProfile)
                            ->maxLength(255),
                        FileUpload::make('photo_url')
                            ->label('Photo')
                            ->image()
                            ->imageEditor()
                            ->disk('direct_public')
                            ->directory('customer-images')
                            ->required($hasProfile),
                    ])->columns(2)
                    ->hidden(! $hasProfile),
                Section::make('Address')
                    ->schema([
                        TextInput::make('full_address')
                            ->label('Address')
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->hidden(! $hasProfile),
            ]);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Prepend 880 to mobile numbers before saving
        if (! empty($data['mobile'])) {
            $data['mobile'] = '880'.ltrim($data['mobile'], '0');
        }
        if (! empty($data['alternative_mobile'])) {
            $data['alternative_mobile'] = '880'.ltrim($data['alternative_mobile'], '0');
        }

        $customerFields = [
            'customer_name',
            'contact_person',
            'parents_name',
            'spouse_name',
            'email',
            'mobile',
            'alternative_mobile',
            'profession',
            'identification_no',
            'photo_url',
            'address',
        ];

        $customerData = Arr::only($data, $customerFields);

        if (isset($data['email'])) {
            $record->update(['email' => $data['email']]);
        }

        // Update CustomersInfo
        $customerInfo = CustomersInfo::where('ppp_user_id', $record->id)->first();
        if ($customerInfo) {
            $customerInfo->update($customerData);
        }

        return $record;
    }
}
