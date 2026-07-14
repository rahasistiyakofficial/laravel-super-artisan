<?php

namespace RahasIstiyak\SuperArtisan\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RahasIstiyak\SuperArtisan\Concerns\GeneratesFiles;

class MakeSuperAction extends Command
{
    use GeneratesFiles;

    protected $signature = 'make:super-action
        {name : Action class name (e.g. CreatePost)}
        {--model=  : Model to use in the action}
        {--force   : Overwrite existing files}
        {--dry-run : Preview without writing}';

    protected $description = 'Generate a single-responsibility Action class';

    public function handle(): int
    {
        $name       = $this->argument('name');
        $modelGuess = $this->option('model') ?? $this->guessModelFromName($name);

        $tokens = $this->buildTokens($modelGuess);
        $tokens['{{ action_name }}'] = $name;

        $content     = $this->resolveStub('action');
        $content     = $this->replaceTokens($content, $tokens);
        $destination = app_path("Actions/{$name}.php");

        $this->line('');
        $this->line("<fg=blue;options=bold>» Action: {$name}</>");
        $this->line(str_repeat('─', 50));
        $this->writeFile($destination, $content, "app/Actions/{$name}.php");
        $this->line('');

        return self::SUCCESS;
    }

    protected function guessModelFromName(string $name): string
    {
        // e.g. "CreatePost" → "Post", "DeleteUserComment" → last word is best guess
        $words = preg_split('/(?=[A-Z])/', $name, -1, PREG_SPLIT_NO_EMPTY);
        return end($words) ?: $name;
    }
}
