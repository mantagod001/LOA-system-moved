<?php

namespace App\Http\Controllers\Staff;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\StudentWhitelist;
use App\Services\StudentWhitelistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class StudentWhitelistController extends Controller
{
    public function index(Request $request): View
    {
        $this->registrar();

        $query = StudentWhitelist::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.trim($request->string('q')->toString()).'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('student_id', 'like', $term)
                        ->orWhere('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->orderBy('student_id');

        $students = $query->paginate(50)->withQueryString();

        return view('staff.student-whitelist', [
            'students' => $students,
            'totalCount' => StudentWhitelist::query()->count(),
            'search' => $request->string('q')->toString(),
        ]);
    }

    public function store(Request $request, StudentWhitelistService $service): RedirectResponse
    {
        $this->registrar();

        $request->validate([
            'csv' => ['required', 'file', 'extensions:csv,txt,xlsx', 'max:5120'],
        ], [
            'csv.required' => 'Please choose a file to upload.',
            'csv.extensions' => 'Please upload an Excel Workbook or a CSV file.',
        ]);

        try {
            $summary = $service->import($request->file('csv'));
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['csv' => $e->getMessage()]);
        }

        $message = sprintf(
            'Upload complete: %d added, %d updated, %d skipped. Existing students were kept.',
            $summary['added'],
            $summary['updated'],
            $summary['skipped']
        );

        return back()->with('status', $message);
    }

    private function registrar(): \App\Models\User
    {
        $user = auth()->user();
        if (! $user || ! $user->role->is(UserRole::Registrar)) {
            abort(403, 'Only the Registrar can manage the official student list.');
        }

        return $user;
    }
}
