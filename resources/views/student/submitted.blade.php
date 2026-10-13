@extends('layouts.app')

@section('title', 'Request submitted')

@section('content')
<x-page-shell eyebrow="EVSU-SASO-F-040 · Submitted" title="Your LOA has been received" width="2xl">
    <x-slot:lead>
        A confirmation has been sent to <strong>{{ $loa->email }}</strong> with a copy of your submitted details.
    </x-slot:lead>

    <div class="space-y-4 rounded-2xl border border-maroon-900/10 bg-white p-6 sm:p-8">

        {{-- Control number --}}
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-maroon-700">Control Number</p>
            <p class="mt-1 rounded-xl bg-maroon-900 px-4 py-4 text-center font-mono text-xl tracking-wider text-gold-400">
                {{ $loa->control_number }}
            </p>
            <p class="mt-2 text-center text-xs text-maroon-800/60">Keep this number — you will need it to follow up on your request.</p>
        </div>

        <hr class="border-maroon-900/10">

        {{-- Form summary --}}
        <div>
            <p class="mb-3 text-sm font-semibold text-maroon-900">Submitted details</p>
            <dl class="space-y-2 text-sm text-maroon-800/80">
                <div class="flex gap-2">
                    <dt class="w-36 shrink-0 font-medium text-maroon-900">Student ID</dt>
                    <dd>{{ $loa->student_id }}</dd>
                </div>
                <div class="flex gap-2">
                    <dt class="w-36 shrink-0 font-medium text-maroon-900">First name</dt>
                    <dd>{{ $loa->first_name ?: $loa->full_name }}</dd>
                </div>
                <div class="flex gap-2">
                    <dt class="w-36 shrink-0 font-medium text-maroon-900">Middle name</dt>
                    <dd>{{ $loa->middle_name ?: '—' }}</dd>
                </div>
                <div class="flex gap-2">
                    <dt class="w-36 shrink-0 font-medium text-maroon-900">Last name</dt>
                    <dd>{{ $loa->last_name ?: '—' }}</dd>
                </div>
                <div class="flex gap-2">
                    <dt class="w-36 shrink-0 font-medium text-maroon-900">Department</dt>
                    <dd>{{ $loa->department?->name ?? '—' }}</dd>
                </div>
                <div class="flex gap-2">
                    <dt class="w-36 shrink-0 font-medium text-maroon-900">Program</dt>
                    <dd>{{ $loa->program?->name ?? '—' }}</dd>
                </div>
                <div class="flex gap-2">
                    <dt class="w-36 shrink-0 font-medium text-maroon-900">Year level</dt>
                    <dd>{{ $loa->year_level ?? '—' }}</dd>
                </div>
                <div class="flex gap-2">
                    <dt class="w-36 shrink-0 font-medium text-maroon-900">Semester</dt>
                    <dd>{{ $loa->academicTermLabel() }}</dd>
                </div>
                <div class="flex gap-2">
                    <dt class="w-36 shrink-0 font-medium text-maroon-900">Leave period</dt>
                    <dd>{{ $loa->start_date?->format('M j, Y') }} — {{ $loa->return_date?->format('M j, Y') }}</dd>
                </div>
                <div class="flex gap-2">
                    <dt class="w-36 shrink-0 font-medium text-maroon-900">Submitted</dt>
                    <dd>{{ $loa->submitted_at?->format('M j, Y g:i A') }}</dd>
                </div>
            </dl>
        </div>

        <hr class="border-maroon-900/10">

        {{-- What happens next --}}
        <div>
            <p class="mb-2 text-sm font-semibold text-maroon-900">What happens next</p>
            <p class="text-sm leading-relaxed text-maroon-800/80">
                @if ($loa->isExtension())
                    The President will review this extension first, then Department Head, SASO, and Campus Director.
                @else
                    Your Department Head will review your application first, followed by the SASO Officer and Campus Director.
                @endif
                You will receive an email update at each step. If your request is not approved, you will receive a link to resubmit with any corrections.
            </p>
            <p class="mt-2 text-sm text-maroon-800/80">
                You can also check your application status anytime at
                <a href="{{ route('student.status') }}" class="font-semibold text-maroon-700 underline underline-offset-2">Check LOA Status</a>
                using your control number above.
            </p>
        </div>

        <a href="{{ route('home') }}" class="inline-flex pt-2 text-sm font-semibold text-maroon-700">Back to home</a>
    </div>
</x-page-shell>
@endsection
