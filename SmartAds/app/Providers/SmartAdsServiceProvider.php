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
        
        // استخدام __DIR__ لضمان تحميل الملفات من نفس مجلد الموديول الحالي
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // 🔥 إجبار تحميل الروتات باستخدام مسارات ثابتة
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/../../routes/segment.php');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/smartads-fcm.php', 'smartads-fcm');
    }

    protected function registerConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/config.php' => config_path($this->moduleNameLower . '.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__ . '/../../config/config.php', $this->moduleNameLower);
    }

    public function registerViews(): void
    {
        $sourcePath = __DIR__ . '/../../resources/views';
        $this->loadViewsFrom($sourcePath, $this->moduleNameLower);
    }

    public function registerTranslations(): void
    {
        $langPath = __DIR__ . '/../../resources/lang';
        $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        $this->loadJsonTranslationsFrom($langPath);
    }
}
