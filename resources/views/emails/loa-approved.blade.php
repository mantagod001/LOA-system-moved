<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LOA Approved</title>
</head>
<body style="margin:0;padding:0;background-color:#f6ebe6;font-family:Arial,Helvetica,sans-serif;color:#2b070d;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f6ebe6;padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:560px;background-color:#ffffff;border-radius:16px;overflow:hidden;">

                    {{-- Header --}}
                    <tr>
                        <td style="background-color:#047857;padding:28px 32px;">
                            <p style="margin:0;font-size:12px;letter-spacing:0.16em;text-transform:uppercase;color:#a7f3d0;">EVSU Ormoc Campus</p>
                            <h1 style="margin:8px 0 0;font-size:22px;line-height:1.3;color:#ffffff;font-weight:700;">Your Leave of Absence is Approved</h1>
                        </td>
                    </tr>

                    {{-- Control number --}}
                    <tr>
                        <td style="padding:24px 32px 0;">
                            <p style="margin:0 0 6px;font-size:13px;color:#047857;text-transform:uppercase;letter-spacing:0.1em;">Control Number</p>
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
                                Your Leave of Absence request has been <strong style="color:#047857;">approved</strong> by all required offices.
                                All three approval stages have been completed.
                            </p>
                        </td>
                    </tr>

                    {{-- PDF attachment notice --}}
                    <tr>
                        <td style="padding:4px 32px 16px;">
                            <div style="background-color:#ecfdf5;border:1px solid #a7f3d0;border-radius:10px;padding:12px 16px;">
                                <p style="margin:0;font-size:13px;line-height:1.5;color:#065f46;font-weight:600;">
                                    📎 Official Copy Attached
                                </p>
                                <p style="margin:4px 0 0;font-size:12px;line-height:1.5;color:#047857;">
                                    Your official approved LOA form (<strong>LOA-{{ $loa->control_number }}.pdf</strong>) is attached to this email. You can download and keep it for your personal records or official clearance.
                                </p>
                            </div>
                        </td>
                    </tr>

                    {{-- Approval stages --}}
                    <tr>
                        <td style="padding:0 32px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f0fdf4;border-radius:10px;padding:16px 20px;">
                                @foreach ($loa->timelineStages() as $stage)
                                @php $label = $stage['label']; $actor = $stage['actor']; $at = $stage['at']; @endphp
                                <tr>
                                    <td style="padding:4px 0;font-size:13px;color:#15803d;">
                                        &#10003; <strong>{{ $label }}</strong>
                                        @if ($actor)
                                            <span style="color:#166534;"> &mdash; {{ $actor->name }}</span>
                                        @endif
                                        @if ($at)
                                            <span style="color:#6b7280;font-size:12px;"> ({{ $at->format('M j, Y') }})</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>

                    {{-- Leave details --}}
                    <tr>
                        <td style="padding:20px 32px 0;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="padding:5px 0;font-size:13px;color:#8a1c30;width:38%;vertical-align:top;">Leave period</td>
                                    <td style="padding:5px 0;font-size:13px;color:#2b070d;font-weight:600;">
                                        {{ $loa->start_date?->format('M j, Y') }} &mdash; {{ $loa->return_date?->format('M j, Y') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:5px 0;font-size:13px;color:#8a1c30;vertical-align:top;">Program</td>
                                    <td style="padding:5px 0;font-size:13px;color:#2b070d;font-weight:600;">{{ $loa->program?->name ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:5px 0;font-size:13px;color:#8a1c30;vertical-align:top;">Department</td>
                                    <td style="padding:5px 0;font-size:13px;color:#2b070d;font-weight:600;">{{ $loa->department?->name ?? '—' }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Next steps --}}
                    <tr>
                        <td style="padding:24px 32px 0;">
                            <p style="margin:0 0 8px;font-size:14px;font-weight:700;color:#2b070d;">Next Steps</p>
                            <p style="margin:0;font-size:13px;line-height:1.7;color:#4a0e18;">
                                Please proceed to the Registrar's Office to complete any required clearance procedures before your leave begins.
                                Keep this email and your control number for reference.
                            </p>
                        </td>
                    </tr>

                    {{-- Footer note --}}
                    <tr>
                        <td style="padding:24px 32px;">
                            <p style="margin:0;font-size:12px;line-height:1.6;color:#8a1c30;">
                                For questions, contact the SASO Office or your Department Head.
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
