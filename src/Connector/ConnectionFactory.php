<?php

namespace Fahlgrendigital\StatamicFormManager\Connector;

use Exception;
use Fahlgrendigital\StatamicFormManager\Contracts\ConnectorContract;
use Fahlgrendigital\StatamicFormManager\Connector\BaseConnection;
use Fahlgrendigital\StatamicFormManager\StatamicFormidableFormDataProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class ConnectionFactory
{
    public function get(string $handle): Collection
    {
        $config = config('statamic-formidable-forms.forms');

        if (!array_key_exists($handle, $config)) {
            return collect([]);
        }

        return collect($config[$handle])->filter(function ($config) {
            // only fetch enabled form managers
            return $config['::enabled'] ?? false;
        })->map(function ($config, $key) {
            // [0] : Manager key
            // [1] : Manager subtype (sales-force, etc)
            $connector_key_parts = explode('::', $key, 2);

            /** @var BaseConnection|ConnectorContract $connector */
            $connector           = static::initManager(
                $connector_key_parts[0],
                $config,
                $connector_key_parts[1] ?? null
            );

            if (Arr::get($config, '::fake')) {
                $this->handleFake($connector, $config);
            }

            if(Arr::get($config, '::debug')) {
                $connector->debug($config['::debug']);
            }

            return $connector;
        })->flatten();
    }

    /**
     * @throws Exception
     */
    protected function initManager(string $key, array $config, ?string $subtype = null): ConnectorContract
    {
        if (!array_key_exists($key, Config::get('statamic-formidable.connectors'))) {
            throw new Exception(sprintf("%s: Form manager map not found [$key]", StatamicFormidableFormDataProvider::PACKAGE_NAME));
        }

        $class = Config::get(sprintf("statamic-formidable.connectors.%s", $key));

        return $class::init($key, $config, $subtype);
    }

    protected function handleFake(BaseConnection $manager, array $config): void
    {
        $manager->fakeIt();

        $type = Arr::get($config, '::fake-type', 'success');

        if ($type == 'success') {
            $manager->fakeSuccess();
        } else {
            $manager->fakeFail();
        }
    }
}