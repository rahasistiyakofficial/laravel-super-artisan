<?php

namespace RahasIstiyak\SuperArtisan\Commands;

use Illuminate\Console\Command;
use RahasIstiyak\SuperArtisan\Concerns\GeneratesFiles;

class MakeSuperPolicy extends Command
{
    use GeneratesFiles;

    protected $signature = 'make:super-policy
        {name : Policy class name (e.g. PostPolicy)}
        {--model=  : Model class to use (defaults to name prefix)}
        {--force   : Overwrite existing files}
        {--dry-run : Preview without writing}';

    protected $description = 'Generate a Gate policy class with all standard authorization methods';

    public function handle(): int
    {
        $name  = $this->argument('name');
        $model = $this->option('model') ?? \Illuminate\Support\Str::before($name, 'Policy');

        $tokens  = $this->buildTokens($model);
        $tokens['{{ name }}'] = $model; // policy stub uses {{ name }} for the model

        $content     = $this->resolveStub('policy');
        $content     = $this->replaceTokens($content, $tokens);

        // Fix class name in content
        $content = str_replace(
            "class {$model}Policy",
            "class {$name}",
            $content
        );

        $destination = app_path("Policies/{$name}.php");

        $this->line('');
        $this->line("<fg=blue;options=bold>» Policy: {$name}</>");
        $this->line(str_repeat('─', 50));
        $this->writeFile($destination, $content, "app/Policies/{$name}.php");

        if (! $this->option('dry-run')) {
            $this->line('');
            $this->line('<fg=gray>Tip: Register this policy in AuthServiceProvider or use auto-discovery.</>');
        }

        $this->line('');
        return self::SUCCESS;
    }
}
