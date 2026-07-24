<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Stubs;

class ComputedKeyCallable
{
    public static ?array $received = null;

    public static function handle(string $key, mixed $value, array $form_data): mixed
    {
        static::$received = compact('key', 'value', 'form_data');

        return 'invoked-via-key';
    }
}
