<?php

namespace Fahlgrendigital\StatamicFormManager\Contracts;

use Illuminate\Http\Client\Response;

interface ResponseGate
{
    public function passes(Response $response): bool;
}