<?php

namespace RahasIstiyak\SuperArtisan\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeSuper extends Command
{
    protected $signature = 'make:super {name} 
        {--livewire : Generate Livewire components} 
        {--vue : Generate Vue components} 
        {--react : Generate React components} 
        {--path= : Base path for generated files (excludes models/migrations)} 
        {--controller_path= : Subfolder within app/Http/Controllers} 
        {--model_path= : Subfolder within app/Models} 
        {--view_path= : Subfolder within resources/views or resources/js} 
        {--migration_path= : Subfolder within database/migrations} 
        {--pattern= : Pattern to use (repository, service)}';

    protected $description = 'Generate MVC or other pattern files with custom paths';

    public function handle()
    {
        $name = $this->argument('name');
        $lowerName = Str::lower($name);
        $path = $this->option('path');
        $controllerPath = $this->option('controller_path');
        $modelPath = $this->option('model_path');
        $viewPath = $this->option('view_path');
        $migrationPath = $this->option('migration_path');
        $pattern = $this->option('pattern') ?? 'mvc';

        // Validate front-end options
        $frontEndOptions = array_filter([
            'livewire' => $this->option('livewire'),
            'vue' => $this->option('vue'),
            'react' => $this->option('react'),
        ]);
        if (count($frontEndOptions) > 1) {
            $this->error('Only one of --livewire, --vue, or --react can be used.');
            return 1;
        }
        $frontEnd = key($frontEndOptions) ?? 'blade';
        $this->info("Hello Super Artisan!");
        // 1. Generate Model
        $modelName = $modelPath ? "{$modelPath}/{$name}" : "{$name}";
        $this->call('make:model', [
            'name' => $modelName,
            '-m' => true,
            '-f' => true,
        ]);
        $this->info("Model generated at: app/{$modelName}.php");

        // 2. Generate Migration
        $migrationOptions = $migrationPath ? ['--path' => "database/migrations/{$migrationPath}"] : [];
        $this->call('make:migration', [
                'name' => 'create_' . Str::plural($lowerName) . '_table',
            ] + $migrationOptions);
        $this->info("Migration generated.");

        // 3. Generate Controller
        $controllerName = $controllerPath
            ? "Http/Controllers/{$controllerPath}/{$name}Controller"
            : ($path ? "Http/Controllers/{$path}/{$name}Controller" : "Http/Controllers/{$name}Controller");
        $this->call('make:controller', [
            'name' => $controllerName,
            '--resource' => true,
            '--model' => $name,
        ]);
        $this->info("Controller generated at: app/{$controllerName}.php");

        // 4. Generate Front-end
        if ($frontEnd === 'livewire') {
            $livewireName = $viewPath
                ? $viewPath . '/' . $name
                : ($path ? $path . '/' . $name : $name);
            $this->call('make:livewire', ['name' => $livewireName]);
            $this->info("Livewire generated at: app/Livewire/{$livewireName}.php");
        } elseif ($frontEnd === 'vue') {
            $vueDir = $viewPath
                ? "js/{$viewPath}"
                : ($path ? "js/{$path}" : 'js/components');
            $this->generateFrontendFile('vue_component', $vueDir, $name, $lowerName, 'vue');
            $this->info("Vue component generated at: resources/{$vueDir}/{$name}.vue");
        } elseif ($frontEnd === 'react') {
            $reactDir = $viewPath
                ? "js/{$viewPath}"
                : ($path ? "js/{$path}" : 'js/components');
            $this->generateFrontendFile('react_component', $reactDir, $name, $lowerName, 'jsx');
            $this->info("React component generated at: resources/{$reactDir}/{$name}.jsx");
        } else {
            // Blade Views
            $viewDir = $viewPath
                ? $viewPath
                : ($path ? $path . '/' . $lowerName : $lowerName);
            $stubs = ['index', 'create', 'edit', 'show'];
            foreach ($stubs as $stub) {
                $destination = resource_path("views/{$viewDir}/{$stub}.blade.php");
                if (!file_exists($destination)) {
                    $publishedStub = base_path("stubs/vendor/super-artisan/view_{$stub}.stub");
                    $defaultStub = __DIR__ . "/../../stubs/view_{$stub}.stub";

                    $stubPath = file_exists($publishedStub) ? $publishedStub : $defaultStub;

                    $content = file_get_contents($stubPath);
                    $content = str_replace('{{ name }}', $name, $content);
                    $content = str_replace('{{ lower_name }}', $lowerName, $content);
                    $dir = dirname($destination);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    file_put_contents($destination, $content);
                    $this->info("Blade view generated at: {$destination}");
                }
            }
        }

        // 5. Handle Patterns
        if ($pattern === 'repository') {
            $this->call('make:repository', [
                'name' => $name . 'Repository',
                '--model' => $name,
            ]);
            $this->info("Repository generated.");
        } elseif ($pattern === 'service') {
            $this->call('make:service', [
                'name' => $name . 'Service',
            ]);
            $this->info("Service generated.");
        }
    }

    protected function generateFrontendFile($stub, $dir, $name, $lowerName, $extension)
    {
        $destination = resource_path("{$dir}/{$name}.{$extension}");
        if (!file_exists($destination)) {
            $content = file_get_contents(__DIR__ . '/../../stubs/' . $stub . '.stub');
            $content = str_replace('{{ name }}', $name, $content);
            $content = str_replace('{{ lower_name }}', $lowerName, $content);
            $dirPath = dirname($destination);
            if (!is_dir($dirPath)) {
                mkdir($dirPath, 0755, true);
            }
            file_put_contents($destination, $content);
        }
    }
}