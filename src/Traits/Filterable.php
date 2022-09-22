<?php

namespace Bakhadyrovf\EasyFilter\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    public function scopeFilter(Builder $builder, array $except = [])
    {
        $filterClass = method_exists($this, 'provideFilter') ? $this->provideFilter() : $this->getFilterClass();

        if (!class_exists($filterClass)) {
            $filterClass = $filterClass . 'Filter';
        }

        return app($filterClass)
            ->setBuilder($builder)
            ->setParameters($except)
            ->apply();
    }

    protected function getFilterClass()
    {
        return str_replace('Models', config('easy-filter.base-folder'), self::class);
    }



}
