<?php

namespace Fahlgrendigital\StatamicFormManager\Facades;

use Illuminate\Support\Facades\Facade;

class ManagerFactoryFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Fahlgrendigital\StatamicFormManager\Support\ManagerFactory::class;
    }
}