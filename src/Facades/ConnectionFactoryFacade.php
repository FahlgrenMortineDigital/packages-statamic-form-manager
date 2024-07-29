<?php

namespace Fahlgrendigital\StatamicFormManager\Facades;

use Fahlgrendigital\StatamicFormManager\Connector\ConnectionFactory;
use Illuminate\Support\Facades\Facade;

class ConnectionFactoryFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ConnectionFactory::class;
    }
}