<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

class AdminLTEServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Register the custom menu item types
        $this->registerCustomMenuItems();
    }
    
    /**
     * Register custom menu item types
     *
     * @return void
     */
    private function registerCustomMenuItems()
    {
        // Register the language dropdown menu item type
        app('adminlte.menu.item-types')->register('language-dropdown-menu', function ($item, $item_key) {
            return view('vendor.adminlte.partials.navbar.menu-item-language-dropdown');
        });
    }
} 