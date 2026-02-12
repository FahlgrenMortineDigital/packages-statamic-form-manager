<?php

namespace Fahlgrendigital\StatamicFormManager\Http\Controllers;

use Fahlgrendigital\StatamicFormManager\Blueprints\ExportBlueprint;
use Statamic\Facades\Scope;
use Inertia\Inertia;

class DashboardController
{
    public function __invoke()
    {
        $blueprint = new ExportBlueprint();
        $columns = $blueprint()
            ->columns()
            ->setPreferred('formidable.columns')
            ->rejectUnlisted()
            ->values();

        return Inertia::render('formidable::Dashboard', [
            'filters' => Scope::filters('exports'),
            'columns' => $columns
        ]);
    }
}