<?php

namespace Fahlgrendigital\StatamicFormManager\Console\Commands;

use Fahlgrendigital\StatamicFormManager\Connector\BaseConnection;
use Fahlgrendigital\StatamicFormManager\Contracts\ConnectorContract;
use Fahlgrendigital\StatamicFormManager\Data\Export;
use Fahlgrendigital\StatamicFormManager\Facades\ConnectionFactoryFacade;
use Illuminate\Console\Command;
use Statamic\Facades\Form;
use Statamic\Forms\Submission;

class ImportSubmissionsFromFiles extends Command
{
    protected $signature = 'formidable:import-submissions {--D|dry-run : Perform a dry run}';

    protected $description = 'Import submissions from files when upgrading to v2.';

    public function handle(): int
    {
        $dry_run = $this->option('dry-run');
        $forms = Form::all();
        $meta = [];

        $forms->each(function(\Statamic\Forms\Form $form) use(&$meta, $dry_run) {
            $this->info("Processing form submissions: {$form->handle()}");

            $bar = $this->output->createProgressBar($form->submissions()->count());

            $bar->start();

            if(!isset($meta[$form->handle()])) {
                $meta[$form->handle()] = 0;
            }

            $form->submissions()->each(function (Submission $submission) use(&$meta, $bar, $dry_run) {
                $connectors = ConnectionFactoryFacade::getConnectors($submission->form->handle());

                $connectors->each(function(BaseConnection $connector) use($submission, &$meta, $dry_run) {
                    $export = Export::firstOrNewFormSubmission($submission, $connector->getHandle());

                    // if pulled from the DB then skip and go to next
                    if(!$export->wasRecentlyCreated) {
                        return true;
                    }

                    // save the record if it is a new one
                    if(!$dry_run) {
                        $export->save();
                    }

                    $meta[$submission->form->handle()]++;

                    return true;
                });

                $bar->advance();

                return true;
            });

            $bar->finish();
            $this->output->newLine(2);
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