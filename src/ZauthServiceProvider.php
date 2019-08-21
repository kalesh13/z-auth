<?php

namespace Zauth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Zauth\Commands\ZclientCommand;
use Zauth\Guards\Zguard;

class ZauthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands(ZclientCommand::class);
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }

        Auth::extend('zauth', function ($app, $name, array $config) {
            return new Zguard(
                Auth::createUserProvider($config['provider']),
                $app['request'],
                $app['cookie']
            );
        });
    }
}
