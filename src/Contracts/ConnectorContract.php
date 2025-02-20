<?php

namespace Fahlgrendigital\StatamicFormManager\Contracts;

interface ConnectorContract
{
    public function send(SubmissionInterface $submission): bool;

    public function getHandle(): string;

    public static function init(string $form_handle, string $key, ?string $subtype = null): self;
}