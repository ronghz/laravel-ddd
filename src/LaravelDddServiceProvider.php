<?php

namespace Ronghz\LaravelDdd;

use Illuminate\Support\ServiceProvider;
use Ronghz\LaravelDdd\Framework\Console\Commands\GeneratorCommand;

class LaravelDddServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/ddd.php',
            'ddd'
        );

        $this->registerCommands();
    }

    protected function registerCommands()
    {
        $this->app->singleton('ddd-generator', function ($app) {
            return new GeneratorCommand();
        });

        $this->commands(['ddd-generator']);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/ddd.php' => config_path('ddd.php')
        ]);
    }
}
