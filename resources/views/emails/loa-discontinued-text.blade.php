Your LOA has been Discontinued — EVSU Ormoc Campus

Control Number: {{ $loa->control_number }}

Dear {{ $loa->full_name }},

Your Leave of Absence application has been marked as discontinued / withdrawn.

@if ($loa->discontinuedByActor)
RECORDED BY
-----------
{{ $loa->discontinuedByActor->name }}@if ($loa->discontinuedByActor->role) ({{ $loa->discontinuedByActor->role->label() }})@endif
@if ($loa->discontinued_at){{ $loa->discontinued_at->format('M j, Y \a\t g:i A') }}
@endif
@endif
@if ($loa->discontinuation_reason)

REASON
------
{{ $loa->discontinuation_reason }}

@endif
Check your application here (control number + student ID):
{{ $statusUrl }}

If you have questions or believe this was done in error, please contact
the SASO Office or your Department Head.

LeaveFlow · EVSU Ormoc Campus
