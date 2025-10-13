# Development Commands

This directory contains documentation for all custom development commands available in Taskio.

## Available Commands

### Environment Management

- **[dev:check](dev-check.md)** - Verify development environment status
  - Database connectivity and tables
  - Cache status and permissions
  - Environment configuration and PHP version
  - Dependencies (Composer, NPM)
  - File permissions

- **[dev:reset](dev-reset.md)** - Reset development environment to clean state
  - Clear cache
  - Drop and recreate database
  - Run migrations
  - Load fixtures

### Testing

- **[dev:test](dev-test.md)** - Run PHPUnit tests with advanced options
  - Filter by test type (unit, functional, integration)
  - Generate coverage reports
  - Parallel execution
  - TestDox format output

## Quick Reference

```bash
# Check environment
php bin/console dev:check

# Reset environment
php bin/console dev:reset

# Run all tests
php bin/console dev:test

# Run unit tests with readable output
php bin/console dev:test --type=unit --testdox

# Run tests with coverage
php bin/console dev:test --coverage
```

## Command Locations

All development commands are located in:
```
src/Command/
├── DevCheckCommand.php
├── DevResetCommand.php
└── DevTestCommand.php
```

## Getting Help

For detailed help on any command:
```bash
php bin/console <command> --help
```

To list all available dev commands:
```bash
php bin/console list dev
```

---

[← Back to Documentation](../README.md)
