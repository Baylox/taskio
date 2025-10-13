# dev:test

Run PHPUnit tests with advanced filtering, coverage reporting, and parallel execution options.

## Usage

```bash
php bin/console dev:test [options]
```

## Description

The `dev:test` command provides a simplified interface for running PHPUnit tests with commonly used options. It automatically handles platform-specific differences (Windows/Linux) and provides clear feedback about test execution.

## Options

| Option | Shortcut | Description |
|--------|----------|-------------|
| `--type=TYPE` | `-t` | Filter tests by type: `unit`, `functional`, or `integration` |
| `--filter=PATTERN` | `-f` | Filter tests by name pattern (regex) |
| `--group=GROUP` | `-g` | Run tests from a specific PHPUnit group |
| `--coverage` | `-c` | Generate HTML code coverage report |
| `--coverage-text` | - | Display code coverage in console |
| `--parallel` | `-p` | Run tests in parallel (requires paratest) |
| `--stop-on-failure` | `-s` | Stop execution on first test failure |
| `--testdox` | - | Display tests in readable TestDox format |
| `--verbose` | `-v` | Increase output verbosity |

## Features

- **Automatic Platform Detection**: Works on Windows and Linux
- **Configuration Summary**: Shows selected options before execution
- **Execution Timer**: Displays total test execution time
- **Coverage Location**: Shows where HTML reports are saved
- **Real-time Output**: Streams test output as it happens

## Basic Examples

### Run All Tests

```bash
php bin/console dev:test
```

Output:
```
Running PHPUnit Tests
=====================

Test Configuration
------------------

 * Test type: all
 * Execution: Sequential

// Executing: vendor\bin\phpunit.bat

PHPUnit 11.5.36 by Sebastian Bergmann and contributors.

..................................................                50 / 50 (100%)

Time: 00:06.818, Memory: 16.00 MB

OK (50 tests, 113 assertions)

[OK] All tests passed! (Execution time: 14.42s)
```

### Run Unit Tests Only

```bash
php bin/console dev:test --type=unit
# or
php bin/console dev:test -t unit
```

### Run with TestDox Format

```bash
php bin/console dev:test --testdox
```

Output:
```
Account (App\Tests\Unit\Entity\Account)
 ✔ User identifier returns email
 ✔ Password defaults to empty string
 ✔ Get roles defaults to role user
 ✔ Email validation constraints

Board (App\Tests\Unit\Entity\Board)
 ✔ Add lane keeps bidirectional consistency
 ✔ Remove lane clears bidirectional consistency
```

## Filtering Options

### Filter by Test Type

Run specific test suites:

```bash
# Unit tests only
php bin/console dev:test --type=unit

# Functional tests
php bin/console dev:test --type=functional

# Integration tests
php bin/console dev:test --type=integration
```

Test type mapping:
- `unit` → `tests/Unit`
- `functional` → `tests/Functional`
- `integration` → `tests/Integration`

### Filter by Name Pattern

Run tests matching a specific pattern:

```bash
# Run all Board-related tests
php bin/console dev:test --filter=Board

# Run specific test class
php bin/console dev:test --filter=BoardVoterTest

# Run specific test method
php bin/console dev:test --filter=testAdminCanDoEverything

# Use regex patterns
php bin/console dev:test --filter="Board.*Test"
```

### Filter by Group

If your tests use PHPUnit groups:

```php
/**
 * @group security
 */
class BoardVoterTest extends TestCase
{
    // ...
}
```

Run by group:
```bash
php bin/console dev:test --group=security
```

## Coverage Reports

### HTML Coverage Report

Generate a detailed HTML coverage report:

```bash
php bin/console dev:test --coverage
```

Output:
```
[OK] All tests passed! (Execution time: 18.32s)

[!] Coverage report generated in: C:\Env\ask-cge\coverage\index.html
    Open it in your browser to view detailed coverage information.
```

Open the report:
```bash
# Windows
start coverage/index.html

# Linux/Mac
open coverage/index.html
```

### Text Coverage Report

Display coverage in terminal:

```bash
php bin/console dev:test --coverage-text
```

Output includes coverage summary in console.

### Both Coverage Formats

```bash
php bin/console dev:test --coverage --coverage-text
```

## Performance Options

### Parallel Execution

Speed up test execution with parallel processing:

```bash
php bin/console dev:test --parallel
```

**Requirements**: Install paratest
```bash
composer require --dev brianium/paratest
```

If paratest is not installed, the command falls back to sequential execution with a notification.

### Stop on First Failure

Debug failing tests faster:

```bash
php bin/console dev:test --stop-on-failure
# or
php bin/console dev:test -s
```

Execution stops immediately when a test fails, allowing you to fix issues one at a time.

## Combining Options

### Unit Tests with TestDox

```bash
php bin/console dev:test --type=unit --testdox
```

### Filtered Tests with Coverage

```bash
php bin/console dev:test --filter=Board --coverage
```

### Fast Debug Mode

```bash
php bin/console dev:test --type=unit --stop-on-failure -v
```

### Complete Test Run

```bash
php bin/console dev:test --coverage --testdox
```

### Parallel with Stop on Failure

```bash
php bin/console dev:test --parallel --stop-on-failure
```

## Advanced Examples

### Test Specific Feature

```bash
# Test all authentication-related tests
php bin/console dev:test --filter=Auth

# With readable output
php bin/console dev:test --filter=Auth --testdox
```

### Quick Unit Test Check

```bash
php bin/console dev:test -t unit --testdox -s
```

### Full Coverage Analysis

```bash
php bin/console dev:test --coverage --coverage-text
```

### Pre-Commit Test

```bash
php bin/console dev:test --type=unit --stop-on-failure
```

## Output Format

### Configuration Summary

Before running tests, shows selected options:
```
Test Configuration
------------------

 * Test type: unit
 * Execution: Sequential
 * Output format: TestDox
 * Stop on failure: enabled
```

### Execution Details

Shows the exact command being executed:
```
// Executing: vendor\bin\phpunit.bat tests/Unit --testdox --stop-on-failure
```

### Results Summary

After completion:
```
[OK] All tests passed! (Execution time: 14.42s)
```

Or on failure:
```
[ERROR] Some tests failed. (Execution time: 8.23s)
```

## Use Cases

### Daily Development Workflow

```bash
# Quick check before commit
php bin/console dev:test -t unit -s

# Full validation before push
php bin/console dev:test --coverage
```

### Debugging Failures

```bash
# Find failing test
php bin/console dev:test -s

# Run specific failing test with verbose output
php bin/console dev:test --filter=FailingTest -v
```

### CI/CD Pipeline

```yaml
test:
  script:
    - php bin/console dev:test --coverage --stop-on-failure
  artifacts:
    paths:
      - coverage/
```

### Code Review

```bash
# Generate coverage for review
php bin/console dev:test --coverage

# Check specific feature tests
php bin/console dev:test --filter=NewFeature --testdox
```

## Common Issues and Solutions

### Paratest Not Found

```
Execution: Sequential (paratest not installed)
```

**Solution**: Install paratest
```bash
composer require --dev brianium/paratest
```

### No Tests Executed

```
No tests executed!
```

**Causes**:
- Filter too restrictive
- Test type directory doesn't exist
- Test files not properly named (*Test.php)

**Solutions**:
```bash
# Check if tests exist
php bin/console dev:test --type=unit -v

# Run without filters
php bin/console dev:test
```

### Coverage Requires Xdebug/PCOV

```
Error: No code coverage driver available
```

**Solution**: Install Xdebug or PCOV
```bash
# Check if Xdebug is installed
php -m | grep xdebug

# Or install PCOV (faster alternative)
pecl install pcov
```

### Memory Limit Exceeded

**Solution**: Increase PHP memory limit
```bash
php -d memory_limit=512M bin/console dev:test --coverage
```

## Tips and Best Practices

### Development

- Use `--testdox` for readable test documentation
- Use `--stop-on-failure` when debugging
- Filter by type for faster feedback loops
- Run unit tests frequently

### Pre-Commit

```bash
# Quick check
php bin/console dev:test -t unit -s
```

### Before Push

```bash
# Full test suite
php bin/console dev:test --stop-on-failure
```

### Code Coverage

```bash
# Generate before pull requests
php bin/console dev:test --coverage
```

### Performance

- Use `--parallel` for large test suites
- Use `--type=unit` for fastest tests
- Use `--stop-on-failure` to save time when debugging

## Configuration

### PHPUnit Configuration

Tests use configuration from:
- `phpunit.xml` (local, git-ignored)
- `phpunit.dist.xml` (versioned template)

### Test Structure

```
tests/
├── Unit/          # Unit tests (--type=unit)
├── Functional/    # Functional tests (--type=functional)
└── Integration/   # Integration tests (--type=integration)
```

## Exit Codes

- **0**: All tests passed
- **1**: One or more tests failed
- **2**: No tests executed

## Scripting

Use in shell scripts:

```bash
#!/bin/bash

# Run tests and capture exit code
if php bin/console dev:test --type=unit --stop-on-failure; then
    echo "✓ Unit tests passed"
else
    echo "✗ Unit tests failed"
    exit 1
fi
```

## Related Commands

- **[dev:check](dev-check.md)** - Verify environment before testing
- **[dev:reset](dev-reset.md)** - Reset environment for clean test run

## Direct PHPUnit Access

If you need PHPUnit-specific options not covered by dev:test:

```bash
# Windows
vendor\bin\phpunit.bat [options]

# Linux/Mac
vendor/bin/phpunit [options]
```

## Source Code

Command implementation: `src/Command/DevTestCommand.php`

---

[← dev:reset](dev-reset.md) | [Back to Commands](README.md)
