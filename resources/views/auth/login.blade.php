@extends('layouts.app')

@section('title', 'Staff login')

@section('content')
<x-page-shell eyebrow="Staff" title="Staff login">
    <x-slot:lead>
        For Department Head, SASO, Campus Director, President, Registrar, and Guidance.
        Staff accounts are created by the System Administrator.
        Students use <a href="{{ route('student.identity') }}" class="font-semibold underline">Request LOA</a> instead.
    </x-slot:lead>

    <form method="POST" action="{{ route('login.store') }}" class="space-y-4 rounded-2xl border border-maroon-900/10 bg-white p-6 sm:p-8">
        @csrf
        <div>
            <label for="email" class="block text-sm font-medium text-maroon-900">EVSU email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                placeholder="name@evsu.edu.ph"
                class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
            @error('email') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
        </div>
        <x-password-field id="password" name="password" label="Password" placeholder="Enter your password" class="text-base" />
        <div class="flex items-center justify-between gap-3">
            <label class="flex items-center gap-2 text-sm text-maroon-800">
                <input type="checkbox" name="remember" class="rounded border-maroon-900/30">
                Remember me
            </label>
            <a href="{{ route('password.request') }}" class="text-sm font-semibold text-maroon-700 underline underline-offset-2">Forgot password?</a>
        </div>
        @if (session('status'))
            <p class="rounded-lg border border-emerald-600/20 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
        @endif
        <button type="submit" class="w-full rounded-xl bg-maroon-800 px-4 py-3.5 text-base font-semibold text-cream-50 hover:bg-maroon-700">
            Log in
        </button>
    </form>
</x-page-shell>
@endsection
