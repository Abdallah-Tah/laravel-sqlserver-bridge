# Changelog

All notable changes to `laravel-sqlserver-bridge` will be documented in this file.

## [1.0.0] - 2024-12-10

### Added
- Initial release
- Support for PHP 8.2, 8.3, and 8.4
- Laravel 11+ compatibility
- Full Eloquent ORM support
- Query Builder with SQL Server grammar
- Multiple database connections support
- Comprehensive logging
- .NET bridge service for SQL Server connectivity
- Configuration file with customizable options
- Detailed README with examples
- MIT License

### Features
- Automatic SQL Server grammar conversion (TOP instead of LIMIT)
- Proper SQL Server identifier quoting with square brackets
- Unicode string support (N prefix)
- Connection pooling via .NET bridge
- Error handling and logging
- Health check endpoint

### Infrastructure
- Service provider with auto-discovery
- Publishable configuration
- PSR-4 autoloading
- Composer package structure

## [Unreleased]

### Planned
- Transaction support
- Connection retry logic
- Query result caching
- Artisan commands for bridge management
- Comprehensive test suite
- Performance optimizations
