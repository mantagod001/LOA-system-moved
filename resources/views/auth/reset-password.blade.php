@extends('layouts.app')

@section('title', 'Set new password')

@section('content')
<x-page-shell eyebrow="Staff" title="Set a new password">
    <x-slot:lead>
        Choose a new password for your LeaveFlow staff account. Use at least 8 characters.
    </x-slot:lead>

    <form method="POST" action="{{ route('password.reset.store', $token) }}" class="space-y-5 rounded-2xl border border-maroon-900/10 bg-white p-6 sm:p-8">
        @csrf

        <x-password-field id="password" name="password" label="New password" placeholder="Enter a new password" class="text-base" />
        @error('password')
            <p class="-mt-3 text-sm text-maroon-600">{{ $message }}</p>
        @enderror

        <x-password-field id="password_confirmation" name="password_confirmation" label="Confirm new password" placeholder="Re-enter the new password" class="text-base" />

        <button type="submit"
                class="w-full rounded-xl bg-maroon-800 px-4 py-3.5 text-base font-semibold text-cream-50 hover:bg-maroon-700">
            Save new password
        </button>
    </form>
</x-page-shell>
@endsection
