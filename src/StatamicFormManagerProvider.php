<?php

namespace Fahlgrendigital\StatamicFormManager;

use Illuminate\Support\ServiceProvider;

class StatamicFormManagerProvider extends ServiceProvider
{
    const PACKAGE_NAME = 'statamic-form-manager';
    CONST VERSION = '1.1';

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/statamic-form-manager.php' => config_path('statamic-form-manager.php'),
            __DIR__ . '/../config/statamic-forms.php'        => config_path('statamic-forms.php'),
        ], 'statamic-form-manager-config');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/statamic-form-manager.php', 'statamic-form-manager'
        );
    }
}