<?php

namespace Fahlgrendigital\StatamicFormManager\Jobs;

use Fahlgrendigital\StatamicFormManager\Contracts\ConnectorContract;
use Fahlgrendigital\StatamicFormManager\Data\Export;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Statamic\Forms\Submission;

class SendFormSubmission implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected ConnectorContract $connector, protected Submission $submission)
    {
    }

    public function handle(): void
    {
        $export  = Export::newFormSubmission($this->submission, $this->connector->getHandle());
        $success = $this->connector->send($this->submission);

        if (!$success) {
            $this->fail(new \Exception("Failed submission"));
            $export->markFailed();
        } else {
            $export->markSucceeded();
        }

        //todo - add option for encrypting submissions
        $this->connector->logPayload($this->submission);
    }
}