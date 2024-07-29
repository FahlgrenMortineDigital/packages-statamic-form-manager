@extends('statamic::layout')
@section('title', 'Formidable Exports')

@section('content')

    <header class="mb-3">
        <h1>Formidable Exports</h1>
    </header>

    @include('statamic::partials.docs-callout', [
        'topic' => 'Formidable',
        'url' => 'https://statamic.com/addons/rias/redirect'
    ])

@endsection