@extends('statamic::layout')
@section('title', 'Formidable Exports')

@section('content')

    <header class="mb-3">
        <h1>Formidable Exports</h1>
    </header>

    <exports-listing :filters="{{ $filters->toJson() }}"></exports-listing>

@endsection