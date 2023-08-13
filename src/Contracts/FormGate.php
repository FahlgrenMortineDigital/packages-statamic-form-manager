<?php

namespace Fahlgrendigital\StatamicFormManager\Contracts;

interface FormGate
{
    public function handle(array $form_data): bool;
}