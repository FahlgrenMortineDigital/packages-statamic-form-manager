<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Stubs;

class EloquentSubmissionStub
{
    public function __construct(private array $data = [], private mixed $modelId = null)
    {
    }

    public function model(): object
    {
        return (object) ['id' => $this->modelId];
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
