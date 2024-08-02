<?php

namespace Fahlgrendigital\StatamicFormManager\Listeners;

use Fahlgrendigital\StatamicFormManager\Contracts\ConnectorContract;
use Fahlgrendigital\StatamicFormManager\Jobs\SendFormSubmission;
use Fahlgrendigital\StatamicFormManager\Connector\ConnectionFactory;
use Illuminate\Support\Facades\Config;
use Statamic\Events\SubmissionSaved;
use Statamic\Forms\Form;

class FormSubmissionsManager
{
    public function handle(SubmissionSaved $event): void
    {
        /** @var Form $form */
        $form     = $event->submission->form;
        $connectors = (new ConnectionFactory())->getConnectors($form->handle());

        $connectors->each(function (ConnectorContract $manager) use ($event) {
            SendFormSubmission::dispatch($manager, $event->submission)
                              ->onConnection(Config::get('statamic-form-manager.queue.connection'))
                              ->onQueue(Config::get('statamic-form-manager.queue.queue'));
        });
    }
}