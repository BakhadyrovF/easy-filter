<?php

namespace Bakhadyrovf\EasyFilter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
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
        try {
            $parameters = Validator::make(request(config('easy-filter.base-parameter')) ?? [], [
                '*' => ['filled', 'string']
            ])->validated();
        } catch (\Exception) {
            return $this;
        }

        if (!empty($parameters)) {
            foreach ($parameters as $parameter => $value) {
                $this->parameters[$this->validateParameter($parameter)] = $this->validateValue($value);
            }
        }

        return $this;
    }

    protected function validateParameter(string $parameter)
    {
        if (Str::contains($parameter, '_')) {
            return Str::camel($parameter);
        }

        return $parameter;
    }

    protected function validateValue(string $value)
    {
        if (Str::contains($value, '[') && Str::contains($value, ']')) {
            return explode(',', str_replace(['[', ']'], '', $value));
        }

        return $value;
    }
}
