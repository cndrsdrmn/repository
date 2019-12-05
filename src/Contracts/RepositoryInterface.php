<?php

namespace Cndrsdrmn\Repositories\Contracts;

use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface RepositoryInterface
{
    /**
     * Do create or update resource
     * 
     * @param  \Illuminate\Http\Request $request
     * @param  \Illuminate\Database\Eloquent\Model|string|null  $model
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function doCreateOrUpdate(Request $request, &$model = null);

    /**
     * Do delete resource
     * 
     * @param  \Illuminate\Database\Eloquent\Model|string  $model
     * @return boolean
     *
     * @throws \App\Exceptions\FailedDeleteException
     */
    public function doDelete($model);

    /**
     * Execute the action create or update
     * 
     * @param  \Illuminate\Http\Request $request
     * @param  \Illuminate\Database\Eloquent\Model|string|null  $model
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function executeCreateOrUpdate(Request $request, &$model = null);

    /**
     * Find model by identifire
     * 
     * @param  string   $model
     * @param  array    $columns
     * @param  boolean  $fail
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findByIdentifire($model, array $columns = ['*'], $fail = true);

	/**
     * {@inheritdoc}
     */
    public function getContainer($service = null);

    /**
     * {@inheritdoc}
     */
    public function getConnection();

	/**
	 * Get model of repository
	 * 
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function getModel();

    /**
     * Get payload fillable of repository
     * 
     * @return array
     */
    public function getPayloadFillable();

    /**
     * Get payload fillable of repository
     * 
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function payloads(Request $request);

    /**
     * {@inheritdoc}
     */
    public function setContainer(Container $container);

    /**
     * {@inheritdoc}
     */
    public function setConnection($name);

    /**
     * {@inheritdoc}
     */
	public function setModel(Model $model);
}