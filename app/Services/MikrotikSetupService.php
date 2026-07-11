<?php

namespace App\Services;

use App\Http\Controllers\MikrotikController;

/**
 * MikrotikSetupService
 *
 * Thin facade over MikrotikController.
 * Every add*() method accepts an optional $editId:
 *   - null  → runs the "add" command (create new)
 *   - string → runs the "set numbers=$editId" command (update existing)
 */
class MikrotikSetupService
{
    public function __construct(protected MikrotikController $ctrl) {}

    public function __call($method, $parameters)
    {
        if (method_exists($this->ctrl, $method)) {
            return $this->ctrl->$method(...$parameters);
        }

        throw new \BadMethodCallException("Method {$method} does not exist on MikrotikController.");
    }

    /**
     * Remove a firewall rule by table ('filter'|'nat'|'mangle') and index number.
     */
    public function removeFirewallRule(string $routerName, string $table, int $index): string
    {
        return match ($table) {
            'filter' => $this->ctrl->removeFirewallFilter($routerName, $index),
            'nat' => $this->ctrl->removeFirewallNat($routerName, $index),
            'mangle' => $this->ctrl->removeFirewallMangle($routerName, $index),
            default => throw new \InvalidArgumentException("Unknown firewall table: {$table}"),
        };
    }

    /**
     * Toggle a firewall rule by table ('filter'|'nat'|'mangle') and index number.
     */
    public function toggleFirewallRule(string $routerName, string $table, int $index, bool $enable): string
    {
        return match ($table) {
            'filter' => $this->ctrl->toggleFirewallFilter($routerName, $index, $enable),
            'nat' => $this->ctrl->toggleFirewallNat($routerName, $index, $enable),
            'mangle' => $this->ctrl->toggleFirewallMangle($routerName, $index, $enable),
            default => throw new \InvalidArgumentException("Unknown firewall table: {$table}"),
        };
    }
}
