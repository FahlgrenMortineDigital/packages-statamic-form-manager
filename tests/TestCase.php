<?php

namespace Fahlgrendigital\StatamicFormManager\Tests;

use Fahlgrendigital\StatamicFormManager\StatamicFormidableFormDataProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            StatamicFormidableFormDataProvider::class
        ];
    }
}