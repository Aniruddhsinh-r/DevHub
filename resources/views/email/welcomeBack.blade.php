<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Back</title>
</head>
<body style="margin:0; padding:0; background-color:#f5f7fb; font-family:Arial, Helvetica, sans-serif; color:#111827;-webkit-font-smoothing:antialiased;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width:500px; margin:0 auto; background-color:#ffffff; border:1px solid #e5e7eb;">
        <tr>
            <td>
                <div style="padding:16px 16px 12px 16px; background-color:#111827;">
                    <h1 style="margin:0; color:#ffffff; font-size:20px; font-weight:800; letter-spacing:-0.02em;">
                        Welcome Back 👋
                    </h1>
                    <p style="margin:4px 0 0 0; color:#9ca3af; font-size:13px; line-height:1.4;">
                        Your account was successfully signed in.
                    </p>
                </div>

                <div style="padding:16px;">
                    <h2 style="margin:0 0 6px 0; font-size:16px; font-weight:700; color:#111827;">
                        Hello, {{ $msg }}
                    </h2>

                    <p style="margin:0 0 10px 0; font-size:13px; line-height:1.5; color:#4b5563;">
                        We noticed a successful login to your account. Everything looks good and your account is active.
                    </p>

                    <p style="margin:0 0 14px 0; font-size:13px; line-height:1.5; color:#4b5563;">
                        If this login wasn't made by you, we strongly recommend changing your password immediately to keep your account secure.
                    </p>

                    <div style="padding:10px 12px; border-radius:8px; background-color:#f9fafb; border:1px solid #e5e7eb;">
                        <p style="margin:0 0 2px 0; font-size:12px; font-weight:700; color:#111827;">
                            Security Tip
                        </p>
                        <p style="margin:0; font-size:12px; line-height:1.4; color:#6b7280;">
                            Never share your password with anyone and always use a strong unique password for your account.
                        </p>
                    </div>

                    <div style="margin-top:16px; padding-top:12px; border-top:1px solid #e5e7eb;">
                        <p style="margin:0; font-size:13px; color:#111827; font-weight:700;">
                            — The Team
                        </p>
                        <p style="margin:4px 0 0 0; font-size:11px; color:#9ca3af; line-height:1.4;">
                            This is an automated security notification sent to protect your account activity.
                        </p>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
