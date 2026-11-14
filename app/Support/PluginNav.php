<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class PluginNav
{
    /**
     * Standard parent alias mapping so plugin developers can use intuitive names.
     */
    protected static array $parentAliases = [
        'dashboard' => ['dashboard', 'home', 'main'],
        'mikrotik' => ['mikrotik', 'router', 'network'],
        'mikrotik_setup' => ['mikrotik_setup', 'mikrotik-setup', 'mikrotik_tools', 'router_setup'],
        'customers' => ['customers', 'clients', 'customer', 'tickets', 'support'],
        'billing' => ['billing', 'collections', 'collection', 'payments', 'payment', 'invoices', 'invoice'],
        'reports' => ['reports', 'report', 'analytics', 'statistics'],
        'finance' => ['finance', 'expenses', 'expense', 'accounts', 'accounting', 'cost'],
        'admin' => ['admin', 'setup', 'settings', 'system', 'administration'],
        'logs' => ['logs', 'audits', 'activity_logs', 'system_logs'],
        'sms' => ['sms', 'sms_management', 'notifications', 'notification', 'messaging'],
        'reseller_dashboard' => ['reseller_dashboard', 'reseller_panel'],
        'reseller_customers' => ['reseller_customers', 'reseller_clients'],
        'reseller_billing' => ['reseller_billing', 'reseller_payments', 'reseller_collection'],
        'reseller_account' => ['reseller_account', 'reseller_wallet', 'wallet', 'vouchers'],
        'reseller_setup' => ['reseller_setup', 'reseller_packages'],
    ];

    /**
     * In-memory cache for the current request.
     */
    protected static ?array $cachedSidebarItems = null;
    protected static ?array $cachedNavbarItems = null;

    /**
     * Clear request cache (useful for testing).
     */
    public static function clearCache(): void
    {
        static::$cachedSidebarItems = null;
        static::$cachedNavbarItems = null;
    }

    /**
     * Retrieve all accessible navigation items for a given target ('sidebar' or 'navbar').
     *
     * @param string $target 'sidebar' | 'navbar'
     * @return array
     */
    public static function getItems(string $target = 'sidebar'): array
    {
        if ($target === 'sidebar' && static::$cachedSidebarItems !== null) {
            return static::$cachedSidebarItems;
        }

        if ($target === 'navbar' && static::$cachedNavbarItems !== null) {
            return static::$cachedNavbarItems;
        }

        // Base items from sidebar filter (common standard)
        $rawItems = Hooks::applyFilters('sidebar.nav.items', []);

        // Also merge navbar-specific filter if requested
        if ($target === 'navbar') {
            $navbarSpecific = Hooks::applyFilters('navbar.nav.items', []);
            $rawItems = array_merge($rawItems, $navbarSpecific);
        }

        $filtered = [];
        foreach ($rawItems as $item) {
            if (!is_array($item)) {
                continue;
            }

            // Target check: skip if explicitly disabled for this target
            if ($target === 'sidebar' && isset($item['show_in_sidebar']) && !$item['show_in_sidebar']) {
                continue;
            }
            if ($target === 'navbar' && isset($item['show_in_navbar']) && !$item['show_in_navbar']) {
                continue;
            }

            // User access authorization check
            if (!static::canAccess($item)) {
                continue;
            }

            $filtered[] = $item;
        }

        if ($target === 'sidebar') {
            static::$cachedSidebarItems = $filtered;
        } else {
            static::$cachedNavbarItems = $filtered;
        }

        return $filtered;
    }

    /**
     * Check if current user has permission and role for this navigation item.
     */
    public static function canAccess(array $item): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // Permission check
        if (!empty($item['permission']) && !$user->can($item['permission'])) {
            return false;
        }

        // Role check
        if (!empty($item['roles'])) {
            $allowedRoles = (array) $item['roles'];
            if (!in_array('*', $allowedRoles) && !$user->hasAnyRole($allowedRoles)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Normalize a parent string against known aliases.
     */
    public static function normalizeParent(string $parent): string
    {
        $clean = strtolower(trim($parent));
        foreach (static::$parentAliases as $canonical => $aliases) {
            if ($clean === $canonical || in_array($clean, $aliases, true)) {
                return $canonical;
            }
        }
        return $clean;
    }

    /**
     * Get all plugin navigation items belonging to a parent section.
     *
     * @param string|array $parentKeys One or more parent keys (e.g. 'finance' or ['finance', 'expenses'])
     * @param string $target 'sidebar' | 'navbar'
     * @return array
     */
    public static function getByParent(string|array $parentKeys, string $target = 'sidebar'): array
    {
        $keys = (array) $parentKeys;
        $normalizedKeys = array_map(fn($k) => static::normalizeParent($k), $keys);

        $results = [];
        foreach (static::getItems($target) as $item) {
            if (empty($item['parent'])) {
                continue;
            }

            $itemParentNorm = static::normalizeParent($item['parent']);
            if (in_array($itemParentNorm, $normalizedKeys, true) || in_array(strtolower($item['parent']), $keys, true)) {
                $results[] = $item;
            }
        }

        return $results;
    }

    /**
     * Check if any plugin items exist for a parent.
     */
    public static function hasParent(string|array $parentKeys, string $target = 'sidebar'): bool
    {
        return !empty(static::getByParent($parentKeys, $target));
    }

    /**
     * Get items grouped by custom section (items with no parent, or an unrecognized custom parent).
     *
     * @param string $target 'sidebar' | 'navbar'
     * @return array<string, array>
     */
    public static function getCustomSections(string $target = 'sidebar'): array
    {
        $sections = [];
        $knownParents = array_keys(static::$parentAliases);

        foreach (static::getItems($target) as $item) {
            $parent = $item['parent'] ?? null;

            // If it has a known parent, it gets rendered inside that parent's slot
            if ($parent && in_array(static::normalizeParent($parent), $knownParents, true)) {
                continue;
            }

            // Group by section or use custom parent as section title
            $sectionTitle = $item['section'] ?? ($parent ? Str::headline($parent) : __('Plugin Extensions'));
            $sections[$sectionTitle][] = $item;
        }

        return $sections;
    }

    /**
     * Safely resolve the link URL for a navigation item.
     */
    public static function resolveUrl(array $item): string
    {
        if (!empty($item['route']) && Route::has($item['route'])) {
            return route($item['route']);
        }

        return $item['url'] ?? '#';
    }

    /**
     * Safely resolve the icon class.
     */
    public static function resolveIcon(array $item, string $default = 'bi bi-puzzle'): string
    {
        return !empty($item['icon']) ? $item['icon'] : $default;
    }

    /**
     * Safely resolve badge details.
     *
     * @return array{text: string, class: string}|null
     */
    public static function resolveBadge(array $item): ?array
    {
        if (empty($item['badge'])) {
            return null;
        }

        return [
            'text' => (string) $item['badge'],
            'class' => $item['badge_class'] ?? 'badge-subtle-primary',
        ];
    }
}

