<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Stubs;

use Fahlgrendigital\StatamicFormManager\Contracts\SubmissionInterface;

class ArraySubmission implements SubmissionInterface
{
    public function __construct(private array $data = [], private ?string $submissionId = null)
    {
    }

    public function id(): ?string
    {
        return $this->submissionId;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
