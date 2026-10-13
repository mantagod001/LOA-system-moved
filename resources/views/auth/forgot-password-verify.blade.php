@extends('layouts.app')

@section('title', 'Enter verification code')

@section('content')
<x-page-shell eyebrow="Staff · Verify email" title="Enter your code">
    <x-slot:lead>
        If an activated staff account exists for <strong>{{ $email }}</strong>, we sent a 6-digit code.
        Enter it below. The code expires in <strong>10 minutes</strong>.
    </x-slot:lead>

    <div class="space-y-5 rounded-2xl border border-maroon-900/10 bg-white p-6 sm:p-8">
        <form method="POST" action="{{ route('password.verify.store', $token) }}" class="space-y-5">
            @csrf

            <div>
                <label for="otp" class="block text-sm font-medium text-maroon-900">Verification code</label>
                <input id="otp" name="otp"
                       type="text"
                       inputmode="numeric"
                       maxlength="6"
                       autocomplete="one-time-code"
                       autofocus
                       required
                       placeholder="_ _ _ _ _ _"
                       class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-4 py-4 text-center text-3xl font-mono tracking-[0.5em] outline-none ring-maroon-700 focus:ring-2 @error('otp') border-red-400 bg-red-50 @enderror">
                @error('otp')
                    <p class="mt-1.5 text-sm text-maroon-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="w-full rounded-xl bg-maroon-800 px-4 py-3.5 text-base font-semibold text-cream-50 hover:bg-maroon-700">
                Verify and send reset link
            </button>
        </form>

        <div class="border-t border-maroon-900/8 pt-4 text-sm text-maroon-800/70 space-y-2">
            <p>Didn't receive the code? Check spam, or start again if the account is not activated yet.</p>
            <p>
                <a href="{{ route('password.request') }}" class="font-semibold text-maroon-700 underline underline-offset-2">
                    Start over
                </a>
            </p>
        </div>
    </div>
</x-page-shell>
@endsection
