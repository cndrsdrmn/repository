<?php

namespace Cndrsdrmn\Repositories\Generators;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class RepositoryMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:repository';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Repository class.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Repository';

    /**
     * Build the class with the given name.
     *
     * @param  string $name
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        return $this->replaceModelImport($stub)
                    ->replaceModel($stub)
                    ->replacePayloads($stub);
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return sprintf('%s%s', Str::studly(trim($this->argument('name'))), $this->type);
    }

    /**
     * Replace model name.
     *
     * @param string $stub
     * @return string
     */
    protected function replaceModel(&$stub)
    {
        $model = explode('\\', $this->getModel());
        $model = array_pop($model);
        $stub  = str_replace('ModelName', $model, $stub);

        return $this;
    }

    /**
     * Get model name to use.
     *
     * @return string
     */
    protected function getModel()
    {
        $name   = $this->argument('name');
        $model  = $this->option('model');

        return $model
            ? $this->rootNamespace() . '\\' . ($model ? str_replace('/', '\\', $model) : '')
            : $this->rootNamespace() . '\\Models\\' . Str::studly(trim(str_replace('/', '\\', $name)));
    }

    /**
     * Replace model import.
     *
     * @param string $stub
     * @return $this
     */
    protected function replaceModelImport(&$stub)
    {
        $stub = str_replace(
            'DummyModel', str_replace('\\\\', '\\', $this->getModel()), $stub
        );

        return $this;
    }

    /**
     * Replace payloads.
     *
     * @param string $stub
     * @return $this
     */
    protected function replacePayloads(&$stub)
    {
        $stub = str_replace(
            'DummyPayloads', $this->getPayloads(), $stub
        );

        return $stub;
    }

    /**
     * Get the payloads to be used.
     *
     * @return string
     */
    protected function getPayloads()
    {
        if ($this->option('payloads') != '') {
            $payloads = $this->option('payloads');
            return $this->parseArray($payloads);
        } else {
            return $this->parseArray('id');
        }
    }

    /**
     * Parse array from definition.
     *
     * @param  string  $definition
     * @param  string  $delimiter
     * @param  int     $indentation
     * @return string
     */
    protected function parseArray($definition, $delimiter = ',', $indentation = 12)
    {
        return str_replace($delimiter, "',\n" . str_repeat(' ', $indentation) . "'", $definition);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/repository.stub';
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Use the provided name as the model.'],
            ['payloads', 'p', InputOption::VALUE_OPTIONAL, 'Use the provided name as the payloads.'],
        ];
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return sprintf('%s\Repositories', $rootNamespace);
    }
}
