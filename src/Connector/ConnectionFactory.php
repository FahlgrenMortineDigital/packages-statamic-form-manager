<?php

namespace Fahlgrendigital\StatamicFormManager\Connector;

use Exception;
use Fahlgrendigital\StatamicFormManager\Contracts\ConnectorContract;
use Fahlgrendigital\StatamicFormManager\StatamicFormidableFormDataProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ConnectionFactory
{
    public function getConnectors(string $handle): Collection
    {
        $config = config('statamic-formidable-forms.forms');

        if (!array_key_exists($handle, $config)) {
            return collect([]);
        }

        return collect($config[$handle])->filter(function ($config) {
            // only fetch enabled form managers
            return $config['::enabled'] ?? false;
        })->map(function ($config, $key) use($handle) {
            return $this->getByConnection($handle, $key);
        })->flatten();
    }

    /**
     * @throws Exception
     */
    public function getByConnection(string $form_handle, string $connection): BaseConnection
    {
        $connector_key_parts = explode('::', $connection, 2);
        $config              = config('statamic-formidable-forms.forms');

        if (!Arr::has($config, $form_handle)) {
            throw new Exception(sprintf("%s: Form not found [%s]", StatamicFormidableFormDataProvider::PACKAGE_NAME, $form_handle));
        }

        if (!Arr::has($config[$form_handle], $connection)) {
            throw new Exception(sprintf("%s: Form connection not found [%s]", StatamicFormidableFormDataProvider::PACKAGE_NAME, $connection));
        }

        $connector = static::initManager(
            $form_handle,
            $connector_key_parts[0],
            $connector_key_parts[1] ?? null
        );

        if (Arr::get($config, '::fake')) {
            $this->handleFake($connector, $config);
        }

        if (Arr::get($config, '::debug')) {
            $connector->debug($config['::debug']);
        }

        return $connector;
    }

    /**
     * @throws Exception
     */
    protected function initManager(string $form_handle, string $key, ?string $subtype = null): BaseConnection
    {
        $class = config(sprintf("statamic-formidable.connectors.%s", $key));

        if (!in_array(ConnectorContract::class, class_implements($class))) {
            throw new Exception(sprintf("The class %s must implement %s", $class, ConnectorContract::class));
        }

        return $class::init($form_handle, $key, $subtype);
    }

    protected function handleFake(BaseConnection $connection, array $config): void
    {
        $connection->fakeIt();

        $type = Arr::get($config, '::fake-type', 'success');

        if ($type == 'success') {
            $connection->fakeSuccess();
        } else {
            $connection->fakeFail();
        }
    }
}