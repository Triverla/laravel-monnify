<?php

namespace Triverla\LaravelMonnify;

use Illuminate\Support\ServiceProvider;
use Triverla\LaravelMonnify\Providers\EventServiceProvider;

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

            if (!class_exists('CreateIncomingWebHooksTable ')) {
                $this->publishes([
                    __DIR__ . '/database/migrations/create_incoming_webhooks_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_incoming_webhooks_table.php'),
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

        $this->app->register(EventServiceProvider::class);

        $this->app->bind('laravel-monnify', function ($app) {
            $baseUrl = config('monnify.base_url');

            return new Monnify(
                $baseUrl,
                config('monnify')
            );
        });
    }
}
