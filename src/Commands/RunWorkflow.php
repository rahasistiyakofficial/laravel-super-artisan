<?php

namespace RahasIstiyak\SuperArtisan\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RunWorkflow extends Command
{
    protected $signature = 'run:workflow
        {workflow : Workflow name as defined in config/super-artisan.php}
        {--name=         : Optional {name} token value to substitute in commands}
        {--dry-run       : Show commands without running them}
        {--stop-on-failure: Stop execution if any command returns non-zero exit code}
        {--list          : List all available workflows and exit}';

    protected $description = 'Execute a predefined sequence of Artisan commands (workflow)';

    protected int $stepsPassed  = 0;
    protected int $stepsFailed  = 0;
    protected int $stepsSkipped = 0;

    public function handle(): int
    {
        if ($this->option('list')) {
            return $this->listWorkflows();
        }

        $workflowKey = $this->argument('workflow');
        $workflows   = config('super-artisan.workflows', []);

        if (! isset($workflows[$workflowKey])) {
            $this->error("Workflow \"{$workflowKey}\" not found.");
            $this->line('');
            $this->line('Available workflows:');
            foreach (array_keys($workflows) as $key) {
                $desc = $workflows[$key]['description'] ?? '';
                $this->line("  <fg=yellow>{$key}</>  {$desc}");
            }
            return self::FAILURE;
        }

        $workflow  = $workflows[$workflowKey];
        $commands  = $workflow['commands'] ?? [];
        $nameToken = $this->option('name');

        $this->printHeader($workflowKey, $workflow, count($commands));

        $startTime = microtime(true);

        foreach ($commands as $index => $commandTemplate) {
            $step = $index + 1;
            $cmd  = $this->resolveCommandTemplate($commandTemplate, $nameToken);

            $this->printStep($step, count($commands), $cmd);

            if ($this->option('dry-run')) {
                $this->line("     <fg=cyan>[dry-run]</> Skipped.");
                $this->stepsSkipped++;
                continue;
            }

            $stepStart  = microtime(true);
            $exitCode   = $this->runArtisanCommand($cmd);
            $elapsed    = round((microtime(true) - $stepStart) * 1000);

            if ($exitCode !== 0) {
                $this->line("     <fg=red>✖ Failed</> <fg=gray>({$elapsed}ms)</>");
                $this->stepsFailed++;

                if ($this->option('stop-on-failure')) {
                    $this->line('');
                    $this->error('Workflow stopped due to failure (--stop-on-failure).');
                    $this->printSummary(microtime(true) - $startTime);
                    return self::FAILURE;
                }
            } else {
                $this->line("     <fg=green>✔ Done</> <fg=gray>({$elapsed}ms)</>");
                $this->stepsPassed++;
            }
        }

        $this->printSummary(microtime(true) - $startTime);

        return $this->stepsFailed > 0 ? self::FAILURE : self::SUCCESS;
    }

    // -------------------------------------------------------------------------
    // Command execution
    // -------------------------------------------------------------------------

    protected function runArtisanCommand(string $cmd): int
    {
        // Parse "command:name --flag --option=value arg1" into parts
        $parts = $this->parseCommandString($cmd);

        if (empty($parts)) {
            return 0;
        }

        $commandName = array_shift($parts);
        $args        = [];
        $options     = [];

        foreach ($parts as $part) {
            if (str_starts_with($part, '--')) {
                $opt = substr($part, 2);
                if (str_contains($opt, '=')) {
                    [$k, $v] = explode('=', $opt, 2);
                    $options["--{$k}"] = $v;
                } else {
                    $options["--{$opt}"] = true;
                }
            } else {
                $args[] = $part;
            }
        }

        // Merge positional args as 'name' (first) then the rest
        $callArgs = $options;
        if (! empty($args)) {
            $callArgs = array_merge(['name' => array_shift($args)], $callArgs);
        }

        try {
            return $this->call($commandName, $callArgs);
        } catch (\Throwable $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }

    protected function parseCommandString(string $cmd): array
    {
        // Simple shell-like tokenizer (supports quoted strings)
        $tokens  = [];
        $current = '';
        $inQuote = false;
        $quote   = '';

        for ($i = 0; $i < strlen($cmd); $i++) {
            $char = $cmd[$i];
            if ($inQuote) {
                if ($char === $quote) {
                    $inQuote = false;
                } else {
                    $current .= $char;
                }
            } elseif ($char === '"' || $char === "'") {
                $inQuote = true;
                $quote   = $char;
            } elseif ($char === ' ') {
                if ($current !== '') {
                    $tokens[] = $current;
                    $current  = '';
                }
            } else {
                $current .= $char;
            }
        }
        if ($current !== '') {
            $tokens[] = $current;
        }

        return $tokens;
    }

    protected function resolveCommandTemplate(string $template, ?string $name): string
    {
        if (! $name) {
            return $template;
        }

        return str_replace(
            ['{name}', '{lower_name}', '{plural}', '{snake}'],
            [$name, Str::lower($name), Str::plural($name), Str::snake($name)],
            $template
        );
    }

    // -------------------------------------------------------------------------
    // Listing
    // -------------------------------------------------------------------------

    protected function listWorkflows(): int
    {
        $workflows = config('super-artisan.workflows', []);

        $this->line('');
        $this->line('<fg=blue;options=bold>Available Workflows</>');
        $this->line(str_repeat('─', 50));

        if (empty($workflows)) {
            $this->line('<fg=gray>No workflows defined in config/super-artisan.php</>');
            return self::SUCCESS;
        }

        foreach ($workflows as $key => $workflow) {
            $desc     = $workflow['description'] ?? '';
            $cmdCount = count($workflow['commands'] ?? []);
            $this->line("  <fg=yellow;options=bold>{$key}</> <fg=gray>({$cmdCount} steps)</> — {$desc}");
            foreach ($workflow['commands'] as $cmd) {
                $this->line("    <fg=gray>• php artisan {$cmd}</>");
            }
            $this->line('');
        }

        return self::SUCCESS;
    }

    // -------------------------------------------------------------------------
    // Output helpers
    // -------------------------------------------------------------------------

    protected function printHeader(string $key, array $workflow, int $totalSteps): void
    {
        $desc    = $workflow['description'] ?? '';
        $dryNote = $this->option('dry-run') ? ' <fg=cyan>[dry-run]</>' : '';

        $this->line('');
        $this->line("<fg=magenta;options=bold>⚡ Workflow: {$key}{$dryNote}</>");
        if ($desc) {
            $this->line("<fg=gray>{$desc}</>");
        }
        $this->line("<fg=gray>{$totalSteps} step(s) to execute</>");
        $this->line(str_repeat('─', 50));
        $this->line('');
    }

    protected function printStep(int $step, int $total, string $cmd): void
    {
        $pad = str_pad($step, strlen((string) $total), '0', STR_PAD_LEFT);
        $this->line("  <fg=blue>[{$pad}/{$total}]</> <fg=white>php artisan {$cmd}</>");
    }

    protected function printSummary(float $elapsed): void
    {
        $elapsed = round($elapsed, 2);
        $this->line('');
        $this->line(str_repeat('─', 50));
        $this->line("<fg=green>Passed: {$this->stepsPassed}</> | <fg=red>Failed: {$this->stepsFailed}</> | <fg=cyan>Skipped: {$this->stepsSkipped}</>");
        $this->line("<fg=gray>Total time: {$elapsed}s</>");

        if ($this->stepsFailed === 0 && ! $this->option('dry-run')) {
            $this->line('');
            $this->line('<fg=green;options=bold>✓ Workflow completed successfully!</>');
        } elseif ($this->option('dry-run')) {
            $this->line('');
            $this->line('<fg=cyan;options=bold>✓ Dry-run complete. No commands were executed.</>');
        }
        $this->line('');
    }
}
