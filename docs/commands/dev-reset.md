# dev:reset

Resets the development environment to a clean state by clearing caches, dropping/recreating the database, running migrations, and loading fixtures.

## Usage

```bash
php bin/console dev:reset
```

## Description

The `dev:reset` command provides a quick way to reset your development environment to a pristine state. It's particularly useful when switching between branches with different database schemas or when you need to start fresh with clean data.

⚠️ **WARNING**: This command will **permanently delete all data** in your database. Use only in development environments.

## What It Does

The command performs the following steps in order:

1. **Clears Cache** - Removes all cached files
2. **Drops Database** - Completely removes the database
3. **Creates Database** - Creates a fresh database
4. **Runs Migrations** - Executes all migrations to set up schema
5. **Loads Fixtures** - Populates database with test data (if available)

## Features

- **Interactive Confirmation**: Prompts for confirmation before proceeding
- **Step-by-Step Progress**: Shows clear progress for each step
- **Error Handling**: Reports errors if any step fails
- **Automatic Cleanup**: Handles cleanup even if steps fail

## Output Example

```
Development Environment Reset
==============================

⚠ WARNING: This command will permanently delete all data in your database.
Are you sure you want to continue? (yes/no) [no]:
> yes

Step 1/5: Clearing cache...
[OK] Cache cleared successfully

Step 2/5: Dropping database...
[OK] Database dropped

Step 3/5: Creating database...
[OK] Database created

Step 4/5: Running migrations...
[OK] Migrations executed successfully

Step 5/5: Loading fixtures...
[OK] Fixtures loaded successfully

[OK] Development environment has been reset successfully!
```

## Use Cases

### Switching Branches

Reset database when switching to a branch with different migrations:
```bash
git checkout feature/new-schema
composer install
php bin/console dev:reset
```

### After Pulling Major Changes

Reset environment after pulling database schema changes:
```bash
git pull origin main
php bin/console dev:reset
```

### Starting Fresh

Clean slate for testing or development:
```bash
php bin/console dev:reset
```

### Testing Migrations

Verify migrations work correctly from scratch:
```bash
php bin/console dev:reset
php bin/console dev:check
```

## Safety Features

### Confirmation Prompt

The command requires explicit confirmation:
```
Are you sure you want to continue? (yes/no) [no]:
```

Responses:
- Type `yes` to proceed
- Type `no` or press Enter to cancel
- Any other input cancels the operation

### Environment Detection

While the command can run in any environment, it's designed for development only. Always verify your environment before running:
```bash
# Check current environment
php bin/console debug:container --env-var=APP_ENV
```

## What Gets Reset

### Deleted
- ✗ All database tables and data
- ✗ All cache files
- ✗ Compiled container
- ✗ Route cache
- ✗ Twig cache

### Preserved
- ✓ Source code files
- ✓ Configuration files (.env, .env.local)
- ✓ Uploaded files (if stored outside database)
- ✓ Log files
- ✓ Vendor dependencies

## Common Issues and Solutions

### Database Already Dropped
```
[ERROR] Database does not exist
```
This is usually safe to ignore - the command will continue with database creation.

### Migration Errors
```
[ERROR] Migration failed
```
**Solution**:
- Check migration files for syntax errors
- Ensure all migration dependencies are met
- Review migration order

### Fixture Loading Fails
```
[ERROR] Fixtures could not be loaded
```
**Solutions**:
- Check if fixtures are properly configured
- Verify fixture class syntax
- Ensure fixture dependencies are met
- Skip fixtures by manually running steps:
```bash
php bin/console cache:clear
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
```

### Permission Denied
```
[ERROR] Permission denied: var/cache
```
**Solution**:
```bash
# Linux/Mac
chmod -R 777 var/cache var/log

# Windows - Check folder permissions
```

## Step-by-Step Breakdown

### Step 1: Clear Cache
Equivalent to:
```bash
php bin/console cache:clear
```

### Step 2: Drop Database
Equivalent to:
```bash
php bin/console doctrine:database:drop --force --if-exists
```

### Step 3: Create Database
Equivalent to:
```bash
php bin/console doctrine:database:create
```

### Step 4: Run Migrations
Equivalent to:
```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

### Step 5: Load Fixtures
Equivalent to:
```bash
php bin/console doctrine:fixtures:load --no-interaction
```

## Tips and Best Practices

### Before Running

1. **Backup Important Data**: If you have test data you want to keep
2. **Commit Changes**: Ensure your work is committed
3. **Check Environment**: Verify you're in development
4. **Close DB Connections**: Close any database GUI tools

### After Running

1. **Verify Setup**: Run `php bin/console dev:check`
2. **Test Application**: Visit your app to ensure it works
3. **Check Fixtures**: Verify test data loaded correctly

### Workflow Integration

Create an alias for common workflow:
```bash
# .bashrc or .zshrc
alias reset-dev='php bin/console dev:reset && php bin/console dev:check'
```

### Team Usage

Document when to use dev:reset in your team guidelines:
- After pulling main branch
- Before starting new features
- When database feels "corrupted"
- After resolving migration conflicts

## CI/CD Integration

Example in CI pipeline:
```yaml
setup:
  script:
    - cp .env.test .env.local
    - composer install
    - php bin/console dev:reset --no-interaction
```

**Note**: In CI, you may want to add a `--no-interaction` option to skip confirmation (would require implementing this option in the command).

## Exit Codes

- **0**: Environment reset successfully
- **1**: Reset failed or user cancelled

## Alternative: Manual Reset

If you need more control, run steps individually:

```bash
# 1. Clear cache
php bin/console cache:clear

# 2. Reset database
php bin/console doctrine:database:drop --force --if-exists
php bin/console doctrine:database:create

# 3. Run migrations
php bin/console doctrine:migrations:migrate --no-interaction

# 4. Load fixtures (optional)
php bin/console doctrine:fixtures:load --no-interaction

# 5. Verify
php bin/console dev:check
```

## Related Commands

- **[dev:check](dev-check.md)** - Verify environment after reset
- **[dev:test](dev-test.md)** - Run tests after reset

## Source Code

Command implementation: `src/Command/DevResetCommand.php`

---

[← dev:check](dev-check.md) | [Back to Commands](README.md) | [dev:test →](dev-test.md)
