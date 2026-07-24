<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Stubs;

use Fahlgrendigital\StatamicFormManager\Contracts\ResponseGate;
use Illuminate\Http\Client\Response;

class PassingResponseGate implements ResponseGate
{
    public static ?Response $received = null;
    public static ?object $lastInstance = null;

    public function passes(Response $response): bool
    {
        static::$received     = $response;
        static::$lastInstance = $this;

        return true;
    }
}
