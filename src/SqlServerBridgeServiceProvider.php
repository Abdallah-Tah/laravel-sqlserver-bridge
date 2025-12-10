<?php

namespace YourVendor\LaravelSqlServerBridge;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use YourVendor\LaravelSqlServerBridge\Database\SqlBridgeConnector;

class SqlServerBridgeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/sqlserver-bridge.php', 'sqlserver-bridge'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/sqlserver-bridge.php' => config_path('sqlserver-bridge.php'),
        ], 'sqlserver-bridge-config');

        // Register the SQL Server Bridge driver
        DB::extend('sqlbridge', function ($config, $name) {
            $connector = new SqlBridgeConnector();
            return $connector->connect($config);
        });
    }
}
