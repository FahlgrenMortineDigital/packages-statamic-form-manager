<?php

namespace Fahlgrendigital\StatamicFormManager\Http\Filters;

use Fahlgrendigital\StatamicFormManager\Data\Export;
use Illuminate\Support\Collection;
use Statamic\Query\Scopes\Filter;

class ByFormHandle extends Filter
{
    public $pinned = true;

    public static function title(): string
    {
        return __('Form');
    }

    public function fieldItems(): array
    {
        return [
            'form_handle' => [
                'type'    => 'select',
                'options' => $this->options()
            ],
        ];
    }

    public function options(): Collection
    {
        return Export::query()->groupBy('form_handle')->select('form_handle')->get()->mapWithKeys(function ($export) {
            return [$export->form_handle => ucfirst($export->form_handle)];
        });
    }

    public function apply($query, $values): void
    {
        $query->where('form_handle', $values['form_handle']);
    }

    public function badge($values): string
    {
        return ucfirst($values['form_handle']);
    }

    public function visibleTo($key): bool
    {
        return $key == 'exports';

    }
}