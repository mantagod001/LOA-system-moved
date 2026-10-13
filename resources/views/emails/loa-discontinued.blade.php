<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LOA Discontinued</title>
</head>
<body style="margin:0;padding:0;background-color:#f6ebe6;font-family:Arial,Helvetica,sans-serif;color:#2b070d;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f6ebe6;padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:560px;background-color:#ffffff;border-radius:16px;overflow:hidden;">

                    {{-- Header --}}
                    <tr>
                        <td style="background-color:#374151;padding:28px 32px;">
                            <p style="margin:0;font-size:12px;letter-spacing:0.16em;text-transform:uppercase;color:#d1d5db;">EVSU Ormoc Campus</p>
                            <h1 style="margin:8px 0 0;font-size:22px;line-height:1.3;color:#ffffff;font-weight:700;">Your LOA has been Discontinued</h1>
                        </td>
                    </tr>

                    {{-- Control number --}}
                    <tr>
                        <td style="padding:24px 32px 0;">
                            <p style="margin:0 0 6px;font-size:13px;color:#374151;text-transform:uppercase;letter-spacing:0.1em;">Control Number</p>
                            <p style="margin:0;font-family:monospace;font-size:22px;font-weight:700;color:#4a0e18;letter-spacing:0.05em;">{{ $loa->control_number }}</p>
                        </td>
                    </tr>

                    {{-- Divider --}}
                    <tr><td style="padding:20px 32px 0;"><hr style="border:none;border-top:1px solid #f6ebe6;"></td></tr>

                    {{-- Message --}}
                    <tr>
                        <td style="padding:20px 32px 0;">
                            <p style="margin:0 0 12px;font-size:15px;line-height:1.7;color:#2b070d;">
                                Dear <strong>{{ $loa->full_name }}</strong>,
                            </p>
                            <p style="margin:0 0 12px;font-size:15px;line-height:1.7;color:#2b070d;">
                                Your Leave of Absence application has been marked as
                                <strong style="color:#374151;">discontinued / withdrawn</strong>.
                            </p>
                        </td>
                    </tr>

                    {{-- Details --}}
                    @if ($loa->discontinuedByActor || $loa->discontinuation_reason)
                    <tr>
                        <td style="padding:0 32px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f3f4f6;border-left:4px solid #374151;border-radius:0 8px 8px 0;padding:16px 20px;">
                                @if ($loa->discontinuedByActor)
                                <tr>
                                    <td style="padding:0 0 8px;font-size:13px;color:#374151;">
                                        <strong>Recorded by:</strong>
                                        {{ $loa->discontinuedByActor->name }}
                                        @if ($loa->discontinuedByActor->role)
                                            ({{ $loa->discontinuedByActor->role->label() }})
                                        @endif
                                        @if ($loa->discontinued_at)
                                            &mdash; {{ $loa->discontinued_at->format('M j, Y \a\t g:i A') }}
                                        @endif
                                    </td>
                                </tr>
                                @endif
                                @if ($loa->discontinuation_reason)
                                <tr>
                                    <td style="font-size:13px;color:#4b5563;">
                                        <strong style="display:block;margin-bottom:6px;color:#374151;">Reason:</strong>
                                        <span style="display:block;background-color:#ffffff;padding:10px 12px;border-radius:6px;color:#2b070d;line-height:1.6;">{{ $loa->discontinuation_reason }}</span>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </td>
                    </tr>
                    @endif

                    {{-- What to do --}}
                    <tr>
                        <td style="padding:24px 32px 0;">
                            <p style="margin:0 0 16px;font-size:13px;line-height:1.7;color:#4a0e18;">
                                You can confirm this on Check LOA Status using your control number and student ID:
                            </p>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 16px;">
                                <tr>
                                    <td align="center" bgcolor="#4a0e18" style="border-radius:10px;">
                                        <a href="{{ $statusUrl }}" target="_blank" style="display:inline-block;padding:12px 22px;font-size:14px;font-weight:700;color:#fdf7f4;text-decoration:none;">
                                            Check LOA status
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:0;font-size:13px;line-height:1.7;color:#4a0e18;">
                                If you have questions or believe this was done in error, please contact the SASO Office or your Department Head.
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding:24px 32px;">
                            <p style="margin:0;font-size:12px;color:#8a1c30;">For assistance, contact the SASO Office or your Department Head.</p>
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
