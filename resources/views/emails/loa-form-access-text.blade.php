Hello{{ filled($accessToken->full_name) ? ' '.$accessToken->full_name : '' }},

Use the link below to open your {{ $accessToken->isExtension() ? 'leave extension' : 'Leave of Absence' }} form (EVSU-SASO-F-040).
Complete it as soon as you can. The link stops working one day after this email is sent.

{{ $formUrl }}

@if (filled($accessToken->student_id))
Student ID: {{ $accessToken->student_id }}
@endif

Copy the full address as one line. Do not forward this email.

If you did not request a Leave of Absence, you can ignore this email.

LeaveFlow · EVSU Ormoc Campus
