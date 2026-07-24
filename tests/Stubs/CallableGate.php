<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Stubs;

class CallableGate
{
    public static function allow(array $form_data): bool
    {
        return true;
    }

    public static function deny(array $form_data): bool
    {
        return false;
    }
}
