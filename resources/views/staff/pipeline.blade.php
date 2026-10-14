@extends('layouts.staff')

@section('eyebrow', 'Approval Pipeline & Records')
@section('title', 'Approval Pipeline & Records')

@section('content')
    <div class="mb-6 rounded-2xl border border-maroon-900/10 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-maroon-600">Sequentially Enforced Order</p>
        <h2 class="mt-1 text-lg font-semibold text-maroon-950">Sequential Approval Pipeline &amp; Authority Clearances</h2>
        <p class="mt-2 text-sm text-maroon-900/70">
            Regular leave: <span class="font-semibold">Department Head → SASO Officer → Campus Director</span>.
            Extensions: <span class="font-semibold">President</span> first, then the same three campus offices.
            Each stage must be approved before the next office can act. A rejection at any stage terminates the pipeline.
        </p>
    </div>

    {{-- ── Filters ─────────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('staff.pipeline') }}"
          class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-maroon-900/10 bg-white px-5 py-4 shadow-sm">

        {{-- Search --}}
        <div class="flex-1 min-w-[180px]">
            <label for="filter-search" class="block text-xs font-semibold uppercase tracking-wider text-maroon-900/50 mb-1">Search</label>
            <input id="filter-search" type="text" name="search" value="{{ $filterSearch }}"
                   placeholder="Name, Student ID, or Control No."
                   class="w-full rounded-lg border border-maroon-900/15 bg-cream-50 px-3 py-2 text-sm text-maroon-950 placeholder-maroon-900/30 focus:outline-none focus:ring-2 focus:ring-maroon-700">
        </div>

        {{-- Department --}}
        @if ($isDh)
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-maroon-900/50 mb-1">Department</label>
                <p class="rounded-lg border border-maroon-900/15 bg-maroon-50 px-3 py-2 text-sm font-semibold text-maroon-900">
                    {{ auth()->user()->department?->name ?? 'Your Department' }}
                </p>
            </div>
        @else
            <div>
                <label for="filter-dept" class="block text-xs font-semibold uppercase tracking-wider text-maroon-900/50 mb-1">Department</label>
                <select id="filter-dept" name="department_id"
                        class="rounded-lg border border-maroon-900/15 bg-cream-50 px-3 py-2 text-sm text-maroon-950 focus:outline-none focus:ring-2 focus:ring-maroon-700">
                    <option value="">All Departments</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $filterDept == $dept->id ? 'selected' : '' }}>
                            {{ $dept->code }} — {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        {{-- Status --}}
        <div>
            <label for="filter-status" class="block text-xs font-semibold uppercase tracking-wider text-maroon-900/50 mb-1">Status</label>
            <select id="filter-status" name="status"
                    class="rounded-lg border border-maroon-900/15 bg-cream-50 px-3 py-2 text-sm text-maroon-950 focus:outline-none focus:ring-2 focus:ring-maroon-700">
                <option value="">All Statuses</option>
                <option value="submitted"    {{ $filterStatus === 'submitted'    ? 'selected' : '' }}>Under Review</option>
                <option value="approved"     {{ $filterStatus === 'approved'     ? 'selected' : '' }}>Approved</option>
                <option value="rejected"     {{ $filterStatus === 'rejected'     ? 'selected' : '' }}>Rejected</option>
                <option value="discontinued" {{ $filterStatus === 'discontinued' ? 'selected' : '' }}>Discontinued</option>
                <option value="overdue"      {{ $filterStatus === 'overdue'      ? 'selected' : '' }}>Overdue return</option>
                <option value="did_not_enroll" {{ $filterStatus === 'did_not_enroll' ? 'selected' : '' }}>Did not enroll</option>
                <option value="draft"        {{ $filterStatus === 'draft'        ? 'selected' : '' }}>Draft</option>
            </select>
        </div>

        {{-- Apply / Clear --}}
        <div class="flex gap-2">
            <button type="submit"
                    class="rounded-lg bg-maroon-800 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-maroon-700">
                Apply
            </button>
            @if ($filterSearch || $filterStatus || $filterDept)
                <a href="{{ route('staff.pipeline') }}"
                   class="rounded-lg border border-maroon-900/20 bg-white px-4 py-2 text-sm font-semibold text-maroon-700 hover:bg-maroon-50">
                    Clear
                </a>
            @endif
        </div>

        {{-- Active filter tags + result count --}}
        @if ($filterSearch || $filterStatus || $filterDept)
            <div class="flex w-full flex-wrap items-center gap-2 border-t border-maroon-900/8 pt-3">
                <span class="text-xs text-maroon-900/50">Showing {{ $loas->total() }} result{{ $loas->total() !== 1 ? 's' : '' }}:</span>
                @if ($filterSearch)
                    <span class="rounded-full bg-maroon-100 px-3 py-1 text-xs font-semibold text-maroon-800">"{{ $filterSearch }}"</span>
                @endif
                @if ($filterDept && ! $isDh)
                    @php $deptLabel = $departments->firstWhere('id', $filterDept)?->code ?? $filterDept; @endphp
                    <span class="rounded-full bg-maroon-100 px-3 py-1 text-xs font-semibold text-maroon-800">Dept: {{ $deptLabel }}</span>
                @endif
                @if ($filterStatus)
                    @php
                        $statusLabels = [
                            'submitted' => 'Under Review', 'approved' => 'Approved',
                            'rejected' => 'Rejected', 'discontinued' => 'Discontinued', 'overdue' => 'Overdue return', 'did_not_enroll' => 'Did not enroll', 'draft' => 'Draft',
                        ];
                    @endphp
                    <span class="rounded-full bg-maroon-100 px-3 py-1 text-xs font-semibold text-maroon-800">
                        Status: {{ $statusLabels[$filterStatus] ?? ucfirst($filterStatus) }}
                    </span>
                @endif
            </div>
        @endif
    </form>

    @if ($loas->isEmpty())
        <div class="rounded-2xl border border-dashed border-maroon-900/15 bg-white p-12 text-center">
            @if ($filterSearch || $filterStatus || $filterDept)
                <p class="text-sm text-maroon-900/60">No LOA requests match your current filters.</p>
                <a href="{{ route('staff.pipeline') }}" class="mt-3 inline-block text-xs font-semibold text-maroon-700 underline underline-offset-2 hover:text-maroon-600">Clear filters</a>
            @else
                <p class="text-sm text-maroon-900/60">No LOA submissions in your current scope yet.</p>
            @endif
        </div>
    @else
        <div class="overflow-x-auto rounded-2xl border border-maroon-900/10 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-maroon-900/10">
                <thead class="bg-maroon-950/5">
                    <tr>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-4">App ID</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-4">Student ID</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-4">Student Name</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-4">President Status</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-4">Dept Head Status</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-4">SASO Status</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-4">Campus Director Status</th>
                        <th scope="col" class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider text-maroon-900/70 sm:px-4">Action Options</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-maroon-900/10 bg-white">
                    @foreach ($loas as $loa)
                        @php
                            $canApprove = \Illuminate\Support\Facades\Gate::allows('approve', $loa);
                            $canReject  = \Illuminate\Support\Facades\Gate::allows('reject', $loa);

                            $statusBadge = function ($status) {
                                if (! $status) {
                                    return '<span class="inline-flex items-center rounded-full border border-gray-300 bg-gray-50 px-3 py-1 text-xs font-medium text-gray-500">—</span>';
                                }
                                return '<span class="'.e($status->badgeClasses()).'">'.e($status->label()).'</span>';
                            };
                        @endphp
                        <tr class="hover:bg-maroon-950/[0.02]">
                            <td class="whitespace-nowrap px-3 py-3 font-mono text-sm font-bold text-maroon-900 sm:px-4">
                                {{ $loa->control_number }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-3 text-sm text-maroon-900/85 sm:px-4">
                                {{ $loa->student_id ?? '—' }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-3 text-sm font-medium text-maroon-950 sm:px-4">
                                {{ $loa->full_name }}
                                @if ($loa->program || $loa->department)
                                    <div class="text-xs text-maroon-900/55">
                                        {{ optional($loa->program)->code ?? '—' }} · {{ optional($loa->department)->name ?? '—' }}
                                    </div>
                                @endif
                                @if ($loa->isOverdue())
                                    <div class="mt-0.5">
                                        <span class="rounded-full border border-red-500 bg-red-50 px-2 py-0.5 text-[11px] font-semibold text-red-800">
                                            Overdue return
                                        </span>
                                    </div>
                                @elseif ($loa->didNotEnroll())
                                    <div class="mt-0.5">
                                        <span class="rounded-full border border-orange-400 bg-orange-50 px-2 py-0.5 text-[11px] font-semibold text-orange-900">
                                            Did not enroll
                                        </span>
                                    </div>
                                @elseif ($loa->returned_at)
                                    <div class="mt-0.5">
                                        <span class="rounded-full border border-emerald-400 bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-800">
                                            Returned
                                        </span>
                                    </div>
                                @endif
                                @if ($loa->isExtension())
                                    <div class="mt-0.5">
                                        <span class="rounded-full border border-indigo-300 bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-800">
                                            Extension
                                        </span>
                                    </div>
                                @endif
                                @if ($loa->resubmit_count > 0)
                                    <div class="mt-0.5">
                                        <span class="rounded-full border border-amber-400 bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700">
                                            Resubmission #{{ $loa->resubmit_count }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-3 text-sm sm:px-4">{!! $statusBadge($loa->isExtension() ? $loa->president_status : null) !!}</td>
                            <td class="whitespace-nowrap px-3 py-3 text-sm sm:px-4">{!! $statusBadge($loa->dept_head_status) !!}</td>
                            <td class="whitespace-nowrap px-3 py-3 text-sm sm:px-4">{!! $statusBadge($loa->saso_status) !!}</td>
                            <td class="whitespace-nowrap px-3 py-3 text-sm sm:px-4">{!! $statusBadge($loa->campus_director_status) !!}</td>
                            <td class="whitespace-nowrap px-3 py-3 text-right text-sm sm:px-4">
                                <div class="inline-flex flex-wrap items-center justify-end gap-2">
                                    <a href="{{ route('staff.loa.show', $loa) }}"
                                       class="inline-flex items-center rounded-md border border-maroon-900/20 bg-white px-3 py-1.5 text-xs font-semibold text-maroon-900 shadow-sm hover:bg-maroon-950/5">
                                        View
                                    </a>

                                    @if ($canApprove)
                                        <form method="POST" action="{{ route('staff.loa.approve', $loa) }}" class="m-0 inline-flex">
                                            @csrf
                                            {{-- Preserve active filters so redirect lands back on same filtered view --}}
                                            @if ($filterSearch)  <input type="hidden" name="search"        value="{{ $filterSearch }}"> @endif
                                            @if ($filterStatus)  <input type="hidden" name="status"        value="{{ $filterStatus }}"> @endif
                                            @if ($filterDept)    <input type="hidden" name="department_id" value="{{ $filterDept }}">   @endif
                                            <button type="submit"
                                                    class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-500">
                                                Approve LOA
                                            </button>
                                        </form>
                                    @endif

                                    @if ($canReject)
                                        <button type="button"
                                                data-reject-trigger
                                                data-control="{{ $loa->control_number }}"
                                                data-action="{{ route('staff.loa.reject', $loa) }}"
                                                data-filter-search="{{ $filterSearch }}"
                                                data-filter-status="{{ $filterStatus }}"
                                                data-filter-dept="{{ $filterDept }}"
                                                class="inline-flex items-center rounded-md border border-red-600 bg-white px-3 py-1.5 text-xs font-semibold text-red-700 shadow-sm hover:bg-red-50">
                                            Reject LOA
                                        </button>
                                    @endif

                                </div>
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
                        <p class="mt-1 text-xs text-maroon-900/50">This reason will be recorded in the audit trail and shown to relevant staff.</p>
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal        = document.getElementById('reject-modal');
    const form         = document.getElementById('reject-form');
    const controlLabel = document.getElementById('reject-control-display');
    const reasonField  = document.getElementById('reject-reason');
    const cancelBtn    = document.getElementById('reject-cancel');

    const open = (control, action, filterSearch, filterStatus, filterDept) => {
        controlLabel.textContent = control;
        form.action = action;
        reasonField.value = '';

        // Inject filter params as hidden inputs so the redirect preserves them
        form.querySelectorAll('.filter-param').forEach(el => el.remove());
        const addHidden = (name, value) => {
            if (! value) return;
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            input.className = 'filter-param';
            form.appendChild(input);
        };
        addHidden('search', filterSearch);
        addHidden('status', filterStatus);
        addHidden('department_id', filterDept);

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
        btn.addEventListener('click', () => open(
            btn.dataset.control,
            btn.dataset.action,
            btn.dataset.filterSearch || '',
            btn.dataset.filterStatus || '',
            btn.dataset.filterDept   || '',
        ));
    });

    cancelBtn.addEventListener('click', close);

    modal.addEventListener('click', e => {
        if (e.target === modal) close();
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') close();
    });
});
</script>
@endpush
