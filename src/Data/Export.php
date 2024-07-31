<?php

namespace Fahlgrendigital\StatamicFormManager\Data;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Statamic\Forms\Submission;

class Export extends Model
{
    protected $guarded = [];

    protected $casts = [
        'submission_payload' => 'array',
        'failed_at'          => 'datetime',
        'exported_at'        => 'datetime',
    ];

    public function getConnectionName(): string
    {
        return config('statamic-formidable.exports.connection');
    }

    /**
     * ================================
     * Scopes
     * ================================
     */

    public function scopeForSubmission(Builder $query, Submission $submission)
    {
        $query->where('submission_id', $submission->id());
    }

    public function scopeForIndexPage(Builder $query)
    {
        $sub_query = Export::query()
                           ->groupBy('submission_id')
                           ->select('submission_id')
                           ->selectRaw('COUNT(exported_at) as exported_count')
                           ->selectRaw('CAST(SUM(CASE WHEN exported_at IS NULL AND failed_at IS NULL THEN 1 ELSE 0 END) AS UNSIGNED) as pending_count')
                           ->selectRaw('COUNT(failed_at) as failed_count');

        $query->joinSub($sub_query, 'sub_query', function ($join) {
            $join->on('exports.submission_id', '=', 'sub_query.submission_id');
        })
              ->groupBy('exports.form_handle', 'sub_query.submission_id', 'sub_query.exported_count', 'sub_query.failed_count', 'sub_query.pending_count')
              ->select(
                  'exports.form_handle',
                  'sub_query.submission_id',
                  'sub_query.exported_count',
                  'sub_query.failed_count',
                  'sub_query.pending_count',
                  DB::raw('CASE WHEN sub_query.exported_count = 0 THEN 0 WHEN sub_query.failed_count > 0 THEN 0 ELSE 1 END as completed')
              );
    }

    /**
     * ================================
     * Custom Methods
     * ================================
     */

    public function markFailed(): void
    {
        $this->update(['failed_at' => now(), 'exported_at' => null]);
    }

    public function markSucceeded(): void
    {
        $this->update(['exported_at' => now(), 'failed_at' => null]);
    }

    public function completed(): bool
    {
        return $this->exported_at !== null && $this->failed_at === null;
    }

    public function pending(): bool
    {
        return $this->exported_at === null && $this->failed_at === null;
    }

    public function failed(): bool
    {
        return $this->failed_at !== null;
    }

    public static function newFormSubmission(Submission $submission, string $destination): Export
    {
        return static::create([
            'submission_id' => $submission->id(),
            'form_handle'   => $submission->form->handle(),
            'destination'   => $destination,
        ]);
    }
}