<?php

namespace Fahlgrendigital\StatamicFormManager\Http\Controllers;

class DashboardController
{
    public function __invoke()
    {
        return view('formidable::dashboard');
    }
}