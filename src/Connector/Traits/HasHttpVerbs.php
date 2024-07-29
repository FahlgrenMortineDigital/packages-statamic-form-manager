<?php

namespace Fahlgrendigital\StatamicFormManager\Connector\Traits;

use Fahlgrendigital\StatamicFormManager\Connector\BaseConnection;

trait HasHttpVerbs
{
    protected ?string $method;

    /**
     * @throws \Exception
     */
    protected function mapHttpVerb(string $method): string
    {
        return match ($method) {
            'get', 'GET' => 'GET',
            'post', 'POST' => 'POST',
            'put', 'PUT' => 'PUT',
            default => throw new \Exception("Invalid HTTP verb: {$method}"),
        };
    }

    /**
     * @throws \Exception
     */
    protected function setHttpVerb(string $method): BaseConnection
    {
        $this->method = $this->mapHttpVerb($method);

        return $this;
    }
}