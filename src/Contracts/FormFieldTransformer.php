<?php

namespace Fahlgrendigital\StatamicFormManager\Contracts;

interface FormFieldTransformer
{
    public function handle(string $key, mixed $value, array $form_data): array;
}