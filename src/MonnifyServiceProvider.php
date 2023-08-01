<?php

namespace Triverla\LaravelMonnify;

use Illuminate\Support\Facades\Route;
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
        Route::middleware('api')->group(function () {
            $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        });

        if (function_exists('config_path') && $this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../resources/config/monnify.php' => config_path('monnify.php'),
            ], 'config');

            if (!class_exists('CreateIncomingWebHooksTable ')) {
                $this->publishes([
                    __DIR__ . '/database/migrations/create_incoming_webhooks_table.php.stub' => database_path('migrations/' .'2023_07_29_000000'. '_create_incoming_webhooks_table.php'),
                    // you can add any number of migrations here
                ], 'migrations');
            }
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
