<?php

namespace Amohamed\LaravelSqlServerBridge\Database;

use Illuminate\Database\Connectors\ConnectorInterface;

class SqlBridgeConnector implements ConnectorInterface
{
    public function connect(array $config)
    {
        $apiUrl = $config['bridge_url'] ?? config('sqlserver-bridge.url', 'http://localhost:5152');

        $pdo = new SqlBridgePDO($apiUrl, $config);

        return new SqlBridgeConnection($pdo, $config);
    }
}
