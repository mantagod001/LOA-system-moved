<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Enums\AcademicSemester;
use App\Enums\ApprovalStageStatus;
use App\Enums\AttachmentCategory;
use App\Enums\ParentRelationship;
use App\Mail\LoaSubmittedMail;
use App\Models\Department;
use App\Models\LoaAccessToken;
use App\Models\LoaRequest;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LoaFormController extends Controller
{
    public function show(string $token): View|RedirectResponse
    {
        $access = $this->resolveToken($token);

        if ($access instanceof RedirectResponse) {
            return $access;
        }

        $request = $access->loaRequest ?? $this->startDraft($access);

        if ($request instanceof RedirectResponse) {
            return $request;
        }

        if ($request->isSubmitted()) {
            return redirect()->route('student.form.submitted', ['token' => $token]);
        }

        $request->load(['parentLoa', 'department', 'program']);

        return view('student.form', [
            'token' => $token,
            'access' => $access,
            'loa' => $request,
            'parentLoa' => $request->parentLoa,
            'departments' => Department::query()->orderBy('name')->get(),
            'programs' => Program::query()->orderBy('name')->get(['id', 'department_id', 'code', 'name']),
            'relationships' => ParentRelationship::cases(),
            'attachmentCategories' => AttachmentCategory::cases(),
            'academicYears' => AcademicSemester::yearOptions($request->academic_year),
            'semesters' => AcademicSemester::cases(),
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $access = $this->resolveToken($token);

        if ($access instanceof RedirectResponse) {
            return $access;
        }

        $loa = $access->loaRequest?->load('parentLoa');

        if (! $loa) {
            return redirect()->route('student.form.show', ['token' => $token]);
        }

        if ($loa->isSubmitted()) {
            return redirect()->route('student.form.submitted', ['token' => $token]);
        }

        $editableRules = [
            'start_date' => ['required', 'date'],
            'return_date' => ['required', 'date', 'after:start_date'],
            'reason' => ['required', 'string', 'max:5000'],
            'parent_first_name' => ['required', 'string', 'max:100'],
            'parent_middle_name' => ['nullable', 'string', 'max:100'],
            'parent_last_name' => ['required', 'string', 'max:100'],
            'parent_relationship' => ['required', Rule::enum(ParentRelationship::class)],
            'parent_phone' => ['required', 'string', 'regex:/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
            'attachment_categories' => ['nullable', 'array', 'max:10'],
            'attachment_categories.*' => ['nullable', 'string', Rule::enum(AttachmentCategory::class)],
        ];

        $identityRules = $loa->isExtension() ? [] : [
            'student_id'  => ['required', 'string', 'regex:/^\d{4}-\d{4,6}$/'],
            'department_id' => ['required', 'exists:departments,id'],
            'program_id' => [
                'required',
                Rule::exists('programs', 'id')->where('department_id', $request->integer('department_id')),
            ],
            'year_level' => ['required', 'string', 'max:20'],
            'academic_year' => ['required', 'string', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! AcademicSemester::isValidYear(is_string($value) ? $value : null)) {
                    $fail('Choose a valid school year, e.g. 2026-2027.');
                }
            }],
            'semester' => ['required', Rule::enum(AcademicSemester::class)],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
        ];

        $validated = $request->validate(array_merge($identityRules, $editableRules), [
            'student_id.required'          => 'Please enter your student number.',
            'student_id.regex'             => 'Student number must follow the format YYYY-NNNNN, e.g. 2021-12345.',
            'first_name.required'          => 'Please enter your first name.',
            'last_name.required'           => 'Please enter your last name.',
            'department_id.required'       => 'Please select your department.',
            'program_id.required'          => 'Please select your academic program.',
            'year_level.required'          => 'Please select your year level.',
            'start_date.required'          => 'Please select the expected start date.',
            'return_date.required'         => 'Please select the expected return date.',
            'return_date.after'            => 'The return date must be after the start date.',
            'reason.required'              => 'Please provide a reason for your leave request.',
            'parent_first_name.required'   => 'Please enter the first name of your parent or guardian.',
            'parent_last_name.required'    => 'Please enter the last name of your parent or guardian.',
            'parent_relationship.required' => 'Please select your relationship with your parent or guardian.',
            'parent_phone.required'        => 'Please enter an active contact number for your parent or guardian.',
            'parent_phone.regex'           => 'Enter a valid 10-digit Philippine mobile number, e.g. 917-123-4567.',
        ]);

        if ($loa->isExtension()) {
            $parent = $loa->parentLoa;
            if (! $parent?->return_date) {
                return back()->withInput()->withErrors(['return_date' => 'The original leave could not be found.']);
            }
            $minReturn = $parent->return_date->copy()->addDay();
            $maxReturn = $parent->return_date->copy()->addYear();
            $newReturn = now()->parse($validated['return_date']);
            if ($newReturn->lt($minReturn) || $newReturn->gt($maxReturn)) {
                return back()
                    ->withInput()
                    ->withErrors(['return_date' => 'The new return date must be after the current leave end and cannot add more than one extra year.']);
            }
            $validated['start_date'] = $parent->start_date?->toDateString() ?? $validated['start_date'];
            $validated['student_id'] = $parent->student_id;
            $validated['department_id'] = $parent->department_id;
            $validated['program_id'] = $parent->program_id;
            $validated['year_level'] = $parent->year_level;
            $validated['academic_year'] = $parent->academic_year;
            $validated['semester'] = $parent->semester?->value;
            $validated['first_name'] = $parent->first_name ?: $loa->first_name;
            $validated['middle_name'] = $parent->middle_name ?: $loa->middle_name;
            $validated['last_name'] = $parent->last_name ?: $loa->last_name;
        } else {
            $existing = LoaRequest::query()
                ->where('student_id', $validated['student_id'])
                ->where('id', '!=', $loa->id)
                ->blockingNewRequest()
                ->exists();

            if ($existing) {
                return back()
                    ->withInput()
                    ->withErrors(['student_id' => 'This student number already has an active LOA request. Only one request is allowed at a time.']);
            }

            if (LoaRequest::latestApprovedFor($validated['student_id'], $loa->email)) {
                return back()
                    ->withInput()
                    ->withErrors(['student_id' => 'This student number already has an approved leave. Use Extend leave if you need more time.']);
            }

            $maxReturn = now()->parse($validated['start_date'])->addYear();
            if (now()->parse($validated['return_date'])->gt($maxReturn)) {
                return back()
                    ->withInput()
                    ->withErrors(['return_date' => 'The approved leave period cannot exceed one year.']);
            }
        }

        DB::transaction(function () use ($loa, $validated, $access, $request) {
            // Normalize phone to E.164-style +63xxxxxxxxxx
            $phone = preg_replace('/[\s\-]/', '', $validated['parent_phone']);
            $phone = ltrim($phone, '0');
            if (! str_starts_with($phone, '+63')) {
                $phone = '+63' . $phone;
            }

            $loa->fill([
                'student_id'          => $validated['student_id'],
                'first_name'          => $validated['first_name'],
                'middle_name'         => $validated['middle_name'] ?? null,
                'last_name'           => $validated['last_name'],
                'department_id'       => $validated['department_id'],
                'program_id'          => $validated['program_id'],
                'year_level'          => $validated['year_level'],
                'academic_year'       => $validated['academic_year'],
                'semester'            => $validated['semester'],
                'start_date'          => $validated['start_date'],
                'return_date'         => $validated['return_date'],
                'reason'              => $validated['reason'],
                'parent_first_name'   => $validated['parent_first_name'],
                'parent_middle_name'  => $validated['parent_middle_name'] ?? null,
                'parent_last_name'    => $validated['parent_last_name'],
                'parent_relationship' => ParentRelationship::from($validated['parent_relationship'])->label(),
                'parent_phone'        => $phone,
            ]);
            $loa->assignControlNumber();
            $loa->status = 'submitted';
            $loa->submitted_at = now();
            if ($loa->isExtension()) {
                $loa->president_status = ApprovalStageStatus::Pending;
                $loa->dept_head_status = null;
            } else {
                $loa->dept_head_status = ApprovalStageStatus::Pending;
            }
            $loa->save();

            // Backfill student_id + split names onto the token for duplicate checks
            $access->update([
                'student_id'  => $validated['student_id'],
                'first_name'  => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name'   => $validated['last_name'],
                'full_name'   => LoaRequest::composeName(
                    $validated['first_name'],
                    $validated['middle_name'] ?? null,
                    $validated['last_name'],
                ),
            ]);

            $categories = $request->input('attachment_categories', []);
            foreach ($request->file('attachments', []) as $index => $file) {
                if (! $file) {
                    continue;
                }

                $category = $categories[$index] ?? null;
                if (! $category || ! AttachmentCategory::tryFrom($category)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "attachment_categories.{$index}" => 'Choose a document type for each file.',
                    ]);
                }

                $path = $file->store('loa-attachments/'.$loa->id, 'local');

                $safeName = mb_substr(
                    preg_replace('/[^\w.\-_ ]/u', '_', basename((string) $file->getClientOriginalName())),
                    0, 255
                );

                $loa->attachments()->create([
                    'original_name' => $safeName ?: 'attachment',
                    'category'      => $category,
                    'path'          => $path,
                    'mime_type'     => $file->getMimeType() ?? 'application/octet-stream',
                    'size'          => $file->getSize(),
                ]);
            }

            $access->forceFill(['used_at' => now()])->save();
        });

        // Send confirmation email with form summary to the student.
        try {
            $loa->load(['department', 'program', 'attachments']);
            Mail::to($loa->email)->send(new LoaSubmittedMail($loa));
        } catch (\Throwable $e) {
            report($e);
            // Non-fatal — submission is already recorded, just log the failure.
        }

        return redirect()->route('student.form.submitted', ['token' => $token]);
    }

    public function submitted(string $token): View|RedirectResponse
    {
        $access = $this->resolveToken($token, allowUsed: true);

        if ($access instanceof RedirectResponse) {
            return $access;
        }

        $loa = $access->loaRequest;

        if (! $loa?->isSubmitted()) {
            return redirect()->route('student.form.show', ['token' => $token]);
        }

        $loa->load(['department', 'program']);

        return view('student.submitted', ['loa' => $loa]);
    }

    private function startDraft(LoaAccessToken $access): LoaRequest|RedirectResponse
    {
        $attrs = [
            'loa_access_token_id' => $access->id,
            'student_id'  => $access->student_id ?: '',
            'first_name'  => $access->first_name,
            'middle_name' => $access->middle_name,
            'last_name'   => $access->last_name,
            'full_name'   => $access->full_name ?: '',
            'email'       => $access->email,
            'status'      => 'draft',
        ];

        if ($access->isExtension()) {
            $parent = $access->parentLoa ?: LoaRequest::find($access->parent_loa_id);
            if (! $parent || $parent->status !== 'approved') {
                return redirect()
                    ->route('student.extend')
                    ->withErrors(['email' => 'No approved leave was found to extend. Request a new LOA first.']);
            }

            $attrs = array_merge($attrs, [
                'is_extension'        => true,
                'parent_loa_id'       => $parent->id,
                'department_id'       => $parent->department_id,
                'program_id'          => $parent->program_id,
                'year_level'          => $parent->year_level,
                'academic_year'       => $parent->academic_year,
                'semester'            => $parent->semester,
                'start_date'          => $parent->start_date,
                'first_name'          => $parent->first_name ?: $attrs['first_name'],
                'middle_name'         => $parent->middle_name ?: $attrs['middle_name'],
                'last_name'           => $parent->last_name ?: $attrs['last_name'],
                'full_name'           => $parent->full_name ?: $attrs['full_name'],
                'student_id'          => $parent->student_id,
                'parent_first_name'   => $parent->parent_first_name,
                'parent_middle_name'  => $parent->parent_middle_name,
                'parent_last_name'    => $parent->parent_last_name,
                'parent_full_name'    => $parent->parent_full_name,
                'parent_relationship' => $parent->parent_relationship,
                'parent_phone'        => $parent->parent_phone,
            ]);
        }

        return LoaRequest::create($attrs);
    }

    private function resolveToken(string $token, bool $allowUsed = false): LoaAccessToken|RedirectResponse
    {
        // Guard: token must be exactly 40 alphanumeric chars — anything else is
        // obviously invalid (email client truncation, partial paste, etc.)
        if (! preg_match('/^[A-Za-z0-9]{40}$/', $token)) {
            return $this->invalidFormRedirect(null, 'That form link appears to be incomplete or was copied incorrectly. Please request a new one.');
        }

        $access = LoaAccessToken::query()
            ->where('token_hash', LoaAccessToken::hashPlainToken($token))
            ->first();

        if (! $access) {
            return $this->invalidFormRedirect(null, 'That form link is invalid. It may have been altered or already replaced. Please request a new one.');
        }

        if ($access->isExpired()) {
            return $this->invalidFormRedirect($access, 'That form link expired 24 hours after it was sent. Please request a new one.');
        }

        if (! $allowUsed && $access->used_at && $access->loaRequest?->isSubmitted()) {
            return redirect()->route('student.form.submitted', ['token' => $token]);
        }

        // Guard: OTP must be verified before the form is accessible
        if (! $access->otp_verified) {
            return redirect()
                ->route('student.identity.verify', ['token' => $token])
                ->withErrors(['otp' => 'Please verify your email first.']);
        }

        return $access;
    }

    private function invalidFormRedirect(?LoaAccessToken $access, string $message): RedirectResponse
    {
        $route = $access?->isExtension() ? 'student.extend' : 'student.identity';

        return redirect()
            ->route($route)
            ->withErrors(['token' => $message]);
    }
}
