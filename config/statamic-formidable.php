<?php

use Fahlgrendigital\StatamicFormManager\Connector;

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
    'connectors' => [
        'mailable' => Connector\MailableConnection::class,
        'http'     => Connector\HttpConnection::class,
    ],

    'exports' => [
        'connection' => env('FORMIDABLE_EXPORT_CONNECTION', 'mysql'),
    ],

    /**
     * ============================================================================
     *
     * Specify the queue connection and queue name to use when dispatching form
     * handlers.
     *
     * ============================================================================
     */
    'queue'   => [
        'connection' => env('FORMIDABLE_QUEUE_CONNECTION', env('QUEUE_CONNECTION', 'sync')),
        'queue'      => env('FORMIDABLE_QUEUE', 'formidable-submissions')
    ],
];
