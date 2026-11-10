@extends('layouts.app')

@section('title', $title)

@section('content')
<x-page-shell eyebrow="Link expired" title="{{ $title }}">
    <x-slot:lead>
        {{ $body }}
    </x-slot:lead>

    <div class="space-y-4 rounded-2xl border border-maroon-900/10 bg-white p-6 sm:p-8">
        <p class="text-sm font-semibold text-maroon-900">What to do next</p>
        <p class="text-sm leading-relaxed text-maroon-800/80">
            Check LOA Status on the home page with your control number if you need to see the current record.
        </p>
        <a href="{{ $actionUrl }}" class="inline-flex rounded-xl bg-maroon-800 px-4 py-3 text-sm font-semibold text-cream-50 hover:bg-maroon-700">
            {{ $actionLabel }}
        </a>
    </div>
</x-page-shell>
@endsection
