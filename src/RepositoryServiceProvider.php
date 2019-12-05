<?php

namespace Cndrsdrmn\Repositories;

use Cndrsdrmn\Repositories\BaseRepository;
use Cndrsdrmn\Repositories\Generators\RepositoryMakeCommand;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerCommands();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(BaseRepository::class, function ($app) {
            return $this->app->make(BaseRepository::class);
        });
    }

    /**
     * Register repositories commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        $this->commands(RepositoryMakeCommand::class);
    }
}
