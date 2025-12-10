<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SQL Server Bridge URL
    |--------------------------------------------------------------------------
    |
    | The URL of your .NET SQL Server Bridge service. This service acts as
    | an intermediary between Laravel and SQL Server, enabling PHP 8.4+
    | to communicate with SQL Server databases.
    |
    */

    'url' => env('SQLBRIDGE_URL', 'http://localhost:5152'),

    /*
    |--------------------------------------------------------------------------
    | Default Timeout
    |--------------------------------------------------------------------------
    |
    | The default timeout in seconds for HTTP requests to the bridge service.
    |
    */

    'timeout' => env('SQLBRIDGE_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Enable Query Logging
    |--------------------------------------------------------------------------
    |
    | When enabled, all SQL queries sent through the bridge will be logged
    | to your Laravel log files. Useful for debugging.
    |
    */

    'log_queries' => env('SQLBRIDGE_LOG_QUERIES', true),

    /*
    |--------------------------------------------------------------------------
    | Log Level
    |--------------------------------------------------------------------------
    |
    | The log level to use when logging queries and errors.
    | Options: 'debug', 'info', 'warning', 'error'
    |
    */

    'log_level' => env('SQLBRIDGE_LOG_LEVEL', 'debug'),

];
