Your Leave of Absence request has been submitted — EVSU Ormoc Campus

--------------------------------------------------
CONTROL NUMBER: {{ $loa->control_number }}
--------------------------------------------------

Keep this number. You will need it to follow up on your request.

SUBMITTED DETAILS
-----------------
Student ID       : {{ $loa->student_id }}
First name       : {{ $loa->first_name ?: $loa->full_name }}
Middle name      : {{ $loa->middle_name ?: '—' }}
Last name        : {{ $loa->last_name ?: '—' }}
Department       : {{ $loa->department?->name ?? '—' }}
Program          : {{ $loa->program?->name ?? '—' }}
Year level       : {{ $loa->year_level ?? '—' }}
Semester         : {{ $loa->academicTermLabel() }}
Leave period     : {{ $loa->start_date?->format('M j, Y') }} to {{ $loa->return_date?->format('M j, Y') }}
Date submitted   : {{ $loa->submitted_at?->format('M j, Y g:i A') }}
Parent / Guardian: {{ $loa->parent_full_name }} ({{ $loa->parent_relationship }}) — {{ $loa->parent_phone }}
@if ($loa->attachments->isNotEmpty())
Attachments      : {{ $loa->attachments->count() }} file(s):
@foreach ($loa->attachments as $file)
  {{ $loop->iteration }}. {{ $file->original_name }} ({{ number_format($file->size / 1024, 1) }} KB)
@endforeach
@else
Attachments      : None submitted
@endif

Reason:
{{ $loa->reason }}

WHAT HAPPENS NEXT
-----------------
@if ($loa->isExtension())
The President will review this extension first. After that, Department Head, SASO, and Campus Director follow the same sequence as a new leave.
@else
Your Department Head will review your application and may contact your
parent or guardian at the number you provided. You will receive email
updates as your request moves through each approval step.
@endif

If you did not submit this request, contact the SASO Office immediately.

LeaveFlow · EVSU Ormoc Campus
