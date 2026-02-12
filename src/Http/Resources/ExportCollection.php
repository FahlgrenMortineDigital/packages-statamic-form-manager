<?php

namespace Fahlgrendigital\StatamicFormManager\Http\Resources;

use Fahlgrendigital\StatamicFormManager\Blueprints\ExportBlueprint;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Statamic\Http\Resources\CP\Concerns\HasRequestedColumns;

class ExportCollection extends ResourceCollection
{
    use HasRequestedColumns;
    
    public $collects = Export::class;

    protected mixed $columnPreferenceKey;

    public function columnPreferenceKey($key): ResourceCollection
    {
        $this->columnPreferenceKey = $key;

        return $this;
    }

    private function setColumns(): void
    {
        $blueprint = new ExportBlueprint();
        $columns = $blueprint()->columns();

        if ($key = $this->columnPreferenceKey) {
            $columns->setPreferred($key);
        }

        $this->columns = $columns->rejectUnlisted()->values();
    }

    public function toArray(Request $request): array
    {
        $this->setColumns();

        return [
            'data' => $this->collection->each(function ($export) {
                $export->columns($this->requestedColumns());
            }),

            'meta' => [
                'columns' => $this->columns,
            ],
        ];
    }
}