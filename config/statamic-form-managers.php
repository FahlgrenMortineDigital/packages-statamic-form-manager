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
    ]
];
