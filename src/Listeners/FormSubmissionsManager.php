<?php

namespace Fahlgrendigital\StatamicFormManager\Listeners;

use Fahlgrendigital\StatamicFormManager\Contracts\FormManager;
use Fahlgrendigital\StatamicFormManager\Jobs\FormSubmissionSender;
use Fahlgrendigital\StatamicFormManager\Jobs\MarkSubmissionExported;
use Fahlgrendigital\StatamicFormManager\Support\ManagerFactory;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Statamic\Events\SubmissionSaved;
use Statamic\Forms\Form;

class FormSubmissionsManager
{
    public function handle(SubmissionSaved $event): void
    {
        /** @var Form $form */
        $form     = $event->submission->form;
        $managers = (new ManagerFactory())->get($form->handle());

        $managers->each(function (FormManager $manager) use ($event) {
            Bus::chain([
                (new FormSubmissionSender($manager, $event->submission)),
                (new MarkSubmissionExported($event->submission))
            ])
               ->onConnection(Config::get('statamic-form-manager.queue.connection'))
               ->onQueue(Config::get('statamic-form-manager.queue.queue'))
               ->dispatch();
        });
    }
}