<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Stubs;

use Fahlgrendigital\StatamicFormManager\Connector\BaseConnection;
use Fahlgrendigital\StatamicFormManager\Connector\ConnectorResponse;
use Fahlgrendigital\StatamicFormManager\Contracts\SubmissionInterface;

class TestConnection extends BaseConnection
{
    public static array $validationRules = [];

    public array $preppedData = [];
    public ?ConnectorResponse $requestResponse = null;
    public int $makeRequestCalls = 0;
    public array $lastRequestData = [];
    public bool $logPayloadReturn = false;

    public function setMaps(array $maps): self
    {
        $this->maps = $maps;

        return $this;
    }

    public function setComputed(array $computed): self
    {
        $this->computed = $computed;

        return $this;
    }

    public function setDefaults(array $defaults): self
    {
        $this->defaults = $defaults;

        return $this;
    }

    public function setHandle(string $handle): self
    {
        $this->handle = $handle;

        return $this;
    }

    public function setGate($gate): self
    {
        return $this->registerFormGate($gate);
    }

    public function callShouldSend(array $form_data): bool
    {
        return $this->shouldSend($form_data);
    }

    public static function callValidateData(array $data): bool
    {
        return static::validateData($data);
    }

    protected function prepData(SubmissionInterface $submission): array
    {
        return $this->preppedData;
    }

    protected function makeRequest(array $data): ConnectorResponse
    {
        $this->makeRequestCalls++;
        $this->lastRequestData = $data;

        return $this->requestResponse ?? (new ConnectorResponse())->setPassState(true);
    }

    public function logPayload(SubmissionInterface $submission): bool
    {
        return $this->logPayloadReturn;
    }

    public static function rules(): array
    {
        return static::$validationRules;
    }
}
