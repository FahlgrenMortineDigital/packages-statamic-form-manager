<?php

namespace Fahlgrendigital\StatamicFormManager\Facades;

use Illuminate\Support\Facades\Facade;

class FormConfig extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Fahlgrendigital\StatamicFormManager\Support\FormConfig::class;
    }
}