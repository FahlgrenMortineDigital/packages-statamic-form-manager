<?php

namespace Fahlgrendigital\StatamicFormManager\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\CP\Column;
use Statamic\CP\Columns;

class Export extends JsonResource
{
    protected $columns;

    public function columns($columns): JsonResource
    {
        $this->columns = $columns;

        return $this;
    }

    public function toArray(Request $request)
    {
        if($this->columns instanceof Columns) {
            $data = [];

            $this->columns->each(function (Column $column) use(&$data) {
                $data[$column->field()] = $this->{$column->field()};
            });

            return $data;
        }

        return [
            'id'            => $this->id,
            'form_handle'   => $this->form_handle,
            'submission_id' => $this->submission_id,
            'exported_at'   => $this->exported_at,
            'failed_at'     => $this->failed_at,
        ];
    }
}