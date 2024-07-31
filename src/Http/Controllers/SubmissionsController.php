<?php

namespace Fahlgrendigital\StatamicFormManager\Http\Controllers;

use Fahlgrendigital\StatamicFormManager\Data\Export;
use Statamic\Facades\FormSubmission;

class SubmissionsController
{
    public function show($submission): \Illuminate\View\View
    {
        $submission = FormSubmission::find($submission);
        $exports    = Export::forSubmission($submission)->get();
        $completed  = $exports->filter(fn(Export $export) => $export->completed())->count() === $exports->count();

        return view('formidable::submission', compact('submission', 'exports', 'completed'));
    }
}