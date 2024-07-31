@extends('statamic::layout')
@section('title', 'Submission')

@section('content')
    <header class="mb-3">
        <div class="flex justify-between">
            <h1>Submission Exports: {{$submission->id()}}</h1>

            @if(!$completed)
                <span class="inline-flex items-center bg-white gap-x-1.5 rounded-md px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                  <svg class="h-1.5 w-1.5 fill-red-500" viewBox="0 0 6 6" aria-hidden="true">
                    <circle cx="3" cy="3" r="3" />
                  </svg>
                  Incomplete
                </span>
            @else
                <span class="inline-flex items-center bg-white gap-x-1.5 rounded-md px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                  <svg class="h-1.5 w-1.5 fill-green-500" viewBox="0 0 6 6" aria-hidden="true">
                    <circle cx="3" cy="3" r="3"/>
                  </svg>
                  Completed
                </span>
            @endif
        </div>
    </header>

    <div class="card p-0">
        @foreach($exports as $index => $export)
            <div class="p-4 @if($loop->even)bg-gray-200 dark:bg-dark-700 @endif @if($loop->last)rounded-b @endif @if(!$loop->first)dark:border-dark-900 border-t @endif">
                <div class="flex justify-between">
                    <h2>{{$export->destination}}</h2>

                    <div class="flex">
                        @if($export->failed())
                            <span class="inline-flex items-center bg-white gap-x-1.5 rounded-md px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                                          <svg class="h-1.5 w-1.5 fill-red-500" viewBox="0 0 6 6" aria-hidden="true">
                                            <circle cx="3" cy="3" r="3" />
                                          </svg>
                                          Failed
                                        </span>
                        @elseif($export->pending())
                            <span class="inline-flex items-center bg-white gap-x-1.5 rounded-md px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                                          <svg class="h-1.5 w-1.5 fill-yellow-500" viewBox="0 0 6 6" aria-hidden="true">
                                            <circle cx="3" cy="3" r="3"/>
                                          </svg>
                                          Pending
                                        </span>
                        @else
                            <span class="inline-flex items-center bg-white gap-x-1.5 rounded-md px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200">
                                          <svg class="h-1.5 w-1.5 fill-green-500" viewBox="0 0 6 6" aria-hidden="true">
                                            <circle cx="3" cy="3" r="3"/>
                                          </svg>
                                          Completed
                                        </span>
                        @endif
                    </div>
                </div>

                <div class="flex justify-between items-center mt-2">
                    <div>
                        @if($export->submission_payload)
                            <json-pretty-print
                                    :data="{{ json_encode($export->submission_payload) }}"
                                    class="text-gray dark:text-dark-150 text-sm my-2"></json-pretty-print>
                        @endif
                    </div>

                    @if(!$export->completed())
                        @if($export->failed())
                            <button class="btn">Retry</button>
                        @else
                            <button class="btn">Run</button>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    @include('statamic::partials.docs-callout', [
        'topic' => 'Formidable',
        'url' => 'https://statamic.com/addons/rias/redirect'
    ])
@endsection