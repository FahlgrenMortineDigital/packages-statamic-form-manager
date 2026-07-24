<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Stubs;

class CallableTransformer
{
    public static function transform($key, $value, $form_data)
    {
        return sprintf('%s|%s|%d', $key, $value, count($form_data));
    }
}
