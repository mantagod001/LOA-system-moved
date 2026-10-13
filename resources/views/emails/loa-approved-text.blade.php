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
Please proceed to the Registrar's Office to complete any required clearance
procedures before your leave begins. Keep this email and your control number
for reference.

For questions, contact the SASO Office or your Department Head.

LeaveFlow · EVSU Ormoc Campus
