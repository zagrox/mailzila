<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Contracts\Events\Dispatcher;

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
    public function boot(ViewFactory $view, Dispatcher $events, ConfigContract $config)
    {
        // Register the language dropdown component
        $this->loadViewsFrom(resource_path('views/vendor/adminlte'), 'adminlte');
        
        // Add the language dropdown to the menu
        $events->listen(BuildingMenu::class, function (BuildingMenu $event) {
            $event->menu->add([
                'type' => 'navbar-dropdown',
                'topnav_right' => true,
                'text' => '',
                'icon' => 'flag-icon flag-icon-us',
                'label' => 'English',
                'id' => 'language-dropdown',
                'submenu' => [
                    [
                        'text' => 'English',
                        'icon' => 'flag-icon flag-icon-us',
                        'url' => '#',
                        'data' => [
                            'lang' => 'en',
                        ]
                    ],
                    [
                        'text' => 'فارسی',
                        'icon' => 'flag-icon flag-icon-ir',
                        'url' => '#',
                        'data' => [
                            'lang' => 'fa',
                        ]
                    ],
                ],
            ]);
        });
    }
} 