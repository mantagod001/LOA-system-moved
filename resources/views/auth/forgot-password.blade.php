@extends('layouts.app')

@section('title', 'Forgot password')

@section('content')
<x-page-shell eyebrow="Staff" title="Forgot password">
    <x-slot:lead>
        Enter your staff EVSU email. We will send a 6-digit code to verify it is you.
        After that, we will send a link to set a new password.
    </x-slot:lead>

    <form method="POST" action="{{ route('password.request.store') }}" class="space-y-5 rounded-2xl border border-maroon-900/10 bg-white p-6 sm:p-8">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-maroon-900">EVSU email</label>
            <input id="email" name="email" type="email"
                   value="{{ old('email') }}"
                   required autocomplete="email" autofocus
                   placeholder="name@evsu.edu.ph"
                   class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2 @error('email') border-red-400 bg-red-50 @enderror">
            @error('email')
                <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="w-full rounded-xl bg-maroon-800 px-4 py-3.5 text-base font-semibold text-cream-50 hover:bg-maroon-700">
            Send verification code
        </button>

        <p class="text-center text-sm text-maroon-800/70">
            <a href="{{ route('login') }}" class="font-semibold text-maroon-700 underline underline-offset-2">Back to login</a>
        </p>
    </form>
</x-page-shell>
@endsection
