<?php

return [
    /*
     | ============================================================================
     |
     | Manager-specific form & mapping values can be set here. Anything set here for
     | a manager will be overridden what's set in the {forms} configuration for that form.
     |
     | Only the following fields are definable:
     | CRM:
     | - default
     | - maps
     | - ::url
     |
     | Transactional:
     | - mailto
     |
     | * Example *
     |
     | 'crm::sales-force' => [
     |        '::url'   => 'https://webto.salesforce.com/servlet/servlet.WebToLead?encoding=UTF-8',
     |       'default' => [
     |           'oid'        => '00D6g000003RO3x',
     |           'debug'      => 0,
     |           'debugEmail' => 'digitalservices@fahlgren.com',
     |       ]
     |   ],
     |
     | ============================================================================
    */
    'defaults' => [
        'crm::sales-force' => [],
        'crm::pardot'      => [],
        'transactional'    => [],
    ],

    /*
     | ============================================================================
     |
     | The keys under forms must match the name of the form as designated in Statamic.
     |
     | Inside the config for each form, you may specify multiple form managers {form-mappings.managers}
     | which are responsible for aggregating submission data and shuttling off to a 3rd party
     | which can be anything from SMTP email or a CRM.
     |
     | Managers must be defined above under the {managers} config. You may specify a subtype
     | (snake-case) for each manager specific to the manager type, ie:
     |
     | - Sales Force => crm::sales-force
     | - Pardot => crm::pardot
     |
     | * Example *
     |
     | 'schedule_consultation' => [
     |      'crm::sales-force' => [
     |          '::enabled'   => true,
     |          '::fake'      => env('FORMS_DEBUG', true),
     |          '::fake-type' => 'success',
     |          'default'     => [
     |              'lead_source' => 'Schedule Consultation',
     |          ],
     |      ]
     | ]
     |
     | ============================================================================
     */
    'forms'    => []
];
