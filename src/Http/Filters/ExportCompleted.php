<?php

namespace Fahlgrendigital\StatamicFormManager\Http\Filters;

use Illuminate\Support\Collection;
use Statamic\Query\Scopes\Filter;

class ExportCompleted extends Filter
{
    public $pinned = true;

    public static function title(): string
    {
        return __('Completed');
    }

    public function fieldItems(): array
    {
        return [
            'completed' => [
                'type'    => 'radio',
                'options' => [
                    'yes'  => __('Yes'),
                    'no' => __('No'),
                ]
            ],
        ];
    }

    public function autoApply(): array
    {
        return [
            'completed' => 'yes'
        ];
    }

    public function badge($values): string
    {
        return $values['completed'] === 'yes' ? __('Completed') : __('Not Completed');
    }

    public function visibleTo($key): bool
    {
        return $key == 'exports';
    }

    protected function options(): Collection
    {
        return collect([
            'yes'  => __('Completed'),
            'no' => __('Not Completed'),
        ]);
    }

    public function apply($query, $values)
    {
        $query->having('completed', $values['completed'] === 'yes' ? 1 : 0);
    }
}