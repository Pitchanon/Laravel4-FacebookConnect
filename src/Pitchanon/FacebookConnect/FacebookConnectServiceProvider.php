<?php

namespace Pitchanon\FacebookConnect;

use Illuminate\Support\ServiceProvider;
use Pitchanon\FacebookConnect\Provider\FacebookConnect;

class FacebookConnectServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../../config/facebook-connect.php', 'facebook-connect');

        $this->app->singleton(FacebookConnect::class, function ($app) {
            return new FacebookConnect($app['config']->get('facebook-connect'));
        });

        $this->app->alias(FacebookConnect::class, 'facebook-connect');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../../config/facebook-connect.php' => $this->app->configPath('facebook-connect.php'),
        ], 'facebook-connect-config');
    }

    public function provides(): array
    {
        return [FacebookConnect::class, 'facebook-connect'];
    }
}
