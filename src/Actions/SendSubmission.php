<?php

namespace Fahlgrendigital\StatamicFormManager\Actions;

use Fahlgrendigital\StatamicFormManager\Contracts\ConnectorContract;
use Fahlgrendigital\StatamicFormManager\Data\Export;
use Statamic\Forms\Submission;

class SendSubmission extends BaseAction
{
    public function __construct(protected ConnectorContract $connector, protected Submission $submission)
    {
    }

    public function handle(): bool
    {
        $export  = Export::firstOrNewFormSubmission($this->submission, $this->connector->getHandle());
        $success = $this->connector->send($this->submission);

        if (!$success) {
            $export->markFailed();
        } else {
            $export->markSucceeded();
        }

        $this->connector->logPayload($this->submission);

        return $success;
    }
}