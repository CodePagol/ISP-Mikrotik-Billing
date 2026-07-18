<?php

namespace App\Http\Controllers;

use App\Models\MikrotikLog;
use App\Models\NotificationLogs;
use App\Models\PPPSecrets;
use App\Models\RouterList;
use App\Services\MikrotikApiService;
use App\Services\MikrotikSSHService;

class MikrotikController extends Controller
{
    // Static connection pools to cache connection sockets per request lifecycle
    protected static array $apiPool = [];

    protected static array $sshPool = [];

    /**
     * Get or create a pooled API service instance
     */
    protected function getPooledApiService(string $ip, int $port, string $username, string $password, int $timeout = 3): MikrotikApiService
    {
        $key = "{$ip}:{$port}:{$username}";
        if (! isset(self::$apiPool[$key])) {
            self::$apiPool[$key] = new MikrotikApiService($ip, $port, $username, $password, $timeout);
        }

        return self::$apiPool[$key];
    }

    /**
     * Get or create a pooled SSH service instance
     */
    protected function getPooledSshService(string $ip, int $port, string $username, string $password, int $timeout = 5): MikrotikSSHService
    {
        $key = "{$ip}:{$port}:{$username}";
        if (! isset(self::$sshPool[$key])) {
            self::$sshPool[$key] = new MikrotikSSHService($ip, $port, $username, $password, $timeout);
        }

        return self::$sshPool[$key];
    }

    /**
     * Invalidate all cached read queries for a specific router by incrementing its version generator.
     */
    public function invalidateRouterCache(string $routerName): void
    {
        $key = "mikrotik:cache_version:{$routerName}";
        if (\Cache::has($key)) {
            \Cache::increment($key);
        } else {
            \Cache::put($key, time(), now()->addDays(30));
        }
        \Log::debug("Mikrotik [{$routerName}] read cache invalidated.");
    }

    // =========================================================================
    // CORE HELPERS
    // =========================================================================

    /**
     * Escape and quote a value for MikroTik CLI.
     * MikroTik uses \ only to escape " and \ itself.
     * addslashes() incorrectly escapes ' which causes syntax errors in RouterOS.
     */
    public function mtQuote(?string $val): string
    {
        if ($val === null) {
            return '""';
        }

        return '"'.str_replace(['\\', '"'], ['\\\\', '\"'], $val).'"';
    }

    /**
     * Try API first (if api_port set), fall back to SSH.
     *
     * Always returns a structured array:
     *   Success → ['status' => true,  'type' => 'API'|'SSH', 'data' => array]
     *   Failure → ['status' => false, 'message' => string, 'errors' => ['api' => ?, 'ssh' => ?]]
     *
     * flash()->error() is triggered automatically on every failure so the
     * caller never has to inspect the array to show a user notification.
     */
    public function checkConnection(
        string $ip,
        ?int $sshPort,
        ?int $apiPort,
        string $username,
        string $password,
        ?string $apiCmd = null,
        ?string $sshCmd = null,
        array $apiParams = [],
        bool $showErrorFlash = true
    ): array {
        $apiError = null;

        if ($apiPort) {
            try {
                if (! $apiCmd) {
                    throw new \InvalidArgumentException('API command required');
                }

                // Short 3s timeout: if API port is unreachable we fail fast and try SSH immediately
                $response = $this->getPooledApiService($ip, $apiPort, $username, $password, 3)
                    ->executeCommand($apiCmd, $apiParams);

                if (is_string($response) && str_starts_with($response, 'Error:')) {
                    throw new \Exception($response);
                }

                // Check for MikroTik API Traps or Error messages in array response
                if (is_array($response)) {
                    if (isset($response['!trap'])) {
                        $msg = $response['!trap'][0]['message'] ?? 'Unknown API Trap';
                        throw new \Exception('API Trap: '.$msg);
                    }
                    if (isset($response['after']['message'])) {
                        throw new \Exception('API Error: '.$response['after']['message']);
                    }
                }

                return ['status' => true, 'type' => 'API', 'data' => $response];

            } catch (\Exception $e) {
                $apiError = $e->getMessage();
                // API failed fast — falling back to SSH immediately
            }
        }

        if ($sshPort) {
            try {
                if (! $sshCmd) {
                    throw new \InvalidArgumentException('SSH command required');
                }

                // 5s SSH timeout as authoritative fallback
                $response = $this->getPooledSshService($ip, $sshPort, $username, $password, 5)
                    ->executeCommandParsable($sshCmd);

                // For WRITE commands, if the response is not empty, check if it contains a MikroTik error
                if ($sshCmd && ! str_contains($sshCmd, 'print')) {
                    if (is_string($response) && ! empty($response)) {
                        $lowered = strtolower($response);
                        if (str_contains($lowered, 'failure') ||
                            str_contains($lowered, 'error') ||
                            str_contains($lowered, 'invalid') ||
                            str_contains($lowered, 'no such') ||
                            str_contains($lowered, 'does not match') ||
                            str_contains($lowered, 'bad command') ||
                            str_contains($lowered, 'syntax') ||
                            str_contains($lowered, 'already') ||
                            str_contains($lowered, 'cannot') ||
                            str_contains($lowered, 'denied') ||
                            str_contains($lowered, 'not ')
                        ) {
                            throw new \Exception($response);
                        }
                    }
                }

                return ['status' => true, 'type' => 'SSH', 'data' => $response];

            } catch (\Exception $e) {
                $message = $apiError ? 'Both API and SSH failed' : 'SSH failed';
                $detail = $apiError
                    ? "API: {$apiError} | SSH: {$e->getMessage()}"
                    : $e->getMessage();

                if ($showErrorFlash) {
                    flash()->error("[{$ip}] {$message}: {$detail}");
                }

                return [
                    'status' => false,
                    'message' => $message,
                    'errors' => ['api' => $apiError, 'ssh' => $e->getMessage()],
                ];
            }
        }

        if ($showErrorFlash) {
            flash()->error("[{$ip}] No API or SSH port configured.");
        }

        return ['status' => false, 'message' => 'No API or SSH port provided', 'errors' => []];
    }

    /**
     * Run a READ command on one or all connected routers.
     * Returns ['router_name' => checkConnection() result, ...]
     */
    public function routerList(?string $routerName = null, ?string $apiCmd = null, ?string $sshCmd = null, array $apiParams = [], bool $showErrorFlash = true): array
    {
        $query = RouterList::where('action', 'connected');
        if ($routerName) {
            $query->where('router_name', $routerName);
        }

        $results = [];
        foreach ($query->get() as $router) {
            $version = \Cache::rememberForever("mikrotik:cache_version:{$router->router_name}", fn () => time());
            $cacheKey = "mikrotik:router_list:{$router->router_name}:v{$version}:".md5($apiCmd.serialize($apiParams).$sshCmd);

            $val = \Cache::get($cacheKey);
            if ($val === null) {
                $val = $this->checkConnection(
                    $router->ip_address,
                    $router->ssh_port,
                    $router->api_port,
                    $router->username,
                    $router->password,
                    $apiCmd,
                    $sshCmd,
                    $apiParams,
                    $showErrorFlash
                );

                // Only cache if the connection attempt was successful
                if (is_array($val) && isset($val['status']) && $val['status'] === true) {
                    \Cache::put($cacheKey, $val, now()->addMinutes(10));
                }
            }
            $results[$router->router_name] = $val;
        }

        return $results;
    }

    /**
     * Optimized Hybrid READ: API for speed, SSH as fallback.
     * Returns: array of items.
     */
    public function singleRead(string $routerName, string $apiCmd, string $sshCmd, array $apiParams = [], bool $showErrorFlash = true, bool $bypassCache = false): array
    {
        $router = $this->findConnectedRouter($routerName);
        if (! $router) {
            if ($showErrorFlash) {
                flash()->error("Router '{$routerName}' not connected.");
            }

            return [];
        }

        $fetchData = function () use ($router, $routerName, $apiCmd, $sshCmd, $apiParams, $showErrorFlash) {
            // Priority 1: High-Speed API (3s timeout — fail fast so SSH fallback is immediate)
            if ($router->api_port) {
                try {
                    $service = $this->getPooledApiService($router->ip_address, $router->api_port, $router->username, $router->password, 3);
                    $isPrint = str_contains($apiCmd, 'print');
                    $res = $service->executeCommand(
                        $apiCmd,
                        $isPrint ? [] : $apiParams,
                        $isPrint ? $apiParams : []
                    );

                    if (is_array($res) && ! isset($res['!trap'])) {
                        return $res;
                    }

                    if (isset($res['!trap'])) {
                        \Log::debug("Mikrotik [{$routerName}] API Read Trap: ".($res['!trap'][0]['message'] ?? 'Unknown Error'));
                    }
                } catch (\Exception $e) {
                    \Log::debug("Mikrotik [{$routerName}] API Read Fail (falling back to SSH): ".$e->getMessage());
                    if (! $router->ssh_port) {
                        throw $e;
                    }
                }
            }

            // Priority 2: Authoritative SSH Fallback (5s timeout)
            if ($router->ssh_port) {
                try {
                    $ssh = $this->getPooledSshService($router->ip_address, $router->ssh_port, $router->username, $router->password, 5);

                    return $ssh->executeCommandParsable($sshCmd);
                } catch (\Exception $e) {
                    \Log::error("Mikrotik [{$routerName}] SSH Read Fail: ".$e->getMessage());
                    if ($showErrorFlash) {
                        throw $e;
                    }
                }
            }

            return [];
        };

        if ($bypassCache) {
            return $fetchData();
        }

        // Generational Caching
        $version = \Cache::rememberForever("mikrotik:cache_version:{$routerName}", fn () => time());
        $cacheKey = "mikrotik:read:{$routerName}:v{$version}:".md5($apiCmd.serialize($apiParams).$sshCmd);

        return \Cache::remember($cacheKey, now()->addMinutes(10), $fetchData);
    }

    /**
     * Optimized Hybrid WRITE: Atomic commands with [find ...] and API ID mapping.
     */
    public function singleWrite(string $routerName, string $command, array $apiParams = []): string
    {
        $router = $this->findConnectedRouter($routerName);
        if (! $router) {
            throw new \Exception("Router '{$routerName}' not connected.");
        }

        // Priority 1: Authoritative SSH for write commands (5s timeout)
        if ($router->ssh_port) {
            try {
                // Construct the full SSH command by appending params
                $sshCmd = $command;
                foreach ($apiParams as $k => $v) {
                    if ($k === '.id') {
                        continue;
                    } // .id is API-specific
                    if (! str_contains($sshCmd, " {$k}=") && ! str_contains($sshCmd, "{$k}=")) {
                        $sshCmd .= " {$k}=".$this->mtQuote((string) $v);
                    }
                }

                $ssh = $this->getPooledSshService($router->ip_address, $router->ssh_port, $router->username, $router->password, 5);
                $res = (string) $ssh->executeCommand($sshCmd);

                // Basic error detection for SSH
                $lowered = strtolower($res);
                if (str_contains($lowered, 'failure') ||
                    str_contains($lowered, 'error') ||
                    str_contains($lowered, 'invalid') ||
                    str_contains($lowered, 'no such') ||
                    str_contains($lowered, 'does not match') ||
                    str_contains($lowered, 'bad command') ||
                    str_contains($lowered, 'syntax') ||
                    str_contains($lowered, 'already') ||
                    str_contains($lowered, 'cannot') ||
                    str_contains($lowered, 'denied') ||
                    str_contains($lowered, 'not ')
                ) {
                    throw new \Exception('MikroTik SSH Error: '.$res);
                }

                $this->invalidateRouterCache($routerName);

                return $res ?: 'OK';
            } catch (\Exception $e) {
                if (! $router->api_port) {
                    throw $e;
                }
                \Log::debug("Mikrotik [{$routerName}] SSH Write failed. Falling back to API. ".$e->getMessage());
            }
        }

        // Priority 2: API fallback for write commands (3s timeout)
        if ($router->api_port) {
            try {
                $api = $this->getPooledApiService($router->ip_address, $router->api_port, $router->username, $router->password, 3);
                $lcmd = ltrim($command, '/');

                // Atomic [find ...] mapping: resolve name-based selector to internal .id for API
                // Supports BOTH orderings: "set [find k=v] props" (correct SSH) and "set props [find k=v]" (legacy)
                if (str_contains($lcmd, '[find ')) {
                    $findPattern = '/^(\/?[^\s]+(?:\s+[^\s]+)*?)\s+(set|remove|enable|disable|print)\s+(.+)$/i';
                    if (preg_match($findPattern, $lcmd, $m) && str_contains($m[3], '[find ')) {
                        $pa = trim($m[1]);
                        $path = '/'.str_replace(' ', '/', $pa);
                        $action = strtolower($m[2]);
                        $rest = trim($m[3]);

                        // [find ...] BEFORE props (correct RouterOS order)
                        if (preg_match('/^\[find\s+(.*?)\]\s*(.*)$/s', $rest, $fm)) {
                            $finder = trim($fm[1]);
                            $props = trim($fm[2]);
                            // [find ...] AFTER props (legacy/backward-compat order)
                        } elseif (preg_match('/^(.*?)\s*\[find\s+(.*?)\]\s*$/s', $rest, $fm)) {
                            $props = trim($fm[1]);
                            $finder = trim($fm[2]);
                        } else {
                            $finder = null;
                            $props = $rest;
                        }

                        if ($finder) {
                            $fparts = explode('=', $finder, 2);
                            $fk = trim($fparts[0]);
                            $fv = trim($fparts[1] ?? '', '"\'');

                            $printResult = $api->executeCommand($path.'/print', [], [$fk => $fv]);
                            if (! empty($printResult) && isset($printResult[0]['.id'])) {
                                $id = $printResult[0]['.id'];
                                $finalParams = array_merge(['.id' => $id], $apiParams);
                                if (! empty($props)) {
                                    preg_match_all('/([^\s=]+)=(".*?"|[^\s"]+)/', $props, $pm, PREG_SET_ORDER);
                                    foreach ($pm as $match) {
                                        $finalParams[trim($match[1])] = trim($match[2], '"\'');
                                    }
                                }
                                $res = $api->executeCommand($path.'/'.$action, $finalParams);
                                if (! is_array($res) || ! isset($res['!trap'])) {
                                    $this->invalidateRouterCache($routerName);

                                    return is_array($res) ? 'OK' : (string) $res;
                                }
                            }
                        }
                    }
                }

                // Normal API fallback Parsing
                preg_match_all('/(?:"[^"]*"|[^\s"]+)+/', $lcmd, $matches);
                $parts = $matches[0] ?? [];
                $baseCmd = '/';
                $params = $apiParams;

                foreach ($parts as $part) {
                    if (str_contains($part, '=')) {
                        [$key, $val] = explode('=', $part, 2);
                        $params[$key] = trim($val, '"');
                    } else {
                        $baseCmd .= ($baseCmd !== '/' ? '/' : '').$part;
                    }
                }

                $res = $api->executeCommand($baseCmd, $params);

                // If API failed (Trap returned), try adding .id=*0 for singleton sets
                if (is_array($res) && isset($res['!trap']) && str_starts_with($baseCmd, '/interface/') && str_ends_with($baseCmd, '/set') && ! isset($params['.id']) && ! isset($params['numbers'])) {
                    $params['.id'] = '*0';
                    $res = $api->executeCommand($baseCmd, $params);
                }

                if (! is_array($res) || ! isset($res['!trap'])) {
                    $this->invalidateRouterCache($routerName);

                    return is_array($res) ? 'OK' : (string) $res;
                }
                if (isset($res['!trap'])) {
                    throw new \Exception($res['!trap'][0]['message'] ?? 'API Trap');
                }

            } catch (\Exception $e) {
                throw new \Exception('Mikrotik WR Failed (API): '.$e->getMessage());
            }
        }

        throw new \Exception("No connection protocol available for '{$routerName}'.");
    }

    protected function forEachRouter(callable $callback, ?string $routerName = null): array
    {
        $query = RouterList::where('action', 'connected');
        if ($routerName) {
            $query->where('router_name', $routerName);
        }

        $results = [];
        foreach ($query->get() as $router) {
            try {
                $results[$router->router_name] = $callback($router->router_name, $router);
            } catch (\Exception $e) {
                $results[$router->router_name] = [
                    'status' => false,
                    'message' => 'Error: '.$e->getMessage(),
                    'errors' => ['exception' => $e->getMessage()],
                ];
            }
        }

        return $results;
    }

    // =========================================================================
    // SYSTEM & CONTROLS
    // =========================================================================

    public function systemOverview(): array
    {
        return $this->routerList(null, '/system/resource/print', '/system resource print');
    }

    // ─── Generic CRUD Helpers ────────────────────────────────────────────────

    public function getItems(string $routerName, string $path): array
    {
        return $this->singleRead($routerName, "{$path}/print", ltrim($path, '/').' print without-paging terse');
    }

    protected function removeByName(string $routerName, string $path, string $name): string
    {
        return $this->singleWrite($routerName, "{$path} remove [find name=".$this->mtQuote($name).']');
    }

    protected function removeByAddress(string $routerName, string $path, string $address): string
    {
        return $this->singleWrite($routerName, "{$path} remove [find address=\"{$address}\"]");
    }

    protected function toggleByName(string $routerName, string $path, string $name, bool $enable): string
    {
        return $this->singleWrite($routerName, "{$path} ".($enable ? 'enable' : 'disable').' [find name='.$this->mtQuote($name).']');
    }

    protected function toggleByIndex(string $routerName, string $path, int $index, bool $enable): string
    {
        return $this->singleWrite($routerName, "{$path} ".($enable ? 'enable' : 'disable')." {$index}");
    }

    protected function removeByIndex(string $routerName, string $path, int $index): string
    {
        return $this->singleWrite($routerName, "{$path} remove {$index}");
    }

    public function moveItem(string $routerName, string $path, string $id, ?string $destinationId = null): string
    {
        $cmd = "{$path} move numbers={$id}";
        if ($destinationId) {
            $cmd .= " destination={$destinationId}";
        }

        return $this->singleWrite($routerName, $cmd);
    }

    // =========================================================================
    // PPP SECRETS
    // =========================================================================

    /**
     * Enable, disable, or remove a PPP secret by username.
     * $action: 'enable' | 'disable' | 'remove'
     */
    public function togglePPPSecret(int|string $customerID, string $routerName, string $username, string $action): void
    {
        $quotedUser = $this->mtQuote($username);

        try {
            if ($action === 'enable' || $action === 'disable') {
                $status = ($action === 'enable') ? 'no' : 'yes';
                $this->singleWrite($routerName, "/ppp secret set [find name={$quotedUser}] disabled={$status}");
            } else {
                // For 'remove' or other actions
                $this->singleWrite($routerName, "/ppp secret {$action} [find name={$quotedUser}]");
            }

            // If we are enabling, we should also ensure the profile is restored if it was 'Expired'
            if ($action === 'enable') {
                $secret = PPPSecrets::where('router_name', $routerName)->where('username', $username)->first();
                if ($secret && $secret->profile) {
                    $this->updatePPPSecret($routerName, $username, 'profile', $secret->profile);
                }
                // Kick session to apply enable+profile
                $this->singleWrite($routerName, "/ppp active remove [find name={$quotedUser}]");
            }
        } catch (\Exception $e) {
            NotificationLogs::create([
                'title' => ucfirst($action).' User',
                'message' => "{$customerID} ({$username}) ".$e->getMessage(),
                'status' => 'Error on Mikrotik Command',
                'type' => 'Mikrotik Command',
            ]);
            throw $e;
        }
    }

    public function enablePPPSecret(int|string $customerID, string $routerName, string $username): void
    {
        // First, ensure the secret is enabled
        $this->togglePPPSecret($customerID, $routerName, $username, 'enable');
    }

    public function disablePPPSecret(int|string $customerID, string $routerName, string $username, bool $logFallback = true): string
    {
        $quotedUser = $this->mtQuote($username);
        $expiredProfile = siteUrlSettings('expired_profile_name') ?? 'Expired';

        try {
            // Priority 1: Soft Disable (Redirect via Expired profile)
            // We use 'set' with 'profile=Expired'. This is the standard way to change properties.
            $this->singleWrite($routerName, "/ppp secret set [find name={$quotedUser}] profile=\"{$expiredProfile}\"");

            // Kick the active session to force reconnect with Expired profile
            $this->singleWrite($routerName, "/ppp active remove [find name={$quotedUser}]");

            // Ensure the secret is enabled (disabled=no) so they can hit the Walled Garden
            $this->singleWrite($routerName, "/ppp secret set [find name={$quotedUser}] disabled=no");

            return 'soft';
        } catch (\Exception $e) {
            // Priority 2: Hard Disable Fallback
            // If 'Expired' profile doesn't exist, we must ensure the user is completely cut off.
            \Log::warning("Soft-disable failed for {$username} on {$routerName}. Falling back to hard disable. Error: ".$e->getMessage());

            try {
                // Using 'set disabled=yes' is more universal than the 'disable' command for secrets
                $this->singleWrite($routerName, "/ppp secret set [find name={$quotedUser}] disabled=yes");

                // Authoritatively kill any current session
                $this->singleWrite($routerName, "/ppp active remove [find name={$quotedUser}]");

                if ($logFallback) {
                    NotificationLogs::create([
                        'title' => 'Disable User (Fallback)',
                        'message' => "{$customerID} ({$username}) profile '{$expiredProfile}' not found. User was HARD DISABLED instead.",
                        'status' => 'Hard Disabled (Fallback)',
                        'type' => 'Mikrotik Command',
                    ]);
                }
                return 'fallback';
            } catch (\Exception $fallbackErr) {
                \Log::error("CRITICAL: Failed to Hard Disable user {$username} on {$routerName}. ".$fallbackErr->getMessage());
                NotificationLogs::create([
                    'title' => 'Disable User FAILED',
                    'message' => "{$customerID} ({$username}) Both soft and hard disable failed on router. ERROR: ".$fallbackErr->getMessage(),
                    'status' => 'Error',
                    'type' => 'Mikrotik Command',
                ]);
                throw $fallbackErr;
            }
        }
    }

    public function removePPPSecret(int|string $customerID, string $routerName, string $username): void
    {
        $this->togglePPPSecret($customerID, $routerName, $username, 'remove');

        // Disconnect active PPPoE session from router (auto logout)
        try {
            $quotedUser = $this->mtQuote($username);
            $this->singleWrite($routerName, "/ppp active remove [find name={$quotedUser}]");
        } catch (\Exception $e) {
            \Log::debug('removePPPSecret active session removal failed/skipped: '.$e->getMessage());
        }
    }

    public function updatePPPSecret(string $routerName, string $username, string $field, string $value): void
    {
        $this->singleWrite($routerName, '/ppp secret set '.$field.'='.$this->mtQuote($value).' [find name='.$this->mtQuote($username).']');
    }

    // =========================================================================
    // PPP PROFILES (existing methods kept intact)
    // =========================================================================

    public function pushProfileToRouters(
        string $name,
        ?string $rateLimit,
        ?string $localAddress = null,
        ?string $remoteAddress = null,
        ?string $routerName = null
    ): array {
        return $this->forEachRouter(function ($rName, $router) use (
            $name,
            $rateLimit,
            $localAddress,
            $remoteAddress
        ) {
            $params = [];

            if (! empty($rateLimit)) {
                $params['rate-limit'] = $rateLimit;
            }

            if (! empty($localAddress)) {
                $params['local-address'] = $localAddress;
            }

            if (! empty($remoteAddress)) {
                $params['remote-address'] = $remoteAddress;
            }

            // Check exists (Robust check via API + SSH)
            $check = $this->singleRead(
                $rName,
                '/ppp/profile/print', // API Path
                'ppp profile print without-paging terse where name='.$this->mtQuote($name), // SSH Path
                ['name' => $name], // Filter for API
                false
            );

            $exists = is_array($check) && count($check) > 0;

            if ($exists) {
                if (! empty($params)) {
                    $cmd = '/ppp profile set [find name='.$this->mtQuote($name).']';
                    $result = $this->singleWrite($rName, $cmd, $params);
                } else {
                    return 'SKIPPED';
                }
            } else {
                $cmd = '/ppp profile add name='.$this->mtQuote($name);
                $result = $this->singleWrite($rName, $cmd, $params);
            }

            return $result ?: 'OK';
        }, $routerName);
    }

    public function updateProfileOnRouters(
        string $oldName,
        string $newName,
        ?string $rateLimit,
        ?string $localAddress = null,
        ?string $remoteAddress = null,
        ?string $routerName = null
    ): array {
        return $this->forEachRouter(function ($rName, $router) use (
            $oldName,
            $newName,
            $rateLimit,
            $localAddress,
            $remoteAddress
        ) {
            $params = [];

            if ($oldName !== $newName) {
                $params['name'] = $newName;
            }

            if (! empty($rateLimit)) {
                $params['rate-limit'] = $rateLimit;
            }

            if (! empty($localAddress)) {
                $params['local-address'] = $localAddress;
            }

            if (! empty($remoteAddress)) {
                $params['remote-address'] = $remoteAddress;
            }

            if (! empty($params)) {
                $cmd = '/ppp profile set [find name='.$this->mtQuote($oldName).']';
                $result = $this->singleWrite($rName, $cmd, $params);

                return $result ?: 'OK';
            }

            return 'SKIPPED';
        }, $routerName);
    }

    public function deleteProfileFromRouters(string $name, ?string $routerName = null): array
    {
        return $this->forEachRouter(function ($rName, $router) use ($name) {
            $cmd = '/ppp profile remove [find name='.$this->mtQuote($name).']';

            return $this->singleWrite($rName, $cmd) ?: 'OK';
        }, $routerName);
    }

    // =========================================================================
    // IP ADDRESSES
    // =========================================================================

    public function getIpAddresses(string $routerName): array
    {
        return $this->singleRead($routerName, '/ip/address/print', '/ip address print without-paging terse');
    }

    public function addIpAddress(string $routerName, string $address, string $interface, ?string $comment = null, ?string $editId = null): string
    {
        $cmd = $editId
            ? "/ip address set numbers={$editId} address=".$this->mtQuote($address).' interface='.$this->mtQuote($interface)
            : '/ip address add address='.$this->mtQuote($address).' interface='.$this->mtQuote($interface);
        if ($comment) {
            $cmd .= ' comment='.$this->mtQuote($comment);
        }

        return $this->singleWrite($routerName, $cmd);
    }

    public function removeIpAddress(string $routerName, string $address): string
    {
        return $this->removeByAddress($routerName, '/ip address', $address);
    }

    // =========================================================================
    // IP POOLS
    // =========================================================================

    public function getIpPools(string $routerName): array
    {
        return $this->getItems($routerName, '/ip/pool');
    }

    public function addIpPool(string $routerName, string $name, string $ranges, ?string $nextPool = null, ?string $editId = null, ?string $comment = null): string
    {
        $cmd = $editId
            ? "/ip pool set numbers={$editId} name=".$this->mtQuote($name).' ranges='.$this->mtQuote($ranges)
            : '/ip pool add name='.$this->mtQuote($name).' ranges='.$this->mtQuote($ranges);
        if ($nextPool) {
            $cmd .= ' next-pool='.$this->mtQuote($nextPool);
        }
        if ($comment) {
            $cmd .= ' comment='.$this->mtQuote($comment);
        }

        return $this->singleWrite($routerName, $cmd);
    }

    public function removeIpPool(string $routerName, string $name): string
    {
        return $this->removeByName($routerName, '/ip pool', $name);
    }

    // =========================================================================
    // DHCP SERVER
    // =========================================================================

    public function getDhcpServers(string $routerName): array
    {
        return $this->getItems($routerName, '/ip/dhcp-server');
    }

    public function getDhcpNetworks(string $routerName): array
    {
        return $this->getItems($routerName, '/ip/dhcp-server/network');
    }

    public function addDhcpServer(string $routerName, array $p, ?string $editId = null): string
    {
        $name = $p['name'] ?? '';
        $iface = $p['interface'] ?? '';
        $pool = $p['address_pool'] ?? 'static-only';
        $lease = $p['lease_time'] ?? '00:10:00';
        $comment = $p['comment'] ?? '';

        $cmd = $editId
            ? "/ip dhcp-server set numbers={$editId} name=".$this->mtQuote($name).' interface='.$this->mtQuote($iface).' address-pool='.$this->mtQuote($pool).' lease-time='.$this->mtQuote($lease)
            : '/ip dhcp-server add name='.$this->mtQuote($name).' interface='.$this->mtQuote($iface).' address-pool='.$this->mtQuote($pool).' lease-time='.$this->mtQuote($lease);
        if (! empty($p['comment'])) {
            $cmd .= ' comment='.$this->mtQuote($p['comment']);
        }

        return $this->singleWrite($routerName, $cmd);
    }

    public function removeDhcpServer(string $routerName, string $name): string
    {
        return $this->removeByName($routerName, '/ip dhcp-server', $name);
    }

    public function toggleDhcpServer(string $routerName, string $name, bool $enable): string
    {
        return $this->toggleByName($routerName, '/ip dhcp-server', $name, $enable);
    }

    public function addDhcpNetwork(string $routerName, array $p, ?string $editId = null): string
    {
        $address = $p['address'] ?? '';
        $gateway = $p['gateway'] ?? '';
        $dns = $p['dns_server'] ?? '';
        $comment = $p['comment'] ?? '';

        $parts = [];
        $parts[] = 'address='.$this->mtQuote($address);

        if (! empty($gateway)) {
            $parts[] = 'gateway='.$this->mtQuote($gateway);
        }
        if (! empty($dns)) {
            $parts[] = 'dns-server='.$this->mtQuote($dns);
        }
        if (! empty($comment)) {
            $parts[] = 'comment='.$this->mtQuote($comment);
        }

        $cmd = $editId
            ? "/ip dhcp-server network set numbers={$editId} ".implode(' ', $parts)
            : '/ip dhcp-server network add '.implode(' ', $parts);

        return $this->singleWrite($routerName, $cmd);
    }

    public function removeDhcpNetwork(string $routerName, string $address): string
    {
        return $this->removeByAddress($routerName, '/ip dhcp-server network', $address);
    }

    // =========================================================================
    // INTERFACES
    // =========================================================================

    public function getInterfaces(string $routerName): array
    {
        return $this->getItems($routerName, '/interface');
    }

    public function toggleInterface(string $routerName, string $name, bool $enable): string
    {
        return $this->toggleByName($routerName, '/interface', $name, $enable);
    }

    // =========================================================================
    // VLANs
    // =========================================================================

    public function getVlans(string $routerName): array
    {
        return $this->getItems($routerName, '/interface/vlan');
    }

    public function addVlan(string $routerName, string $name, int $vlanId, string $interface, ?string $comment = null, ?string $editId = null): string
    {
        $cmd = $editId
            ? "/interface vlan set numbers={$editId} name=".$this->mtQuote($name)." vlan-id={$vlanId} interface=".$this->mtQuote($interface)
            : '/interface vlan add name='.$this->mtQuote($name)." vlan-id={$vlanId} interface=".$this->mtQuote($interface);
        if ($comment) {
            $cmd .= ' comment='.$this->mtQuote($comment);
        }

        return $this->singleWrite($routerName, $cmd);
    }

    public function removeVlan(string $routerName, string $name): string
    {
        return $this->removeByName($routerName, '/interface vlan', $name);
    }

    // =========================================================================
    // BRIDGES
    // =========================================================================

    public function getBridges(string $routerName): array
    {
        return $this->getItems($routerName, '/interface/bridge');
    }

    public function getBridgePorts(string $routerName): array
    {
        return $this->singleRead($routerName, '/interface/bridge/port/print', '/interface bridge port print without-paging terse');
    }

    public function addBridge(string $routerName, string $name, ?string $comment = null): string
    {
        $cmd = '/interface bridge add name='.$this->mtQuote($name);
        if ($comment) {
            $cmd .= ' comment='.$this->mtQuote($comment);
        }

        return $this->singleWrite($routerName, $cmd);
    }

    public function removeBridge(string $routerName, string $name): string
    {
        return $this->removeByName($routerName, '/interface bridge', $name);
    }

    // =========================================================================
    // PPPoE SERVER
    // =========================================================================

    public function getPppoeServers(string $routerName): array
    {
        return $this->singleRead($routerName, '/interface/pppoe-server/server/print', '/interface pppoe-server server print without-paging terse');
    }

    public function addPppoeServer(string $routerName, array $p, ?string $editId = null): string
    {
        $iface = $p['interface'] ?? 'ether1';
        $svcName = $p['service_name'] ?? 'pppoe-server';
        $name = $p['name'] ?? $svcName;
        $mtu = $p['max_mtu'] ?? 1480;
        $mru = $p['max_mru'] ?? 1480;
        $mrru = $p['mrru'] ?? 'disabled';
        $ka = $p['keepalive'] ?? 10;
        $auth = $p['authentication'] ?? 'mschap2';
        $profile = $p['default_profile'] ?? 'default';

        $base = 'interface='.$this->mtQuote($iface).' service-name='.$this->mtQuote($svcName)." max-mtu={$mtu} max-mru={$mru} mrru={$mrru} keepalive-timeout={$ka} authentication=".$this->mtQuote($auth).' default-profile='.$this->mtQuote($profile);
        $cmd = $editId
            ? "/interface pppoe-server server set numbers={$editId} {$base}"
            : "/interface pppoe-server server add {$base} disabled=no";

        return $this->singleWrite($routerName, $cmd);
    }

    public function removePppoeServer(string $routerName, string $name): string
    {
        return $this->removeByName($routerName, '/interface pppoe-server server', $name);
    }

    // =========================================================================
    // OVPN SERVER SETUP
    // =========================================================================

    public function getOvpnConfig(string $routerName): array
    {
        $res = $this->getItems($routerName, '/interface/ovpn-server/server');
        if (empty($res) || (isset($res[0]) && $res[0] === '!trap')) {
            return [];
        }

        return (isset($res[0]) && is_array($res[0])) ? $res[0] : [];
    }

    public function updateOvpnConfig(string $routerName, array $p): string
    {
        $params = [
            'enabled' => ($p['enabled'] ?? false) ? 'yes' : 'no',
            'port' => $p['port'] ?? 1194,
            'mode' => $p['mode'] ?? 'ip',
            'netmask' => $p['netmask'] ?? 24,
            'default-profile' => $p['default_profile'] ?? 'default',
            'certificate' => $p['certificate'] ?? 'none',
            'require-client-certificate' => ($p['require_client_cert'] ?? false) ? 'yes' : 'no',
            'auth' => $p['auth'] ?? 'sha1',
            'cipher' => $p['cipher'] ?? 'aes128-cbc',
            'protocol' => $p['protocol'] ?? 'tcp',
            'mac-address' => $p['mac_address'] ?? '00:00:00:00:00:00',
            'max-mtu' => $p['max_mtu'] ?? 1500,
            'keepalive-timeout' => $p['keepalive_timeout'] ?? 60,
        ];

        // Built CLI command string with smart quoting
        $kvArr = [];
        foreach ($params as $k => $v) {
            $val = (string) $v;
            // Only quote if it contains spaces or is empty
            if ($val === '' || str_contains($val, ' ') || str_contains($val, '"') || str_contains($val, '\\')) {
                $kvArr[] = "{$k}=".$this->mtQuote($val);
            } else {
                $kvArr[] = "{$k}={$val}";
            }
        }
        $kvStr = implode(' ', $kvArr);

        try {
            // Singleton set is authoritative. For API fallback, .id=*0 is handled in singleWrite already.
            $this->singleWrite($routerName, "/interface ovpn-server server set {$kvStr}");

            return 'success';
        } catch (\Exception $e) {
            return 'Error: '.$e->getMessage();
        }
    }

    // =========================================================================
    // PPP PROFILES & SECRETS (setup read methods)
    // =========================================================================

    public function getPppProfiles(string $routerName): array
    {
        return $this->getItems($routerName, '/ppp/profile');
    }

    public function getPppSecrets(string $routerName): array
    {
        return $this->getItems($routerName, '/ppp/secret');
    }

    public function addPppSecret(string $routerName, array $p, ?string $editId = null): string
    {
        $service = $p['service'] ?? 'pppoe';

        $params = [
            'name' => $p['name'] ?? '',
            'password' => $p['password'] ?? '',
            'profile' => $p['profile'] ?? 'default',
            'service' => $service,
        ];
        if (! empty($p['local_address'])) {
            $params['local-address'] = $p['local_address'];
        }
        if (! empty($p['remote_address'])) {
            $params['remote-address'] = $p['remote_address'];
        }
        if (! empty($p['caller_id'])) {
            $params['caller-id'] = $p['caller_id'];
        }
        if (! empty($p['comment'])) {
            $params['comment'] = $p['comment'];
        }

        $sshBase = 'name='.$this->mtQuote($params['name']).' password='.$this->mtQuote($params['password']).' profile='.$this->mtQuote($params['profile'])." service={$service}";
        if (! empty($p['local_address'])) {
            $sshBase .= ' local-address='.$this->mtQuote($p['local_address']);
        }
        if (! empty($p['remote_address'])) {
            $sshBase .= ' remote-address='.$this->mtQuote($p['remote_address']);
        }
        if (! empty($p['caller_id'])) {
            $sshBase .= ' caller-id='.$this->mtQuote($p['caller_id']);
        }
        if (! empty($p['comment'])) {
            $sshBase .= ' comment='.$this->mtQuote($p['comment']);
        }

        $res = $this->routerList($routerName,
            $editId ? '/ppp/secret/set' : '/ppp/secret/add',
            $editId ? "/ppp secret set numbers={$editId} {$sshBase}" : "/ppp secret add {$sshBase}",
            $editId ? array_merge(['.id' => $editId], $params) : $params
        )[$routerName] ?? ['status' => false, 'message' => 'Router not found'];

        if ($res['status'] === false) {
            return 'Error: '.($res['message'] ?? 'Unknown error');
        }

        return 'success';
    }

    public function deletePppSecret(string $routerName, string $name): string
    {
        return $this->removeByName($routerName, '/ppp secret', $name);
    }

    public function getActivePppSessions(string $routerName): array
    {
        return $this->getItems($routerName, '/ppp/active');
    }

    // =========================================================================
    // HOTSPOT
    // =========================================================================

    public function getHotspotServers(string $routerName): array
    {
        return $this->getItems($routerName, '/ip/hotspot');
    }

    public function getHotspotProfiles(string $routerName): array
    {
        return $this->getItems($routerName, '/ip/hotspot/profile');
    }

    public function getHotspotUsers(string $routerName): array
    {
        return $this->getItems($routerName, '/ip/hotspot/user');
    }

    public function getHotspotUserProfiles(string $routerName): array
    {
        return $this->getItems($routerName, '/ip/hotspot/user/profile');
    }

    public function getHotspotActiveSessions(string $routerName): array
    {
        return $this->getItems($routerName, '/ip/hotspot/active');
    }

    public function addHotspotUser(string $routerName, array $p, ?string $oldName = null): string
    {
        $name = $p['name'] ?? '';
        $profile = $p['profile'] ?? 'default';

        $kv = [];
        $kv[] = 'profile='.$this->mtQuote($profile);

        if (! empty($p['password'])) {
            $kv[] = 'password='.$this->mtQuote($p['password']);
        }
        if (isset($p['disabled'])) {
            $kv[] = 'disabled='.(($p['disabled'] === 'true') ? 'yes' : 'no');
        }
        if (! empty($p['limit-uptime'])) {
            $kv[] = 'limit-uptime='.$p['limit-uptime'];
        } // time, unquoted
        if (! empty($p['limit-bytes-total'])) {
            $kv[] = 'limit-bytes-total='.$p['limit-bytes-total'];
        } // number, unquoted
        if (! empty($p['comment'])) {
            $kv[] = 'comment='.$this->mtQuote($p['comment']);
        }

        $kvStr = implode(' ', $kv);

        if ($oldName) {
            $cmd = '/ip hotspot user set [find name='.$this->mtQuote($oldName)."] {$kvStr}";
            if ($name && $name !== $oldName) {
                $cmd .= ' name='.$this->mtQuote($name);
            }
        } else {
            $cmd = '/ip hotspot user add name='.$this->mtQuote($name)." server=all {$kvStr}";
        }

        return $this->singleWrite($routerName, $cmd);
    }

    public function updateHotspotUser(string $routerName, string $username, array $params): string
    {
        // Name from params override or use username
        if (! isset($params['name'])) {
            $params['name'] = $username;
        }

        return $this->addHotspotUser($routerName, $params, $username);
    }

    public function removeHotspotUser(string $routerName, string $name): string
    {
        return $this->removeByName($routerName, '/ip hotspot user', $name);
    }

    public function addHotspotUserProfile(string $routerName, array $p, ?string $oldName = null): string
    {
        $name = trim($p['name'] ?? '');
        $sharedUsers = max(1, (int) ($p['shared_users'] ?? 1));
        $addrPool = (! empty($p['address_pool']) && $p['address_pool'] !== 'none')
                       ? $p['address_pool'] : 'none';

        // Build ALL key=value pairs. Time/rate variables are unquoted. Comment is quoted.
        $kv = [];
        $kv[] = "shared-users={$sharedUsers}";
        $kv[] = 'address-pool='.$this->mtQuote($addrPool);

        if (! empty($p['rate_limit'])) {
            $kv[] = 'rate-limit='.$p['rate_limit'];
        }
        if (! empty($p['session_timeout'])) {
            $kv[] = 'session-timeout='.$p['session_timeout'];
        }
        if (! empty($p['idle_timeout'])) {
            $kv[] = 'idle-timeout='.$p['idle_timeout'];
        }
        if (! empty($p['status_autorefresh'])) {
            $kv[] = 'status-autorefresh='.$p['status_autorefresh'];
        }
        if (! empty($p['on_login'])) {
            $kv[] = 'on-login='.$this->mtQuote($p['on_login']);
        }
        if (! empty($p['comment'])) {
            $kv[] = 'comment='.$this->mtQuote($p['comment']);
        }

        $kvStr = implode(' ', $kv);

        if ($oldName) {
            // UPDATE: [find name=X] + all fields inline in one atomic command
            $cmd = '/ip hotspot user profile set [find name='.$this->mtQuote($oldName)."] {$kvStr}";
            if ($name && $name !== $oldName) {
                $cmd .= ' name='.$this->mtQuote($name);
            }
        } else {
            // ADD: name= first, then all fields inline
            $cmd = '/ip hotspot user profile add name='.$this->mtQuote($name)." {$kvStr}";
        }

        \Log::debug('HotspotUserProfile '.($oldName ? "SET[{$oldName}]" : 'ADD')." CMD: {$cmd}");

        // Pass the complete command with NO extra apiParams.
        // singleWrite SSH runs it verbatim; API fallback parses kv pairs inline.
        return $this->singleWrite($routerName, $cmd);
    }

    public function removeHotspotUserProfile(string $routerName, string $name): string
    {
        return $this->removeByName($routerName, '/ip hotspot user profile', $name);
    }

    /**
     * Sync local Database Packages to Router Hotspot User Profiles
     */
    /**
     * Sync local Database Packages to Router Hotspot User Profiles.
     * Uses same robust exists-check pattern as pushProfileToRouters.
     */
    public function syncHotspotProfilesToRouter(string $routerName, $packages): array
    {
        $results = [];
        foreach ($packages as $pkg) {
            try {
                $p = [
                    'name' => $pkg->package,
                    'rate_limit' => $pkg->mikrotik_rate_limit ?? '',
                    'shared_users' => 1,
                    'status_autorefresh' => '1m',
                    'comment' => 'Synced from DB (ID: '.$pkg->id.')',
                ];

                // Robust existence check: API filter + SSH fallback
                $check = $this->singleRead(
                    $routerName,
                    '/ip/hotspot/user/profile/print',
                    'ip hotspot user profile print without-paging terse where name='.$this->mtQuote($pkg->package),
                    ['name' => $pkg->package],
                    false
                );
                $exists = is_array($check) && count($check) > 0;

                \Log::info("HotspotSync [{$routerName}]: Profile '{$pkg->package}' ".($exists ? 'EXISTS → update' : 'MISSING → add'));
                $this->addHotspotUserProfile($routerName, $p, $exists ? $pkg->package : null);
                $results[$pkg->package] = $exists ? 'Updated' : 'Created';
            } catch (\Exception $e) {
                \Log::error("HotspotSync [{$routerName}]: Error on '{$pkg->package}': ".$e->getMessage());
                $results[$pkg->package] = 'Error: '.$e->getMessage();
            }
        }

        return $results;
    }

    public function getHotspotHosts(string $routerName): array
    {
        return $this->getItems($routerName, '/ip/hotspot/host');
    }

    public function getHotspotCookies(string $routerName): array
    {
        return $this->getItems($routerName, '/ip/hotspot/cookie');
    }

    /**
     * Disconnect (remove) an active hotspot session by user.
     */
    public function disconnectHotspotUser(string $routerName, string $user): string
    {
        try {
            return $this->singleWrite($routerName, '/ip hotspot active remove [find user='.$this->mtQuote($user).']');
        } catch (\Exception $e) {
            return 'Error: '.$e->getMessage();
        }
    }

    /**
     * Push a batch of hotspot users (vouchers) to the router.
     * $users = [['name'=>'...','password'=>'...','profile'=>'...','comment'=>'...'], ...]
     */
    public function pushHotspotUserBatch(string $routerName, array $users): array
    {
        $results = [];
        foreach ($users as $u) {
            try {
                $this->addHotspotUser($routerName, $u);
                $results[$u['name']] = 'OK';
            } catch (\Exception $e) {
                $results[$u['name']] = 'Error: '.$e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Remove a hotspot user from the router.
     */
    public function removeHotspotUserFromRouter(string $routerName, string $name): string
    {
        return $this->removeByName($routerName, '/ip hotspot user', $name);
    }

    /**
     * Toggle (enable/disable) a hotspot user.
     */
    public function toggleHotspotUser(string $routerName, string $name, bool $enable): string
    {
        return $this->toggleByName($routerName, '/ip hotspot user', $name, $enable);
    }

    /**
     * Get hotspot user stats (uptime, bytes) from active sessions joined with user list.
     */
    public function getHotspotUserStats(string $routerName): array
    {
        try {
            $active = $this->getHotspotActiveSessions($routerName);
            $users = $this->getHotspotUsers($routerName);

            $activeMap = [];
            foreach ($active as $sess) {
                $activeMap[$sess['user'] ?? ''] = $sess;
            }

            return array_map(function ($u) use ($activeMap) {
                $u['_online'] = isset($activeMap[$u['name'] ?? '']);
                $u['_session'] = $activeMap[$u['name'] ?? ''] ?? [];

                return $u;
            }, $users);
        } catch (\Exception $e) {
            return [];
        }
    }

    // =========================================================================
    // HOTSPOT REALTIME EXPIRY (Hybrid: MikroTik on-login + App scheduled)
    // =========================================================================

    /**
     * Build the MikroTik on-login script for realtime expiry profiles.
     * This script runs on the router when a user logs in. It checks if
     * a scheduler already exists for this user; if not, it creates one
     * that will disable the user after the validity period expires.
     */
    public function buildRealtimeOnLoginScript(string $validityDuration): string
    {
        // The script uses MikroTik scripting variables:
        // $user - automatically set by hotspot to the logging-in username
        return ':local u $user; '
            . ':if ([:len [/system scheduler find name="hs_expire_$u"]] = 0) do={ '
            . '/system scheduler add name="hs_expire_$u" interval=' . $validityDuration . ' '
            . 'on-event="/ip hotspot user set [find name=\\\"$u\\\"] disabled=yes; '
            . '/ip hotspot active remove [find user=\\\"$u\\\"]; '
            . '/system scheduler remove [find name=hs_expire_$u]" '
            . 'policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon '
            . '}';
    }

    /**
     * Set the on-login script on a hotspot user profile for realtime expiry.
     */
    public function setHotspotProfileOnLoginScript(string $routerName, string $profileName, string $validityDuration): string
    {
        $script = $this->buildRealtimeOnLoginScript($validityDuration);
        $cmd = '/ip hotspot user profile set [find name=' . $this->mtQuote($profileName) . '] '
            . 'on-login=' . $this->mtQuote($script);

        \Log::debug("HotspotProfile on-login script [{$routerName}]: {$cmd}");

        return $this->singleWrite($routerName, $cmd);
    }

    /**
     * Disable a hotspot user on the router (for expiry).
     */
    public function disableHotspotUser(string $routerName, string $name): string
    {
        $cmd = '/ip hotspot user set [find name=' . $this->mtQuote($name) . '] disabled=yes';

        return $this->singleWrite($routerName, $cmd);
    }

    /**
     * Remove the expiry scheduler for a hotspot user from the router.
     */
    public function removeHotspotExpiryScheduler(string $routerName, string $username): string
    {
        $schedulerName = 'hs_expire_' . $username;
        $cmd = '/system scheduler remove [find name=' . $this->mtQuote($schedulerName) . ']';

        try {
            return $this->singleWrite($routerName, $cmd);
        } catch (\Exception $e) {
            // Scheduler may not exist, that's OK
            return '';
        }
    }

    // =========================================================================
    // RADIUS
    // =========================================================================

    public function getRadiusServers(string $routerName): array
    {
        return $this->getItems($routerName, '/radius');
    }

    public function addRadiusServer(string $routerName, array $p, ?string $editId = null): string
    {
        $address = $p['address'] ?? '';
        $service = $p['service'] ?? 'ppp';
        $authPort = $p['auth_port'] ?? 1812;
        $acctPort = $p['acct_port'] ?? 1813;
        $timeout = $p['timeout'] ?? 3000;

        $base = 'address='.$this->mtQuote($address).' secret='.$this->mtQuote($p['secret'] ?? '')." service={$service} authentication-port={$authPort} accounting-port={$acctPort} timeout={$timeout}";
        $cmd = $editId
            ? "/radius set numbers={$editId} {$base}"
            : "/radius add {$base}";

        return $this->singleWrite($routerName, $cmd);
    }

    public function removeRadiusServer(string $routerName, string $address): string
    {
        return $this->removeByAddress($routerName, '/radius', $address);
    }

    public function toggleRadiusServer(string $routerName, string $address, bool $enable): string
    {
        return $this->singleWrite($routerName, '/radius '.($enable ? 'enable' : 'disable')." [find address={$address}]");
    }

    // =========================================================================
    // FIREWALL — FILTER
    // =========================================================================

    public function getFirewallFilter(string $routerName): array
    {
        return $this->getItems($routerName, '/ip/firewall/filter');
    }

    public function addFirewallFilter(string $routerName, array $p, ?string $editId = null): string
    {
        $chain = $p['chain'] ?? 'forward';
        $action = $p['action'] ?? 'accept';
        $protocol = $p['protocol'] ?? '';
        $src = $p['src_address'] ?? '';
        $dst = $p['dst_address'] ?? '';
        $comment = $p['comment'] ?? '';

        $cmd = $editId
            ? "/ip firewall filter set numbers={$editId} chain=".$this->mtQuote($chain).' action='.$this->mtQuote($action)
            : '/ip firewall filter add chain='.$this->mtQuote($chain).' action='.$this->mtQuote($action);

        if ($protocol) {
            $cmd .= ' protocol='.$this->mtQuote($protocol);
        }
        if ($src) {
            $cmd .= ' src-address='.$this->mtQuote($src);
        }
        if ($dst) {
            $cmd .= ' dst-address='.$this->mtQuote($dst);
        }
        if ($comment) {
            $cmd .= ' comment='.$this->mtQuote($comment);
        }

        return $this->singleWrite($routerName, $cmd);
    }

    public function toggleFirewallFilter(string $routerName, int $index, bool $enable): string
    {
        return $this->toggleByIndex($routerName, '/ip firewall filter', $index, $enable);
    }

    public function removeFirewallFilter(string $routerName, int $index): string
    {
        return $this->removeByIndex($routerName, '/ip firewall filter', $index);
    }

    // =========================================================================
    // FIREWALL — NAT
    // =========================================================================

    public function getFirewallNat(string $routerName): array
    {
        return $this->getItems($routerName, '/ip/firewall/nat');
    }

    public function addFirewallNat(string $routerName, array $p, ?string $editId = null): string
    {
        $chain = $p['chain'] ?? 'srcnat';
        $action = $p['action'] ?? 'masquerade';
        $outIface = $p['out_interface'] ?? '';
        $src = $p['src_address'] ?? '';
        $comment = $p['comment'] ?? '';

        $cmd = $editId
            ? "/ip firewall nat set numbers={$editId} chain=".$this->mtQuote($chain).' action='.$this->mtQuote($action)
            : '/ip firewall nat add chain='.$this->mtQuote($chain).' action='.$this->mtQuote($action);

        if ($outIface) {
            $cmd .= ' out-interface='.$this->mtQuote($outIface);
        }
        if ($src) {
            $cmd .= ' src-address='.$this->mtQuote($src);
        }
        if ($comment) {
            $cmd .= ' comment='.$this->mtQuote($comment);
        }

        return $this->singleWrite($routerName, $cmd);
    }

    public function toggleFirewallNat(string $routerName, int $index, bool $enable): string
    {
        return $this->toggleByIndex($routerName, '/ip firewall nat', $index, $enable);
    }

    public function removeFirewallNat(string $routerName, int $index): string
    {
        return $this->removeByIndex($routerName, '/ip firewall nat', $index);
    }

    // =========================================================================
    // FIREWALL — MANGLE
    // =========================================================================

    public function getFirewallMangle(string $routerName): array
    {
        return $this->getItems($routerName, '/ip/firewall/mangle');
    }

    public function toggleFirewallMangle(string $routerName, int $index, bool $enable): string
    {
        return $this->toggleByIndex($routerName, '/ip firewall mangle', $index, $enable);
    }

    public function removeFirewallMangle(string $routerName, int $index): string
    {
        return $this->removeByIndex($routerName, '/ip firewall mangle', $index);
    }

    // =========================================================================
    // FIREWALL — ADDRESS LISTS
    // =========================================================================

    public function getAddressLists(string $routerName): array
    {
        return $this->getItems($routerName, '/ip/firewall/address-list');
    }

    public function addAddressList(string $routerName, string $list, string $address, ?string $comment = null, ?string $editId = null): string
    {
        $quotedList = $this->mtQuote($list);
        $quotedAddr = $this->mtQuote($address);

        $cmd = $editId
            ? "/ip firewall address-list set numbers={$editId} list={$quotedList} address={$quotedAddr}"
            : "/ip firewall address-list add list={$quotedList} address={$quotedAddr}";

        if ($comment) {
            $cmd .= ' comment='.$this->mtQuote($comment);
        }

        return $this->singleWrite($routerName, $cmd);
    }

    public function removeAddressList(string $routerName, string $list, string $address): string
    {
        return $this->singleWrite($routerName, "/ip firewall address-list remove [find list=\"{$list}\" address={$address}]");
    }

    // =========================================================================
    // QUEUES — SIMPLE
    // =========================================================================

    public function getSimpleQueues(string $routerName): array
    {
        return $this->getItems($routerName, '/queue/simple');
    }

    public function addSimpleQueue(string $routerName, array $p, ?string $editId = null): string
    {
        $name = $p['name'] ?? '';
        $target = $p['target'] ?? '';
        $maxLimit = $p['max_limit'] ?? '10M/10M';
        $comment = $p['comment'] ?? '';

        $cmd = $editId
            ? "/queue simple set numbers={$editId} name=".$this->mtQuote($name).' target='.$this->mtQuote($target).' max-limit='.$this->mtQuote($maxLimit)
            : '/queue simple add name='.$this->mtQuote($name).' target='.$this->mtQuote($target).' max-limit='.$this->mtQuote($maxLimit);

        if ($comment) {
            $cmd .= ' comment='.$this->mtQuote($comment);
        }

        return $this->singleWrite($routerName, $cmd);
    }

    public function removeSimpleQueue(string $routerName, string $name): string
    {
        return $this->removeByName($routerName, '/queue simple', $name);
    }

    public function toggleSimpleQueue(string $routerName, string $name, bool $enable): string
    {
        return $this->toggleByName($routerName, '/queue simple', $name, $enable);
    }

    // =========================================================================
    // QUEUES — TREE
    // =========================================================================

    public function getQueueTree(string $routerName): array
    {
        return $this->getItems($routerName, '/queue/tree');
    }

    public function addQueueTree(string $routerName, array $p, ?string $editId = null): string
    {
        $name = $p['name'] ?? '';
        $parent = $p['parent'] ?? 'global';
        $maxLimit = $p['max_limit'] ?? '10M';
        $limitAt = $p['limit_at'] ?? '';
        $priority = $p['priority'] ?? 8;
        $comment = $p['comment'] ?? '';

        $base = 'name='.$this->mtQuote($name).' parent='.$this->mtQuote($parent).' max-limit='.$this->mtQuote($maxLimit)." priority={$priority}";

        $cmd = $editId
            ? "/queue tree set numbers={$editId} {$base}"
            : "/queue tree add {$base}";

        if ($limitAt) {
            $cmd .= ' limit-at='.$this->mtQuote($limitAt);
        }
        if ($comment) {
            $cmd .= ' comment='.$this->mtQuote($comment);
        }

        return $this->singleWrite($routerName, $cmd);
    }

    public function removeQueueTree(string $routerName, string $name): string
    {
        return $this->removeByName($routerName, '/queue tree', $name);
    }

    public function getQueueTypes(string $routerName): array
    {
        return $this->getItems($routerName, '/queue/type');
    }

    // =========================================================================
    // VPN — L2TP / PPTP / SSTP
    // =========================================================================

    public function getL2tpStatus(string $routerName): array
    {
        return $this->singleRead($routerName, '/interface/l2tp-server/server/print', '/interface l2tp-server server print without-paging');
    }

    public function setL2tpServer(string $routerName, bool $enabled, string $profile = 'default', string $auth = 'mschap2', ?string $ipsecSecret = null): string
    {
        $e = $enabled ? 'yes' : 'no';
        $cmd = "/interface l2tp-server server set enabled={$e} default-profile=".$this->mtQuote($profile).' authentication='.$this->mtQuote($auth);

        if ($ipsecSecret) {
            $cmd .= ' ipsec-secret='.$this->mtQuote($ipsecSecret).' use-ipsec=yes';
        }

        return $this->singleWrite($routerName, $cmd);
    }

    public function getPptpStatus(string $routerName): array
    {
        return $this->singleRead($routerName, '/interface/pptp-server/server/print', '/interface pptp-server server print without-paging');
    }

    public function setPptpServer(string $routerName, bool $enabled, string $profile = 'default', string $auth = 'mschap2'): string
    {
        $e = $enabled ? 'yes' : 'no';

        return $this->singleWrite($routerName, "/interface pptp-server server set enabled={$e} default-profile=\"{$profile}\" authentication={$auth}");
    }

    public function getSstpStatus(string $routerName): array
    {
        return $this->singleRead($routerName, '/interface/sstp-server/server/print', '/interface sstp-server server print without-paging');
    }

    public function setSstpServer(string $routerName, bool $enabled, string $profile = 'default', int $port = 443): string
    {
        $e = $enabled ? 'yes' : 'no';

        return $this->singleWrite($routerName, "/interface sstp-server server set enabled={$e} default-profile=\"{$profile}\" port={$port}");
    }

    // =========================================================================
    // TRAFFIC MONITORING
    // =========================================================================

    /**
     * Fetch a single connected router model, or null if not found.
     */
    protected function findConnectedRouter(string $routerName): ?RouterList
    {
        return RouterList::where('router_name', $routerName)
            ->where('action', 'connected')
            ->first();
    }

    public function getLiveTraffic(string $routerName, string $interface): array
    {
        $empty = ['rx-bits-per-second' => 0, 'tx-bits-per-second' => 0];

        try {
            $res = $this->singleRead(
                $routerName,
                '/interface/monitor-traffic',
                "/interface monitor-traffic \"{$interface}\" once",
                ['interface' => $interface, 'once' => ''],
                false, // suppress automatic checkConnection error flash
                true  // bypassCache for live traffic
            );

            if (is_array($res) && isset($res[0]) && is_array($res[0])) {
                return $this->normalizeTrafficData($res[0]);
            }
        } catch (\Exception $e) {
            flash()->warning("[{$routerName}] Live traffic unavailable — ".$e->getMessage());
        }

        return $empty;
    }

    protected function normalizeTrafficData(array $data): array
    {
        $units = ['gbps' => 1_000_000_000, 'mbps' => 1_000_000, 'kbps' => 1_000, 'bps' => 1];

        foreach (['rx-bits-per-second', 'tx-bits-per-second'] as $key) {
            $val = strtolower((string) ($data[$key] ?? '0'));

            if (is_numeric($val)) {
                $data[$key] = (int) $val;

                continue;
            }

            $data[$key] = 0;
            foreach ($units as $suffix => $multiplier) {
                if (str_contains($val, $suffix)) {
                    $data[$key] = (int) ((float) trim(str_replace($suffix, '', $val)) * $multiplier);
                    break;
                }
            }
        }

        return $data;
    }

    // =========================================================================
    // LOG SERVER
    // =========================================================================

    public function getRouterLogs(string $routerName, int $limit = 100): array
    {
        $mapEntry = function ($e): array {
            if (is_string($e)) {
                // Support multiple date formats: 'may/20/2026', 'may/20', '2026-05-20'
                if (preg_match('/^\s*(?:(?<date>[a-zA-Z]{3}\/\d{1,2}(?:\/\d{4})?|\d{4}-\d{1,2}-\d{1,2})\s+)?(?<time>\d{2}:\d{2}:\d{2})\s+(?<topics>[a-zA-Z0-9,\-_]+)\s+(?<message>.*)$/', $e, $matches)) {
                    return [
                        'log_id' => null,
                        'time' => trim(($matches['date'] ?? '').' '.$matches['time']),
                        'topics' => $matches['topics'],
                        'message' => trim($matches['message']),
                        'buffer' => 'memory',
                    ];
                }

                return [
                    'log_id' => null,
                    'time' => date('Y-m-d H:i:s'),
                    'topics' => 'info',
                    'message' => trim($e),
                    'buffer' => 'memory',
                ];
            }

            return [
                'log_id' => $e['.id'] ?? ($e['id'] ?? null),
                'time' => $e['time'] ?? null,
                'topics' => $e['topics'] ?? 'info',
                'message' => (string) ($e['message'] ?? ($e['msg'] ?? '')),
                'buffer' => $e['buffer'] ?? 'memory',
            ];
        };

        $slice = fn (array $res): array => array_map($mapEntry, array_slice(array_reverse($res), 0, $limit));

        try {
            $res = $this->singleRead(
                $routerName,
                '/log/print',
                '/log print without-paging',
                [],
                false // suppress automatic checkConnection error flash
            );

            if (is_array($res)) {
                return $slice($res);
            }
        } catch (\Exception $e) {
            flash()->warning("[{$routerName}] Router logs unavailable — ".$e->getMessage());
        }

        return [];
    }

    public function storeRouterLogs(string $routerName, array $logs): int
    {
        $inserted = 0;
        foreach ($logs as $entry) {
            // Check uniqueness using a combination of message, time, and log_id (if present)
            // MikroTik's transient log_id alone is recycled and unreliable on reboots or buffer wraps.
            $query = MikrotikLog::where('router_name', $routerName)
                ->where('message', $entry['message'] ?? '');

            if (! empty($entry['time'])) {
                $query->where('time', $entry['time']);
            }
            if (! empty($entry['log_id'])) {
                $query->where('log_id', $entry['log_id']);
            }

            if ($query->exists()) {
                continue;
            }

            MikrotikLog::create([
                'router_name' => $routerName,
                'log_id' => $entry['log_id'] ?? null,
                'time' => $entry['time'] ?? null,
                'topics' => $entry['topics'] ?? 'info',
                'message' => $entry['message'] ?? '',
                'buffer' => $entry['buffer'] ?? 'memory',
            ]);
            $inserted++;
        }

        return $inserted;
    }

    public function pruneOldLogs(int $days = 30, ?string $routerName = null): int
    {
        $query = MikrotikLog::where('created_at', '<', now()->subDays($days));
        if ($routerName) {
            $query->where('router_name', $routerName);
        }

        return $query->delete();
    }

    /**
     * Create a backup (.backup) and export an .rsc file for a given router.
     * Reusable logic accessible across the application.
     *
     * @return array [ 'success' => bool, 'message' => string, 'warnings' => array ]
     */
    public function createBackup(string $routerName, ?string $customPrefix = null): array
    {
        $timestamp = date('Ymd_His');
        $prefix = $customPrefix ?: 'AutoBackup';
        $backupName = $prefix.'_'.$timestamp;
        $warnings = [];

        $router = $this->findConnectedRouter($routerName);

        if (! $router) {
            return ['success' => false, 'message' => "Router '{$routerName}' not found or not connected.", 'warnings' => $warnings];
        }

        if (! $router->api_port && ! $router->ssh_port) {
            return ['success' => false, 'message' => 'Neither API nor SSH port configured for this router.', 'warnings' => $warnings];
        }

        // ── Step 1: Save binary .backup on router ────────────────────────
        $backupCreated = false;

        if ($router->api_port) {
            try {
                // Use a fresh API instance with extended 30s timeout for the backup operation
                $api = new MikrotikApiService(
                    $router->ip_address,
                    $router->api_port,
                    $router->username,
                    $router->password,
                    30  // extended timeout for backup operation
                );

                $res = $api->executeCommand('/system/backup/save', [
                    'name' => $backupName,
                    'dont-encrypt' => 'yes',
                ]);

                if (is_string($res) && str_starts_with($res, 'Error:')) {
                    throw new \Exception(substr($res, 7));
                }
                if (is_array($res) && isset($res['!trap'])) {
                    throw new \Exception($res['!trap'][0]['message'] ?? 'API trap error');
                }

                sleep(2); // allow flash write to complete
                $backupCreated = true;
            } catch (\Exception $apiEx) {
                // Silently fall through — API may not be configured/reachable.
                $errMsg = strtolower($apiEx->getMessage());
                if (! str_contains($errMsg, 'socket') && ! str_contains($errMsg, 'connection')) {
                    $warnings[] = 'API backup failed: '.$apiEx->getMessage().'. Falling back to SSH...';
                }
            }
        }

        if (! $backupCreated && $router->ssh_port) {
            try {
                $saveSsh = $this->getPooledSshService(
                    $router->ip_address,
                    $router->ssh_port,
                    $router->username,
                    $router->password,
                    5
                );

                $rawOutput = $saveSsh->executePtyCommand(
                    "/system backup save name={$backupName} dont-encrypt=yes",
                    6  // wait 6s for flash write
                );

                $saveOutput = trim(str_replace(
                    "/system backup save name={$backupName} dont-encrypt=yes",
                    '',
                    $rawOutput
                ));

                $lower = strtolower($saveOutput);
                if (str_contains($lower, 'bad command name') ||
                    str_contains($lower, 'syntax error') ||
                    str_contains($lower, 'expected end of') ||
                    str_contains($lower, 'permission denied') ||
                    str_contains($lower, 'not enough permissions')) {
                    throw new \Exception('Router rejected backup: '.trim($saveOutput));
                }

                $backupCreated = true;
            } catch (\Exception $e) {
                return ['success' => false, 'message' => 'SSH Backup failed: '.$e->getMessage(), 'warnings' => $warnings];
            }
        }

        if (! $backupCreated) {
            return ['success' => false, 'message' => 'No API or SSH port configured — cannot create .backup file.', 'warnings' => $warnings];
        }

        // ── Step 2: Export plain-text .rsc mirror (SSH only) ─────────────
        $localFileName = null;

        if ($router->ssh_port) {
            try {
                $exportSsh = $this->getPooledSshService(
                    $router->ip_address,
                    $router->ssh_port,
                    $router->username,
                    $router->password,
                    5
                );

                $configText = trim($exportSsh->executeCommand('/export'));

                if (! empty($configText)) {
                    if (! is_dir(base_path('backups'))) {
                        mkdir(base_path('backups'), 0755, true);
                    }
                    $localFileName = $routerName.'_'.$timestamp.'.rsc';
                    file_put_contents(base_path('backups/'.$localFileName), $configText);
                } else {
                    $warnings[] = '.rsc export returned empty — SSH may have timed out. Only .backup was created.';
                }
            } catch (\Exception $e) {
                $warnings[] = '.rsc export failed: '.$e->getMessage();
            }
        } else {
            $warnings[] = '.rsc mirror skipped — requires SSH. Only .backup was created on router.';
        }

        // Invalidate cached file lists so BackupManager sees fresh data immediately
        $this->invalidateRouterCache($routerName);

        $msg = "✓ {$backupName}.backup saved on router.";
        if ($localFileName) {
            $msg .= " ✓ {$localFileName} mirrored locally.";
        }

        return ['success' => true, 'message' => $msg, 'warnings' => $warnings];
    }
}
