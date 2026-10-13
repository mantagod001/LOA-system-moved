@php $isExtension = $loa->isExtension(); @endphp
@extends('layouts.app')

@section('title', $isExtension ? 'Leave extension form' : 'LOA form')

@section('content')
<x-page-shell eyebrow="EVSU-SASO-F-040" title="{{ $isExtension ? 'Leave of Absence extension' : 'Leave of Absence application' }}" width="2xl">
    <x-slot:lead>
        Submitting as <strong>{{ $access->email }}</strong>.
        @if ($isExtension)
            Academic details stay as on the approved leave. Pick a new return date (up to one extra year after the current end date). The President reviews this first.
        @else
            Student number and name are filled from your request. You may correct them if needed. <strong>Leave may not exceed one year</strong> from the start date.
        @endif
    </x-slot:lead>

    <form method="POST" action="{{ route('student.form.store', $token) }}" enctype="multipart/form-data" class="space-y-4 rounded-2xl border border-maroon-900/10 bg-white p-6 sm:p-8">
        @csrf

        @if ($isExtension)
            <div class="rounded-xl border border-maroon-900/10 bg-cream-50 px-4 py-4">
                <p class="text-sm font-semibold text-maroon-900">From your approved leave</p>
                <p class="mt-1 text-xs text-maroon-800/70">These details cannot be changed on an extension. Use a new LOA request if they are wrong.</p>
                <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-maroon-800/60">Student number</dt>
                        <dd class="font-medium text-maroon-900">{{ $loa->student_id }}</dd>
                    </div>
                    <div>
                        <dt class="text-maroon-800/60">Name</dt>
                        <dd class="font-medium text-maroon-900">{{ $loa->full_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-maroon-800/60">Department</dt>
                        <dd class="font-medium text-maroon-900">{{ $loa->department?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-maroon-800/60">Program / year</dt>
                        <dd class="font-medium text-maroon-900">{{ $loa->program?->name ?? '—' }}{{ $loa->year_level ? ' · '.$loa->year_level : '' }}</dd>
                    </div>
                </dl>
            </div>
            <input type="hidden" name="student_id" value="{{ $loa->student_id }}">
            <input type="hidden" name="first_name" value="{{ $loa->first_name }}">
            <input type="hidden" name="middle_name" value="{{ $loa->middle_name }}">
            <input type="hidden" name="last_name" value="{{ $loa->last_name }}">
            <input type="hidden" name="department_id" value="{{ $loa->department_id }}">
            <input type="hidden" name="program_id" value="{{ $loa->program_id }}">
            <input type="hidden" name="year_level" value="{{ $loa->year_level }}">
        @else
            <div>
                <label for="student_id" class="block text-sm font-medium text-maroon-900">Student number</label>
                <input id="student_id" name="student_id" required
                    value="{{ old('student_id', $loa->student_id) }}"
                    inputmode="numeric"
                    placeholder="e.g. 2021-12345"
                    data-student-id-input
                    class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                @error('student_id') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-maroon-900">First name</label>
                    <input id="first_name" name="first_name" required value="{{ old('first_name', $loa->first_name) }}"
                        class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                    @error('first_name') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="middle_name" class="block text-sm font-medium text-maroon-900">Middle name <span class="font-normal text-maroon-800/50">(optional)</span></label>
                    <input id="middle_name" name="middle_name" value="{{ old('middle_name', $loa->middle_name) }}"
                        class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                    @error('middle_name') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-medium text-maroon-900">Last name</label>
                    <input id="last_name" name="last_name" required value="{{ old('last_name', $loa->last_name) }}"
                        class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                    @error('last_name') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="department_id" class="block text-sm font-medium text-maroon-900">Department</label>
                    <select id="department_id" name="department_id" required data-department-filter
                        class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                        <option value="">Select department</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected(old('department_id', $loa->department_id) == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="program_id" class="block text-sm font-medium text-maroon-900">Program</label>
                    <select id="program_id" name="program_id" required data-program-select data-selected-program="{{ old('program_id', $loa->program_id) }}"
                        class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                        <option value="">Select department first</option>
                    </select>
                    @error('program_id') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="year_level" class="block text-sm font-medium text-maroon-900">Year level</label>
                    <select id="year_level" name="year_level" required data-year-select
                        class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                        <option value="">Select program first</option>
                    </select>
                    @error('year_level') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <script type="application/json" data-programs-json>@json($programs)</script>
            <input type="hidden" id="year_level_saved" value="{{ old('year_level', $loa->year_level ?? '') }}">
        @endif

        <x-semester-fields :years="$academicYears" :semesters="$semesters" :loa="$loa" :locked="$isExtension" />

        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
            @if ($isExtension)
                <p class="font-semibold">Extension limit</p>
                <p class="mt-1 text-amber-900/80">Your current leave ends on <strong>{{ $parentLoa?->return_date?->format('F j, Y') ?? '—' }}</strong>. The new return date must be after that day and not more than <strong>one extra year</strong>.</p>
            @else
                <p class="font-semibold">One-year leave limit</p>
                <p class="mt-1 text-amber-900/80">EVSU allows a maximum of <strong>one year</strong> from the expected start date. Pick a return date on or before the latest allowed date shown below.</p>
            @endif
            <p class="mt-1 font-medium" data-leave-limit-hint>Select a start date to see the latest allowed return date.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="start_date" class="block text-sm font-medium text-maroon-900">{{ $isExtension ? 'Original start date' : 'Expected start date' }}</label>
                <input id="start_date" name="start_date" type="date" required data-leave-start
                    value="{{ old('start_date', $loa->start_date?->format('Y-m-d')) }}"
                    @if ($isExtension) readonly @endif
                    class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2 {{ $isExtension ? 'text-maroon-900/70' : '' }}">
                @error('start_date') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="return_date" class="block text-sm font-medium text-maroon-900">{{ $isExtension ? 'New return date' : 'Expected return date' }}</label>
                <input id="return_date" name="return_date" type="date" required data-leave-return
                    @if ($isExtension && $parentLoa?->return_date) data-leave-extension-from="{{ $parentLoa->return_date->format('Y-m-d') }}" @endif
                    value="{{ old('return_date', $loa->return_date?->format('Y-m-d')) }}"
                    class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                @error('return_date') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <x-reason-grammar
            :label="$isExtension ? 'Reason for extension' : 'Reason for leave'"
            :value="old('reason', $loa->reason)"
            :url="route('student.form.grammar', $token)"
        />

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label for="parent_first_name" class="block text-sm font-medium text-maroon-900">Parent / guardian first name</label>
                <input id="parent_first_name" name="parent_first_name" required value="{{ old('parent_first_name', $loa->parent_first_name) }}"
                    class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                @error('parent_first_name') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="parent_middle_name" class="block text-sm font-medium text-maroon-900">Middle name <span class="font-normal text-maroon-800/50">(optional)</span></label>
                <input id="parent_middle_name" name="parent_middle_name" value="{{ old('parent_middle_name', $loa->parent_middle_name) }}"
                    class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                @error('parent_middle_name') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="parent_last_name" class="block text-sm font-medium text-maroon-900">Last name</label>
                <input id="parent_last_name" name="parent_last_name" required value="{{ old('parent_last_name', $loa->parent_last_name) }}"
                    class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                @error('parent_last_name') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="parent_relationship" class="block text-sm font-medium text-maroon-900">Relationship</label>
                @php
                    $selectedRelationship = old('parent_relationship');
                    if (! $selectedRelationship && $loa->parent_relationship) {
                        $selectedRelationship = collect($relationships)->first(function ($r) use ($loa) {
                            return $r->label() === $loa->parent_relationship
                                || $r->value === strtolower((string) $loa->parent_relationship);
                        })?->value;
                    }
                @endphp
                <select id="parent_relationship" name="parent_relationship" required
                    class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                    <option value="">Select relationship</option>
                    @foreach ($relationships as $relationship)
                        <option value="{{ $relationship->value }}" @selected($selectedRelationship === $relationship->value)>{{ $relationship->label() }}</option>
                    @endforeach
                </select>
                @error('parent_relationship') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="parent_phone" class="block text-sm font-medium text-maroon-900">Active phone number</label>
                <div class="mt-1 flex rounded-xl border border-maroon-900/15 bg-cream-50 focus-within:ring-2 focus-within:ring-maroon-700 overflow-hidden">
                    <span class="flex items-center bg-maroon-900/5 border-r border-maroon-900/15 px-3 text-sm font-medium text-maroon-700 select-none">+63</span>
                    <input id="parent_phone" name="parent_phone" type="tel" required
                        value="{{ old('parent_phone', ltrim((string) $loa->parent_phone, '+63')) }}"
                        class="flex-1 bg-cream-50 px-3 py-3 text-base outline-none"
                        placeholder="917-123-4567"
                        maxlength="12"
                        inputmode="numeric"
                        data-phone-input>
                </div>
                @error('parent_phone') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <x-attachment-rows :categories="$attachmentCategories" />

        <button type="submit" class="w-full rounded-xl bg-maroon-800 px-4 py-3.5 text-base font-semibold text-cream-50 hover:bg-maroon-700">
            {{ $isExtension ? 'Submit extension request' : 'Submit LOA request' }}
        </button>
    </form>
</x-page-shell>
@endsection
