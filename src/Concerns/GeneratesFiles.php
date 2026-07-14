<?php

namespace RahasIstiyak\SuperArtisan\Concerns;

use Illuminate\Support\Str;

trait GeneratesFiles
{
    /**
     * Resolve a stub's content, preferring published vendor stubs over package defaults.
     *
     * @param  string  $stubName  e.g. 'repository', 'view_index'
     * @return string
     */
    protected function resolveStub(string $stubName): string
    {
        $publishedPath = base_path("stubs/vendor/super-artisan/{$stubName}.stub");
        $defaultPath   = __DIR__ . '/../../stubs/' . $stubName . '.stub';

        $path = file_exists($publishedPath) ? $publishedPath : $defaultPath;

        if (! file_exists($path)) {
            $this->error("Stub not found: {$stubName}.stub");
            return '';
        }

        return file_get_contents($path);
    }

    /**
     * Replace all tokens in stub content.
     *
     * @param  string  $content
     * @param  array   $replacements  ['{{ token }}' => 'value', ...]
     * @return string
     */
    protected function replaceTokens(string $content, array $replacements): string
    {
        foreach ($replacements as $token => $value) {
            $content = str_replace($token, $value, $content);
        }

        return $content;
    }

    /**
     * Write content to a file, respecting --force and --dry-run options.
     *
     * @param  string  $destination  Absolute path
     * @param  string  $content
     * @param  string  $label        Human-friendly label for output
     * @return bool    Whether the file was written
     */
    protected function writeFile(string $destination, string $content, string $label = ''): bool
    {
        $isDryRun = method_exists($this, 'option') && $this->option('dry-run');
        $isForce  = method_exists($this, 'option') && $this->option('force');
        $label    = $label ?: $destination;

        if ($isDryRun) {
            $this->line("  <fg=cyan>[dry-run]</> Would write: <fg=yellow>{$label}</>");
            return false;
        }

        if (file_exists($destination) && ! $isForce) {
            $this->warn("  Skipped (already exists): {$label}");
            return false;
        }

        $dir = dirname($destination);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($destination, $content);

        $this->line("  <fg=green>✔</> Created: <fg=yellow>{$label}</>");

        return true;
    }

    /**
     * Build standard token replacements for a named resource.
     *
     * @param  string       $name       PascalCase name, e.g. 'Post'
     * @param  string|null  $namespace  Optional namespace suffix, e.g. 'Admin'
     * @return array
     */
    protected function buildTokens(string $name, ?string $namespace = null): array
    {
        $plural       = Str::plural($name);
        $lowerName    = Str::lower($name);
        $lowerPlural  = Str::lower($plural);
        $snakeName    = Str::snake($name);
        $snakePlural  = Str::snake($plural);
        $kebabName    = Str::kebab($name);
        $nsPrefix     = $namespace ? "\\{$namespace}" : '';
        $nsDirPrefix  = $namespace ? "{$namespace}\\" : '';

        return [
            '{{ name }}'            => $name,
            '{{ lower_name }}'      => $lowerName,
            '{{ plural }}'          => $plural,
            '{{ lower_plural }}'    => $lowerPlural,
            '{{ snake_name }}'      => $snakeName,
            '{{ snake_plural }}'    => $snakePlural,
            '{{ kebab_name }}'      => $kebabName,
            '{{ namespace }}'       => $namespace ?? 'App',
            '{{ ns_prefix }}'       => $nsPrefix,
            '{{ ns_dir_prefix }}'   => $nsDirPrefix,
        ];
    }
}
