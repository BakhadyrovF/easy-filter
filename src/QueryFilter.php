<?php

namespace Bakhadyrovf\EasyFilter;

use Arr;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class QueryFilter
{
    protected ?array $parameters;
    protected Builder $builder;

    /**
     * @inheritDoc
     */
    public static function filter(string|Builder $builder, array $except = []): Builder
    {
        $builder = $builder instanceof Builder
            ? $builder
            : app($builder)->query();

        return (new static())
            ->setBuilder($builder)
            ->setParameters($except)
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

    protected function setParameters(array $except = [])
    {
        try {
            $parameters = Validator::make(request()->query(), [
                '*' => ['filled', 'string']
            ])->validated();
        } catch (Exception) {
            return $this;
        }

        $exceptions = array_map(function ($item) {
            return Str::snake($item);
        }, $except);

        if (!empty($parameters)) {
            foreach (Arr::except($parameters, $exceptions) as $parameter => $value) {
                if (!$validatedValue = $this->validateValue($value)) {
                    continue;
                }

                $this->parameters[$this->validateParameter($parameter)] = $validatedValue;
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
            if (!$replaced = str_replace(['[', ']'], '', $value)) {
                return false;
            }

            return explode(',', $replaced);
        }

        return !empty($value)
            ? $value
            : false;
    }
}
