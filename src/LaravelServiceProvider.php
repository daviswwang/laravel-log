<?php

namespace Daviswwang\LaravelLog;

use Daviswwang\LaravelLog\Facades\LaravelLog;
use Illuminate\Support\ServiceProvider;


class LaravelServiceProvider extends ServiceProvider
{

    protected $defer = true;

    public function register()
    {
        $this->app->singleton(LaravelLog::class, function () {
            return new LaravelLog();
//            return new LaravelLog(...[array_values(config('default')), config('bucketName')]);
        });
        $this->app->alias(LaravelLog::class, 'laravellog');
    }

    public function provides()
    {
        return [LaravelLog::class, 'laravellog'];
    }

    public function boot()
    {
        $path = realpath(__DIR__ . '/Config/LogConfig.php');
        $this->publishes([$path => config_path('laravellog.php')], 'config');
        $this->mergeConfigFrom($path, 'laravellog');
    }


}
