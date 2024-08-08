<?php

return [
    'setup' => [
        [
            'command' => 'migration:create',
            'description' => 'Create an empty migration file',
            'arguments' => [
                [
                    'name' => 'name',
                    'description' => 'Migration file name',
                    'required' => true,
                ]
            ]
        ],
        [
            'command' => 'migration:run',
            'description' => 'Run all migrations',
            'arguments' => []
        ],
        [
            'command' => 'migration:down',
            'description' => 'Rollback all migrations',
            'arguments' => []
        ],
        [
            'command' => 'db:seed',
            'description' => 'Run database seeders'
        ]
    ],
    'commands' => [
        'migration:create' => App\Commands\Migrations\Create::class,
        'migration:run' => App\Commands\Migrations\Run::class,
        'migration:down' => App\Commands\Migrations\Down::class,
        'db:seed' => App\Commands\Seed::class,
    ]
];
