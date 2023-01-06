<?php

namespace Fahlgrendigital\StatamicFormManager\Jobs;

use Fahlgrendigital\StatamicFormManager\Support\Submission as SubmissionSupport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Statamic\Forms\Submission;

class MarkSubmissionExported implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Submission $submission) {}

    public function handle(): void
    {
        $this->submission->set('exported_at', now());

        SubmissionSupport::silentSave($this->submission);
    }
}