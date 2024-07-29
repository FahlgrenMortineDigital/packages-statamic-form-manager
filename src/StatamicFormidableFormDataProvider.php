<?php

namespace Fahlgrendigital\StatamicFormManager;

use Fahlgrendigital\StatamicFormManager\Connector\ConnectionFactory;
use Fahlgrendigital\StatamicFormManager\Console\Commands\CleanOldExports;
use Fahlgrendigital\StatamicFormManager\Data\Export;
use Fahlgrendigital\StatamicFormManager\Listeners\FormSubmissionsManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Statamic\Events\SubmissionSaved;
use Statamic\Facades\CP\Nav;
use Statamic\Providers\AddonServiceProvider;

class StatamicFormidableFormDataProvider extends AddonServiceProvider
{
    const PACKAGE_NAME = 'statamic-formidable';
    const VERSION = '1.2';

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $commands = [
        CleanOldExports::class
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

        Event::listen(SubmissionSaved::class, FormSubmissionsManager::class);
    }

    public function register(): void
    {
        $this->registerAddonConfig();

        $this->app->singleton(ConnectionFactory::class, function () {
            return new ConnectionFactory();
        });
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
        if (Schema::connection(config('statamic-formidable.export.connection'))->hasTable((new Export())->getTable())) {
            return $this;
        }

        // migrations have not been run so run 'em

        $defaultConnection = DB::getDefaultConnection();
        DB::setDefaultConnection(config('statamic-formidable.exports.connection'));

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