<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'dev:reset',
    description: 'Reset the development environment (database, cache, assets)',
)]
class DevResetCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('no-fixtures', null, InputOption::VALUE_NONE, 'Skip loading fixtures')
            ->addOption('no-cache', null, InputOption::VALUE_NONE, 'Skip cache clearing')
            ->addOption('no-assets', null, InputOption::VALUE_NONE, 'Skip assets building')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Do not ask for confirmation')
            ->setHelp(<<<'HELP'
This command automates the full reset of your development environment:

1. Clears cache (optional)
2. Drops and recreates the database
3. Runs migrations
4. Loads fixtures (optional)
5. Builds frontend assets (optional)

Usage:
  <info>php bin/console dev:reset</info>           # Full reset
  <info>php bin/console dev:reset --no-fixtures</info>  # Without fixtures
  <info>php bin/console dev:reset --no-cache</info>     # Keep cache
HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->confirmReset($io, $input)) {
            return Command::SUCCESS;
        }

        $io->title('Resetting Development Environment');

        $this->clearCache($io, $input);
        $this->resetDatabase($io);
        $this->runMigrations($io);
        $this->loadFixtures($io, $input);
        $this->buildAssets($io, $input);
        $this->displaySuccessMessage($io);

        return Command::SUCCESS;
    }

    private function confirmReset(SymfonyStyle $io, InputInterface $input): bool
    {
        if ($input->getOption('force')) {
            return true;
        }

        if (!$io->confirm('This will destroy all data in your database. Continue?', false)) {
            $io->warning('Operation cancelled.');
            return false;
        }

        return true;
    }

    private function resetDatabase(SymfonyStyle $io): void
    {
        $io->section('Step 2/5: Dropping database...');
        $this->runCommand($io, 'doctrine:database:drop', ['--force' => true, '--if-exists' => true]);

        $io->section('Step 2/5: Creating database...');
        $this->runCommand($io, 'doctrine:database:create');
    }

    private function runMigrations(SymfonyStyle $io): void
    {
        $io->section('Step 3/5: Running migrations...');
        $this->runCommand($io, 'doctrine:migrations:migrate', ['--no-interaction' => true]);
    }

    private function loadFixtures(SymfonyStyle $io, InputInterface $input): void
    {
        if ($input->getOption('no-fixtures')) {
            return;
        }

        $io->section('Step 4/5: Loading fixtures...');

        $fixtureCommand = $this->findAvailableFixtureCommand();

        if ($fixtureCommand === null) {
            $io->warning('No fixtures command found (Foundry/Doctrine). Skipping.');
            return;
        }

        $args = $fixtureCommand === 'doctrine:fixtures:load' ? ['--no-interaction' => true] : [];
        $this->runCommand($io, $fixtureCommand, $args);
    }

    private function findAvailableFixtureCommand(): ?string
    {
        $commands = ['foundry:load-fixtures', 'foundry:load-stories', 'doctrine:fixtures:load'];
        $app = $this->getApplication();

        foreach ($commands as $command) {
            if ($app && $app->has($command)) {
                return $command;
            }
        }

        return null;
    }

    private function clearCache(SymfonyStyle $io, InputInterface $input): void
    {
        if ($input->getOption('no-cache')) {
            return;
        }

        $io->section('Step 1/5: Clearing cache...');
        $this->runCommand($io, 'cache:clear');
    }

    private function buildAssets(SymfonyStyle $io, InputInterface $input): void
    {
        if ($input->getOption('no-assets')) {
            return;
        }

        $io->section('Step 5/5: Building frontend assets...');

        $process = new Process(['npm', 'run', 'build']);
        $process->setTimeout(300);
        $process->run(fn($type, $buffer) => $io->write($buffer));

        if (!$process->isSuccessful()) {
            $io->warning('Asset build failed, but continuing...');
        }
    }

    private function displaySuccessMessage(SymfonyStyle $io): void
    {
        $io->success('Development environment reset successfully!');
        $io->info('Default credentials:');
        $io->listing([
            'Admin: admin@example.com / adminpassword',
            'User: user@example.com / userpassword',
        ]);
    }

    private function runCommand(SymfonyStyle $io, string $commandName, array $arguments = []): int
    {
        $command = $this->getApplication()->find($commandName);
        $input = new ArrayInput(array_merge(['command' => $commandName], $arguments));

        $returnCode = $command->run($input, $io);

        if ($returnCode !== Command::SUCCESS) {
            $io->error(sprintf('Command "%s" failed!', $commandName));
        }

        return $returnCode;
    }
}
