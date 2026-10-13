@php $isExtension = $isExtension ?? false; @endphp
@extends('layouts.app')

@section('title', $isExtension ? 'Extend leave' : 'Request LOA')

@section('content')
<x-page-shell eyebrow="Student · no account needed" title="{{ $isExtension ? 'Extend an approved leave' : 'Request Leave of Absence' }}">
    <x-slot:lead>
        @if ($isExtension)
            Enter the student number and EVSU email from your approved leave. Both must match. After you open the email link, the extension form is filled from that leave. The President reviews extensions first, then Department Head, SASO, and Campus Director.
        @else
            Enter your student number, name, and official EVSU email. We will send a 6-digit code to that email.
            Your name and student number will be filled in on the LOA form after you open the link.
        @endif
    </x-slot:lead>

    @if ($errors->has('token'))
        <p class="mb-4 rounded-lg border border-maroon-600/20 bg-maroon-600/10 px-4 py-3 text-sm text-maroon-800">{{ $errors->first('token') }}</p>
    @endif

    <form method="POST" action="{{ route($isExtension ? 'student.extend.store' : 'student.identity.store') }}" class="space-y-5 rounded-2xl border border-maroon-900/10 bg-white p-6 sm:p-8">
        @csrf

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

        @unless ($isExtension)
        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label for="first_name" class="block text-sm font-medium text-maroon-900">First name</label>
                <input id="first_name" name="first_name" required value="{{ old('first_name') }}"
                       class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2 @error('first_name') border-red-400 bg-red-50 @enderror">
                @error('first_name') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="middle_name" class="block text-sm font-medium text-maroon-900">Middle name <span class="font-normal text-maroon-800/50">(optional)</span></label>
                <input id="middle_name" name="middle_name" value="{{ old('middle_name') }}"
                       class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                @error('middle_name') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="last_name" class="block text-sm font-medium text-maroon-900">Last name</label>
                <input id="last_name" name="last_name" required value="{{ old('last_name') }}"
                       class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2 @error('last_name') border-red-400 bg-red-50 @enderror">
                @error('last_name') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>
        </div>
        @endunless

        <div>
            <label for="email" class="block text-sm font-medium text-maroon-900">EVSU email address</label>
            <input id="email" name="email" type="email"
                   value="{{ old('email') }}"
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
    </form>
</x-page-shell>
@endsection
