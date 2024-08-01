<?php

namespace Fahlgrendigital\StatamicFormManager\Http\Controllers;

use Fahlgrendigital\StatamicFormManager\Actions\SendSubmission;
use Fahlgrendigital\StatamicFormManager\Connector\ConnectionFactory;
use Fahlgrendigital\StatamicFormManager\Data\Export;
use Statamic\Facades\CP\Toast;

class ExportsController
{
    public function __invoke(Export $export)
    {
        $connection = (new ConnectionFactory())->getByConnection($export->form_handle, $export->destination);

        abort_if(is_null($connection), 404, 'Form connection not found');

        $success = SendSubmission::make($connection, $export->submission())->handle();

        if($success) {
            Toast::success('Successfully sent form data off to ' . $export->destination);
        } else {
            Toast::error('Failed to send form data off to ' . $export->destination);
        }

        return redirect()->back();
    }
}