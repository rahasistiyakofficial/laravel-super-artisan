<?php

namespace RahasIstiyak\SuperArtisan\Providers;

use Illuminate\Support\ServiceProvider;
use RahasIstiyak\SuperArtisan\Commands\MakeSuper;
use RahasIstiyak\SuperArtisan\Commands\MakeRepository;
use RahasIstiyak\SuperArtisan\Commands\MakeService;

class SuperArtisanServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeSuper::class,
                MakeRepository::class,
                MakeService::class,
            ]);

            $this->publishes([
                __DIR__ . '/../../config/super-artisan.php' => config_path('super-artisan.php'),
            ], 'super-artisan-config');

            $this->publishes([
                __DIR__ . '/../../stubs/' => base_path('stubs/vendor/super-artisan'),
            ], 'super-artisan-stubs');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/super-artisan.php', 'super-artisan');
    }
}