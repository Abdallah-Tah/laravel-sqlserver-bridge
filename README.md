# Laravel SQL Server Bridge

A Laravel database driver that enables SQL Server connectivity for PHP 8.4+ through a .NET bridge service. This package provides a workaround for the lack of native `sqlsrv` extension support in PHP 8.4.

## Features

- ✅ **PHP 8.4+ Support** - Works with PHP versions that don't have native sqlsrv extension
- ✅ **Laravel 11+** - Fully compatible with modern Laravel applications
- ✅ **Eloquent Support** - Use Eloquent ORM just like with native drivers
- ✅ **Query Builder** - Full Laravel Query Builder support with SQL Server grammar
- ✅ **Multiple Connections** - Support for multiple SQL Server databases
- ✅ **Comprehensive Logging** - Built-in query and error logging
- ✅ **Production Ready** - Battle-tested in production environments

## Requirements

- PHP 8.2, 8.3, or 8.4
- Laravel 11.0+
- .NET 10+ Runtime (for the bridge service)
- SQL Server database

## Installation

### Step 1: Install the Package

```bash
composer require amohamed/laravel-sqlserver-bridge
```

### Step 2: Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=sqlserver-bridge-config
```

This creates `config/sqlserver-bridge.php` where you can customize bridge settings.

### Step 3: Set Up the .NET Bridge Service

The package requires a .NET service to communicate with SQL Server. You can:

**Option A: Download Pre-built Binary**
```bash
# Download from releases
# Extract and run
dotnet SqlBridgeService.dll
```

**Option B: Build from Source**
```bash
cd /path/to/SqlBridgeService
dotnet build
dotnet run --project SqlBridgeService
```

The bridge service will listen on `http://localhost:5152` by default.

### Step 4: Configure Database Connection

Add to your `config/database.php`:

```php
'connections' => [

    'sqlsrv' => [
        'driver' => 'sqlbridge',  // Use 'sqlbridge' instead of 'sqlsrv'
        'bridge_url' => env('SQLBRIDGE_URL', 'http://localhost:5152'),
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', '1433'),
        'database' => env('DB_DATABASE', 'master'),
        'username' => env('DB_USERNAME', 'sa'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8',
        'prefix' => '',
    ],

    // Multiple connections example
    'sqlsrv_secondary' => [
        'driver' => 'sqlbridge',
        'bridge_url' => env('SQLBRIDGE_URL', 'http://localhost:5152'),
        'host' => env('DB_HOST_2', 'localhost'),
        'database' => env('DB_DATABASE_2', 'my_database'),
        'username' => env('DB_USERNAME_2', 'sa'),
        'password' => env('DB_PASSWORD_2', ''),
        'charset' => 'utf8',
        'prefix' => '',
    ],

],
```

### Step 5: Configure Environment Variables

Add to your `.env`:

```env
# Primary SQL Server Connection
DB_CONNECTION=sqlsrv
DB_HOST=your-sql-server-host
DB_PORT=1433
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Bridge Service URL
SQLBRIDGE_URL=http://localhost:5152

# Optional: Secondary Connection
DB_HOST_2=another-sql-server
DB_DATABASE_2=another_database
DB_USERNAME_2=another_username
DB_PASSWORD_2=another_password
```

## Usage

### Using Eloquent ORM

Use Eloquent models exactly as you would with the native driver:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $connection = 'sqlsrv'; // Use the bridge connection
    protected $table = 'users';
}

// Usage
$users = User::where('active', true)->get();
$user = User::find(1);
$user->update(['name' => 'John Doe']);
```

### Using Query Builder

```php
use Illuminate\Support\Facades\DB;

// Select
$users = DB::connection('sqlsrv')
    ->table('users')
    ->where('deleted_at', null)
    ->orderBy('created_at', 'desc')
    ->get();

// Insert
DB::connection('sqlsrv')
    ->table('users')
    ->insert([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'created_at' => now(),
    ]);

// Update
DB::connection('sqlsrv')
    ->table('users')
    ->where('id', 1)
    ->update(['name' => 'Updated Name']);

// Delete
DB::connection('sqlsrv')
    ->table('users')
    ->where('id', 1)
    ->delete();
```

### Raw Queries

```php
// Select
$results = DB::connection('sqlsrv')
    ->select("SELECT TOP 10 * FROM users WHERE active = ?", [1]);

// Insert/Update/Delete
$affected = DB::connection('sqlsrv')
    ->statement("UPDATE users SET active = 1 WHERE id = ?", [1]);
```

### Multiple Connections

```php
// Primary connection
$users = DB::connection('sqlsrv')->table('users')->get();

// Secondary connection
$products = DB::connection('sqlsrv_secondary')->table('products')->get();

// In Models
class Product extends Model
{
    protected $connection = 'sqlsrv_secondary';
}
```

## Configuration

### Bridge Service Configuration

The `.env` variables:

```env
# Bridge URL (where the .NET service is running)
SQLBRIDGE_URL=http://localhost:5152

# Request timeout in seconds
SQLBRIDGE_TIMEOUT=30

# Enable query logging
SQLBRIDGE_LOG_QUERIES=true

# Log level: debug, info, warning, error
SQLBRIDGE_LOG_LEVEL=debug
```

### Running Bridge as a Service

**Windows Service:**
```powershell
# Create Windows Service
sc create SqlBridgeService binPath="C:\path\to\SqlBridgeService.exe"
sc start SqlBridgeService
```

**Linux Systemd:**
```bash
# Create /etc/systemd/system/sqlbridge.service
[Unit]
Description=SQL Server Bridge Service
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/opt/sqlbridge
ExecStart=/usr/bin/dotnet /opt/sqlbridge/SqlBridgeService.dll
Restart=always

[Install]
WantedBy=multi-user.target

# Enable and start
sudo systemctl enable sqlbridge
sudo systemctl start sqlbridge
```

## .NET Bridge Service

The bridge service is a lightweight ASP.NET Core application that uses `Microsoft.Data.SqlClient` to execute SQL Server queries.

### Endpoints

- `GET /api/sql/test` - Health check
- `POST /api/sql/query` - Execute SELECT queries
- `POST /api/sql/execute` - Execute INSERT/UPDATE/DELETE

### Building the Bridge

The bridge service source code is included in this package:

```bash
cd bridge-service
dotnet build
dotnet publish -c Release -o publish
```

## Troubleshooting

### Connection Errors

**Bridge not accessible:**
```bash
# Test bridge connectivity
curl http://localhost:5152/api/sql/test
```

**Expected response:** `"SQL Bridge is running."`

### Query Errors

Check Laravel logs at `storage/logs/laravel.log` for detailed error messages:

```bash
tail -f storage/logs/laravel.log | grep SqlServerBridge
```

### Common Issues

**"could not find driver"**
- Ensure `driver` is set to `'sqlbridge'` in database config
- Clear config cache: `php artisan config:clear`

**"Connection refused"**
- Verify bridge service is running
- Check SQLBRIDGE_URL is correct

**"Incorrect syntax near 'limit'"**
- This shouldn't happen - the package uses SQL Server grammar
- If you see this, clear all caches

## Performance Considerations

- **Connection Pooling**: The .NET bridge automatically pools SQL Server connections
- **Caching**: Consider caching frequently accessed data
- **Query Optimization**: Use eager loading and query optimization as usual

## Security

- **HTTPS**: Use HTTPS for the bridge in production
- **Authentication**: Add API authentication to bridge endpoints
- **Network**: Run bridge on internal network only
- **Credentials**: Never commit database credentials

## Testing

```bash
# Run package tests
composer test

# Run with coverage
composer test:coverage
```

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Credits

- **Author**: Abdallah Mohamed (abdal_cascad@hotmail.com)
- **Contributors**: [All Contributors](../../contributors)

## Support

- **Issues**: [GitHub Issues](https://github.com/amohamed/laravel-sqlserver-bridge/issues)
- **Documentation**: [Full Documentation](https://your-docs-site.com)

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for recent changes.

## Roadmap

- [ ] Add support for transactions
- [ ] Implement connection retry logic
- [ ] Add query result caching
- [ ] Create Artisan commands for bridge management
- [ ] Add comprehensive test suite

---

Made with ❤️ for the Laravel community
