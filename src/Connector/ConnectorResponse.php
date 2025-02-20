<?php

namespace Fahlgrendigital\StatamicFormManager\Connector;

use Illuminate\Http\Client\Response;

class ConnectorResponse
{
    public bool $success;
    public string $message;
    public Response $guzzleResponse;

    public function setPassState(bool $status): self
    {
        $this->success = $status;

        return $this;
    }
}