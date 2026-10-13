@extends('layouts.app')

@section('title', 'Check LOA Status')

@section('content')
<x-page-shell eyebrow="LOA Status Check" title="Check your application status" width="2xl">
    <x-slot:lead>
        Enter your control number and student ID number to view your application status.
        Both must match to protect your information.
    </x-slot:lead>

    {{-- Search form --}}
    <form method="POST" action="{{ route('student.status') }}"
          class="rounded-2xl border border-maroon-900/10 bg-white p-6 sm:p-8 space-y-4">
        @csrf
        <div>
            <label for="control_number" class="block text-sm font-medium text-maroon-900">Control Number</label>
            <input id="control_number" name="control_number" type="text"
                   value="{{ old('control_number') }}"
                   required autofocus autocomplete="off"
                   placeholder="e.g. EVSU-OC-LOA-2026-0001"
                   class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 font-mono text-base outline-none focus:ring-2 focus:ring-maroon-700 @error('control_number') border-red-400 bg-red-50 @enderror">
            @error('control_number')
                <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="student_id" class="block text-sm font-medium text-maroon-900">Student ID Number</label>
            <input id="student_id" name="student_id" type="text"
                   value="{{ old('student_id') }}"
                   required autocomplete="off"
                   inputmode="numeric"
                   placeholder="e.g. 2021-12345"
                   data-student-id-input
                   class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 font-mono text-base outline-none focus:ring-2 focus:ring-maroon-700 @error('student_id') border-red-400 bg-red-50 @enderror">
            <p class="mt-1 text-xs text-maroon-800/60">The student ID you entered when submitting your LOA request.</p>
            @error('student_id')
                <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit"
                class="w-full rounded-xl bg-maroon-800 px-4 py-3 text-base font-semibold text-cream-50 hover:bg-maroon-700">
            Check Status
        </button>
    </form>

    {{-- Not found --}}
    @if ($notFound)
        <div class="mt-5 rounded-2xl border border-red-200 bg-red-50 p-5">
            <p class="text-sm font-semibold text-red-700">No application found.</p>
            <p class="mt-1 text-sm text-red-700/80">
                Make sure you entered the control number and student ID exactly as they appear in your confirmation email.
                The control number looks like <span class="font-mono font-semibold">EVSU-OC-LOA-2026-0001</span>.
            </p>
        </div>
    @endif

    {{-- Result --}}
    @if ($loa)
        @php
            $stageName  = $loa->determineCurrentStage();
            $stageStatus = $loa->currentStageStatus();

            $stages = $loa->timelineStages();
        @endphp

        <div class="mt-5 space-y-5">

            {{-- Status card --}}
            <div class="rounded-2xl border border-maroon-900/10 bg-white p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-maroon-700">Control Number</p>
                        <p class="mt-0.5 font-mono text-xl font-bold text-maroon-950">{{ $loa->control_number }}</p>
                        @if ($loa->isExtension())
                            <p class="mt-1 text-xs font-semibold text-indigo-800">Leave extension{{ $loa->parentLoa?->control_number ? ' of '.$loa->parentLoa->control_number : '' }}</p>
                        @endif
                    </div>
                    @if ($loa->isOverdue())
                        <span class="rounded-full border border-red-600 bg-red-600 px-3 py-1 text-sm font-semibold text-white">Overdue return</span>
                    @elseif ($loa->returned_at)
                        <span class="rounded-full border border-emerald-600 bg-emerald-50 px-3 py-1 text-sm font-semibold text-emerald-800">Returned</span>
                    @elseif ($stageName === 'done' || $loa->status === 'approved')
                        <span class="rounded-full border border-emerald-600 bg-emerald-600 px-3 py-1 text-sm font-semibold text-white">✓ Fully Approved</span>
                    @elseif ($loa->status === 'rejected')
                        <span class="rounded-full border border-red-600 bg-red-600 px-3 py-1 text-sm font-semibold text-white">✕ Not Approved</span>
                    @elseif ($loa->status === 'discontinued')
                        <span class="rounded-full border border-gray-500 bg-gray-500 px-3 py-1 text-sm font-semibold text-white">Discontinued / Withdrawn</span>
                    @elseif ($stageStatus)
                        <span class="{{ $stageStatus->badgeClasses() }}">{{ $loa->currentStageLabel() }}</span>
                    @endif
                </div>

                <dl class="mt-4 grid grid-cols-2 gap-x-6 gap-y-2.5 text-sm sm:grid-cols-3 border-t border-maroon-900/8 pt-4">
                    <div>
                        <dt class="text-xs text-maroon-900/50">Student</dt>
                        <dd class="mt-0.5 font-medium text-maroon-950">{{ $loa->full_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-maroon-900/50">Department</dt>
                        <dd class="mt-0.5 text-maroon-950">{{ $loa->department?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-maroon-900/50">Program</dt>
                        <dd class="mt-0.5 text-maroon-950">{{ $loa->program?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-maroon-900/50">Semester</dt>
                        <dd class="mt-0.5 text-maroon-950">{{ $loa->academicTermLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-maroon-900/50">Leave period</dt>
                        <dd class="mt-0.5 text-maroon-950">
                            {{ $loa->start_date?->format('M j, Y') }} → {{ $loa->return_date?->format('M j, Y') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-maroon-900/50">Submitted</dt>
                        <dd class="mt-0.5 text-maroon-950">{{ $loa->submitted_at?->format('M j, Y') ?? '—' }}</dd>
                    </div>
                    @if ($loa->resubmit_count > 0)
                    <div>
                        <dt class="text-xs text-maroon-900/50">Revision</dt>
                        <dd class="mt-0.5">
                            <span class="rounded-full border border-amber-500 bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">
                                Resubmission #{{ $loa->resubmit_count }}
                            </span>
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- Approval pipeline --}}
            <div class="rounded-2xl border border-maroon-900/10 bg-white p-6">
                <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-maroon-600">Approval Progress</h3>
                <ol class="relative space-y-0 border-l-2 border-maroon-900/10 pl-6">
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
                            $dotIcon = match(true) {
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
                                @if ($isDone)
                                    <p class="text-xs text-maroon-900/60 mt-0.5">
                                        Approved{{ $stage['actor'] ? ' by ' . $stage['actor']->name : '' }}
                                        @if ($stage['at']) · {{ $stage['at']->format('M j, Y') }} @endif
                                    </p>
                                @elseif ($isRejected)
                                    <p class="text-xs text-red-600 mt-0.5">Not approved at this stage</p>
                                @elseif ($isPending)
                                    <p class="text-xs font-medium text-amber-700 mt-0.5">Currently under review</p>
                                @else
                                    <p class="text-xs text-maroon-900/40 mt-0.5">Waiting for prior stage</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>

                @if ($loa->isOverdue())
                    <div class="mt-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3">
                        <p class="text-sm font-semibold text-red-800">Expected return date has passed</p>
                        <p class="text-xs text-red-800/80 mt-1">
                            You were expected back on {{ $loa->return_date?->format('F j, Y') }}.
                            This is not an enrollment record. Contact SASO or the Registrar if you have returned or need an extension.
                        </p>
                    </div>
                @elseif ($loa->returned_at)
                    <div class="mt-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-center">
                        <p class="text-sm font-semibold text-emerald-700">Marked as returned</p>
                        <p class="text-xs text-emerald-600 mt-0.5">Recorded in LeaveFlow on {{ $loa->returned_at->format('F j, Y') }}.</p>
                    </div>
                @elseif ($stageName === 'done' || $loa->status === 'approved')
                    <div class="mt-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-center">
                        <p class="text-sm font-semibold text-emerald-700">✓ Fully Approved</p>
                        <p class="text-xs text-emerald-600 mt-0.5">All approval stages completed. Please proceed to the Registrar's Office.</p>
                    </div>
                @elseif ($loa->status === 'rejected')
                    <div class="mt-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3">
                        <p class="text-sm font-semibold text-red-700">Application not approved</p>
                        @if ($loa->rejection_reason)
                            <p class="text-xs text-red-700/80 mt-1">Reason: {{ $loa->rejection_reason }}</p>
                        @endif
                        <p class="text-xs text-red-700/70 mt-1">Check your email for a resubmission link if you wish to address the concerns raised.</p>
                    </div>
                @elseif ($loa->status === 'discontinued')
                    <div class="mt-4 rounded-lg bg-gray-100 border border-gray-300 px-4 py-3">
                        <p class="text-sm font-semibold text-gray-800">Application Discontinued / Withdrawn</p>
                        @if ($loa->discontinuation_reason)
                            <p class="text-xs text-gray-700 mt-1">Reason: {{ $loa->discontinuation_reason }}</p>
                        @endif
                        <p class="text-xs text-gray-600 mt-1">If you have questions or believe this was done in error, please contact the SASO Office or your Department Head.</p>
                    </div>
                @endif
            </div>

        </div>
    @endif

</x-page-shell>
@endsection
