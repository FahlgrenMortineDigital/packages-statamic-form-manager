<?php

namespace Fahlgrendigital\StatamicFormManager\Facades;

use Fahlgrendigital\StatamicFormManager\Connector\ConnectionFactory;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Support\Collection getConnectors(string $handle)
 * @method static \Illuminate\Database\Connection getConnection(string $handle)
 *
 * @see \Fahlgrendigital\StatamicFormManager\Connector\ConnectionFactory
 */
class ConnectionFactoryFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ConnectionFactory::class;
    }
}