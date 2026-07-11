<?php

namespace App\Services;

use Exception;
use phpseclib3\Net\SSH2;

class MikrotikSSHService
{
    protected SSH2 $ssh;

    public function __construct(string $host, int $port, string $username, string $password, int $timeout = 5)
    {
        $this->ssh = new SSH2($host, $port, $timeout);

        // Append "+ct" to disable console colors and terminal configurations if not already present
        $sshUser = str_ends_with($username, '+ct') ? $username : $username . '+ct';

        if (! $this->ssh->login($sshUser, $password)) {
            // Fallback to login without "+ct" if it fails
            if (! $this->ssh->login($username, $password)) {
                throw new Exception("SSH login failed for {$host}");
            }
        }
    }

    /**
     * Execute a raw SSH command and return the raw output string.
     */
    public function executeCommand(string $command): string
    {
        try {
            return (string) $this->ssh->exec($command);
        } catch (\RuntimeException $e) {
            return 'Error: '.$e->getMessage();
        }
    }

    /**
     * Execute a RouterOS command that requires an interactive PTY session.
     *
     * Some RouterOS commands (notably /system backup save) are silently ignored
     * when sent via SSH exec without a pseudo-terminal. This method allocates a
     * PTY, waits for the interactive shell prompt (consuming the login banner),
     * sends the command, waits for the router to finish, then reads the response.
     *
     * @throws Exception on SSH failure
     */
    public function executePtyCommand(string $command, int $waitSeconds = 5): string
    {
        $this->ssh->enablePTY();

        // Wait for the RouterOS prompt, consuming the full login banner.
        // Without a regex here, banner log lines bleed into the command output.
        $this->ssh->read('/\[.+\@.+\]\s*>/');

        // Send the command
        $this->ssh->write($command."\n");

        // Give RouterOS time to execute (e.g. write backup file to flash)
        sleep($waitSeconds);

        // Read until the next RouterOS prompt — this is the command's own output
        $output = $this->ssh->read('/\[.+\@.+\]\s*>/');

        $this->ssh->write("/quit\n");

        return $output ?? '';
    }

    public function executeCommandParsable(string $command): array
    {
        $output = trim($this->ssh->exec($command));

        if (empty($output)) {
            return [];
        }

        // Clean ANSI escape sequences
        $output = preg_replace('/[\x1b\x9b][[()#;?]*(?:[0-9]{1,4}(?:;[0-9]{0,4})*)?[0-9A-ORZcf-nqry=><]/', '', $output);
        $output = trim($output);

        if (empty($output)) {
            return [];
        }

        if ($this->isColonFormat($output)) {
            return $this->parseColonFormat($output);
        }

        if ($this->isTerseFormat($output)) {
            return $this->parseTerseFormat($output);
        }

        return $this->parseListFormat($output);
    }

    // ─── Format Detectors ─────────────────────────────────────────────────────

    protected function isColonFormat(string $output): bool
    {
        return (bool) preg_match('/^\s*[a-zA-Z][a-zA-Z0-9\-]*:\s*.+$/m', $output);
    }

    protected function isTerseFormat(string $output): bool
    {
        return (bool) preg_match('/^\s*\d+\s+.*[^\s]=[^\s].*$/m', $output);
    }

    // ─── Format Parsers ───────────────────────────────────────────────────────

    /**
     * Normalize parsed values to match the API data types and units.
     */
    protected function normalizeValue(string $key, string $value): mixed
    {
        $value = trim($value);

        // Normalize memory and hdd size keys to bytes
        $sizeKeys = [
            'free-memory', 'total-memory', 'free-hdd-space', 'total-hdd-space',
            'memory-size', 'hdd-size', 'active-flow-bytes', 'inactive-flow-bytes',
        ];
        if (in_array($key, $sizeKeys, true)) {
            if (preg_match('/^([\d\.]+)\s*(?:(GiB|MiB|KiB|B|GB|MB|KB)|(g|m|k))?$/i', $value, $matches)) {
                $number = (float) $matches[1];
                $unit = strtolower($matches[2] ?? $matches[3] ?? '');

                switch ($unit) {
                    case 'gib':
                    case 'gb':
                    case 'g':
                        return (int) ($number * 1024 * 1024 * 1024);
                    case 'mib':
                    case 'mb':
                    case 'm':
                        return (int) ($number * 1024 * 1024);
                    case 'kib':
                    case 'kb':
                    case 'k':
                        return (int) ($number * 1024);
                    case 'b':
                    default:
                        return (int) $number;
                }
            }
        }

        // Normalize CPU frequency
        if ($key === 'cpu-frequency') {
            if (preg_match('/^(\d+)\s*(?:MHz)?$/i', $value, $matches)) {
                return (int) $matches[1];
            }
        }

        // Normalize CPU load and bad-blocks percentage
        if ($key === 'cpu-load' || $key === 'bad-blocks') {
            if (preg_match('/^([\d\.]+)\s*%?$/', $value, $matches)) {
                return str_contains($matches[1], '.') ? (float) $matches[1] : (int) $matches[1];
            }
        }

        // Normalize CPU count and other integer fields
        if ($key === 'cpu-count' || $key === 'write-sect-since-reboot' || $key === 'write-sect-total') {
            if (ctype_digit($value)) {
                return (int) $value;
            }
        }

        // Normalize temperature (e.g. 35C -> 35) and voltage (e.g. 12.1V -> 12.1)
        if ($key === 'temperature') {
            if (preg_match('/^([\d\.]+)\s*C?$/i', $value, $matches)) {
                return str_contains($matches[1], '.') ? (float) $matches[1] : (int) $matches[1];
            }
        }
        if ($key === 'voltage') {
            if (preg_match('/^([\d\.]+)\s*V?$/i', $value, $matches)) {
                return str_contains($matches[1], '.') ? (float) $matches[1] : (int) $matches[1];
            }
        }

        return $value;
    }

    protected function parseColonFormat(string $output): array
    {
        $data = [];
        foreach (explode("\n", $output) as $line) {
            if (str_contains($line, ':')) {
                [$key, $value] = array_map('trim', explode(':', $line, 2));
                $data[$key] = $this->normalizeValue($key, $value);
            }
        }

        return [$data];
    }

    protected function parseTerseFormat(string $output): array
    {
        $entries = [];
        foreach (preg_split('/\r\n|\r|\n/', trim($output)) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $entry = [];

            if (preg_match('/^\s*(\d+)\s*(?:"([^"]+)"|([^\s]+))?/', $line, $m)) {
                $entry['id'] = $m[1];
                $entry['name'] = $m[2] ?? $m[3] ?? null;
                $entry['status'] = str_contains($line, 'X') ? 'disable' : 'active';
            }

            preg_match_all('/([^\s=]+)=("([^"]*)"|[^\s"].*?(?=\s+[^\s=]+=|$))/', $line, $matches, PREG_SET_ORDER);
            foreach ($matches as $kv) {
                $key = $kv[1];
                $value = isset($kv[3]) && $kv[3] !== '' ? $kv[3] : trim($kv[2], '"');
                $entry[$key] = $this->normalizeValue($key, $value);
            }

            $entries[] = $entry;
        }

        return $entries;
    }

    protected function parseListFormat(string $output): array
    {
        return array_values(array_filter(array_map('trim', explode("\n", $output))));
    }
}
