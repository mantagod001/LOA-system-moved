<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LOA Not Approved</title>
</head>
<body style="margin:0;padding:0;background-color:#f6ebe6;font-family:Arial,Helvetica,sans-serif;color:#2b070d;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f6ebe6;padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:560px;background-color:#ffffff;border-radius:16px;overflow:hidden;">

                    {{-- Header --}}
                    <tr>
                        <td style="background-color:#b91c1c;padding:28px 32px;">
                            <p style="margin:0;font-size:12px;letter-spacing:0.16em;text-transform:uppercase;color:#fecaca;">EVSU Ormoc Campus</p>
                            <h1 style="margin:8px 0 0;font-size:22px;line-height:1.3;color:#ffffff;font-weight:700;">Your Leave of Absence Was Not Approved</h1>
                        </td>
                    </tr>

                    {{-- Control number --}}
                    <tr>
                        <td style="padding:24px 32px 0;">
                            <p style="margin:0 0 6px;font-size:13px;color:#b91c1c;text-transform:uppercase;letter-spacing:0.1em;">Control Number</p>
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
                                We regret to inform you that your Leave of Absence request has been
                                <strong style="color:#b91c1c;">not approved</strong> during the review process.
                            </p>
                        </td>
                    </tr>

                    {{-- Rejection details --}}
                    <tr>
                        <td style="padding:0 32px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#fef2f2;border-left:4px solid #b91c1c;border-radius:0 8px 8px 0;padding:16px 20px;">
                                @if ($loa->rejectedByActor)
                                <tr>
                                    <td style="padding:0 0 8px;font-size:13px;color:#991b1b;">
                                        <strong>Reviewed by:</strong>
                                        {{ $loa->rejectedByActor->name }}
                                        ({{ $loa->rejectedByActor->role->label() }})
                                        @if ($loa->rejected_at)
                                            &mdash; {{ $loa->rejected_at->format('M j, Y \a\t g:i A') }}
                                        @endif
                                    </td>
                                </tr>
                                @endif
                                @if ($loa->rejection_reason)
                                <tr>
                                    <td style="padding:0;font-size:13px;color:#7f1d1d;">
                                        <strong style="display:block;margin-bottom:6px;color:#991b1b;">Reason:</strong>
                                        <span style="display:block;background-color:#ffffff;padding:10px 12px;border-radius:6px;color:#4a0e18;line-height:1.6;">{{ $loa->rejection_reason }}</span>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </td>
                    </tr>

                    {{-- What you can do --}}
                    <tr>
                        <td style="padding:24px 32px 0;">
                            <p style="margin:0 0 8px;font-size:14px;font-weight:700;color:#2b070d;">What You Can Do</p>
                            <p style="margin:0 0 16px;font-size:13px;line-height:1.7;color:#4a0e18;">
                                If you would like to address the concerns raised and resubmit your application,
                                click the button below. The link is valid for <strong>7 days</strong> and can only be used once.
                                If that link expires, contact your Department Head or SASO. Do not start a new Request LOA unless this is a different leave.
                            </p>
                            @if ($resubmitUrl)
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="border-radius:8px;background-color:#4a0e18;">
                                        <a href="{{ $resubmitUrl }}"
                                           style="display:inline-block;padding:12px 24px;font-size:14px;font-weight:700;color:#ffffff;text-decoration:none;border-radius:8px;">
                                            Resubmit My LOA Application
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:10px 0 0;font-size:12px;color:#8a1c30;">
                                Or copy this link: <span style="font-family:monospace;word-break:break-all;">{{ $resubmitUrl }}</span>
                            </p>
                            @else
                            <p style="margin:0;font-size:13px;line-height:1.7;color:#4a0e18;">
                                Please visit the SASO Office or contact your Department Head for guidance on next steps.
                            </p>
                            @endif
                        </td>
                    </tr>

                    {{-- Footer note --}}
                    <tr>
                        <td style="padding:24px 32px;">
                            <p style="margin:0;font-size:12px;line-height:1.6;color:#8a1c30;">
                                For questions or assistance, contact the SASO Office or your Department Head.
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
