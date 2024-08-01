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
//        [
//            'command' => 'seed:users',
//            'description' => 'Generate fake data for users table',
//            'arguments' => [
//                [
//                    'name' => 'count',
//                    'description' => 'Count of users',
//                    'required' => false,
//                ]
//            ]
//        ]
    ],
    'commands' => [
        'migration:create' => App\Commands\Migrations\Create::class,
        'migration:run' => App\Commands\Migrations\Run::class,
    ]
];
