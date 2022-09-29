<?php

namespace Outl1ne\NovaTwoFactor;

use Laravel\Nova\Nova;
use Laravel\Nova\Tool;
use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;

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

    public function menu(Request $request)
    {
        if (config('nova-two-factor.menu.show', true)) {
            return MenuSection::make(__('twoFactor.menuItemTitle'))
                ->path('/nova-two-factor')
                ->icon(config('nova-two-factor.menu.icon'));
        }
    }
}
