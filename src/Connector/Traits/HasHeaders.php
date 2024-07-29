<?php

namespace Fahlgrendigital\StatamicFormManager\Connector\Traits;

trait HasHeaders
{
    protected ?array $headers = [];

    public function headers(): array
    {
        return $this->headers;
    }
}