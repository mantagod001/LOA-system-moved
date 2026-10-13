@php $isExtension = $loa->isExtension(); @endphp
@extends('layouts.app')

@section('title', 'Resubmit LOA — ' . $loa->control_number)

@section('content')
<x-page-shell eyebrow="EVSU-SASO-F-040 · Resubmission" title="{{ $isExtension ? 'Resubmit leave extension' : 'Resubmit Leave of Absence' }}" width="2xl">
    <x-slot:lead>
        Updating your application <strong>{{ $loa->control_number }}</strong> for
        <strong>{{ $loa->full_name }}</strong> · {{ $loa->email }}.
        Address the rejection reason below and resubmit.
    </x-slot:lead>

    {{-- Rejection reason banner --}}
    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-5">
        <p class="text-xs font-semibold uppercase tracking-wider text-red-600 mb-2">Rejection Reason</p>
        @if ($loa->rejectedByActor)
            <p class="text-sm text-red-700 mb-1">
                Rejected by <strong>{{ $loa->rejectedByActor->name }}</strong>
                ({{ $loa->rejectedByActor->role->label() }})
                @if ($loa->rejected_at)
                    on {{ $loa->rejected_at->format('F j, Y \a\t g:i A') }}
                @endif
            </p>
        @endif
        @if ($loa->rejection_reason)
            <p class="text-sm text-red-800 whitespace-pre-wrap mt-2 rounded-lg border border-red-200 bg-white px-4 py-3">{{ $loa->rejection_reason }}</p>
        @endif
    </div>

    <form method="POST" action="{{ route('student.resubmit.store', $token) }}" enctype="multipart/form-data"
          class="space-y-4 rounded-2xl border border-maroon-900/10 bg-white p-6 sm:p-8">
        @csrf

        {{-- Student identity — read-only --}}
        <div>
            <label class="block text-sm font-medium text-maroon-900">Student number</label>
            <input type="text" value="{{ $loa->student_id }}" disabled
                   class="mt-1 w-full rounded-xl border border-maroon-900/10 bg-maroon-900/5 px-3 py-3 text-base text-maroon-800/70 cursor-not-allowed">
        </div>
        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-sm font-medium text-maroon-900">First name</label>
                <input type="text" value="{{ $loa->first_name ?: $loa->full_name }}" disabled
                       class="mt-1 w-full rounded-xl border border-maroon-900/10 bg-maroon-900/5 px-3 py-3 text-base text-maroon-800/70 cursor-not-allowed">
            </div>
            <div>
                <label class="block text-sm font-medium text-maroon-900">Middle name</label>
                <input type="text" value="{{ $loa->middle_name ?: '—' }}" disabled
                       class="mt-1 w-full rounded-xl border border-maroon-900/10 bg-maroon-900/5 px-3 py-3 text-base text-maroon-800/70 cursor-not-allowed">
            </div>
            <div>
                <label class="block text-sm font-medium text-maroon-900">Last name</label>
                <input type="text" value="{{ $loa->last_name ?: '—' }}" disabled
                       class="mt-1 w-full rounded-xl border border-maroon-900/10 bg-maroon-900/5 px-3 py-3 text-base text-maroon-800/70 cursor-not-allowed">
            </div>
        </div>

        @if ($isExtension)
            <div class="rounded-xl border border-maroon-900/10 bg-cream-50 px-4 py-4">
                <p class="text-sm font-semibold text-maroon-900">From the approved leave</p>
                <p class="mt-1 text-xs text-maroon-800/70">Department, program, year, and school term stay locked on an extension.</p>
                <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
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
            <input type="hidden" name="department_id" value="{{ $loa->department_id }}">
            <input type="hidden" name="program_id" value="{{ $loa->program_id }}">
            <input type="hidden" name="year_level" value="{{ $loa->year_level }}">
        @else
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
                <p class="mt-1 text-amber-900/80">The new return date must be after the current leave end and cannot add more than <strong>one extra year</strong>.</p>
            @else
                <p class="font-semibold">One-year leave limit</p>
                <p class="mt-1 text-amber-900/80">Leave may not exceed <strong>one year</strong> from the start date.</p>
            @endif
            <p class="mt-1 font-medium" data-leave-limit-hint>Select a start date to see the latest allowed return date.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="start_date" class="block text-sm font-medium text-maroon-900">{{ $isExtension ? 'Original start date' : 'Expected start date' }}</label>
                <input id="start_date" name="start_date" type="date" required data-leave-start value="{{ old('start_date', $loa->start_date?->format('Y-m-d')) }}"
                       @if ($isExtension) readonly @endif
                       class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2 {{ $isExtension ? 'text-maroon-900/70' : '' }}">
                @error('start_date') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="return_date" class="block text-sm font-medium text-maroon-900">{{ $isExtension ? 'New return date' : 'Expected return date' }}</label>
                <input id="return_date" name="return_date" type="date" required data-leave-return
                       @if ($isExtension && $loa->parentLoa?->return_date) data-leave-extension-from="{{ $loa->parentLoa->return_date->format('Y-m-d') }}" @endif
                       value="{{ old('return_date', $loa->return_date?->format('Y-m-d')) }}"
                       class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                @error('return_date') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <x-reason-grammar
            :label="$isExtension ? 'Updated reason for extension' : 'Updated reason for leave'"
            :value="old('reason', $loa->reason)"
            :url="route('student.resubmit.grammar', $token)"
        />

        {{-- Parent / Guardian --}}
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
                           value="{{ old('parent_phone', (function() use ($loa) {
                               $d = substr((string) $loa->parent_phone, 3); // strip +63 → 10 digits
                               return strlen($d) === 10
                                   ? substr($d,0,3).'-'.substr($d,3,3).'-'.substr($d,6)
                                   : $d;
                           })()) }}"
                           class="flex-1 bg-cream-50 px-3 py-3 text-base outline-none"
                           placeholder="917-123-4567"
                           maxlength="12"
                           inputmode="numeric"
                           data-phone-input>
                </div>
                @error('parent_phone') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>
        </div>

        @if ($loa->attachments->count() > 0)
            <p class="text-xs text-maroon-800/60">Previously attached: {{ $loa->attachments->count() }} file(s). New files below are added alongside them.</p>
        @endif
        <x-attachment-rows :categories="$attachmentCategories" />

        <button type="submit"
                class="w-full rounded-xl bg-maroon-800 px-4 py-3.5 text-base font-semibold text-cream-50 hover:bg-maroon-700">
            {{ $isExtension ? 'Resubmit extension' : 'Resubmit LOA Application' }}
        </button>
    </form>
</x-page-shell>
@endsection
