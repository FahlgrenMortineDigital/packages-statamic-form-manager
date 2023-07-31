<?php

use Fahlgrendigital\StatamicFormManager\Managers;

return [
    /*
     | ============================================================================
     |
     | Register additional or override existing form managers here. They key defined
     | for each manager will be used throughout this config so make it simple and
     | clear following snake-case.
     |
     | ============================================================================
    */
    'managers' => [
        'crm'           => Managers\CrmFormManager::class,
        'transactional' => Managers\TransactionalFormManager::class
    ],

    'queue' => [
        'connection' => env('QUEUE_CONNECTION', 'sync'),
        'queue'      => env('STATAMIC_FORM_MANAGER_QUEUE', 'form-submissions')
    ],
];
