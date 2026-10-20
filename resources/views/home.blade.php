@extends('layouts.app')

@section('title', 'Home')

@section('content')
<section class="hero-panel flex min-h-[calc(100vh-4.25rem)] items-center text-maroon-950">
    <div class="mx-auto grid w-full max-w-7xl gap-12 px-4 py-16 sm:px-8 lg:grid-cols-2 lg:items-center lg:gap-16 lg:px-10 lg:py-24">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-maroon-700">EVSU Ormoc Campus</p>
            <h1 class="mt-4 max-w-3xl text-4xl font-semibold leading-tight text-maroon-900 sm:text-5xl lg:text-6xl">Leave of Absence, without the paper trail.</h1>
            <p class="mt-6 max-w-2xl text-lg leading-relaxed text-maroon-800/80 lg:text-xl">
                LeaveFlow is the campus LOA system for students who need an official leave. Request online,
                verify with your EVSU email, then track your application with a Control Number.
            </p>
            <div class="mt-10 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                @guest
                    <a href="{{ route('student.identity') }}" class="inline-flex items-center justify-center rounded-xl bg-maroon-800 px-6 py-3.5 text-center text-base font-semibold text-cream-50 hover:bg-maroon-700">
                        Request LOA
                    </a>
                    <a href="{{ route('student.extend') }}" class="inline-flex items-center justify-center rounded-xl border border-maroon-800/20 bg-white px-6 py-3.5 text-center text-base font-semibold text-maroon-900 hover:bg-cream-100">
                        Extend leave
                    </a>
                    <a href="{{ route('student.status') }}" class="inline-flex items-center justify-center rounded-xl border border-maroon-800/20 bg-white px-6 py-3.5 text-center text-base font-semibold text-maroon-900 hover:bg-cream-100">
                        Check LOA Status
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-xl border border-maroon-800/20 bg-white px-6 py-3.5 text-center text-base font-semibold text-maroon-900 hover:bg-cream-100">
                        Staff login
                    </a>
                @else
                    <a href="{{ route('staff.dashboard') }}" class="inline-flex items-center justify-center rounded-xl bg-maroon-800 px-6 py-3.5 text-center text-base font-semibold text-cream-50 hover:bg-maroon-700">
                        Go to Dashboard
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline-flex">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-maroon-800/20 bg-white px-6 py-3.5 text-center text-base font-semibold text-maroon-900 hover:bg-cream-100">
                            Log out
                        </button>
                    </form>
                @endguest
            </div>
            @guest
                <p class="mt-5 text-sm text-maroon-800/70">Students do not create an account. Staff (Department Head, SASO, Campus Director, President, Registrar, Guidance) sign in here.</p>
            @else
                <p class="mt-5 text-sm text-maroon-800/70">Signed in as <strong>{{ auth()->user()->name }}</strong> &middot; {{ auth()->user()->role->label() }}.</p>
            @endguest
        </div>

        <div class="rounded-2xl border border-maroon-900/10 bg-white p-6 sm:p-8 lg:p-10">
            <p class="text-base font-semibold text-maroon-800">How a student request works</p>
            <ol class="mt-6 space-y-5 text-base leading-relaxed text-maroon-800/80 lg:text-lg">
                <li class="flex gap-4">
                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-maroon-800 text-sm font-bold text-cream-50">1</span>
                    <span>For a new leave, enter your official EVSU email. To extend, student number and that same email must both match the approved leave.</span>
                </li>
                <li class="flex gap-4">
                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-maroon-800 text-sm font-bold text-cream-50">2</span>
                    <span>We send a 6-digit verification code to that email, followed by your private form link.</span>
                </li>
                <li class="flex gap-4">
                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-maroon-800 text-sm font-bold text-cream-50">3</span>
                    <span>Fill in your student information and leave details on EVSU-SASO-F-040. You receive a Control Number upon submission.</span>
                </li>
                <li class="flex gap-4">
                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-maroon-800 text-sm font-bold text-cream-50">4</span>
                    <span>Department Head, SASO, then Campus Director review a new leave. Extensions go to the President first, then the same three offices.</span>
                </li>
            </ol>
        </div>
    </div>
</section>

<section id="about" class="mx-auto max-w-7xl px-4 py-16 sm:px-8 lg:px-10 lg:py-24">
    <div class="max-w-3xl">
        <h2 class="text-3xl font-semibold text-maroon-900 lg:text-4xl">Built for EVSU Ormoc’s LOA process</h2>
        <p class="mt-4 text-base leading-relaxed text-maroon-800/80 lg:text-lg">
            The current paper route is slow when a student cannot come to campus. LeaveFlow keeps the same offices
            in sequence, stores supporting files, and logs every approval with name and date.
        </p>
    </div>

    <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ([
            ['Public request', 'No student portal. Identity is confirmed through your official EVSU email link.'],
            ['Control Number', 'Each application gets an ID such as EVSU-OC-LOA-2026-0001 for tracking and the PDF.'],
            ['Multi-level review', 'New leave: Department Head → SASO → Campus Director. Extensions: President first, then the same three offices.'],
            ['Registrar & Guidance', 'After Campus Director approval, Registrar and Guidance update their records for clearance.'],
            ['Return follow-up', 'Seven days before your return date, you confirm if you are coming back.'],
            ['Staff workspace', 'Employees are invited by the System Administrator to access their office queue.'],
        ] as $card)
            <article class="rounded-2xl border border-maroon-900/10 bg-white p-6 lg:p-7">
                <h3 class="text-lg font-semibold text-maroon-900">{{ $card[0] }}</h3>
                <p class="mt-3 text-base leading-relaxed text-maroon-800/75">{{ $card[1] }}</p>
            </article>
        @endforeach
    </div>
</section>
@endsection
