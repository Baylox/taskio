# dev:check

Performs comprehensive checks on your development environment to ensure everything is properly configured and working.

## Usage

```bash
php bin/console dev:check
```

## Description

The `dev:check` command validates your development environment by running multiple checks across different categories. It provides a detailed report showing which components are working correctly and which need attention.

## What It Checks

### 1. Database Connection
- **Connection**: Verifies database connectivity
- **Tables**: Counts the number of tables in the database
- **Migrations**: Lists available migration files

### 2. Cache Status
- **Directory**: Validates cache directory existence
- **Dev Cache**: Shows cache size
- **Writable**: Verifies write permissions

### 3. Environment Configuration
- **Mode**: Displays current environment (dev/test/prod)
- **Debug**: Shows debug mode status
- **PHP Version**: Validates PHP version (requires 8.3.0+)
- **Memory Limit**: Displays PHP memory limit

### 4. Dependencies
- **Composer**: Checks composer.lock status and age
- **Vendor**: Verifies vendor directory exists
- **Node Modules**: Checks node_modules directory
- **NPM Lock**: Validates package-lock.json existence

### 5. File Permissions
- **Cache Directory**: Verifies var/cache is writable
- **Log Directory**: Verifies var/log is writable

## Features

- **Real-time Progress**: Shows progress percentage as checks are performed
- **Detailed Summary**: Displays results in a clear table format
- **Status Indicators**: Visual checkmarks (✓) and crosses (✗) for each check
- **Exit Codes**: Returns 0 on success, 1 if any check fails

## Output Example

```
Development Environment Check
=============================

Database Connection
-------------------
Progress: 100% (17/17)

Summary
-------
┌─────────────┬──────────────┬────────┬─────────────────────────────┐
│ Category    │ Check        │ Status │ Details                     │
├─────────────┼──────────────┼────────┼─────────────────────────────┤
│ Database    │ Connected    │ ✓      │ Database: cgeagency_test    │
│ Database    │ Tables       │ ✓      │ 15 tables found             │
│ Database    │ Migrations   │ ✓      │ 5 migration(s) available    │
│ Cache       │ Directory    │ ✓      │ Cache directory exists      │
│ Cache       │ Dev Cache    │ ✓      │ 2.45 MB                     │
│ Cache       │ Writable     │ ✓      │ Cache directory is writable │
│ Environment │ Mode         │ ✓      │ Environment: dev            │
│ Environment │ Debug        │ ✓      │ Debug: enabled              │
│ Environment │ .env.local   │ ✓      │ .env.local file found       │
│ Environment │ PHP Version  │ ✓      │ PHP 8.4.12                  │
│ Environment │ Memory Limit │ ✓      │ 512M                        │
│ Dependencies│ Composer     │ ✓      │ composer.lock is up to date │
│ Dependencies│ Vendor       │ ✓      │ Vendor directory exists     │
│ Dependencies│ Node Modules │ ✓      │ node_modules directory...   │
│ Dependencies│ NPM Lock     │ ✓      │ package-lock.json exists    │
│ Permissions │ Cache...     │ ✓      │ var/cache is writable       │
│ Permissions │ Log...       │ ✓      │ var/log is writable         │
└─────────────┴──────────────┴────────┴─────────────────────────────┘

[OK] All checks passed! 100.0% (17/17)
```

## Use Cases

### Daily Development

Run at the start of your day to verify everything is working:
```bash
php bin/console dev:check
```

### After Pulling Changes

Check environment after pulling from version control:
```bash
git pull
composer install
npm install
php bin/console dev:check
```

### CI/CD Integration

Add to your CI pipeline to verify environment setup:
```yaml
test:
  script:
    - php bin/console dev:check
    - php bin/console dev:test
```

### Troubleshooting

When something isn't working, run dev:check to identify the issue:
```bash
php bin/console dev:check
```

## Common Issues and Solutions

### Database Connection Failed
```
✗ Database Connection: Connection refused
```
**Solution**:
- Check if your database server is running
- Verify DATABASE_URL in .env.local
- Ensure database credentials are correct

### No Tables Found
```
✗ Database Tables: No tables found. Run migrations?
```
**Solution**:
```bash
php bin/console doctrine:migrations:migrate
```

### Cache Not Writable
```
✗ Cache Writable: Cache directory is not writable
```
**Solution**:
```bash
# Linux/Mac
chmod -R 777 var/cache var/log

# Windows - Check folder permissions in Explorer
```

### PHP Version Too Old
```
✗ PHP Version: PHP 8.2.0
```
**Solution**: Upgrade PHP to version 8.3.0 or higher

### Missing Dependencies
```
✗ Vendor: Vendor directory not found. Run composer install
```
**Solution**:
```bash
composer install
```

## Exit Codes

- **0**: All checks passed successfully
- **1**: One or more checks failed

Use exit codes in scripts:
```bash
#!/bin/bash
if php bin/console dev:check; then
    echo "Environment is ready!"
else
    echo "Please fix the issues above"
    exit 1
fi
```

## Tips

- Run `dev:check` regularly to catch configuration issues early
- Use it as a pre-commit or pre-deployment check
- Add it to your team's onboarding documentation
- Include it in your CI/CD pipeline for consistency

## Related Commands

- **[dev:reset](dev-reset.md)** - Reset environment if checks fail
- **[dev:test](dev-test.md)** - Run tests after verifying environment

## Source Code

Command implementation: `src/Command/DevCheckCommand.php`

---

[← Back to Commands](README.md) | [dev:reset →](dev-reset.md)
