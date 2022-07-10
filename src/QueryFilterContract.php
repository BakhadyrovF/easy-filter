<?php

namespace Bakhadyrovf\EasyFilter;

use Illuminate\Database\Eloquent\Builder;

interface QueryFilterContract
{
    public static function filter(string|Builder $builder): Builder;
}
