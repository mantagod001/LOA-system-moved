@extends('layouts.app')

@section('title', 'Resubmission received')

@section('content')
<x-page-shell eyebrow="EVSU-SASO-F-040 · Resubmitted" title="Your updated LOA has been received" width="2xl">
    <x-slot:lead>
        Your resubmission is back in the approval queue from the office that last rejected it.
    </x-slot:lead>

    <div class="space-y-4 rounded-2xl border border-maroon-900/10 bg-white p-6 sm:p-8">

        {{-- New control number --}}
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-maroon-700">Updated Control Number</p>
            <p class="mt-1 rounded-xl bg-maroon-900 px-4 py-4 text-center font-mono text-xl tracking-wider text-gold-400">
                {{ $control_number }}
            </p>
            <p class="mt-2 text-center text-xs text-maroon-800/60">
                Keep this number — your application has been updated with the revision suffix.
            </p>
        </div>

        <hr class="border-maroon-900/10">

        {{-- What happens next --}}
        <div>
            <p class="mb-2 text-sm font-semibold text-maroon-900">What happens next</p>
            <p class="text-sm leading-relaxed text-maroon-800/80">
                Offices that already approved this leave stay approved. The office that rejected it reviews again.
                You will receive an email when there is a decision.
            </p>
            <p class="mt-2 text-sm text-maroon-800/80">
                Track your status anytime at
                <a href="{{ route('student.status') }}" class="font-semibold text-maroon-700 underline underline-offset-2">Check LOA Status</a>.
            </p>
        </div>

        <a href="{{ route('home') }}" class="inline-flex pt-2 text-sm font-semibold text-maroon-700">Back to home</a>
    </div>
</x-page-shell>
@endsection
