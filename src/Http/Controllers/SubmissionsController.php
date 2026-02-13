<?php

namespace Fahlgrendigital\StatamicFormManager\Http\Controllers;

use Fahlgrendigital\StatamicFormManager\Contracts\SubmissionInterface;
use Fahlgrendigital\StatamicFormManager\Data\Export;
use Statamic\Facades\FormSubmission;
use Inertia\Inertia;

class SubmissionsController
{
    public function show($submission): \Inertia\Response
    {
        $submission = FormSubmission::find($submission);

        abort_if(!$submission, 404);

        $exports = Export::forSubmission(
                        app(SubmissionInterface::class, ['submission' => $submission])
                    )->get()
                        ->each->append(['run_url', 'completed', 'pending', 'failed']);
        $completed = $exports->filter(fn(Export $export) => $export->completed)->count() === $exports->count();

        return Inertia::render('formidable::Submission', [
            'submission' => $submission,
            'exports' => $exports,
            'completed' => $completed
        ]);
    }
}