<?php

namespace Fahlgrendigital\StatamicFormManager;

use Illuminate\Support\ServiceProvider;

class StatamicFormManagerProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/statamic-form-managers.php' => config_path('statamic-form-managers.php'),
            __DIR__ . '/../config/statamic-forms.php'         => config_path('statamic-forms.php'),
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/statamic-forms.php', 'statamic-forms'
        );
    }
}