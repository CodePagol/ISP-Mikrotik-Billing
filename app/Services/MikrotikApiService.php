<?php

namespace App\Services;

use Exception;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

class MikrotikApiService
{
    protected $client;

    public function __construct($host, $port, $username, $password, int $timeout = 5)
    {
        try {
            $config = new Config([
                'host' => $host,
                'user' => $username,
                'pass' => $password,
                'port' => $port ?? 8728,
                'timeout' => $timeout,
                'attempts' => 1,
            ]);

            $this->client = new Client($config);
        } catch (Exception $e) {
            throw new Exception('Failed to connect to MikroTik: '.$e->getMessage());
        }
    }

    /**
     * Execute a MikroTik API command
     *
     * @param  string  $command  The RouterOS command (e.g., '/system/resource/print')
     * @param  array  $params  Optional command parameters
     * @return array|string
     */
    public function executeCommand(string $command, array $params = [], array $filters = [])
    {
        try {
            $query = new Query($command);

            // Add filters (?) first as required by RouterOS API for some commands
            foreach ($filters as $key => $value) {
                $query->where($key, $value);
            }

            // Add parameters (=)
            foreach ($params as $key => $value) {
                $query->equal($key, $value);
            }

            $response = $this->client->query($query)->read();

            return $response;
        } catch (Exception $e) {
            return 'Error: '.$e->getMessage();
        }
    }
}
