<?php

namespace Fahlgrendigital\StatamicFormManager\Connector\Traits;

use Fahlgrendigital\StatamicFormManager\Contracts\ConnectorContract;

trait CanFake
{
    protected bool $is_faking = false;
    protected ?string $fake_mode = null;

    public function fakeIt(): ConnectorContract
    {
        $this->is_faking = true;

        return $this;
    }

    public function isFaking(): bool
    {
        return $this->is_faking;
    }

    public function getFakeResponse(): bool
    {
        return $this->isSuccess();
    }

    public function fakeSuccess(): ConnectorContract
    {
        $this->fake_mode = 'success';

        return $this;
    }

    public function fakeFail(): ConnectorContract
    {
        $this->fake_mode = 'fail';

        return $this;
    }

    // -------------------------
    //      PRIVATE HELPERS
    // -------------------------

    private function isSuccess(): bool
    {
        return $this->fake_mode == 'success';
    }

    private function isFail(): bool
    {
        return $this->fake_mode == 'fail';
    }
}