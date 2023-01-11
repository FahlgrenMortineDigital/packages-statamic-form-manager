<?php

namespace Fahlgrendigital\StatamicFormManager\Tests;

use Fahlgrendigital\StatamicFormManager\StatamicFormManagerProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            StatamicFormManagerProvider::class
        ];
    }
}