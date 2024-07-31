<?php

namespace Fahlgrendigital\StatamicFormManager\Http\Controllers;

use Statamic\Facades\Scope;

class DashboardController
{
    public function __invoke()
    {
        return view('formidable::dashboard', [
            'filters' => Scope::filters('exports'),
        ]);
    }
}