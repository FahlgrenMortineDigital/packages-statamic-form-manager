<?php

namespace Fahlgrendigital\StatamicFormManager\Data;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Statamic\Forms\Submission;

class Export extends Model
{
    protected $guarded = [];

    protected $casts = [
        'submission_payload' => 'array',
    ];

    public function getConnectionName(): string
    {
        return config('statamic-formidable.exports.connection');
    }

    public function scopeForSubmission(Builder $query, Submission $submission)
    {
        $query->where('submission_id', $submission->id());
    }

    public function failed(): void
    {
        $this->update(['failed_at' => now(), 'exported_at' => null]);
    }

    public function succeeded(): void
    {
        $this->update(['exported_at' => now(), 'failed_at' => null]);
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