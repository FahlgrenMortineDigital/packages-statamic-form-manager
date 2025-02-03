<?php

namespace Fahlgrendigital\StatamicFormManager\Connector\Traits;

use Fahlgrendigital\StatamicFormManager\Contracts\ResponseGate;
use Illuminate\Http\Client\Response;

trait GatesResponses
{
    private string $response_gate;

    protected function registerResponseGate(string $gate): self
    {
        $this->response_gate = $gate;

        return $this;
    }

    protected function hasRegisteredResponseGate(): bool
    {
        return isset($this->response_gate);
    }

    protected function responsesPasses(Response $response): bool
    {
        /** @var ResponseGate $gate */
        $gate = app($this->response_gate);

        return $gate->passes($response);
    }
}