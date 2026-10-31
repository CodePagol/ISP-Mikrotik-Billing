# 🔌 Plugin Development Guide

> **ISP Mikrotik Billing** — Plugin System Documentation  
> Laravel 12 · Filament 5 · Livewire 4

---

## Quick Start (5 minutes)

```bash
# 1. Generate a new plugin scaffold
php artisan plugin:make my-plugin

# 2. Your plugin is at:  plugins/my-plugin/

# 3. Enable it (already enabled by default)
# Edit config/plugins.php → 'enabled' array

# 4. Install (run migrations)
php artisan plugin:install my-plugin
```

That's it. Your plugin is live.

---

## Directory Structure

```
plugins/
└── my-plugin/
    ├── Plugin.php                  ← Entry point (required)
    ├── plugin.json                 ← Metadata (required)
    ├── src/                        ← Your PHP source (optional, but recommended)
    │   ├── Services/
    │   └── Models/
    ├── Filament/
    │   ├── Pages/                  ← Admin pages
    │   ├── Resources/              ← CRUD resources
    │   └── Widgets/                ← Dashboard widgets
    ├── database/
    │   └── migrations/             ← Your DB migrations
    ├── routes/
    │   └── web.php                 ← Custom routes
    ├── config/
    │   └── my-plugin.php           ← Plugin config
    └── resources/
        └── views/                  ← Blade templates
```

---

## plugin.json Fields

```json
{
    "id": "my-plugin",
    "name": "My Plugin",
    "version": "1.0.0",
    "description": "What this plugin does.",
    "author": "Your Name",
    "author_url": "https://yoursite.com",
    "requires_core": "1.0.0",
    "requires_license": false,
    "class": "Plugins\\MyPlugin\\Plugin",
    "tags": ["billing", "network"],
    "support_url": "https://yoursite.com/support"
}
```

| Field | Required | Description |
|-------|----------|-------------|
| `id` | ✅ | Unique slug (must match folder name) |
| `name` | ✅ | Human readable name |
| `version` | ✅ | Semver, e.g. `1.2.0` |
| `class` | ✅ | Fully qualified Plugin class |
| `requires_license` | ❌ | `true` for premium plugins |
| `requires_core` | ❌ | Minimum app version needed |

---

## Plugin.php — Minimum Example

```php
<?php

namespace Plugins\MyPlugin;

use App\Support\AbstractPlugin;

class Plugin extends AbstractPlugin
{
    public function getId(): string      { return 'my-plugin'; }
    public function getName(): string    { return 'My Plugin'; }
    public function getVersion(): string { return '1.0.0'; }
}
```

---

---

## 🧭 1. Changing Navigation (Sidebar & Menus)

There are two primary ways to add or customize navigation in the system:
1. **Main App Sidebar (Bootstrap 5)** — Visible to ISP staff, managers, and resellers.
2. **Filament Admin Panel** — Visible inside the `/admin` Filament control panel.

---

### A. Main App Sidebar Navigation (`sidebar.nav.items` Filter)

To inject new menu items into the main application sidebar, use the `sidebar.nav.items` filter in your plugin's `registerHooks()`:

```php
// plugins/my-plugin/Plugin.php
namespace Plugins\MyPlugin;

use App\Support\AbstractPlugin;
use App\Support\Hooks;

class Plugin extends AbstractPlugin
{
    public function getId(): string      { return 'my-plugin'; }
    public function getName(): string    { return 'My Plugin'; }
    public function getVersion(): string { return '1.0.0'; }

    protected function registerHooks(): void
    {
        Hooks::filter('sidebar.nav.items', function (array $items): array {

            // 1. Place INSIDE an existing system dropdown (e.g. MikroTik, Billing, or SMS):
            $items[] = [
                'parent'     => 'mikrotik',                 // Target parent: 'mikrotik', 'billing', 'collections', or 'sms'
                'title'      => 'OLT / Switch Bindings',    // Sub-item title
                'icon'       => 'bi bi-hdd-network',        // Sub-item icon
                'url'        => url('/olt/bindings'),       // Link URL
                'badge'      => 'New',                      // Optional badge
                'permission' => 'manage-routers',           // Optional permission check
            ];

            // 2. Place as a NEW top-level standalone link under a custom section:
            $items[] = [
                'section'    => 'Hardware & Network',       // Custom section divider header
                'title'      => 'OLT Dashboard',
                'icon'       => 'bi bi-broadcast-pin',
                'url'        => url('/olt-monitor'),
                'badge'      => 'Live',
            ];

            // 3. Place as a multi-level DROPDOWN MENU with nested sub-items:
            $items[] = [
                'section'    => 'Fiber Infrastructure',
                'title'      => 'Fiber Optical',
                'icon'       => 'bi bi-diagram-3',
                'children'   => [
                    [
                        'title' => 'Splitters & PONs',
                        'icon'  => 'bi bi-bezier2',
                        'url'   => url('/fiber/pons'),
                    ],
                    [
                        'title' => 'Core Color Codes',
                        'icon'  => 'bi bi-palette',
                        'url'   => url('/fiber/colors'),
                        'badge' => 'DB',
                    ],
                ],
                'roles'      => ['admin', 'manager'],       // Only visible to admin & manager
            ];

            return $items;
        });
    }
}
```

#### Supported Navigation Item Attributes:
| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `parent` | `string` | `null` | **Placement in existing menus**. Use `'mikrotik'`, `'billing'`, `'collections'`, or `'sms'` to inject directly inside that existing dropdown. |
| `section` | `string` | `'Plugin Extensions'` | **Custom Section Header**. When no `parent` is given, groups your items under this stylish uppercase section heading. |
| `title` | `string` | *required* | The text label displayed in the sidebar menu. |
| `icon` | `string` | `'bi bi-puzzle'` | Bootstrap Icon class (e.g. `bi bi-router`, `bi bi-hdd-network`, `bi bi-diagram-3`). |
| `url` | `string` | *optional* | Direct web URL path (e.g. `/my-plugin/dashboard`). |
| `route` | `string` | *optional* | Laravel named route (e.g. `my-plugin.index`). |
| `children`| `array` | `[]` | Nested sub-items list. Automatically renders as an expandable Bootstrap 5 collapse dropdown with rotating chevron arrow! |
| `badge` | `string` | `null` | Optional pill badge shown on the right (e.g. `'New'`, `'Live'`, `'Pro'`). |
| `permission`| `string` | `null` | Optional Spatie permission slug required to view this item. |
| `roles` | `array` | `[]` | Optional list of allowed roles (e.g. `['admin', 'manager']`). |

---

### B. Filament Admin Sidebar Navigation (`getNavigationItems()`)

To add links directly into the Filament Admin Panel:

```php
// plugins/my-plugin/Plugin.php
use Filament\Navigation\NavigationItem;

public function getNavigationItems(): array
{
    return [
        NavigationItem::make('OLT Management')
            ->url('/admin/olt-manager')
            ->icon('heroicon-o-signal')
            ->group('Network Hardware')     // Groups items under a collapsible heading
            ->sort(20)                      // Priority order in the group
            ->badge('Active', 'success'),   // Optional badge with color
    ];
}
```

---

### C. Admin Pages with Auto-Navigation

Any Filament Page registered via `getFilamentPages()` can automatically declare its navigation details:

```php
// plugins/my-plugin/Filament/Pages/OltDashboard.php
namespace Plugins\MyPlugin\Filament\Pages;

use Filament\Pages\Page;

class OltDashboard extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-server-stack';
    protected static ?string $navigationLabel = 'OLT Manager';
    protected static ?string $navigationGroup = 'Network Infrastructure';
    protected static ?int    $navigationSort  = 15;
    protected static ?string $badgeColor      = 'primary';
    
    protected static string  $view = 'my-plugin::pages.olt-dashboard';

    public static function getNavigationBadge(): ?string
    {
        return 'Online';
    }
}
```

---

## 🔄 2. Changing & Mutating Data (Hooks, Filters & Actions)

The plugin architecture uses a WordPress-style event and filter bus via `App\Support\Hooks`.

- **Filters (`Hooks::filter`)**: Intercept existing variables or arrays, modify them, and **must return** the mutated value.
- **Actions (`Hooks::action`)**: Fire-and-forget event listeners that run business logic (e.g., sync with MikroTik, send WhatsApp, notify admin).

---

### A. Modifying Backend Data with Filters

#### 1. Mutate Payment Amount (e.g. Add Gateway Surcharge or Discount)
```php
Hooks::filter('payment.amount', function (float $amount, $customer, string $gateway): float {
    // Add 1.5% fee if paying via credit card
    if ($gateway === 'stripe_card') {
        return round($amount * 1.015, 2);
    }
    return $amount;
});
```

#### 2. Mutate SMS Text Template Before Dispatch
```php
Hooks::filter('sms.message', function (string $message, $recipient, string $type): string {
    // Append custom company helpline or disclaimer
    return $message . "\nHelpline: 01700-000000";
});
```

#### 3. Customize Invoice PDF Data
```php
Hooks::filter('invoice.data', function (array $data, $customer, $bill): array {
    $data['notes'] = 'Thank you for your business! Fiber optical link active.';
    $data['vat_registration_no'] = 'BIN-123456789';
    return $data;
});
```

#### 4. Filter Available Packages for Specific Customers
```php
Hooks::filter('package.list', function (array $packages, $customer): array {
    // Corporate customers only see corporate packages
    if ($customer && $customer->type === 'corporate') {
        return array_filter($packages, fn($p) => str_contains($p->name, 'Corp'));
    }
    return $packages;
});
```

#### 5. Mutate Customer Table Columns
```php
use Filament\Tables\Columns\TextColumn;

Hooks::filter('customer.table.columns', function (array $columns): array {
    $columns[] = TextColumn::make('onu_mac')
        ->label('ONU MAC Address')
        ->searchable()
        ->sortable();

    return $columns;
});
```

#### 6. Add Custom Stat Cards to Main Dashboard
```php
Hooks::filter('dashboard.stats', function (array $stats): array {
    $stats[] = [
        'label' => 'Active ONUs',
        'value' => 124,
        'icon'  => 'bi bi-hdd-network',
        'color' => 'success',
    ];
    return $stats;
});
```

---

### B. Triggering Side-Effects with Actions

#### 1. React After Payment Success (`payment.success`)
```php
Hooks::action('payment.success', function ($customer, float $amount, $transaction = null): void {
    // 1. Re-enable MikroTik PPPoE / Queue if customer was disabled
    if ($customer->is_disabled) {
        $customer->update(['is_disabled' => false]);
        // Trigger router sync
        app(\App\Services\MikrotikService::class)->enableUser($customer);
    }

    // 2. Send instant WhatsApp notification
    app(\Plugins\WhatsAppNotifier\Services\WhatsAppClient::class)->sendMessage(
        $customer->phone,
        "Dear {$customer->name}, we received Tk {$amount}. Your internet connection is active!"
    );
});
```

#### 2. React When Customer is Disabled (`customer.disabled`)
```php
Hooks::action('customer.disabled', function ($customer): void {
    // Auto-disconnect active PPPoE session from MikroTik immediately
    $router = $customer->router;
    if ($router) {
        app(\App\Services\MikrotikService::class)->killActiveSession($router, $customer->username);
    }
});
```

#### 3. React When Router Reconnects (`router.connected`)
```php
Hooks::action('router.connected', function ($router): void {
    \Illuminate\Support\Facades\Log::info("Router [{$router->name}] online! Running sync...");
});
```

---

## 🎨 3. Changing UI / UX (Frontend Injections, Styles & Blade Views)

You can customize the user interface without editing core Blade template files.

---

### A. Injecting Custom CSS & Head Meta (`layout.head` Filter)

Add external stylesheets, custom fonts, or inline `<style>` tags globally across all pages:

```php
Hooks::filter('layout.head', function (string $html): string {
    $html .= '<link rel="stylesheet" href="' . asset('plugins/my-plugin/css/custom.css') . '">';
    $html .= '<style>
        .custom-olt-badge { background: #00d27a; color: #fff; padding: 2px 8px; border-radius: 12px; }
    </style>';
    return $html;
});
```

---

### B. Injecting Custom Scripts & JS SDKs (`layout.scripts` Filter)

Inject custom analytics, chat widgets, or interactive JS before the closing `</body>` tag:

```php
Hooks::filter('layout.scripts', function (string $html): string {
    $html .= '<script>
        console.log("MyPlugin client scripts active");
        window.addEventListener("olt-signal-warning", e => {
            alert("Signal degradation detected on ONU: " + e.detail.mac);
        });
    </script>';
    return $html;
});
```

---

### C. Registering and Rendering Custom Blade Views

In your `Plugin.php`:

```php
public function getViewNamespaces(): array
{
    return [
        'my-plugin' => $this->pluginPath('resources/views'),
    ];
}
```

Now you can create files in `plugins/my-plugin/resources/views/`:
- `plugins/my-plugin/resources/views/pages/dashboard.blade.php`
- `plugins/my-plugin/resources/views/components/onu-status.blade.php`

And render them anywhere using the namespace prefix:
```blade
{{-- In any Blade template: --}}
@include('my-plugin::components.onu-status', ['signal' => '-19dBm'])
```

---

### D. Adding Custom Tabs to Customer Profile (`customer.detail.tabs`)

Add dedicated tabs (e.g. OLT Graph, Optical Power, Payment Schedule) to the customer view page:

```php
Hooks::filter('customer.detail.tabs', function (array $tabs, $customer): array {
    $tabs['olt_details'] = [
        'title' => 'Optical Link (OLT)',
        'icon'  => 'bi bi-broadcast',
        'view'  => 'my-plugin::customer.olt-tab',
        'data'  => ['customer' => $customer],
    ];
    return $tabs;
});
```

---

### E. Adding Row Actions to Customer Table (`customer.table.actions`)

Add custom 1-click action buttons to the Customer List table:

```php
use Filament\Tables\Actions\Action;

Hooks::filter('customer.table.actions', function (array $actions): array {
    $actions[] = Action::make('check_optical_power')
        ->label('Check Power')
        ->icon('heroicon-o-bolt')
        ->color('warning')
        ->action(function ($record) {
            // run optical check logic
        });

    return $actions;
});
```

---

## ⚙️ 4. Advanced Capabilities (Routes, Database & Config)

### A. Custom Routes (`getRouteFiles()`)

```php
// Plugin.php
public function getRouteFiles(): array
{
    return [
        $this->pluginPath('routes/web.php'),
        $this->pluginPath('routes/api.php'),
    ];
}
```

```php
// plugins/my-plugin/routes/web.php
use Illuminate\Support\Facades\Route;
use Plugins\MyPlugin\Http\Controllers\OltController;

Route::middleware(['web', 'auth'])->prefix('olt')->group(function () {
    Route::get('/', [OltController::class, 'index'])->name('olt.dashboard');
    Route::post('/reboot-onu', [OltController::class, 'reboot'])->name('olt.reboot');
});
```

---

### B. Database Migrations & Eloquent Models (`getMigrationPaths()`)

```php
// Plugin.php
public function getMigrationPaths(): array
{
    return [$this->pluginPath('database/migrations')];
}
```

Create standard Laravel migrations inside `plugins/my-plugin/database/migrations/`:
```php
// 2026_10_21_000001_create_olt_devices_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('olt_devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ip_address');
            $table->string('snmp_community')->default('public');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('olt_devices');
    }
};
```
These migrations are automatically recognized and run during `php artisan plugin:install my-plugin` or `php artisan migrate`.

---

### C. Merging Config Files (`getConfigFiles()`)

```php
// Plugin.php
public function getConfigFiles(): array
{
    return [
        $this->pluginPath('config/olt.php') => 'olt',
    ];
}
```
Now retrieve settings anywhere in your code:
```php
$timeout = config('olt.snmp_timeout', 5);
```

---

## 📋 All Available Core Hooks Reference

### ⚡ Actions (fire-and-forget)

| Hook Name | Parameters | When it fires |
|-----------|-----------|---------------|
| `payment.success` | `$customer, float $amount, $transaction` | After any successful payment |
| `payment.failed` | `$customer, string $reason` | After a failed payment attempt |
| `customer.created` | `$customer` | After a new customer is created |
| `customer.updated` | `$customer` | After customer data is updated |
| `customer.disabled` | `$customer` | When customer is disabled |
| `customer.enabled` | `$customer` | When customer is re-enabled |
| `customer.deleted` | `$customer` | Before a customer is deleted |
| `package.changed` | `$customer, $oldPackage, $newPackage` | When package is changed |
| `router.connected` | `$router` | When a Mikrotik router connects |
| `router.disconnected` | `$router` | When a router disconnects |
| `bill.generated` | `$customer, $billing` | Monthly bill generation |
| `sms.sending` | `$recipient, string $message` | Before an SMS is sent |
| `app.booted` | _(none)_ | After all plugins are booted |

### 🔧 Filters (modify a value and return it)

| Hook Name | Default Value | When it's applied |
|-----------|--------------|-------------------|
| `sidebar.nav.items` | `array` of sidebar item definitions | Main Bootstrap app left sidebar |
| `nav.items` | `array` of `NavigationItem` | Filament admin panel sidebar |
| `layout.head` | `string` HTML | Inside `<head>` across all pages |
| `layout.scripts` | `string` HTML | Before `</body>` across all pages |
| `customer.table.columns` | `array` of `Column` | Building customer data table |
| `customer.table.actions` | `array` of `Action` | Customer table row actions |
| `customer.table.filters` | `array` of `Filter` | Customer table filter bar |
| `customer.form.fields` | `array` of `Field` | Customer create/edit form |
| `customer.detail.tabs` | `array` | Customer profile detail view tabs |
| `customer.actions` | `array` of `Action` | Actions on customer profile |
| `dashboard.stats` | `array` of stat cards | Main dashboard stat cards |
| `dashboard.widgets` | `array` of widget classes | Dashboard widgets list |
| `invoice.data` | `array` | Before generating an invoice PDF |
| `invoice.footer` | `string` HTML | Invoice PDF footer text |
| `sms.message` | `string` | SMS text before sending |
| `payment.amount` | `float` | Payment amount calculation |
| `package.list` | `array` of `Package` | Packages available to customers |

---

## Firing Your Own Hooks

You can define hooks that **other plugins** can hook into:

```php
// In your plugin's code:
use App\Support\Hooks;

// Fire an action
Hooks::doAction('olt-manager.onu_provisioned', $onu, $customer);

// Apply a filter
$config = Hooks::applyFilters('olt-manager.onu_config', $defaultConfig, $customer);
```

---

## Routes

```php
// plugins/my-plugin/routes/web.php
use Illuminate\Support\Facades\Route;
use Plugins\MyPlugin\Http\Controllers\MyController;

Route::prefix('my-plugin')
    ->middleware(['web', 'auth'])
    ->group(function () {
        Route::get('/dashboard', [MyController::class, 'index'])->name('my-plugin.dashboard');
        Route::post('/action', [MyController::class, 'action'])->name('my-plugin.action');
    });
```

---

## Blade Views

```php
// Plugin.php
public function getViewNamespaces(): array
{
    return ['my-plugin' => $this->pluginPath('resources/views')];
}
```

```blade
{{-- Usage in any Blade file: --}}
@include('my-plugin::pages.dashboard')
@extends('my-plugin::layouts.base')
{{ view('my-plugin::partials.widget') }}
```

---

## Config

```php
// Plugin.php
public function getConfigFiles(): array
{
    return [$this->pluginPath('config/my-plugin.php') => 'my-plugin'];
}
```

```php
// Access anywhere:
config('my-plugin.setting_name')
```

---

## Migrations

```php
// Plugin.php
public function getMigrationPaths(): array
{
    return [$this->pluginPath('database/migrations')];
}
```

Migrations are auto-discovered by `php artisan migrate`. No extra setup needed.

---

## Premium Plugin (License Key)

```php
// Plugin.php
public function requiresLicense(): bool { return true; }

// License key is read from .env automatically:
// PLUGIN_LICENSE_MY_PLUGIN=XXXX-XXXX-XXXX-XXXX
```

**How customers get a license:**
1. They purchase on your website (any payment method you choose)
2. After payment, you provide a license key
3. Customer enters the key in **Admin Panel → Plugin Manager → Install via License Key**
4. System validates with your license server → downloads & installs automatically

**Setting up your license server:**
- Create a simple API endpoint at `POST /api/validate` and `POST /api/download`
- See `docs/license-server-example.md` for a reference implementation

---

## Packaging Your Plugin for Distribution

```bash
# Create a distributable ZIP
cd plugins/
zip -r my-plugin-v1.0.0.zip my-plugin/ --exclude "*.git*"
```

The ZIP must contain `Plugin.php` and `plugin.json` at the root level.  
Customers install it via: **Admin Panel → Plugin Manager → Upload ZIP**

---

## Artisan Commands

```bash
# Scaffold a new plugin
php artisan plugin:make my-plugin

# List all installed plugins and their status
php artisan plugin:list

# Run a plugin's migrations
php artisan plugin:install my-plugin

# Remove plugin (disable + cleanup)
php artisan plugin:uninstall my-plugin
```

---

## 💡 Full Real-World Plugin Example: "WhatsApp Notifier"

Here is a complete, functioning plugin that demonstrates **Navigation**, **Data Mutation**, **Event Actions**, **Blade Views**, and **Routes** all working together.

### 1. `plugins/whatsapp-notifier/plugin.json`
```json
{
    "id": "whatsapp-notifier",
    "name": "WhatsApp Notifier",
    "version": "1.0.0",
    "description": "Send instant WhatsApp bill reminders and payment confirmations via UltraMsg API.",
    "author": "Your Company",
    "class": "Plugins\\WhatsAppNotifier\\Plugin"
}
```

### 2. `plugins/whatsapp-notifier/Plugin.php`
```php
<?php

namespace Plugins\WhatsAppNotifier;

use App\Support\AbstractPlugin;
use App\Support\Hooks;

class Plugin extends AbstractPlugin
{
    public function getId(): string      { return 'whatsapp-notifier'; }
    public function getName(): string    { return 'WhatsApp Notifier'; }
    public function getVersion(): string { return '1.0.0'; }

    public function getViewNamespaces(): array
    {
        return ['whatsapp-notifier' => $this->pluginPath('resources/views')];
    }

    public function getRouteFiles(): array
    {
        return [$this->pluginPath('routes/web.php')];
    }

    protected function registerHooks(): void
    {
        // 1. ADD NAVIGATION TO APP SIDEBAR
        Hooks::filter('sidebar.nav.items', function (array $items): array {
            $items[] = [
                'title' => 'WhatsApp Logs',
                'icon'  => 'bi bi-whatsapp text-success',
                'url'   => url('/whatsapp/logs'),
                'badge' => 'Active',
            ];
            return $items;
        });

        // 2. MUTATE INVOICE FOOTER (UI & DATA CHANGE)
        Hooks::filter('invoice.footer', function (string $footerHtml): string {
            return $footerHtml . '<p style="color:#25D366; font-size:12px;">✅ WhatsApp receipt sent to your phone!</p>';
        });

        // 3. ACTION: SEND WHATSAPP MESSAGE WHEN PAYMENT SUCCEEDS
        Hooks::action('payment.success', function ($customer, float $amount): void {
            $phone = $customer->phone;
            $name  = $customer->name;
            $msg   = "Hello {$name}, payment of Tk {$amount} received! Thank you for using our fiber network.";

            // Log or call WhatsApp Gateway API (UltraMsg / GreenAPI / Meta Cloud API)
            \Illuminate\Support\Facades\Log::info("[WhatsApp] Sent to {$phone}: {$msg}");
        });

        // 4. INJECT GLOBAL JAVASCRIPT / CSS (UI/UX CHANGE)
        Hooks::filter('layout.scripts', function (string $html): string {
            $html .= '<script>console.log("WhatsApp Notifier plugin loaded.");</script>';
            return $html;
        });
    }
}
```

### 3. `plugins/whatsapp-notifier/routes/web.php`
```php
<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('whatsapp')->group(function () {
    Route::get('/logs', function () {
        return view('whatsapp-notifier::logs');
    })->name('whatsapp.logs');
});
```

### 4. `plugins/whatsapp-notifier/resources/views/logs.blade.php`
```blade
<x-app-layout>
    <div class="card p-4 shadow-sm rounded-3">
        <h4 class="fw-bold mb-3"><i class="bi bi-whatsapp text-success me-2"></i>WhatsApp Dispatch History</h4>
        <p class="text-muted">Live message logs sent through the WhatsApp gateway.</p>
        <div class="alert alert-success">
            Gateway Status: <strong>Connected</strong> (Instance ID: #1092)
        </div>
    </div>
</x-app-layout>
```

---

## Tips

> [!TIP]
> Use `$this->pluginPath('sub/dir')` in Plugin.php instead of `__DIR__` for cleaner paths.

> [!TIP]
> Prefix your hook names with your plugin ID to avoid conflicts:  
> `'olt-manager.onu_provisioned'` not just `'onu_provisioned'`

> [!IMPORTANT]
> Filter callbacks **must return the value** — forgetting `return` will break the filter chain.

> [!NOTE]  
> Plugins load in the order they appear in `config('plugins.enabled')`.  
> Use `priority` parameter on `Hooks::filter()` to control execution order.

