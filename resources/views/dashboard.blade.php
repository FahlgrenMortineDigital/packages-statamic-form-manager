@extends('statamic::layout')
@section('title', 'Form Submission Exports')

@section('content')

    <header class="mb-3">
        <h1>Formidable</h1>
    </header>

    @include('statamic::partials.docs-callout', [
        'topic' => 'Formidable',
        'url' => 'https://statamic.com/addons/rias/redirect'
    ])

@endsection