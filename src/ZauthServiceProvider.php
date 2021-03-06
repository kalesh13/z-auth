<?php

namespace Zauth;

use Zauth\Guards\Zguard;
use Zauth\Commands\ZclientCommand;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class ZauthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Register  the authentication guard
        Auth::extend('zauth', function ($app, $name, array $config) {
            return new Zguard(
                Auth::createUserProvider($config['provider']),
                $app['request'],
                $app['cookie']
            );
        });
        
        // Register the package middlewares.
        $router = $this->app['router'];

        if (!is_null($router)) {
            $router->aliasMiddleware('auth.admin', \Zauth\Http\Middleware\AdminAuth::class);
            $router->aliasMiddleware('admin.guest', \Zauth\Http\Middleware\AdminRedirectIfAuthenticated::class);
            $router->aliasMiddleware('role', \Zauth\Http\Middleware\CheckRole::class);
            $router->aliasMiddleware('client', \Zauth\Http\Middleware\CheckClient::class);
            $router->aliasMiddleware('adminOrClient', \Zauth\Http\Middleware\AdminOrClient::class);
        }

        // Register package commands and database migrations
        if ($this->app->runningInConsole()) {
            $this->commands(ZclientCommand::class);
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }

}
