<?php

namespace RahasIstiyak\SuperArtisan\Commands;

use Illuminate\Console\Command;
use RahasIstiyak\SuperArtisan\Concerns\GeneratesFiles;

class MakeSuperRequest extends Command
{
    use GeneratesFiles;

    protected $signature = 'make:super-request
        {name : FormRequest class name (e.g. StorePostRequest)}
        {--rules= : Comma-separated rules (e.g. title:required,body:required|max:500)}
        {--force  : Overwrite existing files}
        {--dry-run : Preview without writing}';

    protected $description = 'Generate a FormRequest class with optional inline validation rules';

    public function handle(): int
    {
        $name  = $this->argument('name');
        $rules = $this->buildRulesArray($this->option('rules'));

        $tokens = [
            '{{ name }}'  => $name,
            '{{ rules }}' => $rules,
        ];

        $content     = $this->resolveStub('request');
        $content     = $this->replaceTokens($content, $tokens);
        $destination = app_path("Http/Requests/{$name}.php");

        $this->line('');
        $this->line("<fg=blue;options=bold>» FormRequest: {$name}</>");
        $this->line(str_repeat('─', 50));
        $this->writeFile($destination, $content, "app/Http/Requests/{$name}.php");
        $this->line('');

        return self::SUCCESS;
    }

    protected function buildRulesArray(?string $rulesOption): string
    {
        if (! $rulesOption) {
            return "// 'field' => 'required|string|max:255',";
        }

        $lines = [];
        foreach (explode(',', $rulesOption) as $rule) {
            $parts = explode(':', $rule, 2);
            $field = trim($parts[0]);
            $ruleStr = isset($parts[1]) ? trim($parts[1]) : 'required';
            $lines[] = "            '{$field}' => '{$ruleStr}',";
        }

        return implode("\n", $lines);
    }
}
