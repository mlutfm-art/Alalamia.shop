<?php

namespace Modules\SmartAds\app\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected $moduleNamespace = 'Modules\SmartAds\app\Http\Controllers';

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
        parent::boot();
        $this->map();
    }

    public function map(): void
    {
        $this->mapWebRoutes();
        $this->mapApiRoutes();
    }

    /*
     * Web routes — يُضيف config('route.admin_panel_link') كـ prefix خارجي
     */
    protected function mapWebRoutes(): void
    {
        $adminPrefix = config('route.admin_panel_link', 'admin');

        Route::middleware('web')
            ->prefix($adminPrefix)
            ->namespace($this->moduleNamespace)
            ->group(module_path('SmartAds', 'routes/web.php'));
    }

    protected function mapApiRoutes(): void
    {
        Route::prefix('api/v1/smartads')
            ->middleware('api')
            ->namespace($this->moduleNamespace)
            ->group(module_path('SmartAds', 'routes/api.php'));
    }
}