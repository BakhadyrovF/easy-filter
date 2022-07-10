<?php

namespace Bakhadyrovf\EasyFilter\Providers;

use Bakhadyrovf\EasyFilter\Console\MakeFilterClass;
use Illuminate\Support\ServiceProvider;

class QueryFilterProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../config/easy-filter.php' => config_path('easy-filter.php')], 'easy-filter-config');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/easy-filter.php', 'easy-filter');

        if ($this->app->runningInConsole()){
            $this->commands(MakeFilterClass::class);
        }
    }
}
