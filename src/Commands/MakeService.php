<?php

namespace RahasIstiyak\SuperArtisan\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RahasIstiyak\SuperArtisan\Concerns\GeneratesFiles;

class MakeService extends Command
{
    use GeneratesFiles;

    protected $signature = 'make:service
        {name : Service class name (e.g. PostService)}
        {--model=  : Model to inject (defaults to name prefix)}
        {--force   : Overwrite existing files}
        {--dry-run : Preview what would be generated}';

    protected $description = 'Generate a service class';

    public function handle(): int
    {
        $name  = $this->argument('name');
        $model = $this->option('model') ?? Str::before($name, 'Service');

        $tokens  = $this->buildTokens($model);
        $tokens['{{ name }}'] = $name;

        $content     = $this->resolveStub('service');
        $content     = $this->replaceTokens($content, $tokens);
        $destination = app_path("Services/{$name}.php");

        $this->line('');
        $this->line("<fg=blue;options=bold>» Service: {$name}</>");
        $this->line(str_repeat('─', 50));
        $this->writeFile($destination, $content, "app/Services/{$name}.php");
        $this->line('');

        return self::SUCCESS;
    }
}