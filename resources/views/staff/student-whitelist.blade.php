@extends('layouts.staff')

@section('eyebrow', 'Registrar records')
@section('title', 'Official student list')

@section('content')
    <div class="mb-6 rounded-2xl border border-maroon-900/10 bg-white shadow-sm">
        <div class="border-b border-maroon-900/10 px-5 py-4">
            <p class="text-sm text-maroon-900/80">
                Upload an Excel file or a CSV file. In Excel, click <strong>File</strong>, then <strong>Save As</strong>, then choose <strong>Excel Workbook</strong> or <strong>CSV</strong>.
            </p>
            <p class="mt-2 text-sm text-maroon-900/70">
                First row must be: Student ID, First name, Middle name, Last name, Email. Contact number is optional.
            </p>
        </div>

        <form method="POST" action="{{ route('staff.students.store') }}" enctype="multipart/form-data"
              class="flex flex-wrap items-end gap-4 px-5 py-4">
            @csrf
            <div class="min-w-[16rem] flex-1">
                <label for="csv" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-maroon-900/50">Choose file</label>
                <input id="csv" name="csv" type="file" accept=".csv,.xlsx" required
                       class="w-full rounded-lg border border-maroon-900/15 bg-cream-50 px-3 py-2 text-sm text-maroon-950 file:mr-3 file:rounded-md file:border-0 file:bg-maroon-800 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-cream-50">
            </div>
            <button type="submit" class="rounded-lg bg-maroon-800 px-4 py-2.5 text-sm font-semibold text-cream-50 shadow-sm hover:bg-maroon-700">
                Upload list
            </button>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-maroon-900/10 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-maroon-900/10 px-5 py-4">
            <p class="text-sm font-semibold text-maroon-950">
                {{ $students->total() }} shown
                <span class="font-normal text-maroon-900/55">· {{ $totalCount }} total</span>
            </p>
            <form method="GET" action="{{ route('staff.students.index') }}" class="flex gap-2">
                <input type="search" name="q" value="{{ $search }}" placeholder="Search ID or name"
                       class="rounded-lg border border-maroon-900/15 bg-cream-50 px-3 py-2 text-sm text-maroon-950 outline-none ring-maroon-700 focus:ring-2">
                <button type="submit" class="rounded-lg border border-maroon-900/15 bg-white px-3 py-2 text-sm font-medium text-maroon-800 hover:bg-cream-100">Search</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-cream-50 text-xs uppercase tracking-wider text-maroon-900/55">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Student ID</th>
                        <th class="px-5 py-3 font-semibold">First name</th>
                        <th class="px-5 py-3 font-semibold">Middle name</th>
                        <th class="px-5 py-3 font-semibold">Last name</th>
                        <th class="px-5 py-3 font-semibold">Email</th>
                        <th class="px-5 py-3 font-semibold">Contact</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        <tr class="border-t border-maroon-900/10 hover:bg-cream-50/80">
                            <td class="px-5 py-3 font-medium text-maroon-950">{{ $student->student_id }}</td>
                            <td class="px-5 py-3 text-maroon-950">{{ $student->first_name }}</td>
                            <td class="px-5 py-3 text-maroon-900/70">{{ $student->middle_name ?: '—' }}</td>
                            <td class="px-5 py-3 text-maroon-950">{{ $student->last_name }}</td>
                            <td class="px-5 py-3 text-maroon-900/70">{{ $student->email ?: '—' }}</td>
                            <td class="px-5 py-3 text-maroon-900/70">{{ $student->contact_number ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-sm text-maroon-900/55">No students on the list yet. Upload a file to get started.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($students->hasPages())
            <div class="border-t border-maroon-900/10 px-5 py-3">{{ $students->links() }}</div>
        @endif
    </div>
@endsection
