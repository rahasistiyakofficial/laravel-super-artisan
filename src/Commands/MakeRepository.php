<?php

namespace RahasIstiyak\SuperArtisan\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeRepository extends Command
{
    protected $signature = 'make:repository {name} {--model= : Model to bind to the repository}';
    protected $description = 'Generate a repository class';

    public function handle()
    {
        $name = $this->argument('name');
        $model = $this->option('model') ?? Str::before($name, 'Repository');
        $destination = app_path("Repositories/{$name}.php");

        if (!file_exists($destination)) {
            $content = file_get_contents(__DIR__ . '/../../stubs/repository.stub');
            $content = str_replace('{{ name }}', $name, $content);
            $content = str_replace('{{ model }}', $model, $content);
            $dir = dirname($destination);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents($destination, $content);
            $this->info("Repository created at: {$destination}");
        } else {
            $this->error("Repository already exists at: {$destination}");
        }
    }
}