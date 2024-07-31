@extends('statamic::layout')
@section('title', 'Submission')

@section('content')
    <header class="mb-3">
        <h1>Submission: {{$submission->id()}}</h1>
    </header>

    @include('statamic::partials.docs-callout', [
        'topic' => 'Formidable',
        'url' => 'https://statamic.com/addons/rias/redirect'
    ])
@endsection