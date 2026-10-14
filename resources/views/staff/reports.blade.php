@extends('layouts.staff')

@section('eyebrow', 'Reports & Analytics')
@section('title', 'Reports & Analytics')

@section('content')

    {{-- ── Filters ─────────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('staff.reports') }}"
          class="mb-6 flex flex-wrap items-end gap-4 rounded-2xl border border-maroon-900/10 bg-white px-5 py-4 shadow-sm">

        {{-- Year --}}
        <div>
            <label for="filter-year" class="block text-xs font-semibold uppercase tracking-wider text-maroon-900/50 mb-1">Year</label>
            <select id="filter-year" name="year"
                    class="rounded-lg border border-maroon-900/15 bg-cream-50 px-3 py-2 text-sm text-maroon-950 focus:outline-none focus:ring-2 focus:ring-maroon-700">
                @foreach ($years as $yr)
                    <option value="{{ $yr }}" {{ $filterYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                @endforeach
            </select>
        </div>

        {{-- Department (hidden/fixed for Dept Head) --}}
        @if ($isDh)
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-maroon-900/50 mb-1">Department</label>
                <p class="rounded-lg border border-maroon-900/15 bg-maroon-50 px-3 py-2 text-sm font-semibold text-maroon-900">
                    {{ auth()->user()->department?->name ?? 'Your Department' }}
                </p>
                <input type="hidden" name="department_id" value="{{ $filterDept }}">
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
                <option value="draft"        {{ $filterStatus === 'draft'        ? 'selected' : '' }}>Draft</option>
            </select>
        </div>

        {{-- Apply / Clear --}}
        <div class="flex gap-2">
            <button type="submit"
                    class="rounded-lg bg-maroon-800 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-maroon-700">
                Apply
            </button>
            @if ($filterDept || $filterStatus || $filterYear != now()->year)
                <a href="{{ route('staff.reports') }}"
                   class="rounded-lg border border-maroon-900/20 bg-white px-4 py-2 text-sm font-semibold text-maroon-700 hover:bg-maroon-50">
                    Clear
                </a>
            @endif
        </div>

        {{-- Active filter tags --}}
        @if ($filterDept || $filterStatus || $filterYear != now()->year)
            <div class="flex w-full flex-wrap gap-2 border-t border-maroon-900/8 pt-3">
                <span class="text-xs text-maroon-900/50 self-center">Filtered by:</span>
                <span class="rounded-full bg-maroon-100 px-3 py-1 text-xs font-semibold text-maroon-800">
                    Year: {{ $filterYear }}
                </span>
                @if ($filterDept && ! $isDh)
                    @php $deptName = $departments->firstWhere('id', $filterDept)?->code ?? $filterDept; @endphp
                    <span class="rounded-full bg-maroon-100 px-3 py-1 text-xs font-semibold text-maroon-800">
                        Dept: {{ $deptName }}
                    </span>
                @endif
                @if ($filterStatus)
                    @php
                        $statusLabels = [
                            'submitted' => 'Under Review', 'approved' => 'Approved',
                            'rejected' => 'Rejected', 'discontinued' => 'Discontinued', 'draft' => 'Draft',
                        ];
                    @endphp
                    <span class="rounded-full bg-maroon-100 px-3 py-1 text-xs font-semibold text-maroon-800">
                        Status: {{ $statusLabels[$filterStatus] ?? ucfirst($filterStatus) }}
                    </span>
                @endif
            </div>
        @endif
    </form>

    {{-- ── Summary stat cards ──────────────────────────────────────────────── --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-2xl border border-maroon-900/10 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-maroon-900/60">LOA Count</p>
            <p class="mt-2 text-4xl font-bold tracking-tight text-maroon-950">{{ $totalCount }}</p>
            <p class="mt-1 text-xs text-maroon-900/50">Total in scope</p>
        </div>
        <div class="rounded-2xl border border-maroon-900/10 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-maroon-900/60">Approval Rate</p>
            <p class="mt-2 text-4xl font-bold tracking-tight text-emerald-700">{{ $approvalRate }}<span class="text-xl">%</span></p>
            <p class="mt-1 text-xs text-maroon-900/50">{{ $approvedCount }} approved / {{ $totalCount }} total</p>
        </div>
        <div class="rounded-2xl border border-maroon-900/10 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-maroon-900/60">Processing Time</p>
            <p class="mt-2 text-4xl font-bold tracking-tight text-maroon-950">
                {{ $avgProcessingDays }}<span class="text-xl font-medium text-maroon-900/60"> days</span>
            </p>
            <p class="mt-1 text-xs text-maroon-900/50">Avg submission to final approval</p>
        </div>
        <div class="rounded-2xl border border-maroon-900/10 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-maroon-900/60">Highest Program</p>
            @if ($highestProgram)
                <p class="mt-2 text-2xl font-bold tracking-tight text-maroon-950 truncate" title="{{ $highestProgram['name'] }}">
                    {{ $highestProgram['code'] }}
                </p>
                <p class="mt-1 text-xs text-maroon-900/50">{{ $highestProgram['count'] }} submissions</p>
            @else
                <p class="mt-2 text-2xl font-bold tracking-tight text-maroon-900/30">—</p>
                <p class="mt-1 text-xs text-maroon-900/50">No data yet</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- ── LOA Volume by Department ────────────────────────────────────── --}}
        <div class="rounded-2xl border border-maroon-900/10 bg-white p-6 shadow-sm">
            <h3 class="mb-5 text-sm font-semibold text-maroon-950">Volume by Department</h3>
            @if ($deptRows->sum('count') === 0)
                <p class="py-8 text-center text-sm text-maroon-900/40">No submissions yet.</p>
            @else
                <div class="space-y-3">
                    @foreach ($deptRows as $dept)
                        @php $pct = $maxDeptCount > 0 ? round(($dept['count'] / $maxDeptCount) * 100) : 0; @endphp
                        <div>
                            <div class="mb-1 flex items-center justify-between text-xs">
                                <span class="font-semibold text-maroon-950">{{ $dept['code'] }}</span>
                                <span class="text-maroon-900/60">{{ $dept['count'] }}</span>
                            </div>
                            <div class="h-3 w-full overflow-hidden rounded-full bg-maroon-900/8">
                                <div class="h-full rounded-full bg-maroon-700 transition-all duration-500"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                            <p class="mt-0.5 text-[11px] text-maroon-900/40 truncate">{{ $dept['name'] }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ── Monthly submission count (12 months of selected year) ─────────── --}}
        <div class="rounded-2xl border border-maroon-900/10 bg-white p-6 shadow-sm">
            <h3 class="mb-1 text-sm font-semibold text-maroon-950">Monthly Submissions</h3>
            <p class="mb-4 text-xs text-maroon-900/50">{{ $filterYear }}</p>
            @if ($months->sum('count') === 0)
                <p class="py-8 text-center text-sm text-maroon-900/40">No submissions in {{ $filterYear }}.</p>
            @else
                <div class="flex items-end gap-1.5 h-40 border-b border-l border-maroon-900/10 px-2 pt-2">
                    @foreach ($months as $month)
                        @php $h = $maxMonthCount > 0 ? max(4, round(($month['count'] / $maxMonthCount) * 100)) : 4; @endphp
                        <div class="flex flex-1 flex-col items-center gap-1">
                            <span class="text-[10px] font-semibold text-maroon-900/60">
                                {{ $month['count'] > 0 ? $month['count'] : '' }}
                            </span>
                            <div class="w-full rounded-t-md bg-maroon-600/75 transition-all duration-500"
                                 style="height: {{ $h }}%"></div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-2 flex gap-1.5 px-2">
                    @foreach ($months as $month)
                        <p class="flex-1 text-center text-[10px] font-semibold text-maroon-900/50">{{ $month['label'] }}</p>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ── Status breakdown ────────────────────────────────────────────── --}}
        <div class="rounded-2xl border border-maroon-900/10 bg-white p-6 shadow-sm">
            <h3 class="mb-5 text-sm font-semibold text-maroon-950">Status Distribution</h3>
            @if ($totalCount === 0)
                <p class="py-8 text-center text-sm text-maroon-900/40">No data yet.</p>
            @else
                @php
                    $statusDef = [
                        'submitted'    => ['label' => 'Under Review',  'color' => 'bg-maroon-600'],
                        'approved'     => ['label' => 'Approved',       'color' => 'bg-emerald-500'],
                        'rejected'     => ['label' => 'Rejected',       'color' => 'bg-red-500'],
                        'discontinued' => ['label' => 'Discontinued',   'color' => 'bg-gray-400'],
                        'draft'        => ['label' => 'Draft',          'color' => 'bg-yellow-400'],
                        'did_not_enroll' => ['label' => 'Did not enroll', 'color' => 'bg-orange-500'],
                    ];
                @endphp
                <div class="space-y-3">
                    @foreach ($statusCounts as $key => $count)
                        @php
                            $def = $statusDef[$key] ?? ['label' => ucfirst($key), 'color' => 'bg-gray-400'];
                            $pct = $totalCount > 0 ? round(($count / $totalCount) * 100) : 0;
                        @endphp
                        <div>
                            <div class="mb-1 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-1.5">
                                    <span class="h-2.5 w-2.5 rounded-full {{ $def['color'] }}"></span>
                                    <span class="font-medium text-maroon-950">{{ $def['label'] }}</span>
                                </div>
                                <span class="text-maroon-900/60">{{ $count }} <span class="text-maroon-900/40">({{ $pct }}%)</span></span>
                            </div>
                            <div class="h-2.5 w-full overflow-hidden rounded-full bg-maroon-900/8">
                                <div class="h-full rounded-full {{ $def['color'] }} transition-all duration-500"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ── Top programs ─────────────────────────────────────────────────── --}}
        <div class="rounded-2xl border border-maroon-900/10 bg-white p-6 shadow-sm">
            <h3 class="mb-5 text-sm font-semibold text-maroon-950">Top Programs by Submission Volume</h3>
            @if ($topPrograms->isEmpty())
                <p class="py-8 text-center text-sm text-maroon-900/40">No data yet.</p>
            @else
                <div class="space-y-3">
                    @foreach ($topPrograms as $i => $prog)
                        @php $pct = $maxProgramCount > 0 ? round(($prog['count'] / $maxProgramCount) * 100) : 0; @endphp
                        <div>
                            <div class="mb-1 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-1.5">
                                    <span class="flex h-4 w-4 items-center justify-center rounded-full bg-maroon-800 text-[9px] font-bold text-white">{{ $i + 1 }}</span>
                                    <span class="font-semibold text-maroon-950">{{ $prog['code'] }}</span>
                                    <span class="hidden text-maroon-900/50 sm:inline truncate max-w-[150px]">{{ $prog['name'] }}</span>
                                </div>
                                <span class="font-semibold text-maroon-900/70">{{ $prog['count'] }}</span>
                            </div>
                            <div class="h-2.5 w-full overflow-hidden rounded-full bg-maroon-900/8">
                                <div class="h-full rounded-full bg-gold-500 transition-all duration-500"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
