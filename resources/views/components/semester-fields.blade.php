@props([
    'years',
    'semesters',
    'loa',
    'locked' => false,
])

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="academic_year" class="block text-sm font-medium text-maroon-900">School year</label>
        @if ($locked)
            <p class="mt-1 rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base text-maroon-900/70">{{ $loa->academic_year ?: '—' }}</p>
            <input type="hidden" name="academic_year" value="{{ $loa->academic_year }}">
        @else
            <select id="academic_year" name="academic_year" required
                class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                <option value="">Select school year</option>
                @foreach ($years as $year)
                    <option value="{{ $year }}" @selected(old('academic_year', $loa->academic_year) === $year)>{{ $year }}</option>
                @endforeach
            </select>
        @endif
        @error('academic_year') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="semester" class="block text-sm font-medium text-maroon-900">Semester</label>
        @if ($locked)
            <p class="mt-1 rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base text-maroon-900/70">{{ $loa->semester?->label() ?? '—' }}</p>
            <input type="hidden" name="semester" value="{{ $loa->semester?->value }}">
        @else
            <select id="semester" name="semester" required
                class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                <option value="">Select semester</option>
                @foreach ($semesters as $semester)
                    <option value="{{ $semester->value }}" @selected(old('semester', $loa->semester?->value) === $semester->value)>{{ $semester->label() }}</option>
                @endforeach
            </select>
        @endif
        @error('semester') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
    </div>
</div>
