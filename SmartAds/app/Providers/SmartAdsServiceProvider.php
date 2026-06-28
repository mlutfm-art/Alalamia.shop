<?php

namespace Modules\SmartAds\app\Providers;

use Illuminate\Support\ServiceProvider;

class SmartAdsServiceProvider extends ServiceProvider
{
    protected $moduleName = 'SmartAds';
    protected $moduleNameLower = 'smartads';

    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'database/migrations'));

        /**
         * 🔥 FIX: تحميل الروتات مباشرة بدون RouteServiceProvider
         * لأن النظام عندك لا يقوم بتسجيله بشكل صحيح
         */
        $this->loadRoutesFrom(module_path($this->moduleName, 'routes/web.php'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'routes/segment.php'));
        $this->loadRoutesFrom(module_path($this->moduleName, 'routes/api.php'));
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/smartads-fcm.php', 'smartads-fcm');

        /**
         * ❌ تم إزالة RouteServiceProvider
         * لأنه لا يتم تحميله في نظامك الحالي
         */
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

    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(
            array_merge($this->getPublishableViewPaths(), [$sourcePath]),
            $this->moduleNameLower
        );
    }

    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'resources/lang'), $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'resources/lang'));
        }
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];

        foreach (\Config::get('view.paths', []) as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }

        return $paths;
    }
}