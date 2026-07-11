<?php

namespace App\Livewire;

use App\Http\Controllers\MikrotikController;
use App\Models\MainSiteData;
use App\Models\RouterList;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Rules\ValidPhoneDigits;
use Livewire\Component;

class MainSiteSetup extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        if (! hasAccess(['Super Admin'], ['site-setup'])) {
            abort(403, 'Unauthorized action.');
        }

        $settings = [
            // Identity & Status
            'site_name' => MainSiteData::getValue('site_name', config('app.name')),
            'portal_name' => MainSiteData::getValue('portal_name', 'Code Pagol Ltd'),
            'site_title' => MainSiteData::getValue('site_title'),
            'site_status' => MainSiteData::getValue('site_status', 'active'),
            'site_maintenance' => MainSiteData::getValue('site_maintenance', 0) ? 1 : 0,
            'site_message' => MainSiteData::getValue('site_message'),
            'portal_registration_enabled' => MainSiteData::getValue('portal_registration_enabled', 1) ? 1 : 0,
            'portal_change_password_enabled' => MainSiteData::getValue('portal_change_password_enabled', 1) ? 1 : 0,
            'site_locale' => MainSiteData::getValue('site_locale', 'en'),
            'main_site_locale' => MainSiteData::getValue('main_site_locale', 'en'),
            'portal_theme_preset' => MainSiteData::getValue('portal_theme_preset', 'indigo'),
            'theme_preset' => MainSiteData::getValue('theme_preset', 'fintech'),
            'theme_name' => MainSiteData::getValue('theme_name', 'ocean_blue'),
            'theme_primary_color' => MainSiteData::getValue('theme_primary_color') ?: '#06ad73',
            'theme_accent_color' => MainSiteData::getValue('theme_accent_color') ?: '#ff0000',
            'theme_card_style' => MainSiteData::getValue('theme_card_style', 'glass'),
            'theme_border_radius' => MainSiteData::getValue('theme_border_radius', '16px'),
            'theme_font_size' => MainSiteData::getValue('theme_font_size', 'medium'),
            'theme_font_family' => MainSiteData::getValue('theme_font_family', 'Outfit'),
            'theme_nav_style' => MainSiteData::getValue('theme_nav_style', 'sidebar'),
            'theme_widget_style' => MainSiteData::getValue('theme_widget_style', 'glass'),
            'theme_mode' => MainSiteData::getValue('theme_mode', 'dark'),
            'theme_transparency' => MainSiteData::getValue('theme_transparency', '0.5'),
            'theme_blur' => MainSiteData::getValue('theme_blur', '16px'),
            'theme_animations' => MainSiteData::getValue('theme_animations', '1.0'),
            'theme_gradient_intensity' => MainSiteData::getValue('theme_gradient_intensity', '0.7'),
            'theme_section_height' => MainSiteData::getValue('theme_section_height', 'auto'),
            'portal_primary_color' => MainSiteData::getValue('portal_primary_color') ?: '#6366f1',
            'portal_accent_color' => MainSiteData::getValue('portal_accent_color') ?: '#a78bfa',

            // Assets
            'site_logo' => MainSiteData::getValue('site_logo'),
            'site_icon' => MainSiteData::getValue('site_icon'),
            'site_favicon' => MainSiteData::getValue('site_favicon'),

            // SEO
            'site_description' => MainSiteData::getValue('site_description'),
            'site_keywords' => MainSiteData::getValue('site_keywords'),
            'site_author' => MainSiteData::getValue('site_author'),

            // Contact
            'site_email'   => MainSiteData::getValue('site_email'),
            'site_phone'   => MainSiteData::getValue('site_phone', ''),
            'site_address' => MainSiteData::getValue('site_address'),
            'site_map'     => MainSiteData::getValue('site_map'),

            // Socials (SiteSetting mapping)
            'site_facebook' => MainSiteData::getValue('site_facebook'),
            'site_twitter' => MainSiteData::getValue('site_twitter'),
            'site_instagram' => MainSiteData::getValue('site_instagram'),
            'site_linkedin' => MainSiteData::getValue('site_linkedin'),
            'site_pinterest' => MainSiteData::getValue('site_pinterest'),
            'site_youtube' => MainSiteData::getValue('site_youtube'),
            'site_whatsapp' => MainSiteData::getValue('site_whatsapp'),

            // Billing & Invoicing
            'site_currency' => MainSiteData::getValue('site_currency', 'BDT'),
            'site_invoice_prefix' => MainSiteData::getValue('site_invoice_prefix', 'INV-'),
            'customer_id_prefix' => MainSiteData::getValue('customer_id_prefix', 'FCNET'),
            'site_invoice_logo' => MainSiteData::getValue('site_invoice_logo'),
            'site_invoice_color' => MainSiteData::getValue('site_invoice_color', '#000000'),
            'site_invoice_footer' => MainSiteData::getValue('site_invoice_footer'),
            'site_invoice_notes' => MainSiteData::getValue('site_invoice_notes'),
            'site_invoice_terms' => MainSiteData::getValue('site_invoice_terms'),
            'site_invoice_signature' => MainSiteData::getValue('site_invoice_signature'),
            'disable_check_no' => MainSiteData::getValue('disable_check_no', 0),
            'disable_check_days' => MainSiteData::getValue('disable_check_days', 0),
            'expired_profile_name' => MainSiteData::getValue('expired_profile_name', 'Expired'),

            // Security / Secrets
            'site_secret_key' => MainSiteData::getValue('site_secret_key'),
            'site_secret_value' => MainSiteData::getValue('site_secret_value'),
            'site_secret_validity' => MainSiteData::getValue('site_secret_validity'),
            'site_secret_url' => MainSiteData::getValue('site_secret_url'),
            'site_secret_email' => MainSiteData::getValue('site_secret_email'),

            // Log Server
            'mysql_binary_path' => MainSiteData::getValue('mysql_binary_path', ''),
            'log_server_enabled' => MainSiteData::getValue('log_server_enabled', 0) ? 1 : 0,
            'log_server_routers' => MainSiteData::getValue('log_server_routers', []),
            'log_retention_days' => MainSiteData::getValue('log_retention_days', 30),

            // Payment Gateways
            'payment_bkash_enabled' => MainSiteData::getValue('payment_bkash_enabled', 0) ? 1 : 0,
            'payment_bkash_base_url' => MainSiteData::getValue('payment_bkash_base_url', 'https://tokenized.sandbox.bka.sh/v1.2.0-beta'),
            'payment_bkash_username' => MainSiteData::getValue('payment_bkash_username'),
            'payment_bkash_password' => MainSiteData::getValue('payment_bkash_password'),
            'payment_bkash_app_key' => MainSiteData::getValue('payment_bkash_app_key'),
            'payment_bkash_app_secret' => MainSiteData::getValue('payment_bkash_app_secret'),

            'payment_nagad_enabled' => MainSiteData::getValue('payment_nagad_enabled', 0) ? 1 : 0,
            'payment_nagad_base_url' => MainSiteData::getValue('payment_nagad_base_url', 'http://sandbox.nagad.com.bd:10080/remote-payment-gateway-1.0/api/dfs'),
            'payment_nagad_merchant_id' => MainSiteData::getValue('payment_nagad_merchant_id'),
            'payment_nagad_public_key' => MainSiteData::getValue('payment_nagad_public_key'),
            'payment_nagad_private_key' => MainSiteData::getValue('payment_nagad_private_key'),

            'payment_sslcommerz_enabled' => MainSiteData::getValue('payment_sslcommerz_enabled', 0) ? 1 : 0,
            'payment_sslcommerz_store_id' => MainSiteData::getValue('payment_sslcommerz_store_id'),
            'payment_sslcommerz_store_password' => MainSiteData::getValue('payment_sslcommerz_store_password'),
            'payment_sslcommerz_sandbox' => MainSiteData::getValue('payment_sslcommerz_sandbox', 1) ? 1 : 0,

            // Dynamic Web Content (MainSiteData unique)
            'hero_title' => MainSiteData::getValue('hero_title'),
            'hero_subtitle' => MainSiteData::getValue('hero_subtitle'),
            'hero_button_text' => MainSiteData::getValue('hero_button_text', 'Get Online'),
            'hero_button_link' => MainSiteData::getValue('hero_button_link'),
            'about_title' => MainSiteData::getValue('about_title'),
            'about_body' => MainSiteData::getValue('about_body'),
            'packages_section_title' => MainSiteData::getValue('packages_section_title', 'Internet Packages'),
            'footer_copyright' => MainSiteData::getValue('footer_copyright'),
            'is_active' => MainSiteData::getValue('is_active', 1) ? 1 : 0,
            'registration_link' => MainSiteData::getValue('registration_link'),

            'hero_slides' => MainSiteData::getValue('hero_slides', []),
            'services' => MainSiteData::getValue('services', []),
            'testimonials' => MainSiteData::getValue('testimonials', []),
            'gallery_items' => MainSiteData::getValue('gallery_items', []),
            'gallery_categories' => MainSiteData::getValue('gallery_categories', [
                ['key' => 'category-1', 'label' => 'Equipment'],
                ['key' => 'category-2', 'label' => 'Server'],
                ['key' => 'category-3', 'label' => 'Illustration'],
                ['key' => 'category-4', 'label' => 'Media'],
            ]),
            'valuable_clients' => MainSiteData::getValue('valuable_clients', []),
            'btcl_tariff_link' => MainSiteData::getValue('btcl_tariff_link', '#'),
            'important_links' => MainSiteData::getValue('important_links', []),
            'all_data' => MainSiteData::all()->toArray(),
        ];

        $this->data = $settings;
        $this->form->fill($settings);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('site_logo')
                ->label('Main Site Logo')
                ->image()
                ->directory('brand')
                ->helperText('Recommended: 190x53px transparent PNG'),
            FileUpload::make('site_icon')
                ->label('Square App Icon')
                ->image()
                ->directory('brand')
                ->helperText('Used for smaller UI elements (1:1 ratio)'),
            FileUpload::make('site_favicon')
                ->label('Browser Favicon')
                ->image()
                ->directory('brand')
                ->helperText('Standard browser tab icon (16x16 or 32x32)'),
            FileUpload::make('site_invoice_logo')
                ->label('Invoice Logo')
                ->image()
                ->directory('invoices')
                ->helperText('Logo displayed specifically on invoices.'),
            FileUpload::make('site_invoice_signature')
                ->label('Authorized Signature')
                ->image()
                ->directory('invoices'),
            RichEditor::make('site_invoice_terms')
                ->label('Terms & Conditions')
                ->grow(),
            Select::make('log_server_routers')
                ->multiple()
                ->options(RouterList::pluck('router_name', 'router_name'))
                ->label('Capture logs for:')
                ->searchable(),
            Repeater::make('hero_slides')
                ->label('Hero Slider Images')
                ->schema([
                    FileUpload::make('image')
                        ->image()
                        ->imageEditor()
                        ->imageAspectRatio('8:3')
                        ->automaticallyOpenImageEditorForAspectRatio()
                        ->automaticallyResizeImagesMode('cover')
                        ->automaticallyResizeImagesToWidth('1920')
                        ->automaticallyResizeImagesToHeight('720')
                        ->rules(['image', 'max:20480'])
                        ->required()
                        ->directory('hero'),
                    TextInput::make('caption')->placeholder('Slide caption...'),
                ])->grid(3),
            Repeater::make('services')
                ->label('Our Services')
                ->schema([
                    TextInput::make('icon')
                        ->label('Bootstrap Icon Class')
                        ->placeholder('e.g., bi bi-wifi, bi bi-shield-check, bi bi-speedometer')
                        ->default('bi bi-wifi'),
                    TextInput::make('title')->label('Service Title')->required(),
                    TextInput::make('description')->label('Short Description'),
                ])->columns(3),
            Repeater::make('valuable_clients')
                ->label('Valuable Clients')
                ->schema([
                    TextInput::make('name')
                        ->label('Client Name')
                        ->required(),
                    FileUpload::make('logo')
                        ->label('Client Logo')
                        ->image()
                        ->directory('clients')
                        ->helperText('If uploaded, the logo will be shown. If not, the name will be used.'),
                    TextInput::make('link')
                        ->label('Client Website/Link')
                        ->url()
                        ->placeholder('https://...'),
                ])->columns(3)->grid(3),
            Repeater::make('gallery_categories')
                ->label('Gallery Categories')
                ->schema([
                    TextInput::make('key')
                        ->label('Key')
                        ->required()
                        ->placeholder('Unique key used in gallery items, e.g. category-1')
                        ->inlineLabel(),
                    TextInput::make('label')
                        ->label('Label')
                        ->required()
                        ->placeholder('Human readable label shown on filter buttons')
                        ->inlineLabel(),
                ])
                ->columns(2)
                ->grid(2)
                ->extraAttributes(['class' => 'p-0 m-0'])
                ->helperText('Define categories available for gallery items. New categories uploaded from the uploader will be added automatically.'),
            Repeater::make('gallery_items')
                ->label('Gallery Items')
                ->reorderable()
                ->schema([
                    FileUpload::make('image')
                        ->label('Image')
                        ->image()
                        ->directory('gallery')
                        ->required(),
                    TextInput::make('caption')
                        ->label('Caption')
                        ->placeholder('Optional caption'),
                    Select::make('category')
                        ->label('Category')
                        ->options(fn () => collect(MainSiteData::getValue('gallery_categories', []))
                            ->mapWithKeys(fn ($c) => [($c['key'] ?? $c['label'] ?? '') => $c['label'] ?? $c['key'] ?? ''])
                            ->toArray())
                        ->default('category-1'),
                ])
                ->columns(1)
                ->grid(1)
                ->helperText('Add, reorder, or remove gallery images. Files are stored in the `gallery` folder. Use drag‑and‑drop to change order.'),
            Repeater::make('important_links')
                ->label('Important Links')
                ->schema([
                    TextInput::make('label')
                        ->label('Link Label')
                        ->required()
                        ->placeholder('e.g., BTRC Website'),
                    TextInput::make('url')
                        ->label('URL')
                        ->required()
                        ->placeholder('e.g., https://... or /about'),
                ])->columns(2),
        ])->statePath('data');
    }

    public function save(): void
    {
        $oldLocale = MainSiteData::getValue('site_locale', 'en');
        try {
            $state = $this->form->getState();
            Log::debug('MainSiteSetup save state: '.json_encode([
                'payment_bkash_enabled' => $this->data['payment_bkash_enabled'] ?? 'not_in_state',
                'payment_nagad_enabled' => $this->data['payment_nagad_enabled'] ?? 'not_in_state',
                'payment_sslcommerz_enabled' => $this->data['payment_sslcommerz_enabled'] ?? 'not_in_state',
            ]));
        } catch (ValidationException $e) {
            Log::error('MainSiteSetup validation failed: '.json_encode($e->errors()));
            flash()->error('Validation failed: '.implode(', ', Arr::flatten($e->errors())));
            throw $e;
        }

        $this->validate([
            'data.site_name'   => 'required|string',
            'data.portal_name' => 'required|string',
            'data.site_locale' => 'required|string|in:en,bn',
            'data.main_site_locale' => 'required|string|in:en,bn',
            'data.site_phone'  => ['nullable', 'string', new ValidPhoneDigits],
            'data.site_whatsapp'  => ['nullable', 'string', new ValidPhoneDigits],
        ]);

        // All keys from both migrations
        $keys = [
            'site_name', 'portal_name', 'site_title', 'site_status', 'site_maintenance', 'site_message', 'site_locale', 'main_site_locale',
            'portal_theme_preset', 'portal_primary_color', 'portal_accent_color',
            'portal_registration_enabled', 'portal_change_password_enabled',
            'site_logo', 'site_icon', 'site_favicon',
            'site_description', 'site_keywords', 'site_author',
            'site_email', 'site_phone', 'site_address', 'site_map',
            'site_facebook', 'site_twitter', 'site_instagram', 'site_whatsapp', 'site_linkedin', 'site_youtube', 'site_pinterest',
            'site_currency', 'site_invoice_prefix', 'customer_id_prefix', 'site_invoice_logo', 'site_invoice_color', 'site_invoice_footer', 'site_invoice_notes', 'site_invoice_terms', 'site_invoice_signature',
            'disable_check_no', 'disable_check_days', 'expired_profile_name',
            'site_secret_key', 'site_secret_value', 'site_secret_validity', 'site_secret_url', 'site_secret_email',
            'mysql_binary_path', 'log_server_enabled', 'log_server_routers', 'log_retention_days',
            'hero_title', 'hero_subtitle', 'hero_button_text', 'hero_button_link', 'registration_link',
            'about_title', 'about_body', 'packages_section_title', 'testimonial_title', 'footer_copyright', 'is_active',
            'hero_slides', 'services', 'testimonials', 'gallery_items', 'gallery_categories', 'valuable_clients',
            'btcl_tariff_link', 'important_links',
            'payment_bkash_enabled', 'payment_bkash_base_url', 'payment_bkash_username', 'payment_bkash_password', 'payment_bkash_app_key', 'payment_bkash_app_secret',
            'payment_nagad_enabled', 'payment_nagad_base_url', 'payment_nagad_merchant_id', 'payment_nagad_public_key', 'payment_nagad_private_key',
            'payment_sslcommerz_enabled', 'payment_sslcommerz_store_id', 'payment_sslcommerz_store_password', 'payment_sslcommerz_sandbox',
            'theme_preset', 'theme_name', 'theme_primary_color', 'theme_accent_color', 'theme_card_style',
            'theme_border_radius', 'theme_font_size', 'theme_font_family', 'theme_nav_style',
            'theme_widget_style', 'theme_mode', 'theme_transparency', 'theme_blur', 'theme_animations', 'theme_gradient_intensity',
            'theme_section_height',
        ];

        try {
            foreach ($keys as $key) {
                if (array_key_exists($key, $this->data)) {
                    MainSiteData::setValue($key, $this->data[$key]);
                }
            }

            if (isset($this->data['all_data'])) {
                foreach ($this->data['all_data'] as $item) {
                    if (empty($item['type'])) {
                        continue;
                    }
                    // Don't overwrite keys that were already handled above
                    if (in_array($item['type'], $keys)) {
                        continue;
                    }
                    MainSiteData::setValue($item['type'], $item['value']);
                }
            }

            MainSiteData::setValue('theme_updated_at', time());
            Cache::flush();
            flash()->success('Master Setup saved. All settings and secrets migrated to universal KV store!');

            $newLocale = MainSiteData::getValue('site_locale', 'en');
            if ($oldLocale !== $newLocale) {
                $this->redirect(route('site-settings'), navigate: false);
            }
        } catch (\Exception $e) {
            Log::error('MainSiteSetup save failed: '.json_encode($e->getMessage()));
            flash()->error('Save failed: '.$e->getMessage());
        }
    }

    public function resetThemeSettings(): void
    {
         $themeKeys = [
             'theme_preset',
             'theme_name',
             'theme_primary_color',
             'theme_accent_color',
             'theme_card_style',
             'theme_border_radius',
             'theme_font_size',
             'theme_font_family',
             'theme_nav_style',
             'theme_widget_style',
             'theme_mode',
             'theme_transparency',
             'theme_blur',
             'theme_animations',
             'theme_gradient_intensity',
             'theme_section_height',
             'portal_primary_color',
             'portal_accent_color',
         ];

         foreach ($themeKeys as $key) {
             $value = ($key === 'theme_preset') ? 'default' : null;
             $this->data[$key] = $value;
             MainSiteData::setValue($key, $value);
         }

         MainSiteData::setValue('theme_updated_at', time());
         $this->form->fill($this->data);
         Cache::flush();

         flash()->success('Portal theme settings reset to SASS defaults in the database!');
    }

    public function clearCacheAction(): Action
    {
        return Action::make('clearCacheAction')
            ->label('Clear Cache')
            ->color('warning')
            ->icon('heroicon-m-bolt')
            ->requiresConfirmation()
            ->action(function () {
                Artisan::call('optimize:clear');
                flash()->success('System caches cleared successfully!');
            });
    }

    public function storageLinkAction(): Action
    {
        return Action::make('storageLinkAction')
            ->label('Storage Link')
            ->color('info')
            ->icon('heroicon-m-link')
            ->requiresConfirmation()
            ->modalDescription('This will create a symbolic link from "public/storage" to "storage/app/public". Do this once on every new server deployment so your images work!')
            ->action(function () {
                Artisan::call('storage:link');
                flash()->success(Artisan::output());
            });
    }

    public function cronSetupAction(): Action
    {
        $path = base_path();

        return Action::make('cronSetupAction')
            ->label('Cron Setup')
            ->color('gray')
            ->icon('heroicon-m-clock')
            ->modalHeading('Configure Background Tasks (Cron)')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close')
            ->modalDescription(new HtmlString('
                <p class="mb-3">For automated tasks (like auto-disabling, log polling, and router syncing) to run, you must add the following Cron Job to your server (e.g. cPanel or VPS):</p>
                <div class="p-3 bg-secondary bg-opacity-10 rounded text-wrap text-break font-monospace" style="user-select: all;">
                    * * * * * cd '.escapeshellarg($path).' && php artisan schedule:run >> /dev/null 2>&1
                </div>
                <p class="mt-3 text-sm text-muted">Set it to run <b>Every Minute (* * * * *)</b>.</p>
            '));
    }

    public function backupDatabaseAction(): Action
    {
        return Action::make('backupDatabaseAction')
            ->label('Backup Database')
            ->color('success')
            ->icon('heroicon-m-arrow-down-tray')
            ->action(function () {
                $dbName = config('database.connections.mysql.database');
                $username = config('database.connections.mysql.username');
                $password = config('database.connections.mysql.password');
                $host = config('database.connections.mysql.host');
                $port = config('database.connections.mysql.port');

                $mysqlPath = MainSiteData::getValue('mysql_binary_path', '');

                $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
                $executable = $isWindows ? 'mysqldump.exe' : 'mysqldump';

                if (! empty($mysqlPath)) {
                    $mysqlDumpCmd = rtrim($mysqlPath, '/\\').DIRECTORY_SEPARATOR.$executable;
                } else {
                    $mysqlDumpCmd = app(MainSiteSetup::class)->autoDetectMysqlPath('mysqldump');
                }

                if (! is_dir(base_path('backups'))) {
                    mkdir(base_path('backups'), 0755, true);
                }

                $fileName = 'backup_'.date('Y_m_d_H_i_s').'_'.Str::random(5).'.sql';
                $path = base_path('backups/'.$fileName);

                $args = [
                    $mysqlDumpCmd,
                    '-h', $host,
                    '-P', $port,
                    '-u', $username,
                ];
                if ($password !== '' && $password !== null) {
                    $args[] = "--password={$password}";
                }
                $args[] = $dbName;

                $handle = fopen($path, 'w');
                $errorMessage = '';

                $process = new \Symfony\Component\Process\Process($args);
                $process->setTimeout(300);

                $process->run(function ($type, $buffer) use ($handle, &$errorMessage) {
                    if ($type === \Symfony\Component\Process\Process::OUT) {
                        fwrite($handle, $buffer);
                    } else {
                        $errorMessage .= $buffer;
                    }
                });

                fclose($handle);

                if (! $process->isSuccessful()) {
                    if (file_exists($path)) {
                        unlink($path);
                    }
                    $errorMessage = trim($errorMessage);
                    Log::error('Backup failed: '.$errorMessage);
                    
                    $passwordPreview = $password !== '' && $password !== null ? ' --password=***' : '';
                    $commandPreview = escapeshellarg($mysqlDumpCmd) . " -h " . escapeshellarg($host) . " -P " . escapeshellarg($port) . " -u " . escapeshellarg($username) . $passwordPreview . " " . escapeshellarg($dbName);
                    
                    flash()->error("<b>Backup Failed!</b><br>Error: <code>{$errorMessage}</code><br>Command run: <code style='font-size:10px;'>{$commandPreview}</code>");

                    return;
                }

                flash()->success('Database backup created successfully!');
            });
    }

    public function getBackupFiles()
    {
        $backupDir = base_path('backups');
        if (! is_dir($backupDir)) {
            return [];
        }

        $files = File::files($backupDir);
        $backups = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'sql') {
                $backups[] = [
                    'name' => $file->getFilename(),
                    'size' => number_format($file->getSize() / 1048576, 2).' MB',
                    'date' => Carbon::createFromTimestamp($file->getMTime())->format('Y-m-d H:i:s'),
                    'mtime' => $file->getMTime(),
                ];
            }
        }

        usort($backups, function ($a, $b) {
            return $b['mtime'] <=> $a['mtime'];
        });

        return $backups;
    }

    public function downloadBackupFile(string $name)
    {
        $path = base_path('backups/'.$name);
        if (file_exists($path)) {
            return response()->download($path);
        }

        flash()->error('Backup file not found.');
    }

    public function deleteBackupFile($fileName)
    {
        $path = base_path('backups/'.$fileName);
        if (file_exists($path)) {
            unlink($path);
            flash()->success("Backup {$fileName} deleted successfully!");
        }
    }

    public function restoreFromBackup($fileName)
    {
        $path = base_path('backups/'.$fileName);

        if (! file_exists($path)) {
            flash()->error('Backup file not found on disk.');

            return;
        }

        $dbName = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');

        $mysqlPath = MainSiteData::getValue('mysql_binary_path', '');

        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $executable = $isWindows ? 'mysql.exe' : 'mysql';

        if (! empty($mysqlPath)) {
            $mysqlCmd = escapeshellarg(rtrim($mysqlPath, '/\\').DIRECTORY_SEPARATOR.$executable);
        } else {
            $mysqlCmd = escapeshellarg($this->autoDetectMysqlPath('mysql'));
        }

        $passwordStr = $password ? "--password=\"{$password}\"" : '';
        $command = "{$mysqlCmd} -h {$host} -P {$port} -u {$username} {$passwordStr} {$dbName} < ".escapeshellarg($path).' 2>&1';

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            $errorMessage = implode('<br>', $output);
            Log::error('Restore failed: '.$errorMessage);
            flash()->error("<b>Restore Failed!</b><br>Error: <code>{$errorMessage}</code><br>Command run: <code style='font-size:10px;'>{$command}</code>");

            return;
        }

        flash()->success("Database successfully restored from {$fileName}!");
    }

    public function pollLogs(): void
    {
        try {
            $ctrl = app(MikrotikController::class);
            $enabledRouters = MainSiteData::getValue('log_server_routers', []);

            if (empty($enabledRouters)) {
                flash()->warning("No routers selected for logging in the 'Log Server Operations' section.");

                return;
            }

            $routers = RouterList::where('action', 'connected')
                ->whereIn('router_name', $enabledRouters)
                ->get();

            if ($routers->isEmpty()) {
                flash()->warning("The selected routers aren't currently connected.");

                return;
            }

            $count = 0;
            foreach ($routers as $router) {
                $logs = $ctrl->getRouterLogs($router->router_name, 100);
                if (! empty($logs)) {
                    $ctrl->storeRouterLogs($router->router_name, $logs);
                    $count += count($logs);
                }
            }

            flash()->success($count > 0 ? "Fetched {$count} fresh logs from your selected routers." : 'No new entries retrieved from selected routers.');
        } catch (\Exception $e) {
            flash()->error('Failed to poll routers: '.$e->getMessage());
        }
    }

    public function autoDetectMysqlPath(string $binary = 'mysqldump'): string
    {
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        if ($isWindows) {
            $binaryWithExt = $binary.'.exe';

            exec("where {$binaryWithExt} 2>nul", $output, $returnVar);
            if ($returnVar === 0 && ! empty($output[0])) {
                return trim($output[0]);
            }

            $currentDrive = strtoupper(substr(base_path(), 0, 1));
            $drivesToCheck = array_unique([$currentDrive, 'C', 'D', 'E', 'F']);

            foreach ($drivesToCheck as $drive) {
                $laragonPaths = glob($drive.':\\laragon\\bin\\mysql\\*\\bin\\'.$binaryWithExt);
                if (! empty($laragonPaths) && file_exists($laragonPaths[0])) {
                    return $laragonPaths[0];
                }

                $xamppPath = $drive.':\\xampp\\mysql\\bin\\'.$binaryWithExt;
                if (file_exists($xamppPath)) {
                    return $xamppPath;
                }
            }

            return $binaryWithExt;
        }

        exec("which {$binary} 2>/dev/null", $output, $returnVar);
        if ($returnVar === 0 && ! empty($output[0])) {
            return trim($output[0]);
        }

        $commonUnixPaths = [
            "/usr/bin/{$binary}",
            "/usr/local/bin/{$binary}",
            "/opt/lampp/bin/{$binary}",
            "/opt/homebrew/bin/{$binary}",
        ];

        foreach ($commonUnixPaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return $binary;
    }

    public function updated($name, $value)
    {
        if ($name === 'data.theme_preset') {
            $this->applyPreset($value);
        }
    }

    public function applyPreset(string $preset): void
    {
        $presets = [
            'fintech' => [
                'theme_name' => 'ocean_blue',
                'theme_primary_color' => '#00f2fe',
                'theme_accent_color' => '#4facfe',
                'theme_card_style' => 'flat',
                'theme_border_radius' => '12px',
                'theme_font_size' => 'medium',
                'theme_font_family' => 'Outfit',
                'theme_nav_style' => 'sidebar',
                'theme_widget_style' => 'compact',
                'theme_mode' => 'dark',
                'theme_transparency' => 0.05,
                'theme_blur' => '4px',
                'theme_animations' => 1.0,
                'theme_gradient_intensity' => 0.9,
            ],
            'islamic' => [
                'theme_name' => 'islamic_emerald',
                'theme_primary_color' => '#065f46',
                'theme_accent_color' => '#10b981',
                'theme_card_style' => 'minimal',
                'theme_border_radius' => '24px',
                'theme_font_size' => 'medium',
                'theme_font_family' => 'Inter',
                'theme_nav_style' => 'sidebar',
                'theme_widget_style' => 'minimal',
                'theme_mode' => 'dark',
                'theme_transparency' => 0.1,
                'theme_blur' => '8px',
                'theme_animations' => 0.8,
                'theme_gradient_intensity' => 0.4,
            ],
            'cyber' => [
                'theme_name' => 'amoled',
                'theme_primary_color' => '#00ffcc',
                'theme_accent_color' => '#ff007f',
                'theme_card_style' => 'cyber',
                'theme_border_radius' => '0px',
                'theme_font_size' => 'medium',
                'theme_font_family' => 'Courier New',
                'theme_nav_style' => 'sidebar',
                'theme_widget_style' => 'amoled',
                'theme_mode' => 'dark',
                'theme_transparency' => 0.0,
                'theme_blur' => '0px',
                'theme_animations' => 1.5,
                'theme_gradient_intensity' => 1.0,
            ],
            'elegant' => [
                'theme_name' => 'minimal_light',
                'theme_primary_color' => '#f43f5e',
                'theme_accent_color' => '#fda4af',
                'theme_card_style' => 'soft',
                'theme_border_radius' => '16px',
                'theme_font_size' => 'medium',
                'theme_font_family' => 'Plus Jakarta Sans',
                'theme_nav_style' => 'sidebar',
                'theme_widget_style' => 'minimal',
                'theme_mode' => 'light',
                'theme_transparency' => 0.1,
                'theme_blur' => '6px',
                'theme_animations' => 0.6,
                'theme_gradient_intensity' => 0.5,
            ],
            'glass' => [
                'theme_name' => 'dynamic_gradient',
                'theme_primary_color' => '#ffffff',
                'theme_accent_color' => '#00f2fe',
                'theme_card_style' => 'glass',
                'theme_border_radius' => '24px',
                'theme_font_size' => 'medium',
                'theme_font_family' => 'Outfit',
                'theme_nav_style' => 'sidebar',
                'theme_widget_style' => 'glass',
                'theme_mode' => 'dark',
                'theme_transparency' => 0.6,
                'theme_blur' => '24px',
                'theme_animations' => 1.2,
                'theme_gradient_intensity' => 0.9,
            ],
            'neo' => [
                'theme_name' => 'midnight_purple',
                'theme_primary_color' => '#4f46e5',
                'theme_accent_color' => '#06b6d4',
                'theme_card_style' => 'neo',
                'theme_border_radius' => '12px',
                'theme_font_size' => 'medium',
                'theme_font_family' => 'Outfit',
                'theme_nav_style' => 'sidebar',
                'theme_widget_style' => 'glass',
                'theme_mode' => 'dark',
                'theme_transparency' => 0.2,
                'theme_blur' => '10px',
                'theme_animations' => 1.0,
                'theme_gradient_intensity' => 0.85,
            ],
            'spiritual' => [
                'theme_name' => 'soft_gold',
                'theme_primary_color' => '#0f766e',
                'theme_accent_color' => '#0d9488',
                'theme_card_style' => 'spiritual',
                'theme_border_radius' => '32px',
                'theme_font_size' => 'large',
                'theme_font_family' => 'Playfair Display',
                'theme_nav_style' => 'sidebar',
                'theme_widget_style' => 'transparent',
                'theme_mode' => 'dark',
                'theme_transparency' => 0.35,
                'theme_blur' => '16px',
                'theme_animations' => 0.5,
                'theme_gradient_intensity' => 0.6,
            ],
        ];

        if (isset($presets[$preset])) {
            foreach ($presets[$preset] as $key => $value) {
                $this->data[$key] = $value;
            }
        }
    }

    public function render()
    {
        return view('livewire.main-site-setup')->layout('layouts.app');
    }
}
