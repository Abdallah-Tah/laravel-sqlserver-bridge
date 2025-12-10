<?php

namespace YourVendor\LaravelSqlServerBridge\Database;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SqlBridgePDO
{
    protected string $baseUrl;
    protected array $config;

    public function __construct(string $baseUrl, array $config)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->config = $config;
    }

    public function query(string $sql)
    {
        Log::debug('SqlServerBridge: Executing query', [
            'sql' => substr($sql, 0, 200),
            'database' => $this->config['database'] ?? 'unknown'
        ]);

        $response = Http::timeout(30)->post("{$this->baseUrl}/api/sql/query", [
            'host' => $this->config['host'],
            'database' => $this->config['database'],
            'username' => $this->config['username'],
            'password' => $this->config['password'],
            'sql' => $sql,
        ]);

        if (!$response->successful()) {
            $error = $response->json('message', 'Unknown error');
            Log::error('SqlServerBridge: Query failed', [
                'status' => $response->status(),
                'error' => $error,
                'response_body' => $response->body(),
                'sql' => substr($sql, 0, 200)
            ]);
            throw new Exception("SQL Server Bridge query failed: {$error}");
        }

        $data = $response->json();
        return $data['data'] ?? $data;
    }

    public function execute(string $sql)
    {
        Log::debug('SqlServerBridge: Executing command', [
            'sql' => substr($sql, 0, 200),
            'database' => $this->config['database'] ?? 'unknown'
        ]);

        $response = Http::timeout(30)->post("{$this->baseUrl}/api/sql/execute", [
            'host' => $this->config['host'],
            'database' => $this->config['database'],
            'username' => $this->config['username'],
            'password' => $this->config['password'],
            'sql' => $sql,
        ]);

        if (!$response->successful()) {
            $error = $response->json('message', 'Unknown error');
            Log::error('SqlServerBridge: Execute failed', [
                'status' => $response->status(),
                'error' => $error,
                'sql' => substr($sql, 0, 200)
            ]);
            throw new Exception("SQL Server Bridge execute failed: {$error}");
        }

        $data = $response->json();
        return $data['rowsAffected'] ?? 0;
    }
}
