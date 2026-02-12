<?php

namespace Fahlgrendigital\StatamicFormManager\Blueprints;

use Statamic\Facades\Blueprint;

class ExportBlueprint extends Blueprint
{
    public function __invoke()
    {
        return Blueprint::make()->setContents([
            'sections' => [
                'main' => [
                    'fields' => [
                        [
                            'handle' => 'form_handle',
                            'field' => [
                                'type' => 'text',
                            ],
                        ],
                        [
                            'handle' => 'submission_id',
                            'field' => [
                                'type' => 'text',
                            ],
                        ],
                        [
                            'handle' => 'exported_count',
                            'field' => [
                                'type' => 'text',
                            ],
                        ],
                        [
                            'handle' => 'failed_count',
                            'field' => [
                                'type' => 'text',
                            ],
                        ],
                        [
                            'handle' => 'pending_count',
                            'field' => [
                                'type' => 'text',
                            ],
                        ],
                        [
                            'handle' => 'earliest_created_at',
                            'field' => [
                                'type' => 'date',
                            ],
                        ],
                        [
                            'handle' => 'completed',
                            'field' => [
                                'type' => 'toggle',
                            ]
                        ]
                    ],
                ],
            ],
        ]);
    }
}