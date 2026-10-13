<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your LOA form link</title>
</head>
<body style="margin:0;padding:0;background-color:#f6ebe6;font-family:Arial,Helvetica,sans-serif;color:#2b070d;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f6ebe6;padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:560px;background-color:#ffffff;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="background-color:#4a0e18;padding:28px 32px;">
                            <p style="margin:0;font-size:12px;letter-spacing:0.16em;text-transform:uppercase;color:#d4b36a;">EVSU Ormoc Campus</p>
                            <h1 style="margin:8px 0 0;font-size:24px;line-height:1.3;color:#fdf7f4;font-weight:700;">LeaveFlow</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 12px;font-size:16px;line-height:1.6;color:#2b070d;">Hello{{ filled($accessToken->full_name) ? ' '.$accessToken->full_name : '' }},</p>
                            <p style="margin:0 0 24px;font-size:15px;line-height:1.7;color:#4a0e18;">
                                Use the button below to open your {{ $accessToken->isExtension() ? 'leave extension' : 'Leave of Absence' }} form (EVSU-SASO-F-040). Complete it as soon as you can. The link stops working one day after this email is sent.
                            </p>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin:0 auto 28px;">
                                <tr>
                                    <td align="center" bgcolor="#6b1424" style="border-radius:10px;">
                                        <a href="{{ $formUrl }}" target="_blank" style="display:inline-block;padding:14px 28px;font-size:16px;font-weight:700;color:#fdf7f4;text-decoration:none;">
                                            {{ $accessToken->isExtension() ? 'Open extension form' : 'Open LOA form' }}
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            @if (filled($accessToken->student_id))
                            <p style="margin:0 0 8px;font-size:13px;line-height:1.6;color:#8a1c30;">Student ID: {{ $accessToken->student_id }}</p>
                            @endif
                            <p style="margin:0;font-size:12px;line-height:1.6;color:#8a1c30;">If the button does not open the form, copy the full address below into your browser (one line, nothing missing):</p>
                            <p style="margin:8px 0 0;font-size:12px;line-height:1.5;color:#6b1424;"><a href="{{ $formUrl }}" style="color:#6b1424;text-decoration:underline;word-break:break-all;">{{ $formUrl }}</a></p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 32px 32px;">
                            <p style="margin:0;font-size:12px;line-height:1.6;color:#8a1c30;">
                                If you did not request a Leave of Absence, you can ignore this email.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#fdf7f4;padding:18px 32px;border-top:1px solid #f6ebe6;">
                            <p style="margin:0;font-size:12px;color:#8a1c30;">LeaveFlow · Eastern Visayas State University — Ormoc Campus</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
