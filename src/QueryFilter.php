<?php

namespace Bakhadyrovf\EasyFilter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class QueryFilter implements QueryFilterContract
{
    protected ?array $parameters;
    protected Builder $builder;

    /**
     * @inheritDoc
     */
    public static function filter(string|Builder $builder): Builder
    {
        $builder = $builder instanceof Builder
            ? $builder
            : app($builder)->query();

        return (new static())
            ->setBuilder($builder)
            ->setParameters()
            ->apply();
    }

    protected function apply(): Builder
    {
        if (!empty($this->parameters)) {
            foreach ($this->parameters as $parameter => $value) {
                if (method_exists($this, $parameter)) {
                    call_user_func([$this, $parameter], $this->builder, $value);
                }
            }
        }

        return $this->builder;
    }

    protected function setBuilder(Builder $builder)
    {
        $this->builder = $builder;

        return $this;
    }

    protected function setParameters()
    {
        $parameters = request('filters');

        if (!empty($parameters)) {
            foreach ($parameters as $parameter => $value) {
                $this->parameters[Str::camel($parameter)] = $value;
            }
        }

        return $this;
    }
}
