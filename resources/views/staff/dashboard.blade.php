@extends('layouts.staff')

@section('eyebrow', 'Dashboard Overview')
@section('title', 'Dashboard Overview')

@section('content')
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
        <div class="rounded-2xl border border-maroon-900/10 bg-white p-6 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-maroon-900/60">Total Applications</p>
            <p class="mt-2 text-4xl font-bold tracking-tight text-maroon-950">{{ $stats['total'] }}</p>
            <p class="mt-1 text-xs text-maroon-900/50">In your current scope</p>
        </div>
        <div class="rounded-2xl border border-maroon-900/10 bg-white p-6 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-maroon-900/60">Overdue returns</p>
            <p class="mt-2 text-4xl font-bold tracking-tight {{ $stats['overdue'] > 0 ? 'text-red-700' : 'text-maroon-950' }}">{{ $stats['overdue'] }}</p>
            <p class="mt-1 text-xs text-maroon-900/50">Approved leave end date has passed. Not an enrollment check.</p>
        </div>
        <div class="rounded-2xl border border-maroon-900/10 bg-white p-6 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-maroon-900/60">Active Role</p>
            <p class="mt-2 text-3xl font-bold tracking-tight text-maroon-700">{{ $stats['activeRole'] }}</p>
            @if (optional($user->department)->name)
                <p class="mt-1 text-xs text-maroon-900/50">{{ $user->department->name }}</p>
            @endif
        </div>
    </div>

    {{-- ── Filters ─────────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('staff.dashboard') }}"
          class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl border border-maroon-900/10 bg-white px-5 py-4 shadow-sm">

        <div class="flex-1 min-w-[180px]">
            <label for="dash-search" class="block text-xs font-semibold uppercase tracking-wider text-maroon-900/50 mb-1">Search</label>
            <input id="dash-search" type="text" name="search" value="{{ $filterSearch }}"
                   placeholder="Name, Student ID, or Control No."
                   class="w-full rounded-lg border border-maroon-900/15 bg-cream-50 px-3 py-2 text-sm text-maroon-950 placeholder-maroon-900/30 focus:outline-none focus:ring-2 focus:ring-maroon-700">
        </div>

        <div>
            <label for="dash-status" class="block text-xs font-semibold uppercase tracking-wider text-maroon-900/50 mb-1">Status</label>
            <select id="dash-status" name="status"
                    class="rounded-lg border border-maroon-900/15 bg-cream-50 px-3 py-2 text-sm text-maroon-950 focus:outline-none focus:ring-2 focus:ring-maroon-700">
                <option value="">All Statuses</option>
                <option value="submitted"    {{ $filterStatus === 'submitted'    ? 'selected' : '' }}>Under Review</option>
                <option value="approved"     {{ $filterStatus === 'approved'     ? 'selected' : '' }}>Approved</option>
                <option value="rejected"     {{ $filterStatus === 'rejected'     ? 'selected' : '' }}>Rejected</option>
                <option value="discontinued" {{ $filterStatus === 'discontinued' ? 'selected' : '' }}>Discontinued</option>
                <option value="overdue"      {{ $filterStatus === 'overdue'      ? 'selected' : '' }}>Overdue return</option>
                <option value="draft"        {{ $filterStatus === 'draft'        ? 'selected' : '' }}>Draft</option>
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit"
                    class="rounded-lg bg-maroon-800 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-maroon-700">
                Apply
            </button>
            @if ($filterSearch || $filterStatus)
                <a href="{{ route('staff.dashboard') }}"
                   class="rounded-lg border border-maroon-900/20 bg-white px-4 py-2 text-sm font-semibold text-maroon-700 hover:bg-maroon-50">
                    Clear
                </a>
            @endif
        </div>

        @if ($filterSearch || $filterStatus)
            <div class="flex w-full flex-wrap items-center gap-2 border-t border-maroon-900/8 pt-3">
                <span class="text-xs text-maroon-900/50">Showing {{ $loas->total() }} result{{ $loas->total() !== 1 ? 's' : '' }}</span>
                @if ($filterSearch)
                    <span class="rounded-full bg-maroon-100 px-3 py-1 text-xs font-semibold text-maroon-800">"{{ $filterSearch }}"</span>
                @endif
                @if ($filterStatus)
                    @php $statusLabels = ['submitted'=>'Under Review','approved'=>'Approved','rejected'=>'Rejected','discontinued'=>'Discontinued','overdue'=>'Overdue return']; @endphp
                    <span class="rounded-full bg-maroon-100 px-3 py-1 text-xs font-semibold text-maroon-800">{{ $statusLabels[$filterStatus] ?? ucfirst($filterStatus) }}</span>
                @endif
            </div>
        @endif
    </form>

    @if ($overdueLoas->isNotEmpty())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50/70 p-5 shadow-sm">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-red-700">Overdue returns</p>
                    <p class="mt-0.5 text-sm text-red-800/80">Expected return date has passed and staff have not marked the student as returned.</p>
                </div>
                <a href="{{ route('staff.dashboard', ['status' => 'overdue']) }}" class="text-xs font-semibold text-red-800 underline underline-offset-2">View all</a>
            </div>
            <ul class="divide-y divide-red-100">
                @foreach ($overdueLoas as $overdue)
                    <li class="flex flex-wrap items-center justify-between gap-2 py-2.5 first:pt-0 last:pb-0">
                        <div>
                            <p class="font-mono text-sm font-bold text-maroon-950">{{ $overdue->control_number }}</p>
                            <p class="text-sm text-maroon-900">{{ $overdue->full_name }} · due {{ $overdue->return_date?->format('M j, Y') }}</p>
                        </div>
                        <a href="{{ route('staff.loa.show', $overdue) }}" class="rounded-md border border-red-300 bg-white px-3 py-1.5 text-xs font-semibold text-red-800 hover:bg-red-50">View</a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-3 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-maroon-950">LOA Submissions Overview</h2>
    </div>

    @if ($loas->isEmpty())
        <div class="rounded-2xl border border-dashed border-maroon-900/15 bg-white p-12 text-center">
            @if ($filterSearch || $filterStatus)
                <p class="text-sm text-maroon-900/60">No LOA requests match your current filters.</p>
                <a href="{{ route('staff.dashboard') }}" class="mt-3 inline-block text-xs font-semibold text-maroon-700 underline underline-offset-2">Clear filters</a>
            @else
                <p class="text-sm text-maroon-900/60">No LOA submissions in your current scope yet.</p>
            @endif
        </div>
    @else
        <div class="overflow-x-auto rounded-2xl border border-maroon-900/10 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-maroon-900/10">
                <thead class="bg-maroon-950/5">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-6">App ID</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-6">Student ID</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-6">Student Name</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-6">Date Effective</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-6">Return Date</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-6">Approval Stage / Status</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-6">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-maroon-900/10 bg-white">
                    @foreach ($loas as $loa)
                        @php
                            $stage = $loa->currentStageStatus();
                            $stageLabel = $loa->currentStageLabel();
                            $stageClasses = $stage ? $stage->badgeClasses() : match ($loa->determineCurrentStage()) {
                                'done'     => 'border border-emerald-600 text-white bg-emerald-600 rounded-full px-3 py-1 text-xs font-medium',
                                'rejected' => 'border border-red-600 text-white bg-red-600 rounded-full px-3 py-1 text-xs font-medium',
                                default    => 'border border-gray-400 text-gray-700 bg-gray-50 rounded-full px-3 py-1 text-xs font-medium',
                            };
                        @endphp
                        <tr class="hover:bg-maroon-950/[0.02]">
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-sm font-bold text-maroon-900 sm:px-6">
                                {{ $loa->control_number }}
                                @if ($loa->resubmit_count > 0)
                                    <span class="ml-1 rounded-full border border-amber-400 bg-amber-50 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700">R{{ $loa->resubmit_count }}</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-maroon-900/85 sm:px-6">{{ $loa->student_id ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-maroon-950 sm:px-6">
                                {{ $loa->full_name }}
                                @if ($loa->program || $loa->department)
                                    <div class="text-xs text-maroon-900/55">{{ optional($loa->program)->code ?? '—' }} · {{ optional($loa->department)->name ?? '—' }}</div>
                                @endif
                                @if ($loa->isExtension())
                                    <div class="mt-0.5"><span class="rounded-full border border-indigo-300 bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-800">Extension</span></div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-maroon-900/85 sm:px-6">{{ optional($loa->start_date)->toFormattedDateString() ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-maroon-900/85 sm:px-6">{{ optional($loa->return_date)->toFormattedDateString() ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm sm:px-6">
                                <div class="flex flex-col gap-1">
                                    <span class="{{ $loa->statusBadgeClasses() }}">{{ $loa->statusLabel() }}</span>
                                    @if ($loa->isOverdue())
                                        <span class="border border-red-600 text-white bg-red-600 rounded-full px-3 py-1 text-xs font-medium">Overdue return</span>
                                    @elseif ($loa->returned_at)
                                        <span class="border border-emerald-600 text-emerald-800 bg-emerald-50 rounded-full px-3 py-1 text-xs font-medium">Returned</span>
                                    @endif
                                    @if ($loa->status === 'submitted')
                                        <span class="{{ $stageClasses }}">{{ $stageLabel }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm sm:px-6">
                                <a href="{{ route('staff.loa.show', $loa) }}"
                                   class="inline-flex items-center rounded-md border border-maroon-900/20 bg-white px-3 py-1.5 text-xs font-semibold text-maroon-900 shadow-sm hover:bg-maroon-950/5">
                                    View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($loas->hasPages())
            <div class="mt-4 px-1">{{ $loas->links() }}</div>
        @endif
    @endif
@endsection
