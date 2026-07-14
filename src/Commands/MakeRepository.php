<?php

namespace RahasIstiyak\SuperArtisan\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RahasIstiyak\SuperArtisan\Concerns\GeneratesFiles;

class MakeRepository extends Command
{
    use GeneratesFiles;

    protected $signature = 'make:repository
        {name : Repository class name (e.g. PostRepository)}
        {--model=   : Model class to bind (defaults to name prefix)}
        {--no-interface : Skip generating the interface}
        {--no-binding   : Skip generating/updating RepositoryServiceProvider}
        {--force        : Overwrite existing files}
        {--dry-run      : Preview what would be generated}';

    protected $description = 'Generate a repository class, interface, and service provider binding';

    public function handle(): int
    {
        $name  = $this->argument('name');
        $model = $this->option('model') ?? Str::before($name, 'Repository');

        $this->line('');
        $this->line("<fg=blue;options=bold>» Repository: {$name}</>");
        $this->line(str_repeat('─', 50));

        // 1. Repository Class
        $this->generateRepositoryClass($name, $model);

        // 2. Interface
        if (! $this->option('no-interface')) {
            $this->generateRepositoryInterface($name, $model);
        }

        // 3. Service Provider binding
        if (! $this->option('no-binding')) {
            $this->generateOrUpdateServiceProvider($name, $model);
        }

        $this->line('');
        if (! $this->option('dry-run')) {
            $this->line('<fg=green>✔ Repository scaffolding complete!</>');
            $this->line('');
            $this->line('<fg=gray>Next steps:</>');
            $this->line("  1. Add <fg=yellow>RepositoryServiceProvider::class</> to <fg=yellow>bootstrap/providers.php</> (Laravel 11+)");
            $this->line("  2. Add validation rules to your repository methods as needed.");
        }

        return self::SUCCESS;
    }

    protected function generateRepositoryClass(string $name, string $model): void
    {
        $tokens  = $this->buildTokens($model);
        // Override {{ name }} with the repository class name
        $tokens['{{ name }}'] = $name;

        $content     = $this->resolveStub('repository');
        $content     = $this->replaceTokens($content, $tokens);
        $destination = app_path("Repositories/{$name}.php");

        $this->writeFile($destination, $content, "app/Repositories/{$name}.php");
    }

    protected function generateRepositoryInterface(string $name, string $model): void
    {
        $interfaceName = "{$name}Interface";
        $tokens        = $this->buildTokens($model);
        $tokens['{{ name }}'] = $model; // For the interface stub's {{ name }}Interface

        $content     = $this->resolveStub('repository_interface');
        $content     = $this->replaceTokens($content, $tokens);

        // Also create the Contracts directory
        $destination = app_path("Repositories/Contracts/{$interfaceName}.php");
        $this->writeFile($destination, $content, "app/Repositories/Contracts/{$interfaceName}.php");
    }

    protected function generateOrUpdateServiceProvider(string $name, string $model): void
    {
        $providerPath = app_path('Providers/RepositoryServiceProvider.php');
        $interfaceFqn = "App\\Repositories\\Contracts\\{$name}Interface";
        $repoFqn      = "App\\Repositories\\{$name}";

        if ($this->option('dry-run')) {
            $this->line("  <fg=cyan>[dry-run]</> Would bind: <fg=yellow>{$interfaceFqn}</> → <fg=yellow>{$repoFqn}</>");
            return;
        }

        if (! file_exists($providerPath)) {
            // Create a fresh provider
            $content = $this->buildProviderContent($interfaceFqn, $repoFqn);
            $this->writeFile($providerPath, $content, "app/Providers/RepositoryServiceProvider.php");
        } else {
            // Append binding to existing provider if not already present
            $existing = file_get_contents($providerPath);
            $binding  = "\$this->app->bind(\\{$interfaceFqn}::class, \\{$repoFqn}::class);";

            if (str_contains($existing, $repoFqn)) {
                $this->warn("  Binding already exists in RepositoryServiceProvider.");
                return;
            }

            // Insert before the last closing brace of the register() or boot() method
            $existing = preg_replace(
                '/(\s*}\s*}\s*)$/',
                "\n        {$binding}\n    }\n}\n",
                $existing,
                1
            );
            file_put_contents($providerPath, $existing);
            $this->line("  <fg=green>✔</> Binding added to: <fg=yellow>app/Providers/RepositoryServiceProvider.php</>");
        }
    }

    protected function buildProviderContent(string $interfaceFqn, string $repoFqn): string
    {
        return "<?php\n\nnamespace App\\Providers;\n\nuse Illuminate\\Support\\ServiceProvider;\n\nclass RepositoryServiceProvider extends ServiceProvider\n{\n    public function register(): void\n    {\n        \$this->app->bind(\\\n            {$interfaceFqn}::class,\n            \\{$repoFqn}::class\n        );\n    }\n\n    public function boot(): void\n    {\n        //\n    }\n}\n";
    }
}