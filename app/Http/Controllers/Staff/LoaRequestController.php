<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Enums\AcademicSemester;
use App\Models\LoaAttachment;
use App\Models\LoaRequest;
use App\Services\LoaPdfGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LoaRequestController extends Controller
{
    public function pipeline(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();

        if ($user->role->is(\App\Enums\UserRole::Administrator)) {
            return redirect()->route('staff.profile');
        }

        // ── Filters ───────────────────────────────────────────────────────────
        $filterStatus = $request->string('status')->toString() ?: null;
        $filterDept   = $request->integer('department_id') ?: null;
        $filterSearch = trim($request->string('search')->toString()) ?: null;

        $validStatuses = ['submitted', 'approved', 'rejected', 'discontinued', 'draft', 'overdue', 'did_not_enroll'];
        if ($filterStatus && ! in_array($filterStatus, $validStatuses, true)) {
            $filterStatus = null;
        }

        // Dept Head is always locked to their own dept
        $isDh = $user->role->is(\App\Enums\UserRole::DepartmentHead);
        if ($isDh) {
            $filterDept = null; // scope handled by scopeForUser
        }

        $loas = LoaRequest::query()
            ->with(['department', 'program', 'parentLoa', 'extensions', 'presidentActor', 'deptHeadActor', 'sasoActor', 'campusDirectorActor'])
            ->scopeForUser($user)
            ->when($filterStatus, fn ($q) => $q->applyStatusFilter($filterStatus))
            ->when($filterDept && ! $isDh, fn ($q) => $q->where('department_id', $filterDept))
            ->when($filterSearch, fn ($q) => $q->where(function ($q2) use ($filterSearch) {
                $q2->where('full_name', 'like', "%{$filterSearch}%")
                   ->orWhere('student_id', 'like', "%{$filterSearch}%")
                   ->orWhere('control_number', 'like', "%{$filterSearch}%");
            }))
            ->latest('submitted_at')
            ->paginate(25)
            ->withQueryString();

        $departments = \App\Models\Department::query()->orderBy('name')->get(['id', 'code', 'name']);

        return view('staff.pipeline', compact(
            'user', 'loas', 'departments', 'isDh',
            'filterStatus', 'filterDept', 'filterSearch',
        ));
    }

    public function pdf(LoaRequest $loaRequest): HttpResponse
    {
        abort_unless(auth()->user()->canViewLoaRequest($loaRequest), 403);

        // Only allow download if fully approved
        abort_unless($loaRequest->status === 'approved', 403, 'PDF is only available after full approval.');

        $generator = new LoaPdfGenerator();
        $pdfContent = $generator->generate($loaRequest);

        $filename = 'LOA-' . $loaRequest->control_number . '.pdf';

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function show(LoaRequest $loaRequest): View|RedirectResponse
    {
        abort_unless(auth()->user()->canViewLoaRequest($loaRequest), 403);

        $loaRequest->load([
            'department',
            'program',
            'attachments',
            'parentLoa',
            'presidentActor',
            'deptHeadActor',
            'sasoActor',
            'campusDirectorActor',
            'rejectedByActor',
            'discontinuedByActor',
            'returnedByActor',
            'didNotEnrollByActor',
            'extensions',
            'rejectionHistory.rejectedByActor',
        ]);

        // Smart back URL — return to wherever the user came from (pipeline or dashboard).
        $referer = request()->headers->get('referer', '');
        $backUrl = str_contains($referer, '/staff/pipeline')
            ? $referer
            : route('staff.pipeline');

        $expectedReturn = $loaRequest->expectedReturnTerm();

        return view('staff.loa-show', [
            'loa'     => $loaRequest,
            'backUrl' => $backUrl,
            'academicYears' => AcademicSemester::yearOptions(
                $loaRequest->did_not_enroll_academic_year ?? $expectedReturn['year'] ?? null
            ),
            'semesters' => AcademicSemester::cases(),
            'expectedReturn' => $expectedReturn,
        ]);
    }

    public function attachment(LoaRequest $loaRequest, LoaAttachment $attachment): StreamedResponse
    {
        // All authenticated staff can view attachments — only DH is dept-scoped.
        // The dept.scope middleware already blocked cross-dept DH before reaching here.
        abort_unless(auth()->user()->canViewLoaRequest($loaRequest), 403);
        abort_unless($attachment->loa_request_id === $loaRequest->id, 404);
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        return Storage::disk('local')->download($attachment->path, $attachment->original_name);
    }

    public function approve(Request $request, LoaRequest $loaRequest): RedirectResponse
    {
        $this->authorize('approve', $loaRequest);

        $user = $request->user();
        $loaRequest->markApprovedBy($user);

        return redirect()->route('staff.pipeline', $request->only(['status', 'department_id', 'search']))->with('status', "LOA {$loaRequest->control_number} approved by {$user->role->label()}.");
    }

    public function reject(Request $request, LoaRequest $loaRequest): RedirectResponse
    {
        $this->authorize('reject', $loaRequest);

        $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ], [
            'reason.required' => 'A rejection reason is required.',
        ]);

        $user = $request->user();
        $loaRequest->markRejectedBy($user, $request->input('reason'));

        return redirect()->route('staff.pipeline', $request->only(['status', 'department_id', 'search']))->with('status', "LOA {$loaRequest->control_number} rejected by {$user->role->label()}.");
    }

    public function discontinue(Request $request, LoaRequest $loaRequest): RedirectResponse
    {
        $this->authorize('discontinue', $loaRequest);

        $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ], [
            'reason.required' => 'A reason for discontinuation is required.',
        ]);

        $user = $request->user();
        $emailed = $loaRequest->markDiscontinued($user, $request->input('reason'));

        $message = "LOA {$loaRequest->control_number} marked as discontinued.";
        $message .= $emailed
            ? " The student was emailed at {$loaRequest->email}."
            : ' The student email could not be sent. They can still check status with their control number and student ID.';

        return redirect()
            ->route('staff.loa.show', $loaRequest)
            ->with('status', $message);
    }

    public function markReturned(Request $request, LoaRequest $loaRequest): RedirectResponse
    {
        $this->authorize('markReturned', $loaRequest);

        $loaRequest->markReturned($request->user());

        return redirect()
            ->route('staff.loa.show', $loaRequest)
            ->with('status', "LOA {$loaRequest->control_number} marked as returned. This is a LeaveFlow record, not an enrollment check.");
    }

    public function markDidNotEnroll(Request $request, LoaRequest $loaRequest): RedirectResponse
    {
        $this->authorize('markDidNotEnroll', $loaRequest);

        $validated = $request->validate([
            'academic_year' => ['required', 'string', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! AcademicSemester::isValidYear(is_string($value) ? $value : null)) {
                    $fail('Choose a valid school year, e.g. 2026-2027.');
                }
            }],
            'semester' => ['required', \Illuminate\Validation\Rule::enum(AcademicSemester::class)],
        ]);

        $loaRequest->markDidNotEnroll(
            $request->user(),
            $validated['academic_year'],
            AcademicSemester::from($validated['semester']),
        );

        return redirect()
            ->route('staff.loa.show', $loaRequest)
            ->with('status', "LOA {$loaRequest->control_number} recorded as did not enroll for {$loaRequest->didNotEnrollTermLabel()}. This is a LeaveFlow note, not an SIS record.");
    }
}
