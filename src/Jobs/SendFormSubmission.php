<?php

namespace Fahlgrendigital\StatamicFormManager\Jobs;

use Fahlgrendigital\StatamicFormManager\Actions\SendSubmission;
use Fahlgrendigital\StatamicFormManager\Contracts\ConnectorContract;
use Fahlgrendigital\StatamicFormManager\Contracts\SubmissionInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendFormSubmission implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected ConnectorContract $connector, protected SubmissionInterface $submission)
    {
    }

    public function handle(): void
    {
        $success = SendSubmission::make($this->connector, $this->submission)
                      ->handle();

        if(!$success) {
            $this->fail(new \Exception('Failed to send form submission: ' . $this->submission->id()));
        }
    }
}