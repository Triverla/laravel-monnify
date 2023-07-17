<?php

namespace Triverla\LaravelMonnify;

use Illuminate\Support\ServiceProvider;

class MonnifyServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;


    /**
     * Boot the service provider.
     */
    public function boot()
    {
        if (function_exists('config_path') && $this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../resources/config/monnify.php' => config_path('monnify.php'),
            ], 'config');
        }
    }


    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../resources/config/monnify.php', 'monnify');

        $this->app->bind('laravel-monnify', function ($app) {
            $baseUrl = config('monnify.base_url');

            return new Monnify(
                $baseUrl,
                config('monnify')
            );
        });
    }
}
