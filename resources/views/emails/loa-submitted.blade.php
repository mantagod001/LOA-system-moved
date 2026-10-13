<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LOA Submitted</title>
</head>
<body style="margin:0;padding:0;background-color:#f6ebe6;font-family:Arial,Helvetica,sans-serif;color:#2b070d;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f6ebe6;padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:560px;background-color:#ffffff;border-radius:16px;overflow:hidden;">

                    {{-- Header --}}
                    <tr>
                        <td style="background-color:#4a0e18;padding:28px 32px;">
                            <p style="margin:0;font-size:12px;letter-spacing:0.16em;text-transform:uppercase;color:#d4b36a;">EVSU Ormoc Campus</p>
                            <h1 style="margin:8px 0 0;font-size:22px;line-height:1.3;color:#fdf7f4;font-weight:700;">Leave of Absence — Submitted</h1>
                        </td>
                    </tr>

                    {{-- Control number highlight --}}
                    <tr>
                        <td style="padding:24px 32px 0;">
                            <p style="margin:0 0 6px;font-size:13px;color:#8a1c30;text-transform:uppercase;letter-spacing:0.1em;">Your Control Number</p>
                            <p style="margin:0;font-family:monospace;font-size:22px;font-weight:700;color:#4a0e18;letter-spacing:0.05em;">{{ $loa->control_number }}</p>
                            <p style="margin:10px 0 0;font-size:14px;color:#6b1424;">Keep this number — you will need it to track your request.</p>
                        </td>
                    </tr>

                    {{-- Divider --}}
                    <tr><td style="padding:20px 32px 0;"><hr style="border:none;border-top:1px solid #f6ebe6;"></td></tr>

                    {{-- Form summary --}}
                    <tr>
                        <td style="padding:20px 32px 0;">
                            <p style="margin:0 0 16px;font-size:15px;font-weight:700;color:#2b070d;">Your submitted details</p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="padding:5px 0;font-size:13px;color:#8a1c30;width:38%;vertical-align:top;">Student ID</td>
                                    <td style="padding:5px 0;font-size:13px;color:#2b070d;font-weight:600;">{{ $loa->student_id }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:5px 0;font-size:13px;color:#8a1c30;vertical-align:top;">First name</td>
                                    <td style="padding:5px 0;font-size:13px;color:#2b070d;font-weight:600;">{{ $loa->first_name ?: $loa->full_name }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:5px 0;font-size:13px;color:#8a1c30;vertical-align:top;">Middle name</td>
                                    <td style="padding:5px 0;font-size:13px;color:#2b070d;font-weight:600;">{{ $loa->middle_name ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:5px 0;font-size:13px;color:#8a1c30;vertical-align:top;">Last name</td>
                                    <td style="padding:5px 0;font-size:13px;color:#2b070d;font-weight:600;">{{ $loa->last_name ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:5px 0;font-size:13px;color:#8a1c30;vertical-align:top;">Department</td>
                                    <td style="padding:5px 0;font-size:13px;color:#2b070d;font-weight:600;">{{ $loa->department?->name ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:5px 0;font-size:13px;color:#8a1c30;vertical-align:top;">Program</td>
                                    <td style="padding:5px 0;font-size:13px;color:#2b070d;font-weight:600;">{{ $loa->program?->name ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:5px 0;font-size:13px;color:#8a1c30;vertical-align:top;">Year level</td>
                                    <td style="padding:5px 0;font-size:13px;color:#2b070d;font-weight:600;">{{ $loa->year_level ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:5px 0;font-size:13px;color:#8a1c30;vertical-align:top;">Semester</td>
                                    <td style="padding:5px 0;font-size:13px;color:#2b070d;font-weight:600;">{{ $loa->academicTermLabel() }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:5px 0;font-size:13px;color:#8a1c30;vertical-align:top;">Leave period</td>
                                    <td style="padding:5px 0;font-size:13px;color:#2b070d;font-weight:600;">
                                        {{ $loa->start_date?->format('M j, Y') }} — {{ $loa->return_date?->format('M j, Y') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:5px 0;font-size:13px;color:#8a1c30;vertical-align:top;">Reason</td>
                                    <td style="padding:5px 0;font-size:13px;color:#2b070d;">{{ $loa->reason }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:5px 0;font-size:13px;color:#8a1c30;vertical-align:top;">Parent / Guardian</td>
                                    <td style="padding:5px 0;font-size:13px;color:#2b070d;font-weight:600;">
                                        {{ $loa->parent_full_name }} ({{ $loa->parent_relationship }})<br>
                                        {{ $loa->parent_phone }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:5px 0;font-size:13px;color:#8a1c30;vertical-align:top;">Date submitted</td>
                                    <td style="padding:5px 0;font-size:13px;color:#2b070d;font-weight:600;">{{ $loa->submitted_at?->format('M j, Y g:i A') }}</td>
                                </tr>
                                @if ($loa->attachments->isNotEmpty())
                                <tr>
                                    <td style="padding:5px 0;font-size:13px;color:#8a1c30;vertical-align:top;">Attachments</td>
                                    <td style="padding:5px 0;font-size:13px;color:#2b070d;">
                                        {{ $loa->attachments->count() }} file(s) uploaded:<br>
                                        @foreach ($loa->attachments as $file)
                                            <span style="display:block;margin-top:3px;">
                                                {{ $loop->iteration }}. {{ $file->original_name }}
                                                <span style="color:#8a1c30;">({{ number_format($file->size / 1024, 1) }} KB)</span>
                                            </span>
                                        @endforeach
                                    </td>
                                </tr>
                                @else
                                <tr>
                                    <td style="padding:5px 0;font-size:13px;color:#8a1c30;vertical-align:top;">Attachments</td>
                                    <td style="padding:5px 0;font-size:13px;color:#8a1c30;font-style:italic;">None submitted</td>
                                </tr>
                                @endif
                            </table>
                        </td>
                    </tr>

                    {{-- What happens next --}}
                    <tr>
                        <td style="padding:24px 32px 0;">
                            <p style="margin:0 0 10px;font-size:14px;font-weight:700;color:#2b070d;">What happens next</p>
                            <p style="margin:0 0 8px;font-size:13px;line-height:1.7;color:#4a0e18;">
                                @if ($loa->isExtension())
                                    The President will review this extension first. After that, Department Head, SASO, and Campus Director follow the same sequence as a new leave.
                                @else
                                    Your Department Head will review your application and may contact your parent or guardian at the number you provided.
                                @endif
                                You will receive updates by email as your request moves through each step.
                            </p>
                        </td>
                    </tr>

                    {{-- Footer note --}}
                    <tr>
                        <td style="padding:24px 32px;">
                            <p style="margin:0;font-size:12px;line-height:1.6;color:#8a1c30;">
                                If you did not submit this request, please contact the SASO Office immediately.
                            </p>
                        </td>
                    </tr>

                    {{-- Footer bar --}}
                    <tr>
                        <td style="background-color:#fdf7f4;padding:16px 32px;border-top:1px solid #f6ebe6;">
                            <p style="margin:0;font-size:12px;color:#8a1c30;">LeaveFlow · Eastern Visayas State University — Ormoc Campus</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
