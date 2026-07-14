<?php

namespace RahasIstiyak\SuperArtisan\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RahasIstiyak\SuperArtisan\Concerns\GeneratesFiles;

class MakeSuper extends Command
{
    use GeneratesFiles;

    protected $signature = 'make:super
        {name : Resource name(s), comma-separated for bulk (e.g. Post or Post,Comment,Tag)}
        {--livewire        : Generate Livewire component}
        {--vue             : Generate Vue component}
        {--react           : Generate React component}
        {--api             : Generate API resource controller (JSON responses)}
        {--filament        : Generate Filament resource}
        {--path=           : Base path for controllers and views}
        {--controller_path= : Subfolder within app/Http/Controllers}
        {--model_path=      : Subfolder within app/Models}
        {--view_path=       : Subfolder within resources/views or resources/js}
        {--migration_path=  : Subfolder within database/migrations}
        {--pattern=         : Pattern to use (repository, service)}
        {--domain=          : Wrap files under a domain namespace (e.g. Blog)}
        {--blueprint=       : Run a named blueprint from config/super-artisan.php}
        {--force           : Overwrite existing files}
        {--dry-run         : Show what would be generated without writing files}
        {--interactive     : Prompt for options interactively}';

    protected $description = 'Generate MVC or pattern-based files for one or more resources (v2.0)';

    public function handle(): int
    {
        // Interactive mode: prompt for any missing options
        if ($this->option('interactive')) {
            $this->runInteractiveMode();
        }

        // Validate front-end flags
        $frontEndOptions = array_filter([
            'livewire' => $this->option('livewire'),
            'vue'      => $this->option('vue'),
            'react'    => $this->option('react'),
            'api'      => $this->option('api'),
            'filament' => $this->option('filament'),
        ]);
        if (count($frontEndOptions) > 1) {
            $this->error('Only one of --livewire, --vue, --react, --api, or --filament can be used.');
            return self::FAILURE;
        }

        // Bulk support: comma-separated names
        $names = array_map('trim', explode(',', $this->argument('name')));

        if (count($names) > 1) {
            $this->line('');
            $this->line("<fg=magenta;options=bold>🚀 Super Artisan v2.0 — Bulk Generation</>");
            $this->line("<fg=gray>Generating " . count($names) . " resources: " . implode(', ', $names) . "</>");
            $this->line('');
        }

        foreach ($names as $name) {
            $this->generateResource($name);
        }

        $this->line('');
        if ($this->option('dry-run')) {
            $this->line('<fg=cyan;options=bold>✓ Dry-run complete. No files were written.</>');
        } else {
            $this->line('<fg=green;options=bold>✓ All done!</>');
        }

        return self::SUCCESS;
    }

    // -------------------------------------------------------------------------
    // Core generation orchestrator
    // -------------------------------------------------------------------------

    protected function generateResource(string $name): void
    {
        $this->line('');
        $this->line("<fg=blue;options=bold>» Generating: {$name}</>");
        $this->line(str_repeat('─', 50));

        // Blueprint mode overrides everything
        if ($blueprint = $this->option('blueprint')) {
            $this->runBlueprint($name, $blueprint);
            return;
        }

        $this->generateModel($name);
        $this->generateController($name);
        $this->generateFrontend($name);
        $this->generatePattern($name);
    }

    // -------------------------------------------------------------------------
    // Model + Migration
    // -------------------------------------------------------------------------

    protected function generateModel(string $name): void
    {
        if ($this->option('dry-run')) {
            $modelPath = $this->resolveModelPath($name);
            $this->line("  <fg=cyan>[dry-run]</> Would create model: <fg=yellow>app/{$modelPath}.php</>");
            $this->line("  <fg=cyan>[dry-run]</> Would create migration: <fg=yellow>database/migrations/..._create_" . Str::snake(Str::plural($name)) . "_table.php</>");
            return;
        }

        $modelName = $this->resolveModelPath($name);
        $migrationOptions = ['name' => $modelName, '-m' => true, '-f' => true];

        $customMigPath = $this->option('migration_path');
        if ($customMigPath) {
            $migrationOptions['--path'] = "database/migrations/{$customMigPath}";
        }

        $this->call('make:model', $migrationOptions);
        $this->line("  <fg=green>✔</> Model + Migration: <fg=yellow>app/Models/{$modelName}.php</>");
    }

    protected function resolveModelPath(string $name): string
    {
        $modelPath = $this->option('model_path') ?? $this->option('path');
        return $modelPath ? "{$modelPath}/{$name}" : $name;
    }

    // -------------------------------------------------------------------------
    // Controller
    // -------------------------------------------------------------------------

    protected function generateController(string $name): void
    {
        $pattern    = $this->option('pattern');
        $isApi      = $this->option('api');
        $isFilament = $this->option('filament');
        $domain     = $this->option('domain');

        // Filament uses its own command
        if ($isFilament) {
            $this->generateFilamentResource($name);
            return;
        }

        // Repository pattern uses a custom controller stub
        if ($pattern === 'repository') {
            $this->generateRepositoryController($name);
            return;
        }

        // API controller
        if ($isApi) {
            $this->generateApiController($name);
            return;
        }

        // Standard controller
        $controllerPath = $this->resolveControllerPath($name);

        if ($this->option('dry-run')) {
            $this->line("  <fg=cyan>[dry-run]</> Would create controller: <fg=yellow>app/{$controllerPath}.php</>");
            return;
        }

        $this->call('make:controller', [
            'name'       => $controllerPath,
            '--resource' => true,
            '--model'    => $name,
        ]);
        $this->line("  <fg=green>✔</> Controller: <fg=yellow>app/{$controllerPath}.php</>");
    }

    protected function resolveControllerPath(string $name): string
    {
        $ctrlPath = $this->option('controller_path') ?? $this->option('path');
        $domain   = $this->option('domain');
        $base     = 'Http/Controllers';

        if ($domain) {
            $base = "Http/Controllers/{$domain}";
        } elseif ($ctrlPath) {
            $base = "Http/Controllers/{$ctrlPath}";
        }

        return "{$base}/{$name}Controller";
    }

    protected function generateApiController(string $name): void
    {
        $lowerName   = Str::lower($name);
        $lowerPlural = Str::lower(Str::plural($name));
        $nsPrefix    = $this->resolveNamespacePrefix();

        $tokens  = $this->buildTokens($name, $this->option('domain') ?? $this->option('controller_path') ?? $this->option('path'));
        $content = $this->resolveStub('api_controller');
        $content = $this->replaceTokens($content, $tokens);

        $controllerPath = $this->resolveControllerPath($name);
        $destination    = app_path("{$controllerPath}.php");

        $this->writeFile($destination, $content, "app/{$controllerPath}.php");
    }

    protected function generateRepositoryController(string $name): void
    {
        $tokens  = $this->buildTokens($name, $this->option('domain') ?? $this->option('controller_path') ?? $this->option('path'));
        $content = $this->resolveStub('repository_controller');
        $content = $this->replaceTokens($content, $tokens);

        $controllerPath = $this->resolveControllerPath($name);
        $destination    = app_path("{$controllerPath}.php");

        $this->writeFile($destination, $content, "app/{$controllerPath}.php");
    }

    protected function generateFilamentResource(string $name): void
    {
        if ($this->option('dry-run')) {
            $this->line("  <fg=cyan>[dry-run]</> Would create Filament resource: <fg=yellow>app/Filament/Resources/{$name}Resource.php</>");
            return;
        }

        $tokens  = $this->buildTokens($name);
        $content = $this->resolveStub('filament_resource');
        $content = $this->replaceTokens($content, $tokens);

        $destination = app_path("Filament/Resources/{$name}Resource.php");
        $this->writeFile($destination, $content, "app/Filament/Resources/{$name}Resource.php");
    }

    // -------------------------------------------------------------------------
    // Frontend
    // -------------------------------------------------------------------------

    protected function generateFrontend(string $name): void
    {
        $lowerName = Str::lower($name);

        if ($this->option('livewire')) {
            $this->generateLivewire($name, $lowerName);
        } elseif ($this->option('vue')) {
            $this->generateVueComponent($name, $lowerName);
        } elseif ($this->option('react')) {
            $this->generateReactComponent($name, $lowerName);
        } elseif ($this->option('api') || $this->option('filament')) {
            // No blade views for API or Filament
        } else {
            $this->generateBladeViews($name, $lowerName);
        }
    }

    protected function generateLivewire(string $name, string $lowerName): void
    {
        $viewPath    = $this->option('view_path');
        $path        = $this->option('path');
        $livewireName = $viewPath
            ? "{$viewPath}/{$name}"
            : ($path ? "{$path}/{$name}" : $name);

        if ($this->option('dry-run')) {
            $this->line("  <fg=cyan>[dry-run]</> Would create Livewire: <fg=yellow>app/Livewire/{$livewireName}.php</>");
            return;
        }

        $this->call('make:livewire', ['name' => $livewireName]);
        $this->line("  <fg=green>✔</> Livewire: <fg=yellow>app/Livewire/{$livewireName}.php</>");
    }

    protected function generateVueComponent(string $name, string $lowerName): void
    {
        $viewPath = $this->option('view_path');
        $path     = $this->option('path');
        $dir      = $viewPath ? "js/{$viewPath}" : ($path ? "js/{$path}" : 'js/components');

        $this->generateFrontendFile('vue_component', $dir, $name, $lowerName, 'vue');
        $this->line("  <fg=green>✔</> Vue: <fg=yellow>resources/{$dir}/{$name}.vue</>");
    }

    protected function generateReactComponent(string $name, string $lowerName): void
    {
        $viewPath = $this->option('view_path');
        $path     = $this->option('path');
        $dir      = $viewPath ? "js/{$viewPath}" : ($path ? "js/{$path}" : 'js/components');

        $this->generateFrontendFile('react_component', $dir, $name, $lowerName, 'jsx');
        $this->line("  <fg=green>✔</> React: <fg=yellow>resources/{$dir}/{$name}.jsx</>");
    }

    protected function generateBladeViews(string $name, string $lowerName): void
    {
        $viewPath = $this->option('view_path');
        $path     = $this->option('path');
        $viewDir  = $viewPath ?: ($path ? "{$path}/{$lowerName}" : $lowerName);

        $tokens = $this->buildTokens($name);

        foreach (['index', 'create', 'edit', 'show'] as $view) {
            $destination = resource_path("views/{$viewDir}/{$view}.blade.php");
            $content     = $this->resolveStub("view_{$view}");
            $content     = $this->replaceTokens($content, $tokens);
            $this->writeFile($destination, $content, "resources/views/{$viewDir}/{$view}.blade.php");
        }
    }

    protected function generateFrontendFile(string $stub, string $dir, string $name, string $lowerName, string $ext): void
    {
        $tokens      = $this->buildTokens($name);
        $content     = $this->resolveStub($stub);
        $content     = $this->replaceTokens($content, $tokens);
        $destination = resource_path("{$dir}/{$name}.{$ext}");

        $this->writeFile($destination, $content, "resources/{$dir}/{$name}.{$ext}");
    }

    // -------------------------------------------------------------------------
    // Patterns
    // -------------------------------------------------------------------------

    protected function generatePattern(string $name): void
    {
        $pattern = $this->option('pattern');

        if ($pattern === 'repository') {
            $this->call('make:repository', [
                'name'    => "{$name}Repository",
                '--model' => $name,
                '--force' => $this->option('force'),
            ]);
        } elseif ($pattern === 'service') {
            $this->call('make:service', [
                'name'    => "{$name}Service",
                '--model' => $name,
                '--force' => $this->option('force'),
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Blueprint mode
    // -------------------------------------------------------------------------

    protected function runBlueprint(string $name, string $blueprintKey): void
    {
        $blueprints = config('super-artisan.blueprints', []);

        if (! isset($blueprints[$blueprintKey])) {
            $this->error("Blueprint '{$blueprintKey}' not found in config/super-artisan.php.");
            $this->line('Available blueprints: ' . implode(', ', array_keys($blueprints)));
            return;
        }

        $blueprint = $blueprints[$blueprintKey];
        $this->line("  <fg=gray>Running blueprint: {$blueprintKey} — {$blueprint['description']}</>");

        foreach ($blueprint['commands'] as $commandTemplate) {
            $cmd = str_replace(
                ['{name}', '{lower_name}', '{plural}'],
                [$name, Str::lower($name), Str::plural($name)],
                $commandTemplate
            );

            if ($this->option('dry-run')) {
                $this->line("  <fg=cyan>[dry-run]</> Would run: <fg=yellow>php artisan {$cmd}</>");
                continue;
            }

            // Parse command string into name + options
            $parts       = explode(' ', $cmd);
            $commandName = array_shift($parts);
            $args        = [];
            $opts        = [];

            foreach ($parts as $part) {
                if (str_starts_with($part, '--')) {
                    $opt = ltrim($part, '-');
                    if (str_contains($opt, '=')) {
                        [$k, $v] = explode('=', $opt, 2);
                        $opts["--{$k}"] = $v;
                    } else {
                        $opts["--{$opt}"] = true;
                    }
                } else {
                    $args[] = $part;
                }
            }

            $this->call($commandName, array_merge($args ? ['name' => implode(' ', $args)] : [], $opts));
        }
    }

    // -------------------------------------------------------------------------
    // Interactive mode
    // -------------------------------------------------------------------------

    protected function runInteractiveMode(): void
    {
        $this->line('');
        $this->line('<fg=magenta;options=bold>🎯 Super Artisan — Interactive Mode</>');
        $this->line('');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function resolveNamespacePrefix(): string
    {
        $domain  = $this->option('domain');
        $ctrlPath = $this->option('controller_path') ?? $this->option('path');

        if ($domain) {
            return "\\{$domain}";
        }
        if ($ctrlPath) {
            return "\\{$ctrlPath}";
        }

        return '';
    }
}