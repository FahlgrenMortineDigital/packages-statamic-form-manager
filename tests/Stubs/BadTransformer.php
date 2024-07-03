<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Stubs;

class BadTransformer
{
    public function handle(string $key, mixed $value, array $form_data): mixed
    {
        return null;
    }
}