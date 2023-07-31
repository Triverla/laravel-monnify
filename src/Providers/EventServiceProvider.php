<?php

namespace Triverla\LaravelMonnify\Providers;

use Illuminate\Support\ServiceProvider;
use Triverla\LaravelMonnify\Events\NewIncomingWebHook;
use Triverla\LaravelMonnify\Listeners\MonnifyWebhookListener;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        NewIncomingWebHook::class => [
            MonnifyWebhookListener::class,
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot(): void
    {
       //
    }
}
