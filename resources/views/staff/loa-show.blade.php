@extends('layouts.staff')

@section('eyebrow', $loa->control_number)
@section('title', $loa->full_name)

@section('content')

    @if ($loa->isOverdue())
        <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-5 py-4">
            <p class="text-sm font-semibold text-red-800">Expected return date has passed</p>
            <p class="mt-1 text-sm text-red-800/80">
                This student was expected back on {{ $loa->return_date?->format('F j, Y') }}
                ({{ $loa->daysOverdue() }} day{{ $loa->daysOverdue() === 1 ? '' : 's' }} overdue),
                around <strong>{{ $loa->expectedReturnTermLabel() }}</strong>.
                LeaveFlow does not see enrollment. Mark <strong>returned</strong> if they came back, or <strong>did not enroll</strong> if your office confirmed they did not enroll that term.
            </p>
        </div>
    @elseif ($loa->didNotEnroll())
        <div class="mb-4 rounded-2xl border border-orange-200 bg-orange-50 px-5 py-4">
            <p class="text-sm font-semibold text-orange-950">Did not enroll</p>
            <p class="mt-1 text-sm text-orange-900/80">
                Recorded as not enrolled for <strong>{{ $loa->didNotEnrollTermLabel() }}</strong>
                on {{ $loa->did_not_enroll_at?->format('F j, Y g:i A') }}
                @if ($loa->didNotEnrollByActor)
                    by {{ $loa->didNotEnrollByActor->name }}
                @endif
                . This is a LeaveFlow note after your office checked enrollment — not an automatic SIS lookup.
            </p>
        </div>
    @elseif ($loa->returned_at)
        <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4">
            <p class="text-sm font-semibold text-emerald-800">Marked as returned</p>
            <p class="mt-1 text-sm text-emerald-800/80">
                Recorded {{ $loa->returned_at->format('F j, Y g:i A') }}
                @if ($loa->returnedByActor)
                    by {{ $loa->returnedByActor->name }}
                @endif
                . This is a LeaveFlow note, not an SIS enrollment record.
            </p>
        </div>
    @endif

    {{-- ── Top meta bar ─────────────────────────────────────────────────────── --}}
    <div class="mb-6 flex flex-wrap items-center gap-3 rounded-2xl border border-maroon-900/10 bg-white/70 px-5 py-3 shadow-sm">
        <span class="text-sm text-maroon-900/70">
            Viewing as <strong class="text-maroon-950">{{ auth()->user()->name }}</strong>
            · {{ auth()->user()->role->label() }}
            @if (auth()->user()->department_id)
                · {{ auth()->user()->department->name ?? '' }}
            @endif
        </span>
        <span class="text-maroon-900/20">|</span>
        <span class="text-sm text-maroon-900/70">{{ $loa->department?->name }} · {{ $loa->program?->name }}</span>
        <x-loa-status-badge :loa="$loa" />
        @php
            $stageStatus = $loa->currentStageStatus();
            $stageName   = $loa->determineCurrentStage();
        @endphp
        @if ($stageStatus)
            <span class="{{ $stageStatus->badgeClasses() }}">{{ $loa->currentStageLabel() }}</span>
        @elseif ($stageName === 'done')
            <span class="rounded-full border border-emerald-600 bg-emerald-600 px-3 py-1 text-xs font-medium text-white">Fully Approved</span>
        @elseif ($stageName === 'rejected')
            <span class="rounded-full border border-red-600 bg-red-600 px-3 py-1 text-xs font-medium text-white">Rejected</span>
        @endif
        @if ($loa->isOverdue())
            <span class="rounded-full border border-red-600 bg-red-600 px-3 py-1 text-xs font-medium text-white">Overdue return</span>
        @elseif ($loa->didNotEnroll())
            <span class="rounded-full border border-orange-500 bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-900">Did not enroll</span>
        @elseif ($loa->returned_at)
            <span class="rounded-full border border-emerald-600 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800">Returned</span>
        @endif
        @if ($loa->isExtension())
            <span class="rounded-full border border-indigo-400 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-800">
                Extension{{ $loa->parentLoa?->control_number ? ' of '.$loa->parentLoa->control_number : '' }}
            </span>
        @endif
        @if ($loa->resubmit_count > 0)
            <span class="rounded-full border border-amber-500 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                Resubmission #{{ $loa->resubmit_count }}
            </span>
        @endif
        @if ($loa->submitted_at)
            <span class="ml-auto text-xs text-maroon-800/50">Submitted {{ $loa->submitted_at->format('M j, Y g:i A') }}</span>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- ── Left column ──────────────────────────────────────────────────── --}}
        <div class="space-y-4 lg:col-span-2">

            {{-- Student details — always visible, compact grid --}}
            <div class="rounded-2xl border border-maroon-900/10 bg-white p-5 shadow-sm">
                <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-maroon-600">Student Details</h2>
                <div class="grid grid-cols-2 gap-x-6 gap-y-2.5 text-sm sm:grid-cols-3">
                    <div>
                        <p class="text-xs text-maroon-900/50">Student ID</p>
                        <p class="mt-0.5 font-mono font-semibold text-maroon-950">{{ $loa->student_id ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-maroon-900/50">First name</p>
                        <p class="mt-0.5 text-maroon-950">{{ $loa->first_name ?: $loa->full_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-maroon-900/50">Middle name</p>
                        <p class="mt-0.5 text-maroon-950">{{ $loa->middle_name ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-maroon-900/50">Last name</p>
                        <p class="mt-0.5 text-maroon-950">{{ $loa->last_name ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-maroon-900/50">Email</p>
                        <p class="mt-0.5 text-maroon-950 truncate">{{ $loa->email }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-maroon-900/50">Year Level</p>
                        <p class="mt-0.5 text-maroon-950">{{ $loa->year_level ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-maroon-900/50">Semester</p>
                        <p class="mt-0.5 text-maroon-950">{{ $loa->academicTermLabel() }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-maroon-900/50">Program</p>
                        <p class="mt-0.5 text-maroon-950">{{ $loa->program?->name ?? '—' }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-xs text-maroon-900/50">Leave Period</p>
                        <p class="mt-0.5 font-semibold text-maroon-950">
                            {{ $loa->start_date?->format('M j, Y') ?? '—' }}
                            <span class="mx-1 text-maroon-900/40">→</span>
                            {{ $loa->return_date?->format('M j, Y') ?? '—' }}
                            @if ($loa->start_date && $loa->return_date)
                                <span class="ml-1 text-xs font-normal text-maroon-900/50">({{ $loa->start_date->diffInDays($loa->return_date) }} days)</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-span-2 sm:col-span-3">
                        <p class="text-xs text-maroon-900/50">Parent / Guardian</p>
                        <p class="mt-0.5 text-maroon-950 text-sm">
                            <span class="font-semibold">{{ $loa->parent_full_name }}</span>
                            <span class="text-maroon-900/50"> ({{ $loa->parent_relationship }})</span>
                            <span class="mx-1 text-maroon-900/30">·</span>
                            {{ $loa->parent_phone }}
                        </p>
                    </div>
                </div>

                {{-- Reason — collapsible --}}
                <details class="mt-3 border-t border-maroon-900/5 pt-3 group">
                    <summary class="flex cursor-pointer items-center justify-between list-none">
                        <p class="text-xs font-semibold uppercase tracking-wider text-maroon-900/50">Reason for Leave</p>
                        <span class="text-xs text-maroon-900/40 group-open:hidden">Show ▾</span>
                        <span class="text-xs text-maroon-900/40 hidden group-open:inline">Hide ▴</span>
                    </summary>
                    <p class="mt-2 text-sm leading-relaxed text-maroon-800/85 whitespace-pre-wrap">{{ $loa->reason }}</p>
                </details>
            </div>

            {{-- Supporting Documents — collapsible --}}
            <div class="rounded-2xl border border-maroon-900/10 bg-white shadow-sm overflow-hidden">
                <details class="group">
                    <summary class="flex cursor-pointer items-center justify-between px-5 py-3.5 list-none hover:bg-maroon-950/[0.02]">
                        <div class="flex items-center gap-2">
                            <p class="text-xs font-semibold uppercase tracking-wider text-maroon-600">Supporting Documents</p>
                            <span class="rounded-full bg-maroon-100 px-2 py-0.5 text-xs font-bold text-maroon-700">{{ $loa->attachments->count() }}</span>
                        </div>
                        <span class="text-xs text-maroon-900/40 group-open:hidden">Show ▾</span>
                        <span class="text-xs text-maroon-900/40 hidden group-open:inline">Hide ▴</span>
                    </summary>
                    <div class="border-t border-maroon-900/8 px-5 py-4 space-y-2">
                        @forelse ($loa->attachments as $file)
                            @php
                                $ext  = strtolower(pathinfo($file->original_name, PATHINFO_EXTENSION));
                                $icon = match(true) {
                                    in_array($ext, ['jpg','jpeg','png','webp','gif']) => '🖼',
                                    $ext === 'pdf'                                    => '📋',
                                    in_array($ext, ['doc','docx'])                   => '📝',
                                    default                                           => '📄',
                                };
                            @endphp
                            <a href="{{ route('staff.loa.attachment', [$loa, $file]) }}"
                               target="_blank" rel="noopener"
                               class="group/file flex items-center gap-3 rounded-xl border border-maroon-900/10 bg-maroon-950/[0.02] px-4 py-2.5 transition hover:border-maroon-900/20 hover:bg-maroon-50">
                                <span class="text-lg leading-none">{{ $icon }}</span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-maroon-950 group-hover/file:text-maroon-700">{{ $file->original_name }}</p>
                                    <p class="text-xs text-maroon-900/40">{{ $file->category?->label() ? $file->category->label().' · ' : '' }}{{ number_format($file->size / 1024, 1) }} KB · {{ strtoupper($ext) }}</p>
                                </div>
                                <span class="shrink-0 text-xs font-semibold text-maroon-700 opacity-0 group-hover/file:opacity-100 transition">Open ↗</span>
                            </a>
                        @empty
                            <p class="text-sm italic text-maroon-900/50">No files attached to this application.</p>
                        @endforelse
                    </div>
                </details>
            </div>

            {{-- Rejection History — collapsible --}}
            {{-- Discontinuation details --}}
            @if ($loa->status === 'discontinued')
                <div class="rounded-2xl border border-gray-300 bg-gray-50 p-5 shadow-sm">
                    <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-600">Discontinuation Details</h2>
                    @if ($loa->discontinuedByActor)
                        <p class="text-sm text-gray-700">
                            Recorded by <strong>{{ $loa->discontinuedByActor->name }}</strong>
                            ({{ $loa->discontinuedByActor->role->label() }})
                            @if ($loa->discontinued_at)
                                on {{ $loa->discontinued_at->format('F j, Y \a\t g:i A') }}
                            @endif
                        </p>
                    @endif
                    @if ($loa->discontinuation_reason)
                        <div class="mt-3 rounded-lg border border-gray-200 bg-white px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Reason</p>
                            <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ $loa->discontinuation_reason }}</p>
                        </div>
                    @endif
                </div>
            @endif

            @php
                $allRejections = $loa->rejectionHistory;
                $totalRejections = $allRejections->count();
            @endphp
            @if ($totalRejections > 0)
                <div class="rounded-2xl border border-amber-200 bg-amber-50 shadow-sm overflow-hidden">
                    <details class="group" @if($loa->status === 'rejected') open @endif>
                        <summary class="flex cursor-pointer items-center justify-between px-5 py-3.5 list-none hover:bg-amber-100/50">
                            <div class="flex items-center gap-2">
                                <p class="text-xs font-semibold uppercase tracking-wider text-amber-700">Rejection History</p>
                                <span class="rounded-full bg-amber-200 px-2 py-0.5 text-xs font-bold text-amber-800">{{ $totalRejections }}</span>
                            </div>
                            <span class="text-xs text-amber-600 group-open:hidden">Show ▾</span>
                            <span class="text-xs text-amber-600 hidden group-open:inline">Hide ▴</span>
                        </summary>

                        <div class="border-t border-amber-200 px-5 py-4 space-y-3">
                            @foreach ($allRejections as $entry)
                                @php
                                    $cycleLabel = $entry->resubmit_cycle === 0
                                        ? 'Original submission'
                                        : 'Resubmission #' . $entry->resubmit_cycle;
                                    $isLatest = $loop->last && $loa->status === 'rejected';
                                @endphp
                                <div class="rounded-xl border {{ $isLatest ? 'border-red-200' : 'border-amber-200' }} bg-white p-4">
                                    <div class="flex flex-wrap items-start justify-between gap-2 mb-1.5">
                                        <div>
                                            <span class="text-xs font-semibold uppercase tracking-wider {{ $isLatest ? 'text-red-600' : 'text-amber-600' }}">{{ $cycleLabel }}</span>
                                            <p class="text-sm font-semibold text-maroon-950 mt-0.5">
                                                Rejected at <strong>{{ $entry->stage_label }}</strong>
                                            </p>
                                        </div>
                                        <span class="text-xs text-maroon-900/50 shrink-0">{{ $entry->rejected_at->format('M j, Y · g:i A') }}</span>
                                    </div>
                                    @if ($entry->rejectedByActor)
                                        <p class="text-xs text-maroon-900/60 mb-2">
                                            By <strong>{{ $entry->rejectedByActor->name }}</strong> ({{ $entry->rejectedByActor->role->label() }})
                                        </p>
                                    @endif
                                    @if ($entry->reason)
                                        <p class="text-sm whitespace-pre-wrap rounded-lg {{ $isLatest ? 'bg-red-50 border border-red-100 text-red-800' : 'bg-amber-50 border border-amber-100 text-amber-900' }} px-3 py-2">{{ $entry->reason }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </details>
                </div>
            @endif

        </div>

        {{-- ── Right column: approval timeline + actions ────────────────────── --}}
        <div class="space-y-4">

            {{-- Approval timeline --}}
            <div class="rounded-2xl border border-maroon-900/10 bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-xs font-semibold uppercase tracking-wider text-maroon-600">Approval Timeline</h2>

                @php
                    $stages = $loa->timelineStages();
                @endphp

                <ol class="relative space-y-0 border-l-2 border-maroon-900/10 pl-5">
                    @foreach ($stages as $i => $stage)
                        @php
                            $s          = $stage['status'];
                            $isDone     = $s?->value === 'approved';
                            $isRejected = $s?->value === 'rejected';
                            $isPending  = $s?->value === 'pending';
                            $dotColor   = match(true) {
                                $isDone     => 'bg-emerald-500 ring-emerald-200',
                                $isRejected => 'bg-red-500 ring-red-200',
                                $isPending  => 'bg-amber-400 ring-amber-100',
                                default     => 'bg-maroon-900/15 ring-maroon-900/5',
                            };
                            $dotIcon    = match(true) {
                                $isDone     => '✓',
                                $isRejected => '✕',
                                $isPending  => '…',
                                default     => (string)($i + 1),
                            };
                        @endphp
                        <li class="relative pb-5 last:pb-0">
                            <span class="absolute -left-[1.45rem] flex h-6 w-6 items-center justify-center rounded-full ring-4 text-[10px] font-bold
                                         {{ $dotColor }} {{ $isDone || $isRejected ? 'text-white' : ($isPending ? 'text-amber-900' : 'text-maroon-900/40') }}">
                                {{ $dotIcon }}
                            </span>
                            <div class="ml-1">
                                <p class="text-sm font-semibold text-maroon-950">{{ $stage['label'] }}</p>
                                @if ($isDone || $isRejected)
                                    @if ($stage['actor'])
                                        <p class="text-xs text-maroon-900/60 mt-0.5">
                                            {{ $isDone ? 'Approved' : 'Rejected' }} by {{ $stage['actor']->name }}
                                        </p>
                                    @endif
                                    @if ($stage['at'])
                                        <p class="text-xs text-maroon-900/40 mt-0.5">{{ $stage['at']->format('M j, Y · g:i A') }}</p>
                                    @endif
                                @elseif ($isPending)
                                    <p class="mt-0.5 text-xs font-medium text-amber-700">Awaiting action</p>
                                @else
                                    <p class="mt-0.5 text-xs text-maroon-900/40">Locked</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>

                @if ($stageName === 'done')
                    <div class="mt-4 rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-2.5 text-center">
                        <p class="text-sm font-semibold text-emerald-700">✓ Fully Approved</p>
                    </div>
                @elseif ($loa->status === 'rejected')
                    <div class="mt-4 rounded-lg bg-red-50 border border-red-200 px-3 py-2.5 text-center">
                        <p class="text-sm font-semibold text-red-700">✕ Rejected</p>
                    </div>
                @endif
            </div>

            {{-- Status flash --}}
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Action buttons --}}
            @canany(['approve', 'reject'], $loa)
                <div class="rounded-2xl border border-maroon-900/10 bg-white p-4 shadow-sm space-y-2.5">
                    <p class="text-xs text-maroon-900/60">
                        Acting as <strong>{{ auth()->user()->role->label() }}</strong> · <strong>{{ $loa->currentStageLabel() }}</strong>
                    </p>
                    @can('approve', $loa)
                        <form method="POST" action="{{ route('staff.loa.approve', $loa) }}">
                            @csrf
                            <button type="submit"
                                    class="w-full rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">
                                ✓ Approve LOA
                            </button>
                        </form>
                    @endcan
                    @can('reject', $loa)
                        <button type="button"
                                data-reject-trigger
                                data-control="{{ $loa->control_number }}"
                                data-action="{{ route('staff.loa.reject', $loa) }}"
                                class="w-full rounded-xl border border-red-600 bg-white px-4 py-2.5 text-sm font-semibold text-red-700 shadow-sm hover:bg-red-50">
                            ✕ Reject LOA
                        </button>
                    @endcan
                </div>
            @endcanany

            @can('markReturned', $loa)
                <form method="POST" action="{{ route('staff.loa.returned', $loa) }}" class="m-0" onsubmit="return confirm('Mark this student as returned from leave? This is a LeaveFlow record only.');">
                    @csrf
                    <button type="submit"
                            class="block w-full rounded-xl border border-emerald-600 bg-white px-4 py-2.5 text-center text-sm font-semibold text-emerald-800 shadow-sm hover:bg-emerald-50">
                        Mark student as returned
                    </button>
                </form>
            @endcan

            @can('markDidNotEnroll', $loa)
                <button type="button"
                        data-did-not-enroll-trigger
                        data-control="{{ $loa->control_number }}"
                        data-action="{{ route('staff.loa.did-not-enroll', $loa) }}"
                        class="block w-full rounded-xl border border-orange-400 bg-white px-4 py-2.5 text-center text-sm font-semibold text-orange-900 shadow-sm hover:bg-orange-50">
                    Did not enroll this semester
                </button>
            @endcan

            {{-- Discontinue button (shown separately — available even after approval) --}}
            @can('discontinue', $loa)
                <button type="button"
                        data-discontinue-trigger
                        data-control="{{ $loa->control_number }}"
                        data-action="{{ route('staff.loa.discontinue', $loa) }}"
                        class="block w-full rounded-xl border border-gray-400 bg-white px-4 py-2.5 text-center text-sm font-semibold text-gray-600 shadow-sm hover:bg-gray-50">
                    Mark as Discontinued
                </button>
            @endcan

            {{-- Download + Back --}}
            @if ($loa->status === 'approved')
                <a href="{{ route('staff.loa.pdf', $loa) }}"
                   class="block w-full rounded-xl bg-maroon-800 px-4 py-2.5 text-center text-sm font-semibold text-white shadow-sm hover:bg-maroon-700">
                    ↓ Download Approved PDF
                </a>
            @endif
            <a href="{{ $backUrl }}"
               class="block w-full rounded-xl border border-maroon-900/15 bg-white px-4 py-2.5 text-center text-sm font-semibold text-maroon-700 shadow-sm hover:bg-maroon-50">
                ← Back
            </a>
        </div>
    </div>

    {{-- Reject modal --}}
    <div id="reject-modal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-maroon-950/60 backdrop-blur-sm p-4"
         role="dialog" aria-modal="true" aria-labelledby="reject-modal-title">
        <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl">
            <div class="border-b border-maroon-900/10 px-6 py-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-red-600">Reject Application</p>
                <h3 id="reject-modal-title" class="mt-1 text-lg font-bold text-maroon-950">
                    Reject <span id="reject-control-display" class="font-mono"></span>
                </h3>
            </div>
            <form id="reject-form" method="POST">
                @csrf
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label for="reject-reason" class="block text-sm font-medium text-maroon-900 mb-1">
                            Reason for rejection <span class="text-red-600">*</span>
                        </label>
                        <textarea id="reject-reason" name="reason" rows="4" required
                                  placeholder="Explain why this LOA request is being rejected…"
                                  class="w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-sm outline-none ring-red-500 focus:ring-2 resize-none"></textarea>
                        <p class="mt-1 text-xs text-maroon-900/50">Recorded in the audit trail and shown to relevant staff.</p>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-maroon-900/10 px-6 py-4">
                    <button type="button" id="reject-cancel"
                            class="rounded-lg border border-maroon-900/20 bg-white px-4 py-2 text-sm font-semibold text-maroon-700 hover:bg-maroon-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                        Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Discontinue modal --}}
    <div id="discontinue-modal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-maroon-950/60 backdrop-blur-sm p-4"
         role="dialog" aria-modal="true" aria-labelledby="discontinue-modal-title">
        <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl">
            <div class="border-b border-maroon-900/10 px-6 py-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-600">Discontinue Application</p>
                <h3 id="discontinue-modal-title" class="mt-1 text-lg font-bold text-maroon-950">
                    Discontinue <span id="discontinue-control-display" class="font-mono"></span>
                </h3>
            </div>
            <form id="discontinue-form" method="POST">
                @csrf
                <div class="px-6 py-5 space-y-4">
                    <p class="text-sm text-maroon-900/70">
                        This will mark the LOA as <strong>Discontinued / Withdrawn</strong>.
                        The student will receive an email notification.
                    </p>
                    <div>
                        <label for="discontinue-reason" class="block text-sm font-medium text-maroon-900 mb-1">
                            Reason <span class="text-red-600">*</span>
                        </label>
                        <textarea id="discontinue-reason" name="reason" rows="3" required
                                  placeholder="e.g. Student withdrew request, Student did not depart…"
                                  class="w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-sm outline-none focus:ring-2 focus:ring-gray-500 resize-none"></textarea>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-maroon-900/10 px-6 py-4">
                    <button type="button" id="discontinue-cancel"
                            class="rounded-lg border border-maroon-900/20 bg-white px-4 py-2 text-sm font-semibold text-maroon-700 hover:bg-maroon-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="rounded-lg bg-gray-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500">
                        Confirm Discontinuation
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Did not enroll modal --}}
    <div id="did-not-enroll-modal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-maroon-950/60 backdrop-blur-sm p-4"
         role="dialog" aria-modal="true" aria-labelledby="did-not-enroll-modal-title">
        <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl">
            <div class="border-b border-maroon-900/10 px-6 py-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-orange-800">Did not enroll</p>
                <h3 id="did-not-enroll-modal-title" class="mt-1 text-lg font-bold text-maroon-950">
                    Record <span id="did-not-enroll-control-display" class="font-mono"></span>
                </h3>
            </div>
            <form id="did-not-enroll-form" method="POST">
                @csrf
                <div class="px-6 py-5 space-y-4">
                    <p class="text-sm text-maroon-900/70">
                        Use this after your office confirms the student did <strong>not enroll</strong> in the semester they were due back.
                        Suggested from the return date: <strong>{{ $loa->expectedReturnTermLabel() }}</strong>. This is not an automatic SIS check.
                    </p>
                    <div>
                        <label for="did-not-enroll-year" class="block text-sm font-medium text-maroon-900 mb-1">School year</label>
                        <select id="did-not-enroll-year" name="academic_year" required
                                class="w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-sm outline-none ring-orange-600 focus:ring-2">
                            <option value="">Select school year</option>
                            @foreach ($academicYears as $year)
                                <option value="{{ $year }}" @selected(($expectedReturn['year'] ?? null) === $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="did-not-enroll-semester" class="block text-sm font-medium text-maroon-900 mb-1">Semester</label>
                        <select id="did-not-enroll-semester" name="semester" required
                                class="w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-sm outline-none ring-orange-600 focus:ring-2">
                            <option value="">Select semester</option>
                            @foreach ($semesters as $semester)
                                <option value="{{ $semester->value }}" @selected(($expectedReturn['semester'] ?? null)?->value === $semester->value)>{{ $semester->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-maroon-900/10 px-6 py-4">
                    <button type="button" id="did-not-enroll-cancel"
                            class="rounded-lg border border-maroon-900/20 bg-white px-4 py-2 text-sm font-semibold text-maroon-700 hover:bg-maroon-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="rounded-lg bg-orange-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-600">
                        Confirm did not enroll
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal        = document.getElementById('reject-modal');
    const form         = document.getElementById('reject-form');
    const controlLabel = document.getElementById('reject-control-display');
    const reasonField  = document.getElementById('reject-reason');
    const cancelBtn    = document.getElementById('reject-cancel');

    const open = (control, action) => {
        controlLabel.textContent = control;
        form.action = action;
        reasonField.value = '';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        reasonField.focus();
    };
    const close = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        reasonField.value = '';
    };

    document.querySelectorAll('[data-reject-trigger]').forEach(btn => {
        btn.addEventListener('click', () => open(btn.dataset.control, btn.dataset.action));
    });
    cancelBtn?.addEventListener('click', close);
    modal?.addEventListener('click', e => { if (e.target === modal) close(); });

    // ── Discontinue modal ─────────────────────────────────────────────────────
    const dModal       = document.getElementById('discontinue-modal');
    const dForm        = document.getElementById('discontinue-form');
    const dControlLabel= document.getElementById('discontinue-control-display');
    const dReasonField = document.getElementById('discontinue-reason');
    const dCancelBtn   = document.getElementById('discontinue-cancel');

    const dOpen = (control, action) => {
        dControlLabel.textContent = control;
        dForm.action = action;
        dReasonField.value = '';
        dModal.classList.remove('hidden');
        dModal.classList.add('flex');
        dReasonField.focus();
    };
    const dClose = () => {
        dModal.classList.add('hidden');
        dModal.classList.remove('flex');
        dReasonField.value = '';
    };

    document.querySelectorAll('[data-discontinue-trigger]').forEach(btn => {
        btn.addEventListener('click', () => dOpen(btn.dataset.control, btn.dataset.action));
    });
    dCancelBtn?.addEventListener('click', dClose);
    dModal?.addEventListener('click', e => { if (e.target === dModal) dClose(); });

    const eModal = document.getElementById('did-not-enroll-modal');
    const eForm = document.getElementById('did-not-enroll-form');
    const eControlLabel = document.getElementById('did-not-enroll-control-display');
    const eCancelBtn = document.getElementById('did-not-enroll-cancel');

    const eOpen = (control, action) => {
        if (eControlLabel) eControlLabel.textContent = control;
        if (eForm) eForm.action = action;
        eModal?.classList.remove('hidden');
        eModal?.classList.add('flex');
    };
    const eClose = () => {
        eModal?.classList.add('hidden');
        eModal?.classList.remove('flex');
    };

    document.querySelectorAll('[data-did-not-enroll-trigger]').forEach(btn => {
        btn.addEventListener('click', () => eOpen(btn.dataset.control, btn.dataset.action));
    });
    eCancelBtn?.addEventListener('click', eClose);
    eModal?.addEventListener('click', e => { if (e.target === eModal) eClose(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') { close(); dClose(); eClose(); } });
});
</script>
@endpush