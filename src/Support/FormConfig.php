<?php

namespace Fahlgrendigital\StatamicFormManager\Support;

use Fahlgrendigital\StatamicFormManager\Contracts\FormManager;
use Fahlgrendigital\StatamicFormManager\Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class FormConfig
{
    public function get(string $handle): Collection
    {
        $config = config('form-mappings.forms');

        if (!array_key_exists($handle, $config)) {
            return collect([]);
        }

        return collect($config[$handle])->filter(function ($config) {
            return $config['::enabled'];
        })->map(function ($config, $key) {
            $manager_key_parts = explode('::', $key, 2);
            $manager           = static::initManager(
                $manager_key_parts[0],
                $config,
                $manager_key_parts[1] ?? null
            );

            if (Arr::get($config, '::fake')) {
                $this->handleFake($manager, $config);
            }

            return $manager;
        })->flatten();
    }

    protected function initManager(string $key, array $config, ?string $subtype = null): FormManager
    {
        if (!array_key_exists($key, config('form-mappings.managers'))) {
            throw new Exception('Form manager map not found');
        }

        $class = config(sprintf("form-mappings.managers.%s", $key));

        return $class::init($key, $config, $subtype);
    }

    protected function handleFake(FormManager $manager, array $config): void
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