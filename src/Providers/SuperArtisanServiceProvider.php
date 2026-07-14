<?php

namespace RahasIstiyak\SuperArtisan\Providers;

use Illuminate\Support\ServiceProvider;
use RahasIstiyak\SuperArtisan\Commands\MakeSuper;
use RahasIstiyak\SuperArtisan\Commands\MakeRepository;
use RahasIstiyak\SuperArtisan\Commands\MakeService;
use RahasIstiyak\SuperArtisan\Commands\MakeSuperRequest;
use RahasIstiyak\SuperArtisan\Commands\MakeSuperPolicy;
use RahasIstiyak\SuperArtisan\Commands\MakeSuperAction;
use RahasIstiyak\SuperArtisan\Commands\RunWorkflow;
use RahasIstiyak\SuperArtisan\Commands\ListWorkflows;

class SuperArtisanServiceProvider extends ServiceProvider
{
    /**
     * All Super Artisan commands.
     *
     * @var array<class-string>
     */
    protected array $commands = [
        MakeSuper::class,
        MakeRepository::class,
        MakeService::class,
        MakeSuperRequest::class,
        MakeSuperPolicy::class,
        MakeSuperAction::class,
        RunWorkflow::class,
        ListWorkflows::class,
    ];

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);

            // Publish config
            $this->publishes([
                __DIR__ . '/../../config/super-artisan.php' => config_path('super-artisan.php'),
            ], 'super-artisan-config');

            // Publish stubs
            $this->publishes([
                __DIR__ . '/../../stubs/' => base_path('stubs/vendor/super-artisan'),
            ], 'super-artisan-stubs');
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/super-artisan.php',
            'super-artisan'
        );
    }
}