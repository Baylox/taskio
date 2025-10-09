# Testing Guide

Quick guide for running tests in Taskio.

## Running Tests

**With Docker:**
```bash
docker compose exec app php bin/phpunit
```

**Without Docker:**
```bash
php bin/phpunit
```

## Run Specific Tests

**Unit tests only:**
```bash
php bin/phpunit tests/Unit
```

**Functional tests only:**
```bash
php bin/phpunit tests/Functional
```

**A specific test file:**
```bash
php bin/phpunit tests/Unit/Security/Voter/BoardVoterTest.php
```

## First Time Setup

If tests fail because the test database doesn't exist:

```bash
# Create test database and run migrations
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test --no-interaction
```

## That's It!

Just run `php bin/phpunit` before pushing to make sure you didn't break anything.

---

[← Back to README](../README.md)
