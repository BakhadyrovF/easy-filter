<?php

namespace Bakhadyrovf\EasyFilter\Providers;

use Bakhadyrovf\EasyFilter\Console\MakeFilterClass;
use Illuminate\Support\ServiceProvider;

class QueryFilterProvider extends ServiceProvider
{
    public function register()
    {
        if ($this->app->runningInConsole()){
            $this->commands(MakeFilterClass::class);
        }
    }
}
