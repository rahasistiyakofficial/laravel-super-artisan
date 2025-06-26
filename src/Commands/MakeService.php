<?php

namespace RahasIstiyak\SuperArtisan\Commands;

use Illuminate\Console\Command;

class MakeService extends Command
{
    protected $signature = 'make:service {name}';
    protected $description = 'Generate a service class';

    public function handle()
    {
        $name = $this->argument('name');
        $destination = app_path("Services/{$name}.php");

        if (!file_exists($destination)) {
            $content = file_get_contents(__DIR__ . '/../../stubs/service.stub');
            $content = str_replace('{{ name }}', $name, $content);
            $dir = dirname($destination);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents($destination, $content);
            $this->info("Service created at: {$destination}");
        } else {
            $this->error("Service already exists at: {$destination}");
        }
    }
}