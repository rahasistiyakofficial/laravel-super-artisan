<?php

return [
    'blueprints' => [
        'crud' => [
            'description' => 'Generates a full CRUD stack for a resource.',
            'commands' => [
                'make:model {name} -m -f',
                'make:controller {name}Controller --resource --model={name}',
            ],
        ],
        'livewire' => [
            'description' => 'Generates a Livewire component with model and migration.',
            'commands' => [
                'make:model {name} -m',
                'make:livewire {name}',
            ],
        ],
    ],
    'workflows' => [
        'deploy' => [
            'description' => 'Clears caches and runs migrations.',
            'commands' => [
                'optimize:clear',
                'migrate --force',
                'route:cache',
                'view:cache',
            ],
        ],
    ],
];