<?php

namespace Fahlgrendigital\StatamicFormManager;

use Fahlgrendigital\StatamicFormManager\Connector\ConnectionFactory;
use Fahlgrendigital\StatamicFormManager\Console\Commands\CleanOldExports;
use Fahlgrendigital\StatamicFormManager\Console\Commands\ImportSubmissionsFromFiles;
use Fahlgrendigital\StatamicFormManager\Contracts\SubmissionInterface;
use Fahlgrendigital\StatamicFormManager\Data\Export;
use Fahlgrendigital\StatamicFormManager\Data\SubmissionWrapper;
use Fahlgrendigital\StatamicFormManager\Http\Filters\ByFormHandle;
use Fahlgrendigital\StatamicFormManager\Http\Filters\ExportCompleted;
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

    protected $vite = [
        'input' => [
            'resources/js/addon.js',
            'resources/css/addon.css',
        ],
        'publicDirectory' => 'resources/dist'
    ];

    protected $commands = [
        CleanOldExports::class
    ];

    public function boot(): void
    {
        parent::boot();

        if (!$this->app->runningInConsole()) {
            $this->commands([
                CleanOldExports::class,
                ImportSubmissionsFromFiles::class
            ]);
        }

        $this->publishes([
            __DIR__ . '/../database/migrations/create_exports_table.php.stub' => database_path('migrations/' . now()->format('Y_m_d_His') . '_create_exports_table.php'),
            __DIR__ . '/../database/migrations/rename_exports_table.php.stub' => database_path('migrations/' . now()->addSecond()->format('Y_m_d_His') . '_rename_exports_table.php'),
            __DIR__ . '/../database/migrations/add_errors_to_exports_table.php.stub' => database_path('migrations/' . now()->addSeconds(2)->format('Y_m_d_His') . '_add_errors_to_exports_table.php'),
        ], 'statamic-formidable-migrations');
    }

    public function bootAddon(): void
    {
        ExportCompleted::register();
        ByFormHandle::register();

        $this->bootAddonNav()->bootAddonViews();
    }

    public function register(): void
    {
        $this->registerAddonConfig();

        $this->app->singleton(ConnectionFactory::class, function () {
            return new ConnectionFactory();
        });

        $this->app->bind(SubmissionInterface::class, function ($app, $params) {
            return new SubmissionWrapper($params['submission']);
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