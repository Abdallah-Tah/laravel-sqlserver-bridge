<?php

namespace YourVendor\LaravelSqlServerBridge\Database;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Grammars\SqlServerGrammar as QueryGrammar;
use Illuminate\Database\Schema\Grammars\SqlServerGrammar as SchemaGrammar;
use Illuminate\Database\Query\Processors\SqlServerProcessor;

class SqlBridgeConnection extends Connection
{
    protected SqlBridgePDO $bridgePdo;

    public function __construct(SqlBridgePDO $pdo, array $config)
    {
        parent::__construct(fn() => null, $config['database'] ?? '', $config['prefix'] ?? '', $config);
        $this->bridgePdo = $pdo;

        // Force SQL Server grammar usage
        $this->useDefaultQueryGrammar();
        $this->useDefaultSchemaGrammar();
        $this->useDefaultPostProcessor();
    }

    /**
     * Get the default query grammar instance.
     */
    protected function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new QueryGrammar);
    }

    /**
     * Get the default schema grammar instance.
     */
    protected function getDefaultSchemaGrammar()
    {
        return $this->withTablePrefix(new SchemaGrammar);
    }

    /**
     * Get the default post processor instance.
     */
    protected function getDefaultPostProcessor()
    {
        return new SqlServerProcessor;
    }

    public function select($query, $bindings = [], $useReadPdo = true)
    {
        $sql = $this->prepareQuery($query, $bindings);
        $result = $this->bridgePdo->query($sql);

        // Convert to objects like Laravel expects
        return array_map(fn($row) => (object) $row, $result);
    }

    public function selectOne($query, $bindings = [], $useReadPdo = true)
    {
        $records = $this->select($query, $bindings, $useReadPdo);
        return array_shift($records);
    }

    public function statement($query, $bindings = [])
    {
        $sql = $this->prepareQuery($query, $bindings);
        $this->bridgePdo->execute($sql);
        return true;
    }

    public function affectingStatement($query, $bindings = [])
    {
        $sql = $this->prepareQuery($query, $bindings);
        return $this->bridgePdo->execute($sql);
    }

    public function unprepared($query)
    {
        return $this->bridgePdo->execute($query);
    }

    protected function prepareQuery(string $query, array $bindings)
    {
        if (empty($bindings)) {
            return trim($query);
        }

        foreach ($bindings as $value) {
            if ($value === null) {
                $value = 'NULL';
            } elseif (is_numeric($value)) {
                // Keep numeric values as-is
                $value = $value;
            } elseif (is_bool($value)) {
                $value = $value ? '1' : '0';
            } else {
                // Use SQL Server's proper escaping (double single quotes) and N prefix for Unicode
                $value = "N'" . str_replace("'", "''", $value) . "'";
            }
            $query = preg_replace('/\?/', $value, $query, 1);
        }

        return trim($query);
    }

    public function getDriverName()
    {
        return 'sqlbridge';
    }
}
