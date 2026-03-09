<?php

namespace KgySathsara\Monitor;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use KgySathsara\Monitor\Commands\MonitorCommand;
use KgySathsara\Monitor\Commands\CleanLogsCommand;
use KgySathsara\Monitor\Middleware\KgySathsaraAuth;

class KgySathsaraServiceProvider extends ServiceProvider
{
    /**
     * KGY Sathsara's Monitor Version
     */
    const VERSION = '1.0.0';

    public function boot()
    {
        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations')
        ], 'kgy-sathsara-migrations');

        // Publish config
        $this->publishes([
            __DIR__.'/../config/kgy-sathsara.php' => config_path('kgy-sathsara.php')
        ], 'kgy-sathsara-config');

        // Publish views
        $this->publishes([
            __DIR__.'/views' => resource_path('views/vendor/kgy-sathsara')
        ], 'kgy-sathsara-views');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        
        // Load views
        $this->loadViewsFrom(__DIR__.'/views', 'kgy-sathsara');

        // Register middleware
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('kgy-sathsara-auth', KgySathsaraAuth::class);

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                MonitorCommand::class,
                CleanLogsCommand::class,
            ]);
        }

        // Log package booted
        \Illuminate\Support\Facades\Log::info('KGY Sathsara Monitor v' . self::VERSION . ' loaded successfully');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/kgy-sathsara.php', 'kgy-sathsara'
        );

        $this->app->singleton('kgy-sathsara-monitor', function () {
            return new KgySathsaraMonitor();
        });
    }
}