<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'dev:check',
    description: 'Check the development environment status (database, cache, services, etc.)',
)]
class DevCheckCommand extends Command
{
    private array $checks = [];
    private int $totalChecks = 17; // Nombre total estimé de checks
    private SymfonyStyle $io;

    public function __construct(
        private readonly Connection $connection,
        private readonly ParameterBagInterface $params,
    ) {
        parent::__construct();
    }

    private function getProjectDir(): string
    {
        return $this->params->get('kernel.project_dir');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->title('Development Environment Check');

        $this->checkDatabase($this->io);
        $this->checkCache($this->io);
        $this->checkEnvironment($this->io);
        $this->checkDependencies($this->io);
        $this->checkPermissions($this->io);

        $this->displaySummary($this->io);

        return $this->hasErrors() ? Command::FAILURE : Command::SUCCESS;
    }

    private function checkDatabase(SymfonyStyle $io): void
    {
        $io->section('Database Connection');

        try {
            $this->connection->connect();
            $databaseName = $this->connection->getDatabase();

            $this->addCheck('Database', 'Connected', true, "Database: $databaseName");

            // Check tables count
            $tables = $this->connection->createSchemaManager()->listTableNames();
            $tableCount = count($tables);

            if ($tableCount === 0) {
                $this->addCheck('Database', 'Tables', false, 'No tables found. Run migrations?');
            } else {
                $this->addCheck('Database', 'Tables', true, "$tableCount tables found");
            }

            // Check if migrations are up to date
            $this->checkMigrations($io);

        } catch (Exception $e) {
            $this->addCheck('Database', 'Connection', false, $e->getMessage());
        }
    }

    private function checkMigrations(SymfonyStyle $io): void
    {
        $migrationsDir = $this->getProjectDir() . '/migrations';

        if (!is_dir($migrationsDir)) {
            $this->addCheck('Database', 'Migrations', true, 'No migrations directory');
            return;
        }

        $migrationFiles = glob($migrationsDir . '/*.php');
        $migrationCount = count($migrationFiles);

        $this->addCheck('Database', 'Migrations', true, "$migrationCount migration(s) available");
    }

    private function checkCache(SymfonyStyle $io): void
    {
        $io->section('Cache Status');

        $cacheDir = $this->getProjectDir() . '/var/cache';
        $devCacheDir = $cacheDir . '/dev';

        if (!is_dir($cacheDir)) {
            $this->addCheck('Cache', 'Directory', false, 'Cache directory not found');
            return;
        }

        $this->addCheck('Cache', 'Directory', true, 'Cache directory exists');

        if (is_dir($devCacheDir)) {
            $size = $this->getDirectorySize($devCacheDir);
            $this->addCheck('Cache', 'Dev Cache', true, $this->formatBytes($size));
        } else {
            $this->addCheck('Cache', 'Dev Cache', true, 'Empty (will be created on next request)');
        }

        // Check cache writability
        if (is_writable($cacheDir)) {
            $this->addCheck('Cache', 'Writable', true, 'Cache directory is writable');
        } else {
            $this->addCheck('Cache', 'Writable', false, 'Cache directory is not writable');
        }
    }

    private function checkEnvironment(SymfonyStyle $io): void
    {
        $io->section('Environment Configuration');

        $env = $this->params->get('kernel.environment');
        $debug = $this->params->get('kernel.debug');

        $this->addCheck('Environment', 'Mode', true, "Environment: $env");
        $this->addCheck('Environment', 'Debug', true, 'Debug: ' . ($debug ? 'enabled' : 'disabled'));

        // Check .env file
        $envFile = $this->getProjectDir() . '/.env.local';
        if (file_exists($envFile)) {
            $this->addCheck('Environment', '.env.local', true, '.env.local file found');
        } else {
            $this->addCheck('Environment', '.env.local', true, 'Using default .env file');
        }

        // Check PHP version
        $phpVersion = PHP_VERSION;
        $requiredVersion = '8.3.0';
        $isPhpOk = version_compare($phpVersion, $requiredVersion, '>=');

        $this->addCheck('Environment', 'PHP Version', $isPhpOk, "PHP $phpVersion");

        // Check memory limit
        $memoryLimit = ini_get('memory_limit');
        $this->addCheck('Environment', 'Memory Limit', true, $memoryLimit);
    }

    private function checkDependencies(SymfonyStyle $io): void
    {
        $io->section('Dependencies');

        // Check composer.lock
        $composerLock = $this->getProjectDir() . '/composer.lock';
        if (file_exists($composerLock)) {
            $lockAge = time() - filemtime($composerLock);
            $daysOld = floor($lockAge / 86400);

            if ($daysOld > 30) {
                $this->addCheck('Dependencies', 'Composer', true, "composer.lock is $daysOld days old");
            } else {
                $this->addCheck('Dependencies', 'Composer', true, 'composer.lock is up to date');
            }
        } else {
            $this->addCheck('Dependencies', 'Composer', false, 'composer.lock not found. Run composer install');
        }

        // Check vendor directory
        $vendorDir = $this->getProjectDir() . '/vendor';
        if (is_dir($vendorDir)) {
            $this->addCheck('Dependencies', 'Vendor', true, 'Vendor directory exists');
        } else {
            $this->addCheck('Dependencies', 'Vendor', false, 'Vendor directory not found. Run composer install');
        }

        // Check node_modules
        $nodeModules = $this->getProjectDir() . '/node_modules';
        if (is_dir($nodeModules)) {
            $this->addCheck('Dependencies', 'Node Modules', true, 'node_modules directory exists');
        } else {
            $this->addCheck('Dependencies', 'Node Modules', false, 'node_modules not found. Run npm install');
        }

        // Check package-lock.json
        $packageLock = $this->getProjectDir() . '/package-lock.json';
        if (file_exists($packageLock)) {
            $this->addCheck('Dependencies', 'NPM Lock', true, 'package-lock.json exists');
        } else {
            $this->addCheck('Dependencies', 'NPM Lock', true, 'No package-lock.json (might use yarn/pnpm)');
        }
    }

    private function checkPermissions(SymfonyStyle $io): void
    {
        $io->section('File Permissions');

        $directories = [
            'var/cache' => 'Cache directory',
            'var/log' => 'Log directory',
        ];

        foreach ($directories as $dir => $description) {
            $fullPath = $this->getProjectDir() . '/' . $dir;

            if (!is_dir($fullPath)) {
                $this->addCheck('Permissions', $description, false, "Directory not found: $dir");
                continue;
            }

            if (is_writable($fullPath)) {
                $this->addCheck('Permissions', $description, true, "$dir is writable");
            } else {
                $this->addCheck('Permissions', $description, false, "$dir is not writable");
            }
        }
    }

    private function addCheck(string $category, string $name, bool $success, string $message): void
    {
        $this->checks[] = [
            'category' => $category,
            'name' => $name,
            'success' => $success,
            'message' => $message,
        ];

        // Afficher la progression en temps réel
        $this->displayProgress();
    }

    private function displayProgress(): void
    {
        $currentCount = count($this->checks);
        $percentage = round(($currentCount / $this->totalChecks) * 100);

        $this->io->write("\r<comment>Progress:</comment> {$percentage}% ({$currentCount}/{$this->totalChecks})", false);

        if ($currentCount === $this->totalChecks) {
            $this->io->newLine();
        }
    }

    private function displaySummary(SymfonyStyle $io): void
    {
        $io->newLine();
        $io->section('Summary');

        $rows = [];
        foreach ($this->checks as $check) {
            $status = $check['success'] ? '<fg=green>✓</>' : '<fg=red>✗</>';
            $rows[] = [
                $check['category'],
                $check['name'],
                $status,
                $check['message'],
            ];
        }

        $io->table(['Category', 'Check', 'Status', 'Details'], $rows);

        $totalChecks = count($this->checks);
        $successCount = count(array_filter($this->checks, fn($c) => $c['success']));
        $failureCount = $totalChecks - $successCount;

        $io->newLine();

        $percentage = $totalChecks > 0 ? round(($successCount / $totalChecks) * 100, 1) : 0;

        if ($failureCount === 0) {
            $io->success("All checks passed! {$percentage}% ({$successCount}/{$totalChecks})");
        } else {
            $io->warning("{$failureCount} check(s) failed. {$percentage}% passed ({$successCount}/{$totalChecks})");
        }
    }

    private function hasErrors(): bool
    {
        return count(array_filter($this->checks, fn($c) => !$c['success'])) > 0;
    }

    private function getDirectorySize(string $path): int
    {
        $size = 0;

        if (!is_dir($path)) {
            return 0;
        }

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        } catch (Exception $e) {
            // Ignore errors reading directory
        }

        return $size;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
