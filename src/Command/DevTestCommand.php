<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'dev:test',
    description: 'Run PHPUnit tests with advanced options (filtering, coverage, parallel execution)',
)]
class DevTestCommand extends Command
{
    public function __construct(
        private readonly ParameterBagInterface $params,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Filter tests by type (unit, functional, integration)', null)
            ->addOption('filter', 'f', InputOption::VALUE_OPTIONAL, 'Filter tests by name pattern', null)
            ->addOption('coverage', 'c', InputOption::VALUE_NONE, 'Generate code coverage report (HTML)')
            ->addOption('coverage-text', null, InputOption::VALUE_NONE, 'Generate code coverage report (text in console)')
            ->addOption('parallel', 'p', InputOption::VALUE_NONE, 'Run tests in parallel (requires paratest)')
            ->addOption('stop-on-failure', 's', InputOption::VALUE_NONE, 'Stop on first failure')
            ->addOption('group', 'g', InputOption::VALUE_OPTIONAL, 'Run tests from specific group', null)
            ->addOption('testdox', null, InputOption::VALUE_NONE, 'Display tests in TestDox format')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $projectDir = $this->params->get('kernel.project_dir');

        $io->title('Running PHPUnit Tests');

        // Build the command
        $command = $this->buildTestCommand($input, $projectDir);

        $io->section('Test Configuration');
        $io->listing($this->getConfigSummary($input));

        $io->newLine();
        $io->comment('Executing: ' . $command);
        $io->newLine();

        // Execute the tests
        $startTime = microtime(true);

        $process = Process::fromShellCommandline($command, $projectDir, null, null, null);
        $process->setTty(false);

        $exitCode = $process->run(function ($type, $buffer) use ($output) {
            $output->write($buffer);
        });

        $executionTime = round(microtime(true) - $startTime, 2);

        $io->newLine();

        if ($exitCode === 0) {
            $io->success("All tests passed! (Execution time: {$executionTime}s)");
        } else {
            $io->error("Some tests failed. (Execution time: {$executionTime}s)");
        }

        // Display coverage report location if generated
        if ($input->getOption('coverage')) {
            $coverageDir = $projectDir . '/coverage';
            $io->note("Coverage report generated in: $coverageDir/index.html");
            $io->comment("Open it in your browser to view detailed coverage information.");
        }

        return $exitCode;
    }

    private function buildTestCommand(InputInterface $input, string $projectDir): string
    {
        $useParallel = $input->getOption('parallel');
        $isWindows = DIRECTORY_SEPARATOR === '\\';

        // Check if we should use paratest or phpunit
        if ($useParallel && $this->isParatestAvailable($projectDir)) {
            $command = $isWindows ? 'vendor\\bin\\paratest.bat' : 'vendor/bin/paratest';
        } else {
            $command = $isWindows ? 'vendor\\bin\\phpunit.bat' : 'vendor/bin/phpunit';

            if ($useParallel) {
                // Fallback message will be shown later
            }
        }

        // Add filter by type
        if ($type = $input->getOption('type')) {
            $testPath = $this->getTestPathByType($type);
            if ($testPath) {
                $command .= ' ' . $testPath;
            }
        }

        // Add filter by name pattern
        if ($filter = $input->getOption('filter')) {
            $command .= ' --filter=' . escapeshellarg($filter);
        }

        // Add group filter
        if ($group = $input->getOption('group')) {
            $command .= ' --group=' . escapeshellarg($group);
        }

        // Add coverage options
        if ($input->getOption('coverage')) {
            $command .= ' --coverage-html=coverage';
        }

        if ($input->getOption('coverage-text')) {
            $command .= ' --coverage-text';
        }

        // Add stop on failure
        if ($input->getOption('stop-on-failure')) {
            $command .= ' --stop-on-failure';
        }

        // Add testdox format
        if ($input->getOption('testdox')) {
            $command .= ' --testdox';
        }

        // Add verbose if specified (using Symfony's built-in verbose option)
        if ($input->getOption('verbose')) {
            $command .= ' --verbose';
        }

        return $command;
    }

    private function getTestPathByType(?string $type): ?string
    {
        if (!$type) {
            return null;
        }

        $type = strtolower($type);

        $paths = [
            'unit' => 'tests/Unit',
            'functional' => 'tests/Functional',
            'integration' => 'tests/Integration',
        ];

        return $paths[$type] ?? null;
    }

    private function isParatestAvailable(string $projectDir): bool
    {
        $paratestPath = $projectDir . '/vendor/bin/paratest';
        $paratestPathCmd = $projectDir . '/vendor/bin/paratest.bat';

        return file_exists($paratestPath) || file_exists($paratestPathCmd);
    }

    private function getConfigSummary(InputInterface $input): array
    {
        $summary = [];

        if ($type = $input->getOption('type')) {
            $summary[] = "Test type: $type";
        } else {
            $summary[] = "Test type: all";
        }

        if ($filter = $input->getOption('filter')) {
            $summary[] = "Filter: $filter";
        }

        if ($group = $input->getOption('group')) {
            $summary[] = "Group: $group";
        }

        if ($input->getOption('coverage')) {
            $summary[] = "Coverage: HTML report enabled";
        }

        if ($input->getOption('coverage-text')) {
            $summary[] = "Coverage: Text output enabled";
        }

        if ($input->getOption('parallel')) {
            $projectDir = $this->params->get('kernel.project_dir');
            if ($this->isParatestAvailable($projectDir)) {
                $summary[] = "Execution: Parallel (using paratest)";
            } else {
                $summary[] = "Execution: Sequential (paratest not installed)";
            }
        } else {
            $summary[] = "Execution: Sequential";
        }

        if ($input->getOption('stop-on-failure')) {
            $summary[] = "Stop on failure: enabled";
        }

        if ($input->getOption('testdox')) {
            $summary[] = "Output format: TestDox";
        }

        return $summary;
    }
}
