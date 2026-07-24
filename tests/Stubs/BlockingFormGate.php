<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Stubs;

use Fahlgrendigital\StatamicFormManager\Contracts\FormGate;

class BlockingFormGate implements FormGate
{
    public static ?array $received = null;

    public function handle(array $form_data): bool
    {
        static::$received = $form_data;

        return false;
    }
}
