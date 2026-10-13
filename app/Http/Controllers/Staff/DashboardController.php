<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\LoaRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();

        if ($user->role->is(\App\Enums\UserRole::Administrator)) {
            return redirect()->route('staff.profile');
        }

        $filterStatus = $request->string('status')->toString() ?: null;
        $filterSearch = trim($request->string('search')->toString()) ?: null;

        $validStatuses = ['submitted', 'approved', 'rejected', 'discontinued', 'draft', 'overdue'];
        if ($filterStatus && ! in_array($filterStatus, $validStatuses, true)) {
            $filterStatus = null;
        }

        $loas = LoaRequest::query()
            ->with(['department', 'program', 'parentLoa', 'extensions', 'presidentActor', 'deptHeadActor', 'sasoActor', 'campusDirectorActor'])
            ->scopeForUser($user)
            ->when($filterStatus, fn ($q) => $q->applyStatusFilter($filterStatus))
            ->when($filterSearch, fn ($q) => $q->where(function ($q2) use ($filterSearch) {
                $q2->where('full_name', 'like', "%{$filterSearch}%")
                   ->orWhere('student_id', 'like', "%{$filterSearch}%")
                   ->orWhere('control_number', 'like', "%{$filterSearch}%");
            }))
            ->latest('submitted_at')
            ->paginate(25)
            ->withQueryString();

        $overdueQuery = LoaRequest::query()->scopeForUser($user)->overdue();

        $stats = [
            'total'      => LoaRequest::query()->scopeForUser($user)->count(),
            'overdue'    => (clone $overdueQuery)->count(),
            'activeRole' => $user->role->label(),
        ];

        $overdueLoas = $filterStatus === 'overdue'
            ? collect()
            : (clone $overdueQuery)
                ->with(['department', 'program'])
                ->orderBy('return_date')
                ->limit(8)
                ->get();

        return view('staff.dashboard', compact('user', 'loas', 'stats', 'overdueLoas', 'filterStatus', 'filterSearch'));
    }
}
