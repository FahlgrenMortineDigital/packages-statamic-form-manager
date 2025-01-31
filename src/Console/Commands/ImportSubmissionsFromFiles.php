<?php

namespace Fahlgrendigital\StatamicFormManager\Console\Commands;

use Fahlgrendigital\StatamicFormManager\Data\Export;
use Fahlgrendigital\StatamicFormManager\Facades\ConnectionFactoryFacade;
use Illuminate\Console\Command;
use Statamic\Facades\Form;
use Statamic\Forms\Submission;

class ImportSubmissionsFromFiles extends Command
{
    protected $signature = 'formidable:import-submissions';

    protected $description = 'Import submissions from files when upgrading to v2.';

    public function handle(): int
    {
        $forms = Form::all();
        $meta = [];

        $forms->each(function(\Statamic\Forms\Form $form) use(&$meta) {
            $form->submissions()->each(function (Submission $submission) use(&$meta) {
                $connectors = ConnectionFactoryFacade::getConnectors($submission->form->handle());

                $connectors->each(function($connector) use($submission, &$meta) {
                    $export = Export::firstOrNewFormSubmission($submission, $connector);

                    // if pulled from the DB then move on
                    if(!$export->wasRecentlyCreated) {
                        return true;
                    }

                    // save the record if it is a new one
                    $export->save();

                    if(!isset($meta[$submission->form->handle()])) {
                        $meta[$submission->form->handle()] = 0;
                    }

                    $meta[$submission->form->handle()]++;

                    return true;
                });

                return true;
            });
        });

        $table_data = [];
        foreach ($meta as $handle => $count) {
            $table_data[] = ['Form Handle' => $handle, 'Export Count' => $count];
        }

        // Display the table
        $this->table(['Form', 'Export Count'], $table_data);

        return \Symfony\Component\Console\Command\Command::SUCCESS;
    }
}