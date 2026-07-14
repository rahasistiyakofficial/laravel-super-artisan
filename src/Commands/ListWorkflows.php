<?php

namespace RahasIstiyak\SuperArtisan\Commands;

use Illuminate\Console\Command;

class ListWorkflows extends Command
{
    protected $signature = 'super:list
        {--blueprints : Show blueprints instead of workflows}
        {--all        : Show both workflows and blueprints}';

    protected $description = 'List all available Super Artisan workflows and blueprints';

    public function handle(): int
    {
        $showAll        = $this->option('all');
        $showBlueprints = $this->option('blueprints') || $showAll;
        $showWorkflows  = ! $this->option('blueprints') || $showAll;

        $this->line('');
        $this->line('<fg=magenta;options=bold>🚀 Super Artisan v2.0</>');
        $this->line('');

        if ($showWorkflows) {
            $this->printWorkflows();
        }

        if ($showBlueprints) {
            $this->printBlueprints();
        }

        $this->line('<fg=gray>Run any workflow: php artisan run:workflow {name}</>');
        $this->line('<fg=gray>Run a blueprint:  php artisan make:super {Name} --blueprint={key}</>');
        $this->line('');

        return self::SUCCESS;
    }

    protected function printWorkflows(): void
    {
        $workflows = config('super-artisan.workflows', []);

        $this->line('<fg=blue;options=bold>Workflows</>');
        $this->line(str_repeat('─', 50));

        if (empty($workflows)) {
            $this->line('<fg=gray>  No workflows defined.</>');
            $this->line('');
            return;
        }

        foreach ($workflows as $key => $workflow) {
            $desc     = $workflow['description'] ?? '';
            $steps    = count($workflow['commands'] ?? []);
            $this->line("  <fg=yellow;options=bold>{$key}</> <fg=gray>({$steps} steps)</> — {$desc}");
        }

        $this->line('');
    }

    protected function printBlueprints(): void
    {
        $blueprints = config('super-artisan.blueprints', []);

        $this->line('<fg=blue;options=bold>Blueprints</>');
        $this->line(str_repeat('─', 50));

        if (empty($blueprints)) {
            $this->line('<fg=gray>  No blueprints defined.</>');
            $this->line('');
            return;
        }

        foreach ($blueprints as $key => $blueprint) {
            $desc  = $blueprint['description'] ?? '';
            $steps = count($blueprint['commands'] ?? []);
            $this->line("  <fg=yellow;options=bold>{$key}</> <fg=gray>({$steps} commands)</> — {$desc}");
        }

        $this->line('');
    }
}
