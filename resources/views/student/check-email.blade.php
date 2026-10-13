@php $isExtension = $isExtension ?? false; @endphp
@extends('layouts.app')

@section('title', 'Check your email')

@section('content')
<x-page-shell eyebrow="Link sent" title="Check your email">
    <x-slot:lead>
        We sent a private {{ $isExtension ? 'leave extension' : 'LOA' }} form link to <strong>{{ session('email') }}</strong>.
        Open that message and click <strong>{{ $isExtension ? 'Open extension form' : 'Open LOA form' }}</strong>. Use the button the same day you receive the email.
    </x-slot:lead>

    <div class="space-y-4 rounded-2xl border border-maroon-900/10 bg-white p-6 sm:p-8">
        <p class="text-base leading-relaxed text-maroon-800/80">
            Check your inbox and spam folder. The email comes from <strong>EVSU OCC LOA</strong>.
        </p>

        <ul class="space-y-2 text-sm text-maroon-800/70">
            <li class="flex gap-2">
                <span class="mt-0.5 shrink-0 text-maroon-700">→</span>
                <span>Open the link <strong>on the same device or network</strong> where this system is accessible. If you requested from a campus computer, open the email on that same computer.</span>
            </li>
            <li class="flex gap-2">
                <span class="mt-0.5 shrink-0 text-maroon-700">→</span>
                <span>Do <strong>not</strong> forward the email — the link is private to you and will only work once.</span>
            </li>
            <li class="flex gap-2">
                <span class="mt-0.5 shrink-0 text-maroon-700">→</span>
                <span>If the button does not work, copy the full link from the email and paste it into your browser's address bar.</span>
            </li>
            <li class="flex gap-2">
                <span class="mt-0.5 shrink-0 text-maroon-700">→</span>
                <span>If the link says "invalid" or "expired", come back here and request a new one.</span>
            </li>
        </ul>

        <div class="flex flex-wrap gap-4 pt-2">
            <a href="{{ route($isExtension ? 'student.extend' : 'student.identity') }}" class="text-sm font-semibold text-maroon-700 underline underline-offset-2">Request a new link</a>
            <a href="{{ route('home') }}" class="text-sm font-semibold text-maroon-700">Back to home</a>
        </div>
    </div>
</x-page-shell>
@endsection
