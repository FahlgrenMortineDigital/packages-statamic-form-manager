<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Stubs;

use Fahlgrendigital\StatamicFormManager\Connector\ConnectorResponse;
use Fahlgrendigital\StatamicFormManager\Connector\HttpConnection;
use Illuminate\Http\Client\Response;

class TestHttpConnection extends HttpConnection
{
    public function setHandle(string $handle): self
    {
        $this->handle = $handle;

        return $this;
    }

    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    public function callSetHttpVerb(string $method): self
    {
        return $this->setHttpVerb($method);
    }

    public function callMapHttpVerb(string $method): string
    {
        return $this->mapHttpVerb($method);
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function callRegisterResponseGate(string $gate): self
    {
        return $this->registerResponseGate($gate);
    }

    public function callHasRegisteredResponseGate(): bool
    {
        return $this->hasRegisteredResponseGate();
    }

    public function callResponsesPasses(Response $response): bool
    {
        return $this->responsesPasses($response);
    }

    public function callMakeRequest(array $data): ConnectorResponse
    {
        return $this->makeRequest($data);
    }
}
