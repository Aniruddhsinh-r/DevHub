<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f3f4f6; margin: 0; padding: 10px 0; width: 100%; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f3f4f6; width: 100%; margin: 0; padding: 0;">
        <tr>
            <td align="center" style="padding: 0 16px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 500px; background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03); margin: 0 auto;">
                    <tr>
                        <td align="center" style="padding: 24px; border-bottom: 1px solid #f3f4f6; background-color: #ffffff;">
                            <span style="font-size: 18px; font-weight: 800; color: #4f46e5; letter-spacing: -0.03em;">
                                {{ config('app.name', 'MyPlatform') }}
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 32px 24px; background-color: #ffffff;">
                            <h2 style="font-size: 18px; font-weight: 700; color: #111827; margin-top: 0; margin-bottom: 12px; letter-spacing: -0.01em;">Security Verification Code</h2>
                            <p style="font-size: 14px; line-height: 1.6; color: #4b5563; margin-top: 0; margin-bottom: 24px;">We received a request to update your account password security credentials. Use the 6-digit dynamic code down below to authorized and complete the changes:</p>

                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0;">
                                <tr>
                                    <td align="center">
                                        <div style="background-color: #fafafa; color: #111827; padding: 14px 28px; border-radius: 12px; font-size: 26px; font-weight: 800; letter-spacing: 6px; display: inline-block; border: 1px solid #e5e7eb; font-family: 'Courier New', Courier, monospace;">{{ $msg }}</div>
                                    </td>
                                </tr>
                            </table>
                            <p style="font-size: 13px; line-height: 1.5; color: #6b7280; margin-top: 24px; margin-bottom: 0;">This secure identification code will automatically expire in 10 minutes. If you did not issue this verification action, no further actions are necessary.</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 20px 24px; background-color: #fafafa; border-top: 1px solid #f3f4f6; text-align: center;">
                            <p style="font-size: 11px; color: #9ca3af; margin: 0 0 6px 0; font-weight: 600;">
                                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                            </p>
                            <p style="font-size: 11px; color: #9ca3af; margin: 0; line-height: 1.4;">
                                This message was dispatched directly as an automated secure administrative account update alert.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
