<?php

namespace Fahlgrendigital\StatamicFormManager\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Statamic\CP\Column;
use Statamic\CP\Columns;

class ExportCollection extends ResourceCollection
{
    public $collects = Export::class;

    protected mixed $columnPreferenceKey;

    public function columnPreferenceKey($key): ResourceCollection
    {
        $this->columnPreferenceKey = $key;

        return $this;
    }

    private function setColumns(): void
    {
        $columns = [
            Column::make('form_handle')->label('Form')->sortable(true),
            Column::make('submission_id')->label('Submission'),
            Column::make('exported_count')->label('Exported'),
            Column::make('failed_count')->label('Failed'),
        ];

        $columns = new Columns($columns);

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
                $export->columns($this->columns);
            }),

            'meta' => [
                'columns' => $this->columns,
            ],
        ];
    }
}