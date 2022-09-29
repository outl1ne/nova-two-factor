<?php

return [
    'enabled' => env('NOVA_TWO_FA_ENABLE', true),

    'user_id_column' => 'id',
    'user_model' => \App\Models\User::class,

    /* Exclude any routes from 2fa security */
    'excluded_routes' => [
        //
    ]
];
