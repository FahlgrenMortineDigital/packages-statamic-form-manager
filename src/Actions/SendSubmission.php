<?php

namespace Fahlgrendigital\StatamicFormManager\Actions;

use Fahlgrendigital\StatamicFormManager\Contracts\ConnectorContract;
use Fahlgrendigital\StatamicFormManager\Contracts\SubmissionInterface;
use Fahlgrendigital\StatamicFormManager\Data\Export;

class SendSubmission extends BaseAction
{
    public function __construct(protected ConnectorContract $connector, protected SubmissionInterface $submission)
    {
    }

    public function handle(): bool
    {
        $success = $this->connector->send($this->submission);
        // create the export after the HTTP call is made in case it throws any exceptions.
        // we don't want any orphaned exports due to HTTP exceptions.
        $export  = Export::firstOrNewFormSubmission($this->submission, $this->connector->getHandle());

        if (!$success) {
            $export->markFailed();
        } else {
            $export->markSucceeded();
        }

        $this->connector->logPayload($this->submission);

        return $success;
    }
}