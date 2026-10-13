<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LeaveFlow') — EVSU Ormoc Campus</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            maroon: {
                                950: '#2b070d',
                                900: '#4a0e18',
                                800: '#6b1424',
                                700: '#8a1c30',
                                600: '#a4283c',
                                500: '#c43b50',
                            },
                            cream: {
                                50: '#fdf7f4',
                                100: '#f6ebe6',
                            },
                            gold: {
                                400: '#d4b36a',
                                500: '#c4a35a',
                            },
                        },
                        fontFamily: {
                            sans: ['Outfit', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        },
                    },
                },
            };
        </script>
        <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    @endif
</head>
<body class="flex min-h-screen flex-col bg-cream-50 font-sans antialiased text-maroon-950">
    <header class="sticky top-0 z-40 border-b border-maroon-900/10 bg-maroon-900 text-cream-50">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-3 sm:px-8 lg:px-10">
            <a href="{{ route('home') }}" class="rounded-md px-1 py-1 text-base font-semibold tracking-wide text-cream-50 hover:text-gold-400">
                Home
            </a>

            <button type="button" class="inline-flex items-center rounded-md border border-cream-50/20 px-3 py-2 text-sm md:hidden" data-nav-toggle aria-label="Open menu">
                Menu
            </button>

            <nav class="hidden items-center gap-2 md:flex">
                <a href="{{ route('home') }}#about" class="rounded-md px-3 py-2 text-sm text-cream-100 hover:bg-white/10">About</a>
                @guest
                    <a href="{{ route('student.identity') }}" class="rounded-md bg-gold-500 px-3 py-2 text-sm font-semibold text-maroon-950 hover:bg-gold-400">Request LOA</a>
                    <a href="{{ route('student.extend') }}" class="rounded-md px-3 py-2 text-sm text-cream-100 hover:bg-white/10">Extend leave</a>
                @endguest
                @auth
                    <a href="{{ route('staff.dashboard') }}" class="rounded-md px-3 py-2 text-sm hover:bg-white/10">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-md px-3 py-2 text-sm hover:bg-white/10">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-md px-3 py-2 text-sm hover:bg-white/10">Staff login</a>
                @endauth
            </nav>
        </div>
        <nav class="hidden flex-col gap-1 border-t border-white/10 px-4 py-3 sm:px-8 md:hidden lg:px-10" data-nav-panel>
            <a href="{{ route('home') }}#about" class="rounded-md px-3 py-2 text-sm text-cream-100">About</a>
            @guest
                <a href="{{ route('student.identity') }}" class="rounded-md bg-gold-500 px-3 py-2 text-sm font-semibold text-maroon-950">Request LOA</a>
                <a href="{{ route('student.extend') }}" class="rounded-md px-3 py-2 text-sm text-cream-100">Extend leave</a>
            @endguest
            @auth
                <a href="{{ route('staff.dashboard') }}" class="rounded-md px-3 py-2 text-sm">Dashboard</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-md px-3 py-2 text-left text-sm">Log out</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="rounded-md px-3 py-2 text-sm">Staff login</a>
            @endauth
        </nav>
    </header>

    <main class="flex flex-1 flex-col">
        @if (session('status'))
            <div class="mx-auto max-w-7xl px-4 pt-4 sm:px-8 lg:px-10">
                <p class="rounded-lg border border-maroon-800/15 bg-white px-4 py-3 text-sm text-maroon-800">{{ session('status') }}</p>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="mt-auto border-t border-maroon-900/10 bg-maroon-950 text-cream-100">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-8 text-sm sm:px-8 lg:px-10 md:flex-row md:items-center md:justify-between">
            <p>LeaveFlow · Eastern Visayas State University — Ormoc Campus</p>
            <div class="flex flex-col gap-1 md:items-end">
                <p class="text-cream-100/70">Leave of Absence requests for students. Staff portal for review and approval.</p>
                @auth
                    <p class="text-xs text-cream-100/40">
                        [Role: {{ auth()->user()->role->value }}@if (auth()->user()->department_id) · Dept: {{ auth()->user()->department->code ?? '?' }}@endif]
                    </p>
                @endauth
            </div>
        </div>
    </footer>
    @unless (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        <script src="{{ asset('js/ui.js') }}"></script>
    @endunless
</body>
</html>
