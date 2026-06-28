<?php
namespace Modules\Predictions\app\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Modules\Predictions\app\Events\PredictionMatchOpened;
use Modules\Predictions\app\Listeners\NotifyCustomersPredictionOpened;
use Modules\Predictions\app\Events\BannerActivated;
use Modules\Predictions\app\Listeners\NotifyCustomersBannerActivated;
use Modules\Predictions\app\Services\PredictionService;

class PredictionsServiceProvider extends ServiceProvider
{
    protected string $moduleName      = 'Predictions';
    protected string $moduleNameLower = 'predictions';

    public function boot(): void
    {
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'database/migrations'));
        $this->registerEvents();
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->singleton(PredictionService::class);
    }

    protected function registerEvents(): void
    {
        Event::listen(
            PredictionMatchOpened::class,
            NotifyCustomersPredictionOpened::class
        );

        /* ── Banner notifications ── */
        Event::listen(
            BannerActivated::class,
            NotifyCustomersBannerActivated::class
        );
    }

    protected function registerConfig(): void
    {
        $this->publishes([
            module_path($this->moduleName, 'config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'config/config.php'),
            $this->moduleNameLower
        );
    }

    protected function registerViews(): void
    {
        $sourcePath = module_path($this->moduleName, 'resources/views');
        $this->loadViewsFrom(
            array_merge($this->getPublishableViewPaths(), [$sourcePath]),
            $this->moduleNameLower
        );
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (app('config')->get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }
}
