<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LOA Verification Code</title>
</head>
<body style="margin:0;padding:0;background-color:#f6ebe6;font-family:Arial,Helvetica,sans-serif;color:#2b070d;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f6ebe6;padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:480px;background-color:#ffffff;border-radius:16px;overflow:hidden;">

                    {{-- Header --}}
                    <tr>
                        <td style="background-color:#4a0e18;padding:28px 32px;">
                            <p style="margin:0;font-size:12px;letter-spacing:0.16em;text-transform:uppercase;color:#d4b36a;">EVSU Ormoc Campus</p>
                            <h1 style="margin:8px 0 0;font-size:20px;line-height:1.3;color:#fdf7f4;font-weight:700;">LOA Verification Code</h1>
                        </td>
                    </tr>

                    {{-- OTP code --}}
                    <tr>
                        <td style="padding:32px 32px 0;">
                            <p style="margin:0 0 8px;font-size:13px;color:#8a1c30;text-transform:uppercase;letter-spacing:0.1em;">Your one-time code</p>
                            <p style="margin:0;font-family:monospace;font-size:42px;font-weight:700;color:#4a0e18;letter-spacing:0.25em;">{{ $otp }}</p>
                            <p style="margin:10px 0 0;font-size:13px;color:#6b1424;">This code expires in <strong>10 minutes</strong>.</p>
                        </td>
                    </tr>

                    {{-- Divider --}}
                    <tr><td style="padding:20px 32px 0;"><hr style="border:none;border-top:1px solid #f6ebe6;"></td></tr>

                    {{-- Instructions --}}
                    <tr>
                        <td style="padding:20px 32px 0;">
                            <p style="margin:0 0 10px;font-size:14px;line-height:1.7;color:#2b070d;">
                                Enter this code on the LOA request page to verify your EVSU email address
                                (<strong>{{ $email }}</strong>) and continue with your Leave of Absence application.
                            </p>
                            <p style="margin:0 0 10px;font-size:14px;line-height:1.7;color:#2b070d;">
                                <strong>What happens next:</strong> after this code is verified, we email your private form link. Open that link the same day.
                            </p>
                            <p style="margin:0;font-size:13px;color:#8a1c30;">
                                If you did not request this code, you can safely ignore this email.
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color:#fdf7f4;padding:16px 32px;margin-top:24px;border-top:1px solid #f6ebe6;">
                            <p style="margin:0;font-size:12px;color:#8a1c30;">LeaveFlow · Eastern Visayas State University — Ormoc Campus</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
