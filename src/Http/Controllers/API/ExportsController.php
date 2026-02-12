<?php

namespace Fahlgrendigital\StatamicFormManager\Http\Controllers\API;

use Fahlgrendigital\StatamicFormManager\Data\Export;
use Fahlgrendigital\StatamicFormManager\Http\Resources\Api\ExportResource;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;

class ExportsController
{
    use QueriesFilters;

    public function index()
    {
        $query = Export::query()->when(request('search'), function ($query, $search) {
            $query->where('form_handle', 'like', "%{$search}%")->orWhere('submission_payload', 'like', "%{$search}%");
        })->forIndexPage();

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

        return ExportResource::collection($exports);
    }

    public function show(Export $export)
    {

    }
}