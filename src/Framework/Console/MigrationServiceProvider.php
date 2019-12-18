<?php
namespace Ronghz\LaravelDdd\Framework\Console;

use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Database\Console\Migrations\ResetCommand as MigrateResetCommand;
use Illuminate\Database\Console\Migrations\RollbackCommand as MigrateRollbackCommand;
use Illuminate\Database\Console\Migrations\StatusCommand as MigrateStatusCommand;
use Illuminate\Database\Migrations\Migrator;

class MigrationServiceProvider extends \Illuminate\Database\MigrationServiceProvider
{
    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerMigrateCommand(): void
    {
        $this->app->singleton('command.migrate', function ($app) {
            $this->autoLoadMigrations($app['migrator']);
            return new MigrateCommand($app['migrator']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerMigrateResetCommand(): void
    {
        $this->app->singleton('command.migrate.reset', function ($app) {
            $this->autoLoadMigrations($app['migrator']);
            return new MigrateResetCommand($app['migrator']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerMigrateRollbackCommand(): void
    {
        $this->app->singleton('command.migrate.rollback', function ($app) {
            $this->autoLoadMigrations($app['migrator']);
            return new MigrateRollbackCommand($app['migrator']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerMigrateStatusCommand(): void
    {
        $this->app->singleton('command.migrate.status', function ($app) {
            $this->autoLoadMigrations($app['migrator']);
            return new MigrateStatusCommand($app['migrator']);
        });
    }

    private function autoLoadMigrations(Migrator &$migrator): void
    {
        //自动获取框架基类的migrations文件
        $basePath = __DIR__ . '/../../Domain/';
        foreach (scandir($basePath) as $dir) {
            if ($dir != '.' && $dir != '..') {
                $path = $basePath . $dir . '/Models/migrations';
                if (is_dir($basePath . $dir) && is_dir($path)) {
                    $migrator->path($path);
                }
            }
        }
        //自动获取所有领域的migrations文件
        $basePath = app_path() . '/Domain/';
        foreach (scandir($basePath) as $dir) {
            if ($dir != '.' && $dir != '..') {
                $path = $basePath . $dir . '/Models/migrations';
                if (is_dir($basePath . $dir) && is_dir($path)) {
                    $migrator->path($path);
                }
            }
        }
    }
}
