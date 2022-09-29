<?php

namespace Outl1ne\NovaTwoFactor;

use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class NovaTwoFactor extends Tool
{
    public function boot()
    {
        Nova::script('nova-two-factor', __DIR__ . '/../dist/js/entry.js');
        Nova::style('nova-two-factor', __DIR__ . '/../dist/css/entry.css');
    }

    public static function getTableName()
    {
        return config('nova-two-factor.table', 'users_two_factor');
    }

    public static function getExcludedRoutes()
    {
        $except = [
            'nova-vendor/nova-two-factor/authenticate',
            'nova-vendor/nova-two-factor/recover'
        ];

        return array_merge($except, config('nova-two-factor.excluded_routes'));
    }
}
