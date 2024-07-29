<?php

namespace Fahlgrendigital\StatamicFormManager\Contracts;

use Statamic\Forms\Submission;

interface ConnectorContract
{
    public function send(Submission $submission): bool;

    public static function init(string $key, array $config, ?string $subtype = null): self;
}