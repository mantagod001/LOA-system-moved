<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Staff Portal') — LeaveFlow EVSU-OC</title>
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
@php
    $user = auth()->user();
    $roleLabel = $user?->role?->label() ?? 'Staff';
    $deptName = $user?->department?->name;
    $nameParts = explode(' ', trim($user?->name ?? 'U'));
    $initials = strtoupper(($nameParts[0][0] ?? 'U') . (end($nameParts)[0] ?? ''));
    $activeRoute = request()->route()->getName() ?? '';
@endphp
<body class="min-h-screen bg-cream-50 font-sans antialiased text-maroon-950 md:flex">
    <aside class="hidden shrink-0 flex-col justify-between bg-gradient-to-b from-maroon-900 to-maroon-950 text-cream-50 md:flex md:w-64 md:min-h-screen md:sticky md:top-0">
        <div class="flex flex-col gap-6 px-4 py-6">
            <div class="rounded-lg bg-maroon-700 px-4 py-3 shadow-inner">
                <p class="text-lg font-bold tracking-wide text-cream-50">LeaveFlow</p>
                <p class="mt-0.5 text-[11px] uppercase tracking-wider text-cream-100/75">EVSU-OC LOA Portal</p>
            </div>

            <nav class="flex flex-col gap-1" aria-label="Staff navigation">
                @unless ($user?->role->is(\App\Enums\UserRole::Administrator))
                <a href="{{ route('staff.dashboard') }}"
                   class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition {{ str_starts_with($activeRoute, 'staff.dashboard') ? 'bg-maroon-800/80 text-cream-50' : 'text-cream-100/85 hover:bg-white/10 hover:text-cream-50' }}">
                    <span aria-hidden="true" class="text-base leading-none">▣</span>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('staff.pipeline') }}"
                   class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition {{ str_starts_with($activeRoute, 'staff.pipeline') ? 'bg-maroon-800/80 text-cream-50' : 'text-cream-100/85 hover:bg-white/10 hover:text-cream-50' }}">
                    <span aria-hidden="true" class="text-base leading-none">⇌</span>
                    <span>Approval Pipeline</span>
                </a>
                <a href="{{ route('staff.reports') }}"
                   class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition {{ str_starts_with($activeRoute, 'staff.reports') ? 'bg-maroon-800/80 text-cream-50' : 'text-cream-100/85 hover:bg-white/10 hover:text-cream-50' }}">
                    <span aria-hidden="true" class="text-base leading-none">📊</span>
                    <span>Reports &amp; Analytics</span>
                </a>
                @endunless
                <a href="{{ route('staff.profile') }}"
                   class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition {{ str_starts_with($activeRoute, 'staff.profile') ? 'bg-maroon-800/80 text-cream-50' : 'text-cream-100/85 hover:bg-white/10 hover:text-cream-50' }}">
                    <span aria-hidden="true" class="text-base leading-none">👤</span>
                    <span>User Profile</span>
                </a>

                @if ($user?->role->is(\App\Enums\UserRole::Administrator))
                    <a href="{{ route('admin.users.index') }}"
                       class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition {{ str_starts_with($activeRoute, 'admin.users') ? 'bg-maroon-800/80 text-cream-50' : 'text-cream-100/85 hover:bg-white/10 hover:text-cream-50' }}">
                        <span aria-hidden="true" class="text-base leading-none">⚙</span>
                        <span>User Management</span>
                    </a>
                @endif

                <div class="my-2 h-px w-full bg-cream-200/10"></div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-cream-100/85 transition hover:bg-white/10 hover:text-cream-50">
                        <span aria-hidden="true" class="text-base leading-none">⎋</span>
                        <span>Logout</span>
                    </button>
                </form>
            </nav>
        </div>

        <div class="border-t border-cream-200/10 px-4 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-maroon-600 text-sm font-bold uppercase tracking-wide text-cream-50 shadow-inner" title="{{ $user?->name }}">
                    {{ $initials }}
                </div>
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-cream-50">{{ $user?->name }}</p>
                    <p class="truncate text-[11px] text-cream-100/70">{{ $roleLabel }}</p>
                </div>
            </div>
        </div>
    </aside>

    <div class="md:hidden w-full sticky top-0 z-40 border-b border-white/10 bg-gradient-to-b from-maroon-900 to-maroon-950 text-cream-50">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="rounded-md bg-maroon-700 px-3 py-1.5">
                <p class="text-sm font-bold tracking-wide">LeaveFlow</p>
            </div>
            <button type="button" class="rounded-md border border-cream-50/20 px-3 py-1.5 text-xs" data-nav-toggle aria-label="Open staff menu">
                Menu
            </button>
        </div>
        <nav class="hidden flex-col gap-1 border-t border-white/10 px-3 py-3 text-sm" data-nav-panel>
            @unless ($user?->role->is(\App\Enums\UserRole::Administrator))
            <a href="{{ route('staff.dashboard') }}" class="rounded-md px-3 py-2 hover:bg-white/10">▣ Dashboard</a>
            <a href="{{ route('staff.pipeline') }}" class="rounded-md px-3 py-2 hover:bg-white/10">⇌ Approval Pipeline</a>
            <a href="{{ route('staff.reports') }}" class="rounded-md px-3 py-2 hover:bg-white/10">📊 Reports &amp; Analytics</a>
            @endunless
            <a href="{{ route('staff.profile') }}" class="rounded-md px-3 py-2 hover:bg-white/10">👤 User Profile</a>
            @if ($user?->role->is(\App\Enums\UserRole::Administrator))
                <a href="{{ route('admin.users.index') }}" class="rounded-md px-3 py-2 hover:bg-white/10">⚙ User Management</a>
            @endif
            <div class="my-1 h-px w-full bg-cream-200/10"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full rounded-md px-3 py-2 text-left hover:bg-white/10">⎋ Logout</button>
            </form>
            <div class="mt-2 flex items-center gap-2 px-1">
                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-maroon-600 text-xs font-bold uppercase text-cream-50">{{ $initials }}</div>
                <div class="text-xs">
                    <p class="font-semibold">{{ $user?->name }}</p>
                    <p class="text-cream-100/70">{{ $roleLabel }}</p>
                </div>
            </div>
        </nav>
    </div>

    <div class="flex min-h-screen flex-1 flex-col">
        @if (session('status'))
            <div class="mx-auto w-full max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
            </div>
        @endif
        @if ($errors->any())
            <div class="mx-auto w-full max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <header class="mx-auto w-full max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-4 pb-6 border-b border-maroon-900/10 mb-6">
                <div class="min-w-0">
                    @hasSection('eyebrow')
                        <p class="text-xs font-semibold uppercase tracking-wider text-maroon-600">@yield('eyebrow')</p>
                    @endif
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-maroon-950 sm:text-3xl">@yield('title', 'Staff Portal')</h1>
                </div>
                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold text-maroon-950">{{ $user?->name }}</p>
                        <p class="text-xs text-maroon-900/70">
                            {{ $roleLabel }}@if ($deptName) · {{ $deptName }}@endif
                        </p>
                    </div>
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-maroon-600 text-sm font-bold uppercase tracking-wide text-cream-50 ring-2 ring-white" title="{{ $user?->name }}">
                        {{ $initials }}
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 w-full mx-auto max-w-7xl px-4 pb-12 sm:px-6 lg:px-8">
            @yield('content')
        </main>

        <footer class="border-t border-maroon-900/10 bg-white/60">
            <div class="mx-auto flex max-w-7xl flex-col gap-1 px-4 py-6 text-xs text-maroon-900/60 sm:px-6 lg:px-8 sm:flex-row sm:items-center sm:justify-between">
                <p>LeaveFlow · Eastern Visayas State University — Ormoc Campus</p>
                <p>
                    [Role: {{ $user?->role->value }}@if ($user?->department_id) · Dept: {{ $user?->department->code ?? '?' }}@endif]
                </p>
            </div>
        </footer>
    </div>

    @unless (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        <script src="{{ asset('js/ui.js') }}"></script>
    @endunless
    @stack('scripts')
</body>
</html>
