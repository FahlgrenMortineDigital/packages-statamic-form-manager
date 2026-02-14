<?php

namespace Fahlgrendigital\StatamicFormManager\Http\Controllers\API;

use Fahlgrendigital\StatamicFormManager\Data\Export;
use Fahlgrendigital\StatamicFormManager\Http\Resources\ExportCollection;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;
use Statamic\Http\Requests\FilteredRequest;

class ExportsController
{
    use QueriesFilters;

    public function index(FilteredRequest $request)
    {
        $query = Export::query()->when($request->search, function ($query, $search) {
                    $query->where('form_handle', 'like', "%{$search}%")
                        ->orWhere('submission_payload', 'like', "%{$search}%");
                })->forIndexPage();
        $activeFilterBadges = $this->queryFilters(
            $query, 
            $request->filters
        );

        $sortField     = request('sort');
        $sortDirection = request('order', 'asc');
    
        if (!$sortField && !$request->search) {
            $sortField     = 'earliest_created_at';
            $sortDirection = 'desc';
        }

        if ($sortField) {
            $query->orderBy($sortField, $sortDirection);
        }

        $exports = $query->paginate(request('perPage'));

        return (new ExportCollection($exports))
            ->columnPreferenceKey('exports.columns')
            ->additional([
                'meta' => [
                    'activeFilterBadges' => $activeFilterBadges,
                ]
            ]);
    }
}