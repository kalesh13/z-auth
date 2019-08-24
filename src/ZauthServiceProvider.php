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
        // Register the package middlewares.
        $router = $this->app['router'];

        if (!is_null($router)) {
            $router->aliasMiddleware('auth.admin', \Zauth\Http\Middleware\AdminAuth::class);
            $router->aliasMiddleware('admin.guest', \Zauth\Http\Middleware\AdminRedirectIfAuthenticated::class);
            $router->aliasMiddleware('role', \Zauth\Http\Middleware\CheckRole::class);
            $router->aliasMiddleware('client', \Zauth\Http\Middleware\CheckClient::class);
            $router->aliasMiddleware('adminOrClient', \Zauth\Http\Middleware\AdminOrClient::class);
        }

        // Register  the authentication guard
        Auth::extend('zauth', function ($app, $name, array $config) {
            return new Zguard(
                Auth::createUserProvider($config['provider']),
                $app['request'],
                $app['cookie']
            );
        });
    }
}
