<?php
namespace Modules\Predictions\app\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }

    protected function mapApiRoutes(): void
    {
        // تم تعديل الـ Prefix إلى api/v1 ليتطابق مع AppConstants.baseUrl في Flutter
        Route::prefix('api/v1')
            ->middleware('api')
            ->group(module_path('Predictions', 'routes/api.php'));
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->group(module_path('Predictions', 'routes/web.php'));
    }
}
