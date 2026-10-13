@extends('layouts.app')

@section('title', 'Check your email')

@section('content')
<x-page-shell eyebrow="Link sent" title="Check your email">
    <x-slot:lead>
        If the code was correct, we sent a private password reset link to your staff email.
        Open that message and click <strong>Set new password</strong>. The link expires in one hour.
    </x-slot:lead>

    <div class="space-y-4 rounded-2xl border border-maroon-900/10 bg-white p-6 sm:p-8">
        <p class="text-base leading-relaxed text-maroon-800/80">
            Check your inbox and spam folder. The email comes from <strong>EVSU OCC LOA</strong>.
        </p>
        <a href="{{ route('login') }}" class="inline-flex text-sm font-semibold text-maroon-700 underline underline-offset-2">Back to login</a>
    </div>
</x-page-shell>
@endsection
