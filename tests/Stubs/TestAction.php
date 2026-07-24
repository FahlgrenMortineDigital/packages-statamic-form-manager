<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Stubs;

use Fahlgrendigital\StatamicFormManager\Actions\BaseAction;

class TestAction extends BaseAction
{
    public function __construct(public $a = null, public $b = null)
    {
    }

    public function handle(): bool
    {
        return true;
    }
}
