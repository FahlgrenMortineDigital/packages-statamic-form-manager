<?php

namespace Fahlgrendigital\StatamicFormManager\Connector\Traits;

use Fahlgrendigital\StatamicFormManager\Connector\BaseConnection;
use Fahlgrendigital\StatamicFormManager\Connector\ConnectorResponse;
use Fahlgrendigital\StatamicFormManager\Enums\FakeResponse;

trait CanFake
{
    protected bool $is_faking = false;
    protected ?string $fake_mode = null;

    public function fakeIt(): BaseConnection
    {
        $this->is_faking = true;

        return $this;
    }

    public function isFaking(): bool
    {
        return $this->is_faking;
    }

    public function getFakeResponse(): ConnectorResponse
    {
        $response = new ConnectorResponse();
        $response->setPassState($this->isSuccess());

        return $response;
    }

    public function fakeSuccess(): BaseConnection
    {
        $this->fake_mode = FakeResponse::SUCCESS->value;

        return $this;
    }

    public function fakeFail(): BaseConnection
    {
        $this->fake_mode = FakeResponse::FAILURE->value;

        return $this;
    }

    // -------------------------
    //      PRIVATE HELPERS
    // -------------------------

    private function isSuccess(): bool
    {
        return $this->fake_mode == FakeResponse::SUCCESS->value;
    }

    private function isFail(): bool
    {
        return $this->fake_mode == FakeResponse::FAILURE->value;
    }
}