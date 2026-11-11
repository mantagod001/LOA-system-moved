Your Leave of Absence has been APPROVED — EVSU Ormoc Campus

Control Number: {{ $loa->control_number }}

Dear {{ $loa->full_name }},

Your Leave of Absence request has been APPROVED by all required offices.
Required approval stages have been completed:

@foreach ($loa->timelineStages() as $stage)
  [OK] {{ $stage['label'] }}{{ $stage['actor'] ? ' — ' . $stage['actor']->name : '' }}{{ $stage['at'] ? ' (' . $stage['at']->format('M j, Y') . ')' : '' }}

@endforeach

LEAVE DETAILS
-------------
Leave period : {{ $loa->start_date?->format('M j, Y') }} to {{ $loa->return_date?->format('M j, Y') }}
Program      : {{ $loa->program?->name ?? '—' }}
Department   : {{ $loa->department?->name ?? '—' }}

NEXT STEPS
----------
Keep this email, your control number, and the attached PDF. Registrar and
Guidance update office records after approval; they do not approve the leave.
Seven days before the return date, you will receive an email reminder.
Go to school that day to enroll at the Registrar, or use Extend leave
if you need more time. Staff may later record whether you returned or
did not enroll.

For questions, contact the SASO Office or your Department Head.

LeaveFlow · EVSU Ormoc Campus
