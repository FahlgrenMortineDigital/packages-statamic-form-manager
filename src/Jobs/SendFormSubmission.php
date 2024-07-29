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

    public function __construct(protected ConnectorContract $manager, protected Submission $submission)
    {
    }

    public function handle(): void
    {
        $export  = Export::newFormSubmission($this->submission, $this->manager->getHandle());
        $success = $this->manager->send($this->submission);

        if (!$success) {
            $this->fail(new \Exception("Failed submission"));
            $export->failed();
        } else {
            $export->succeeded();
        }
    }
}