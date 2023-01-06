<?php

namespace Fahlgrendigital\StatamicFormManager\Support;

use Statamic\Facades\File;
use Statamic\Facades\YAML;
use Statamic\Forms\Submission as SubmissionEntry;
use Statamic\Support\Arr;

class Submission
{
    /**
     * Copy of vendor/statamic/cms/src/Forms/Submission@save
     *
     * @param SubmissionEntry $submission
     * @return void
     */
    public static function silentSave(SubmissionEntry $submission)
    {
        File::put(
            $submission->getPath(),
            YAML::dump(
                Arr::removeNullValues($submission->data()->all())
            )
        );
    }
}