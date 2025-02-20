<?php

namespace Fahlgrendigital\StatamicFormManager\Contracts;

use Fahlgrendigital\StatamicFormManager\Connector\ConnectorResponse;

interface ConnectorContract
{
    public function send(SubmissionInterface $submission): ConnectorResponse;

    public function getHandle(): string;

    public static function init(string $form_handle, string $key, ?string $subtype = null): self;
}