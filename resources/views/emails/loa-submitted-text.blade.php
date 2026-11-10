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
The President reviews this extension first, then Department Head, SASO, and Campus Director.
@else
Your Department Head reviews first, then SASO, then Campus Director.
Your Department Head may contact your parent or guardian at the number you provided.
@endif
You will receive an email at each step. Keep your control number. The official copy after approval also includes it.

If you did not submit this request, contact the SASO Office immediately.

LeaveFlow · EVSU Ormoc Campus
