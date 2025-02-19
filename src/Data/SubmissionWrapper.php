<?php

namespace Fahlgrendigital\StatamicFormManager\Data;

use Fahlgrendigital\StatamicFormManager\Contracts\SubmissionInterface;

/**
 * @property $form
 */
class SubmissionWrapper implements SubmissionInterface
{
    public function __construct(protected $submission)
    {
    }

    public function id(): ?string
    {
        if ($this->submission instanceof \Statamic\Forms\Submission) {
            return $this->submission->id();
        } else if (class_exists('\Statamic\Eloquent\Forms\Submission') && $this->submission instanceof \Statamic\Eloquent\Forms\Submission) {
            return $this->submission->model()->id;
        }

        return null;
    }

    public function toArray(): array
    {
        $data = $this->submission->toArray();

        if (class_exists('\Statamic\Eloquent\Forms\Submission') && $this->submission instanceof \Statamic\Eloquent\Forms\Submission) {
            $data['id'] = $this->submission->model()->id;
        }

        return $data;
    }

    public function __call($method, $arguments)
    {
        return call_user_func_array([$this->submission, $method], $arguments);
    }
}