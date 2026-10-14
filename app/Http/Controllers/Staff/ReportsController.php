<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\LoaRequest;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportsController extends Controller
{
    public function __invoke(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();

        if ($user->role->is(\App\Enums\UserRole::Administrator)) {
            return redirect()->route('staff.profile');
        }

        // ── Validate filters ──────────────────────────────────────────────────
        $filterDept   = $request->integer('department_id') ?: null;
        $filterStatus = $request->string('status')->toString() ?: null;
        $filterYear   = $request->integer('year') ?: now()->year;

        // Dept Head can only see their own department — ignore filter override
        $isDh = $user->role->is(\App\Enums\UserRole::DepartmentHead);
        if ($isDh) {
            $filterDept = $user->department_id;
        }

        // Clamp year to valid range
        $minYear = 2024;
        $maxYear = now()->year;
        if ($filterYear < $minYear || $filterYear > $maxYear) {
            $filterYear = $maxYear;
        }

        $validStatuses = ['submitted', 'approved', 'rejected', 'discontinued', 'draft'];
        if ($filterStatus && ! in_array($filterStatus, $validStatuses, true)) {
            $filterStatus = null;
        }

        // ── Base query scoped to role + dept/status filters ──────────────────
        $base = LoaRequest::query()->scopeForUser($user);

        if ($filterDept) {
            $base->where('department_id', $filterDept);
        }

        // Year-scoped base (dept filter applied, NO status filter — used for
        // aggregate stats and status distribution so they always show full picture)
        $baseYear = (clone $base)->whereYear('submitted_at', $filterYear);

        // Status filter applied only for top-programs (scoped view)
        $baseYearFiltered = (clone $baseYear);
        if ($filterStatus) {
            $baseYearFiltered->where('status', $filterStatus);
        }

        // ── Summary counts (year + dept filtered, no status filter) ───────────
        $totalCount     = (clone $baseYear)->count();
        $submittedCount = (clone $baseYear)->where('status', 'submitted')->count();
        $approvedCount  = (clone $baseYear)->where('status', 'approved')->count();
        $rejectedCount  = (clone $baseYear)->where('status', 'rejected')->count();

        $approvalRate = $totalCount > 0
            ? round(($approvedCount / $totalCount) * 100)
            : 0;

        // ── Avg processing days (submission → Campus Director final approval) ──
        $processed = (clone $baseYear)
            ->whereNotNull('submitted_at')
            ->whereNotNull('campus_director_at')
            ->get(['submitted_at', 'campus_director_at']);

        $avgProcessingDays = $processed->isNotEmpty()
            ? round($processed->avg(fn ($r) => $r->submitted_at->diffInDays($r->campus_director_at)), 1)
            : '—';

        // ── LOAs by department ────────────────────────────────────────────────
        $deptRows = Department::query()
            ->orderBy('code')
            ->get(['id', 'code', 'name'])
            ->map(function ($dept) use ($baseYear) {
                $count = (clone $baseYear)->where('department_id', $dept->id)->count();
                return [
                    'code'  => $dept->code,
                    'name'  => $dept->name,
                    'count' => $count,
                ];
            });

        $maxDeptCount = $deptRows->max('count') ?: 1;

        // ── LOAs by status (always uses unfiltered-by-status base) ───────────
        $statusCounts = [
            'submitted'    => (clone $baseYear)->where('status', 'submitted')->count(),
            'approved'     => (clone $baseYear)->where('status', 'approved')->count(),
            'rejected'     => (clone $baseYear)->where('status', 'rejected')->count(),
            'discontinued' => (clone $baseYear)->where('status', 'discontinued')->count(),
            'draft'        => (clone $baseYear)->where('status', 'draft')->count(),
            'did_not_enroll' => (clone $baseYear)->whereNotNull('did_not_enroll_at')->count(),
        ];

        // ── Top 5 programs by volume (respects status filter) ─────────────────
        $topPrograms = (clone $baseYearFiltered)
            ->select('program_id', DB::raw('COUNT(*) as loa_count'))
            ->whereNotNull('program_id')
            ->groupBy('program_id')
            ->orderByDesc('loa_count')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                $prog = Program::query()->find($row->program_id);
                return [
                    'code'  => $prog?->code ?? '—',
                    'name'  => $prog?->name ?? 'Unknown',
                    'count' => $row->loa_count,
                ];
            });

        $maxProgramCount = $topPrograms->max('count') ?: 1;

        // ── Monthly submissions (all 12 months of selected year, consistent base) ─
        $months = collect();
        for ($m = 1; $m <= 12; $m++) {
            $count = (clone $baseYear)
                ->whereMonth('submitted_at', $m)
                ->count();
            $months->push([
                'label' => date('M', mktime(0, 0, 0, $m, 1)),
                'count' => $count,
            ]);
        }
        $maxMonthCount = $months->max('count') ?: 1;

        // ── Highest program (for summary card) ────────────────────────────────
        $highestProgram = $topPrograms->first();

        // ── Data for filter dropdowns ─────────────────────────────────────────
        $departments = Department::query()->orderBy('name')->get(['id', 'code', 'name']);
        $years       = collect(range($maxYear, $minYear));

        return view('staff.reports', compact(
            'user',
            'totalCount',
            'approvedCount',
            'rejectedCount',
            'approvalRate',
            'avgProcessingDays',
            'deptRows',
            'maxDeptCount',
            'statusCounts',
            'topPrograms',
            'maxProgramCount',
            'months',
            'maxMonthCount',
            'highestProgram',
            'departments',
            'years',
            'filterDept',
            'filterStatus',
            'filterYear',
            'isDh',
        ));
    }
}
