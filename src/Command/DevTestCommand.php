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
        $command = $this->getBaseCommand($input, $projectDir);
        $command .= $this->buildTestPathOption($input);
        $command .= $this->buildFilterOptions($input);
        $command .= $this->buildCoverageOptions($input);
        $command .= $this->buildExecutionOptions($input);

        return $command;
    }

    private function getBaseCommand(InputInterface $input, string $projectDir): string
    {
        $useParallel = $input->getOption('parallel');
        $isWindows = DIRECTORY_SEPARATOR === '\\';

        if ($useParallel && $this->isParatestAvailable($projectDir)) {
            return $isWindows ? 'vendor\\bin\\paratest.bat' : 'vendor/bin/paratest';
        }

        return $isWindows ? 'vendor\\bin\\phpunit.bat' : 'vendor/bin/phpunit';
    }

    private function buildTestPathOption(InputInterface $input): string
    {
        $type = $input->getOption('type');
        if (!$type) {
            return '';
        }

        $testPath = $this->getTestPathByType($type);
        return $testPath ? ' ' . $testPath : '';
    }

    private function buildFilterOptions(InputInterface $input): string
    {
        $options = '';

        if ($filter = $input->getOption('filter')) {
            $options .= ' --filter=' . escapeshellarg($filter);
        }

        if ($group = $input->getOption('group')) {
            $options .= ' --group=' . escapeshellarg($group);
        }

        return $options;
    }

    private function buildCoverageOptions(InputInterface $input): string
    {
        $options = '';

        if ($input->getOption('coverage')) {
            $options .= ' --coverage-html=coverage';
        }

        if ($input->getOption('coverage-text')) {
            $options .= ' --coverage-text';
        }

        return $options;
    }

    private function buildExecutionOptions(InputInterface $input): string
    {
        $options = '';

        if ($input->getOption('stop-on-failure')) {
            $options .= ' --stop-on-failure';
        }

        if ($input->getOption('testdox')) {
            $options .= ' --testdox';
        }

        if ($input->getOption('verbose')) {
            $options .= ' --verbose';
        }

        return $options;
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
        return array_filter([
            $this->getTestTypeSummary($input),
            $this->getFilterSummary($input),
            $this->getGroupSummary($input),
            $this->getCoverageSummary($input),
            $this->getCoverageTextSummary($input),
            $this->getExecutionModeSummary($input),
            $this->getStopOnFailureSummary($input),
            $this->getTestDoxSummary($input),
        ]);
    }

    private function getTestTypeSummary(InputInterface $input): string
    {
        $type = $input->getOption('type');
        return "Test type: " . ($type ?: 'all');
    }

    private function getFilterSummary(InputInterface $input): ?string
    {
        $filter = $input->getOption('filter');
        return $filter ? "Filter: $filter" : null;
    }

    private function getGroupSummary(InputInterface $input): ?string
    {
        $group = $input->getOption('group');
        return $group ? "Group: $group" : null;
    }

    private function getCoverageSummary(InputInterface $input): ?string
    {
        return $input->getOption('coverage') ? "Coverage: HTML report enabled" : null;
    }

    private function getCoverageTextSummary(InputInterface $input): ?string
    {
        return $input->getOption('coverage-text') ? "Coverage: Text output enabled" : null;
    }

    private function getExecutionModeSummary(InputInterface $input): string
    {
        if (!$input->getOption('parallel')) {
            return "Execution: Sequential";
        }

        $projectDir = $this->params->get('kernel.project_dir');
        if ($this->isParatestAvailable($projectDir)) {
            return "Execution: Parallel (using paratest)";
        }

        return "Execution: Sequential (paratest not installed)";
    }

    private function getStopOnFailureSummary(InputInterface $input): ?string
    {
        return $input->getOption('stop-on-failure') ? "Stop on failure: enabled" : null;
    }

    private function getTestDoxSummary(InputInterface $input): ?string
    {
        return $input->getOption('testdox') ? "Output format: TestDox" : null;
    }
}
