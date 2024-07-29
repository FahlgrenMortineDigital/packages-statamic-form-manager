<?php

namespace Fahlgrendigital\StatamicFormManager;

use Fahlgrendigital\StatamicFormManager\Data\Export;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Statamic\Facades\CP\Nav;
use Statamic\Providers\AddonServiceProvider;

class StatamicFormManagerProvider extends AddonServiceProvider
{
    const PACKAGE_NAME = 'statamic-formidable';
    const VERSION = '1.2';

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    public function boot(): void
    {
        parent::boot();

        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../database/migrations/create_exports_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_exports_table.php'),
        ], 'statamic-formidable-migrations');
    }

    public function bootAddon(): void
    {
        $this->bootDatabase()->bootAddonNav()->bootAddonViews();
    }

    public function register(): void
    {
        $this->registerAddonConfig();
    }

    protected function registerAddonConfig(): self
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/statamic-formidable.php', 'statamic-formidable'
        );

        $this->publishes([
            __DIR__ . '/../config/statamic-formidable.php'       => config_path('statamic-formidable.php'),
            __DIR__ . '/../config/statamic-formidable-forms.php' => config_path('statamic-formidable-forms.php'),
        ], 'statamic-formidable-config');

        return $this;
    }

    protected function bootDatabase(): self
    {
        // table exists so bail
        if (Schema::connection(config('statamic-form-manager.export.connection'))->hasTable((new Export())->getTable())) {
            return $this;
        }

        // migrations have not been run so run 'em

        $defaultConnection = DB::getDefaultConnection();
        DB::setDefaultConnection(config('statamic-form-manager.exports.connection'));

        require_once(__DIR__ . '/../database/migrations/create_exports_table.php.stub');
        (new \CreateExportsTable())->up();

        DB::setDefaultConnection($defaultConnection);

        return $this;
    }

    protected function bootAddonViews(): self
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'formidable');

        return $this;
    }

    protected function bootAddonNav(): self
    {
        Nav::extend(function ($nav) {
            $items = [];

            $items['Dashboard'] = cp_route('formidable.index');

            $nav->tools('Formidable')
                ->route('formidable.index')
                ->icon('checkboxes')
                ->active('formidable')
                ->can('view redirects')
                ->children($items);
        });

        return $this;
    }
}