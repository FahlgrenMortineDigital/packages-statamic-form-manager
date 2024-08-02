<?php

namespace Fahlgrendigital\StatamicFormManager\Contracts;

use Statamic\Forms\Submission;

interface ConnectorContract
{
    public function send(Submission $submission): bool;

    public static function init(string $form_handle, string $key, ?string $subtype = null): self;
}