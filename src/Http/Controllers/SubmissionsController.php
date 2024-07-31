<?php

namespace Fahlgrendigital\StatamicFormManager\Http\Controllers;

use Statamic\Facades\FormSubmission;

class SubmissionsController
{
    public function show($submission): \Illuminate\View\View
    {
        $submission = FormSubmission::find($submission);

        return view('formidable::submission', compact('submission'));
    }
}