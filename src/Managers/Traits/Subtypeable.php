<?php

namespace Fahlgrendigital\StatamicFormManager\Managers\Traits;

trait Subtypeable
{
    protected static function buildConfigKey(string $type, string $subtype = null): string
    {
        $global_key = $type;

        if (!empty($subtype)) {
            $global_key .= "::$subtype";
        }

        return $global_key;
    }
}