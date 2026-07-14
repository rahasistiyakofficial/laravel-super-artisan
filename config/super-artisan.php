<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Blueprints
    |--------------------------------------------------------------------------
    |
    | Blueprints define sets of Artisan commands to generate files for a
    | resource. Run with: php artisan make:super {Name} --blueprint={key}
    |
    | Available tokens in commands: {name}, {lower_name}, {plural}, {snake}
    |
    */
    'blueprints' => [

        'crud' => [
            'description' => 'Full CRUD: Model + Migration + Factory + Resource Controller + Blade Views.',
            'commands'    => [
                'make:super {name}',
            ],
        ],

        'api-resource' => [
            'description' => 'API CRUD: Model + Migration + Factory + API Controller.',
            'commands'    => [
                'make:super {name} --api',
            ],
        ],

        'repository-crud' => [
            'description' => 'Full CRUD with Repository pattern: Model + Migration + Repository (class + interface + provider) + Controller.',
            'commands'    => [
                'make:super {name} --pattern=repository',
            ],
        ],

        'livewire' => [
            'description' => 'Model + Migration + Livewire component.',
            'commands'    => [
                'make:super {name} --livewire',
            ],
        ],

        'filament-resource' => [
            'description' => 'Model + Migration + Filament resource.',
            'commands'    => [
                'make:super {name} --filament',
            ],
        ],

        'full-stack' => [
            'description' => 'Complete stack: Model + Migration + Repository + Policy + FormRequest + Controller.',
            'commands'    => [
                'make:super-policy {name}Policy --model={name}',
                'make:super-request Store{name}Request',
                'make:super-request Update{name}Request',
                'make:super {name} --pattern=repository',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Workflows
    |--------------------------------------------------------------------------
    |
    | Workflows define sequences of Artisan commands to run in order.
    | Run with: php artisan run:workflow {key}
    |
    | Supports {name} token via --name= option.
    | Supports --dry-run and --stop-on-failure flags.
    |
    */
    'workflows' => [

        'deploy' => [
            'description' => 'Production deployment: clear caches, run migrations, rebuild caches.',
            'commands'    => [
                'down',
                'optimize:clear',
                'migrate --force',
                'route:cache',
                'view:cache',
                'config:cache',
                'event:cache',
                'up',
            ],
        ],

        'fresh' => [
            'description' => 'Wipe and re-seed the database (local development only).',
            'commands'    => [
                'migrate:fresh --seed',
            ],
        ],

        'optimize' => [
            'description' => 'Rebuild all application caches.',
            'commands'    => [
                'optimize:clear',
                'config:cache',
                'route:cache',
                'view:cache',
                'event:cache',
            ],
        ],

        'clear' => [
            'description' => 'Clear all application caches.',
            'commands'    => [
                'optimize:clear',
                'view:clear',
                'debugbar:clear',
            ],
        ],

        'test' => [
            'description' => 'Run the full test suite.',
            'commands'    => [
                'config:clear',
                'test',
            ],
        ],

        'test-and-deploy' => [
            'description' => 'Run tests, then deploy if they pass (use with --stop-on-failure).',
            'commands'    => [
                'test',
                'optimize:clear',
                'migrate --force',
                'route:cache',
                'view:cache',
                'config:cache',
            ],
        ],

        'queue-restart' => [
            'description' => 'Gracefully restart all queue workers.',
            'commands'    => [
                'queue:restart',
            ],
        ],

        'ide-helper' => [
            'description' => 'Regenerate IDE helper files (requires barryvdh/laravel-ide-helper).',
            'commands'    => [
                'ide-helper:generate',
                'ide-helper:meta',
                'ide-helper:models --write-mixin',
            ],
        ],

    ],

];