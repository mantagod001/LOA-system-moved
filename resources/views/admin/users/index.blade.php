@extends('layouts.staff')

@section('eyebrow', 'Administrator')
@section('title', 'User Management')

@section('content')

    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm text-maroon-900/60">All staff accounts. Only the Administrator can add, edit, or remove users.</p>
        </div>
        <a href="{{ route('admin.users.create') }}"
           class="rounded-xl bg-maroon-800 px-4 py-2 text-sm font-semibold text-cream-50 shadow-sm hover:bg-maroon-700">
            + Invite User
        </a>
    </div>

    {{-- Success / status flash --}}
    @if (session('status'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->has('delete') || $errors->has('resend'))
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first('delete') ?: $errors->first('resend') }}
        </div>
    @endif

    {{-- Invite link copy banner --}}
    @if (session('invite_link'))
        <div class="mb-5 rounded-xl border border-amber-400 bg-amber-50 p-4">
            <p class="mb-2 text-sm font-semibold text-amber-800">
                Setup link for <strong>{{ session('invite_link_for') }}</strong> — copy and share manually:
            </p>
            <div class="flex items-center gap-2">
                <input id="invite-link-input" type="text" readonly value="{{ session('invite_link') }}"
                       class="flex-1 rounded-lg border border-amber-300 bg-white px-3 py-2 font-mono text-xs text-amber-900 outline-none">
                <button onclick="navigator.clipboard.writeText(document.getElementById('invite-link-input').value).then(()=>this.textContent='Copied!')"
                        class="rounded-lg border border-amber-400 bg-white px-3 py-2 text-xs font-semibold text-amber-800 hover:bg-amber-100">
                    Copy
                </button>
            </div>
            <p class="mt-1.5 text-xs text-amber-700">Expires in 48 hours. Opening it invalidates any previous link for this user.</p>
        </div>
    @endif

    {{-- Users table --}}
    <div class="overflow-x-auto rounded-2xl border border-maroon-900/10 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-maroon-900/10">
            <thead class="bg-maroon-950/5">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70">User</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70">Role</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70">Department</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70">Status</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-maroon-900/70">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-maroon-900/10">
                @forelse ($users as $person)
                    @php
                        $parts    = explode(' ', trim($person->name));
                        $initials = strtoupper(($parts[0][0] ?? 'U') . (end($parts)[0] ?? ''));
                        $roleColor = match ($person->role) {
                            \App\Enums\UserRole::Administrator  => 'bg-purple-100 text-purple-800 border-purple-300',
                            \App\Enums\UserRole::DepartmentHead => 'bg-blue-100 text-blue-800 border-blue-300',
                            \App\Enums\UserRole::SasoOfficer    => 'bg-amber-100 text-amber-800 border-amber-300',
                            \App\Enums\UserRole::CampusDirector => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                            \App\Enums\UserRole::President      => 'bg-indigo-100 text-indigo-800 border-indigo-300',
                            \App\Enums\UserRole::Registrar      => 'bg-teal-100 text-teal-800 border-teal-300',
                            \App\Enums\UserRole::Guidance       => 'bg-sky-100 text-sky-800 border-sky-300',
                            default                             => 'bg-gray-100 text-gray-700 border-gray-300',
                        };
                    @endphp
                    <tr class="hover:bg-maroon-950/[0.02]">
                        {{-- User column: avatar + name + email --}}
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-maroon-700 text-xs font-bold uppercase text-cream-50">
                                    {{ $initials }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-maroon-950">{{ $person->name }}</p>
                                    <p class="truncate text-xs text-maroon-900/55">{{ $person->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $roleColor }}">
                                {{ $person->role->label() }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-sm text-maroon-900/70">{{ $person->department?->name ?? '—' }}</td>
                        <td class="px-5 py-3">
                            @if ($person->isActivated())
                                <span class="rounded-full border border-emerald-600 bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">Active</span>
                            @else
                                <span class="rounded-full border border-amber-500 bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700">Pending invite</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="inline-flex flex-wrap items-center justify-end gap-2">
                                <a href="{{ route('admin.users.edit', $person) }}"
                                   class="rounded-md border border-maroon-900/20 bg-white px-3 py-1.5 text-xs font-semibold text-maroon-700 hover:bg-maroon-50">
                                    Edit
                                </a>

                                @if (! $person->isActivated())
                                    <form method="POST" action="{{ route('admin.users.resend', $person) }}">
                                        @csrf
                                        <button type="submit"
                                                class="rounded-md border border-blue-400 bg-white px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50">
                                            Resend
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.users.get-link', $person) }}">
                                        @csrf
                                        <button type="submit"
                                                class="rounded-md border border-amber-400 bg-white px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-50">
                                            Get Link
                                        </button>
                                    </form>
                                @endif

                                @if ($person->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $person) }}"
                                          onsubmit="return confirm('Remove {{ addslashes($person->name) }}? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-md border border-red-400 bg-white px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50">
                                            Remove
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-sm text-maroon-900/50">No users yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="mt-3 text-xs text-maroon-900/40">{{ $users->count() }} user{{ $users->count() !== 1 ? 's' : '' }} total</p>
@endsection
