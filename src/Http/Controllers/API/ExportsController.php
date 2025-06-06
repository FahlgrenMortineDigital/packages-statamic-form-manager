<?php

namespace Fahlgrendigital\StatamicFormManager\Http\Controllers\API;

use Fahlgrendigital\StatamicFormManager\Data\Export;
use Fahlgrendigital\StatamicFormManager\Http\Resources\ExportCollection;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;

class ExportsController
{
    use QueriesFilters;

    public function index()
    {
        $query = Export::query()->when(request('search'), function ($query, $search) {
            $query->where('form_handle', 'like', "%{$search}%")->orWhere('submission_payload', 'like', "%{$search}%");
        })->forIndexPage();

        // Statamic vue component utf8btoa encodes the filters, so we need to decode them
        $activeFilterBadges = $this->queryFilters($query, json_decode(base64_decode(request()->get('filters')), true));

        $sortField     = request('sort');
        $sortDirection = request('order', 'asc');

        if (!$sortField && !request('search')) {
            $sortField     = 'earliest_created_at';
            $sortDirection = 'desc';
        }

        if ($sortField) {
            $query->orderBy($sortField, $sortDirection);
        }

        $exports = $query->paginate(request('perPage'));

        return (new ExportCollection($exports))
            ->columnPreferenceKey('form_handle')
            ->additional([
            'meta' => [
                'activeFilterBadges' => $activeFilterBadges,
            ],
        ]);
    }

    public function show(Export $export)
    {

    }
}