<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Stubs;

use Fahlgrendigital\StatamicFormManager\Contracts\FormFieldTransformer;

class GoodTransformer implements FormFieldTransformer
{
    public function handle(string $key, mixed $value, array $form_data): mixed
    {
        return 'test';
    }
}