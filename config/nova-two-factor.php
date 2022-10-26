<?php

return [
    'enabled' => env('NOVA_2FA_ENABLED', true),

    'table' => 'users_two_factor',

    // Define the connection where the table should be exists
    'connection' => null,

    // Optionally disable automatically loading migrations
    'autoload_migrations' => true,

    // Define the user model
    'user_model' => \App\Models\User::class,

    // Menu options
    'menu' => [
        'show' => true,
        'icon' => 'lock-closed',
    ],

    // Exclude routes from 2FA
    'excluded_routes' => [
        //
    ]
];
