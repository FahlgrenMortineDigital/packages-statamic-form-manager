<?php

namespace Fahlgrendigital\StatamicFormManager\Jobs;

use Fahlgrendigital\StatamicFormManager\Contracts\FormManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Statamic\Forms\Submission;

class FormSubmissionSender implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected FormManager $manager, protected Submission $submission)
    {
    }

    public function handle(): void
    {
        $success = $this->manager->send($this->submission);

        if (!$success) {
            $this->fail(new \Exception("Failed submission"));

            return;
        }

        return;
    }
}