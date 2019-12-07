<?php

namespace Cndrsdrmn\Repositories;

use Cndrsdrmn\Repositories\Contracts\RepositoryInterface;
use Cndrsdrmn\Repositories\Exceptions\RepositoryException;
use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ReflectionObject;

abstract class BaseRepository implements RepositoryInterface
{
	/**
     * The array of booted repositories.
     *
     * @var array
     */
    protected static $booted = [];

    /**
     * The array of payload fillable 
     *
     * @var array
     */
    protected $payloadFillable = [];

    /**
     * The IoC container instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * The connection name for the repository.
     *
     * @var string
     */
    protected $connection;

	/**
	 * Create instace of model
	 * 
	 * @var string|\Illuminate\Database\Eloquent\Model
	 */
	protected $model;

	/**
	 * Create instance of Base Repository
	 *
	 * @return void
	 */
	public function __construct()
	{
        $this->bootIfNotBooted();

        $this->model = $this->createModel();
	}

	/**
     * Check if the repository needs to be booted and if so, do it.
     *
     * @return void
     */
    protected function bootIfNotBooted()
    {
        if (! isset(static::$booted[static::class])) {
            static::$booted[static::class] = true;

        	static::boot();
        }
    }

	/**
     * The "booting" method of the repository.
     *
     * @return void
     */
    protected function boot()
    {
        static::bootScopes();
    }

    /**
     * Boot all of the bootable traits on the repository.
     *
     * @return void
     */
    protected function bootScopes()
    {
        $scope = new RepositoryScope;

        $reflection = new ReflectionObject($this);

        foreach (get_class_methods($this) as $method) {

        	if (preg_match('/^scope(.+)$/', $method, $matches)) {

        		$scopeName 	 = lcfirst($matches[1]);

        		$scopeMethod = $reflection->getMethod($method)->getClosure($this)->bindTo(null);

        		$scope->addScope($scopeName, $scopeMethod);
            }
        }

		$scope->applyScopes();
    }

    /**
     * {@inheritdoc}
     */
    public function createModel()
    {
        if (is_string($model = $this->getModel())) {

            if (! class_exists($class = '\\'.ltrim($model, '\\'))) {
                throw new RepositoryException("Class {$model} does NOT exist!");
            }

            $model = $this->getContainer()->make($class);
        }
     
        if (! empty($this->connection)) {
            $model = $model->setConnection($this->connection);
        }

        if (! $model instanceof Model) {
            throw new RepositoryException(
            	"Class {$model} must be an instance of \\Illuminate\\Database\\Eloquent\\Model"
            );
        }

        return $model;
    }

    /**
     * Do create or update resource
     * 
     * @param  \Illuminate\Http\Request $request
     * @param  \Illuminate\Database\Eloquent\Model|string|null  $model
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function doCreateOrUpdate(Request $request, &$model = null)
    {
        return $this->doTransaction(function () use ($request, $model) {

            return $this->executeCreateOrUpdate($request, $model);
        });
    }

    /**
     * Do delete resource
     * 
     * @param  \Illuminate\Database\Eloquent\Model|string  $model
     * @return boolean
     * 
     * @throws \App\Exceptions\FailedDeleteException
     */
    public function doDelete($model)
    {
        if ( ! $model instanceof Model ) {
            $model = $this->findByIdentifire($model);
        }

        return $model->delete();
    }

    /**
     * Execute a Closure within a transaction.
     *
     * @param  \Closure  $callback
     * @param  int  $attempts
     * @return mixed
     *
     * @throws \Throwable
     */
    final function doTransaction(Closure $callback, $attempts = 1)
    {
        return DB::transaction($callback, $attempts);
    }

    /**
     * Execute the action create or update
     * 
     * @param  \Illuminate\Http\Request $request
     * @param  \Illuminate\Database\Eloquent\Model|string|null  $model
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function executeCreateOrUpdate(Request $request, &$model = null)
    {
        $data = $this->payloads($request);

        if ( ! is_null($model) ) {
            $model = $this->findByIdentifire($model);
            $model->update($data);
        } else {
            $model = $this->model->create($data);
        }

        return $model;
    }

    /**
     * Find model by identifire
     * 
     * @param  string   $model
     * @param  array    $columns
     * @param  boolean  $fail
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findByIdentifire($model, array $columns = ['*'], $fail = true)
    {
        $primaryKey = $this->model->getKeyName();

        $query = $this->model->where($primaryKey, (int) $model);

        if ( method_exists($this->model, 'getSlugKeyName') ) {
            $query->orWhere($this->model->getSlugKeyName(), $model);
        }

        if ( method_exists($this->model, 'getUuidKeyName') ) {
            $query->orWhere($this->model->getUuidKeyName(), $model);
        }

        $method = $fail ? 'firstOrFail' : 'first';

        return $query->{$method}($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getContainer($service = null)
    {
        return is_null($service) ? ($this->container ?: app()) : ($this->container[$service] ?: app($service));
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection(): string
    {
        return $this->connection;
    }

	/**
	 * Get model of repository
	 * 
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function getModel()
	{
		return $this->model;
	}

    /**
     * Get payload fillable of repository
     * 
     * @return array
     */
    public function getPayloadFillable()
    {
        return $this->payloadFillable;
    }

    /**
     * Get payload fillable of repository
     * 
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function payloads(Request $request)
    {
        return $request->only($this->getPayloadFillable() ?: $this->getFillable());
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setConnection($name)
    {
        $this->connection = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
	public function setModel(Model $model)
	{
		$this->model = $model;

        $this->createModel();

		return $this;
	}

	/**
     * Handle dynamic method calls into the Response.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, array $parameters)
    {
        return call_user_func_array([$this->createModel(), $method], $parameters);
    }
}
