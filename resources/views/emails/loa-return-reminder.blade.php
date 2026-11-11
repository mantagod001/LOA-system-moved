<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your leave ends in 7 days</title>
</head>
<body style="margin:0;padding:0;background-color:#f6ebe6;font-family:Arial,Helvetica,sans-serif;color:#2b070d;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f6ebe6;padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:560px;background-color:#ffffff;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="background-color:#4a0e18;padding:28px 32px;">
                            <p style="margin:0;font-size:12px;letter-spacing:0.16em;text-transform:uppercase;color:#d4b36a;">EVSU Ormoc Campus</p>
                            <h1 style="margin:8px 0 0;font-size:22px;line-height:1.3;color:#fdf7f4;font-weight:700;">Your leave ends in 7 days</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 32px 0;">
                            <p style="margin:0 0 6px;font-size:13px;color:#8a1c30;text-transform:uppercase;letter-spacing:0.1em;">Control Number</p>
                            <p style="margin:0;font-family:monospace;font-size:22px;font-weight:700;color:#4a0e18;letter-spacing:0.05em;">{{ $loa->control_number }}</p>
                        </td>
                    </tr>
                    <tr><td style="padding:20px 32px 0;"><hr style="border:none;border-top:1px solid #f6ebe6;"></td></tr>
                    <tr>
                        <td style="padding:20px 32px 32px;">
                            <p style="margin:0 0 12px;font-size:15px;line-height:1.7;color:#2b070d;">Dear <strong>{{ $loa->full_name }}</strong>,</p>
                            <p style="margin:0 0 12px;font-size:15px;line-height:1.7;color:#2b070d;">
                                Your approved leave ends on <strong>{{ $loa->return_date?->format('F j, Y') }}</strong>.
                            </p>
                            <p style="margin:0 0 12px;font-size:15px;line-height:1.7;color:#2b070d;">
                                On that date, go to school. If you are returning, enroll at the Registrar. LeaveFlow does not enroll you.
                            </p>
                            <p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#2b070d;">
                                If you need more time, use Extend leave before that date. Use the same student number and EVSU email as this leave.
                            </p>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 16px;">
                                <tr>
                                    <td style="border-radius:8px;background-color:#4a0e18;">
                                        <a href="{{ $extendUrl }}" style="display:inline-block;padding:12px 24px;font-size:14px;font-weight:700;color:#ffffff;text-decoration:none;border-radius:8px;">Extend leave</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:0;font-size:13px;line-height:1.7;color:#4a0e18;">
                                If you already sent an extension request, wait for that decision. This email does not extend your leave.
                            </p>
                        </td>
                    </tr>
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
