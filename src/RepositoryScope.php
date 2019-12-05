<?php

namespace Cndrsdrmn\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Closure;

class RepositoryScope implements Scope
{
    /**
     * The array of scopes repository
     * 
     * @var array
     */
	protected $scopes = [];

	/**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        // 
    }

    /**
     * Apply the scopes to the Eloquent builder instance and return it.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function applyScopes()
    {
        foreach ($this->scopes as $name => $scope) {

            Builder::macro($name, function () use ($scope) {

                $arguments = func_get_args();

                array_unshift($arguments, $this);

                return call_user_func_array($scope, $arguments) ?: $this;
            });
        }
    }

    /**
     * Add new scope
     * 
     * @param string  $name
     * @param Closure $scope
     */
    public function addScope(string $name, Closure $scope)
    {
    	$this->scopes[$name] = $scope;
    }
}