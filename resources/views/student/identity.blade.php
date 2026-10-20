@php $isExtension = $isExtension ?? false; @endphp
@extends('layouts.app')

@section('title', $isExtension ? 'Extend leave' : 'Request LOA')

@section('content')
<x-page-shell eyebrow="Student · no account needed" title="{{ $isExtension ? 'Extend an approved leave' : 'Request Leave of Absence' }}">
    <x-slot:lead>
        @if ($isExtension)
            Enter the student number and EVSU email from your approved leave. Both must match. After you open the email link, the extension form is filled from that leave. The President reviews extensions first, then Department Head, SASO, and Campus Director.
        @else
            Enter your official EVSU email address. We will send a 6-digit verification code to confirm it's you.
        @endif
    </x-slot:lead>

    @if ($errors->has('token'))
        <p class="mb-4 rounded-lg border border-maroon-600/20 bg-maroon-600/10 px-4 py-3 text-sm text-maroon-800">{{ $errors->first('token') }}</p>
    @endif

    <form method="POST" action="{{ route($isExtension ? 'student.extend.store' : 'student.identity.store') }}" class="space-y-5 rounded-2xl border border-maroon-900/10 bg-white p-6 sm:p-8">
        @csrf

        @if ($isExtension)
            <div>
                <label for="student_id" class="block text-sm font-medium text-maroon-900">Student number</label>
                <input id="student_id" name="student_id" required
                       value="{{ old('student_id') }}"
                       inputmode="numeric"
                       autofocus
                       placeholder="e.g. 2021-12345"
                       data-student-id-input
                       class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2 @error('student_id') border-red-400 bg-red-50 @enderror">
                @error('student_id')
                    <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <div>
            <label for="email" class="block text-sm font-medium text-maroon-900">EVSU email address</label>
            <input id="email" name="email" type="email"
                   value="{{ old('email') }}"
                   @if (! $isExtension) autofocus @endif
                   required autocomplete="email"
                   placeholder="name@evsu.edu.ph"
                   class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2 @error('email') border-red-400 bg-red-50 @enderror">
            <p class="mt-1.5 text-xs text-maroon-800/60">Only <strong>@evsu.edu.ph</strong> addresses are accepted. Gmail, Yahoo, and other personal emails will be rejected.</p>
            @error('email')
                <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="w-full rounded-xl bg-maroon-800 px-4 py-3.5 text-base font-semibold text-cream-50 hover:bg-maroon-700">
            Send verification code
        </button>

        <div class="border-t border-maroon-900/10 pt-4 flex flex-wrap items-center justify-between gap-3 text-sm text-maroon-800/70">
            @if ($isExtension)
                <p>
                    Applying for a new leave instead?
                    <a href="{{ route('student.identity') }}" class="font-semibold text-maroon-700 underline underline-offset-2 hover:text-maroon-600">Request new LOA</a>
                </p>
            @else
                <p>
                    Need to extend an approved leave instead?
                    <a href="{{ route('student.extend') }}" class="font-semibold text-maroon-700 underline underline-offset-2 hover:text-maroon-600">Extend leave</a>
                </p>
            @endif
            <div class="flex items-center gap-4">
                <a href="{{ route('student.status') }}" class="font-semibold text-maroon-700 underline underline-offset-2 hover:text-maroon-600">Check status</a>
                <a href="{{ route('home') }}" class="font-semibold text-maroon-800/70 hover:text-maroon-900">Back to home</a>
            </div>
        </div>
    </form>
</x-page-shell>
@endsection
