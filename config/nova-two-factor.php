<?php

return [
    'enabled' => env('NOVA_2FA_ENABLED', true),

    'table' => 'users_two_factor',

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
