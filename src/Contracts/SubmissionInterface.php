<?php

namespace Fahlgrendigital\StatamicFormManager\Contracts;

interface SubmissionInterface
{
    public function id(): ?string;
    public function toArray(): array;
}