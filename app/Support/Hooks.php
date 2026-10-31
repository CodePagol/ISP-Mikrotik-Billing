<?php

namespace App\Support;

/**
 * ─────────────────────────────────────────────────────────────────────────────
 *  Plugin Hook & Filter System  (WordPress-style)
 * ─────────────────────────────────────────────────────────────────────────────
 *
 *  ACTIONS  — run code at a specific point (no return value needed)
 *  FILTERS  — modify a value and return it
 *
 *  ┌─────────────────────────────────────────────────────────┐
 *  │  HOW TO USE IN YOUR PLUGIN                              │
 *  │                                                         │
 *  │  // Add a navigation item                               │
 *  │  Hooks::filter('nav.items', function($items) {          │
 *  │      $items[] = [...];                                  │
 *  │      return $items;                                     │
 *  │  });                                                    │
 *  │                                                         │
 *  │  // Run code after a payment                            │
 *  │  Hooks::action('payment.success', function($customer) { │
 *  │      // send whatsapp message etc.                      │
 *  │  });                                                     │
 *  │                                                         │
 *  │  // Modify customer table columns                       │
 *  │  Hooks::filter('customer.table.columns', fn($cols) => [ │
 *  │      ...$cols, TextColumn::make('olt_port')             │
 *  │  ]);                                                    │
 *  └─────────────────────────────────────────────────────────┘
 *
 *  Available core hooks are documented in PLUGIN_DEVELOPMENT.md
 */
class Hooks
{
    /** @var array<string, array<array{callable, int}>> */
    protected static array $actions = [];

    /** @var array<string, array<array{callable, int}>> */
    protected static array $filters = [];

    // ─────────────────────────────────────────────────────────────────────────
    //  ACTIONS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Register a callback for an action hook.
     *
     * @param  string    $hook      Hook name, e.g. 'payment.success'
     * @param  callable  $callback  The function to call
     * @param  int       $priority  Lower = runs first (default 10)
     */
    public static function action(string $hook, callable $callback, int $priority = 10): void
    {
        static::$actions[$hook][] = [$callback, $priority];

        // Keep sorted by priority
        usort(static::$actions[$hook], fn ($a, $b) => $a[1] <=> $b[1]);
    }

    /**
     * Fire an action hook. All registered callbacks will run.
     *
     * @param  string  $hook  Hook name
     * @param  mixed   ...$args  Arguments passed to each callback
     */
    public static function doAction(string $hook, mixed ...$args): void
    {
        foreach (static::$actions[$hook] ?? [] as [$callback]) {
            try {
                $callback(...$args);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error(
                    "[Hooks] Action [{$hook}] callback error: {$e->getMessage()}"
                );
            }
        }
    }

    /**
     * Check if any callbacks are registered for an action.
     */
    public static function hasAction(string $hook): bool
    {
        return ! empty(static::$actions[$hook]);
    }

    /**
     * Remove all callbacks for an action hook.
     */
    public static function removeAction(string $hook): void
    {
        unset(static::$actions[$hook]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  FILTERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Register a callback for a filter hook.
     * The callback MUST return the (modified) value.
     *
     * @param  string    $hook      Filter name, e.g. 'customer.table.columns'
     * @param  callable  $callback  Must return the modified value
     * @param  int       $priority  Lower = runs first (default 10)
     */
    public static function filter(string $hook, callable $callback, int $priority = 10): void
    {
        static::$filters[$hook][] = [$callback, $priority];

        usort(static::$filters[$hook], fn ($a, $b) => $a[1] <=> $b[1]);
    }

    /**
     * Apply all filter callbacks to a value and return the result.
     *
     * @param  string  $hook     Filter name
     * @param  mixed   $value    The value to filter
     * @param  mixed   ...$args  Extra context arguments (read-only)
     * @return mixed  The filtered value
     */
    public static function applyFilters(string $hook, mixed $value, mixed ...$args): mixed
    {
        foreach (static::$filters[$hook] ?? [] as [$callback]) {
            try {
                $result = $callback($value, ...$args);
                // If callback returns null, keep the previous value (safety net)
                $value = $result ?? $value;
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error(
                    "[Hooks] Filter [{$hook}] callback error: {$e->getMessage()}"
                );
            }
        }

        return $value;
    }

    /**
     * Check if any callbacks are registered for a filter.
     */
    public static function hasFilter(string $hook): bool
    {
        return ! empty(static::$filters[$hook]);
    }

    /**
     * Remove all callbacks for a filter hook.
     */
    public static function removeFilter(string $hook): void
    {
        unset(static::$filters[$hook]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  DEBUG
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * List all registered hooks (for debugging / plugin manager UI).
     */
    public static function registeredHooks(): array
    {
        return [
            'actions' => array_keys(static::$actions),
            'filters' => array_keys(static::$filters),
        ];
    }

    /**
     * Clear all hooks (used in tests).
     */
    public static function reset(): void
    {
        static::$actions = [];
        static::$filters = [];
    }
}
