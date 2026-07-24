<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Stubs;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Collection;

class QueueableMailableStub extends Mailable
{
    use Queueable;

    public function __construct(public Collection $data)
    {
    }
}
