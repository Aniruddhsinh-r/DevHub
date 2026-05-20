<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome Back</title>
</head>
<body style="margin:0; padding:40px 20px; background:#f5f7fb; font-family:Arial, Helvetica, sans-serif; color:#111827;">
    <div style="max-width:620px; margin:auto; background:#ffffff; border-radius:28px; overflow:hidden; border:1px solid #e5e7eb;">
        <div style="padding:32px 40px; background:#111827;">
            <h1 style="margin:0; color:#ffffff; font-size:28px; font-weight:800; letter-spacing:-0.03em;">
                Welcome Back 👋
            </h1>

            <p style="margin:12px 0 0; color:#9ca3af; font-size:15px; line-height:1.7;">
                Your account was successfully signed in.
            </p>
        </div>

        <div style="padding:40px;">
            <h2 style="margin:0 0 18px; font-size:20px; font-weight:800; color:#111827;">
                Hello, {{ $msg }}
            </h2>

            <p style="margin:0; font-size:15px; line-height:1.9; color:#4b5563;">
                We noticed a successful login to your account. Everything looks good and your account is active.
            </p>

            <p style="margin:22px 0 0; font-size:15px; line-height:1.9; color:#4b5563;">
                If this login wasn't made by you, we strongly recommend changing your password immediately to keep your account secure.
            </p>

            <div style="margin-top:30px; padding:20px; border-radius:20px; background:#f9fafb; border:1px solid #e5e7eb;">
                <p style="margin:0; font-size:14px; font-weight:700; color:#111827;">
                    Security Tip
                </p>
                <p style="margin:10px 0 0; font-size:14px; line-height:1.8; color:#6b7280;">
                    Never share your password with anyone and always use a strong unique password for your account.
                </p>
            </div>

            <div style="margin-top:40px; padding-top:24px; border-top:1px solid #e5e7eb;">
                <p style="margin:0; font-size:15px; color:#111827; font-weight:700;">
                    — The Team
                </p>

                <p style="margin:10px 0 0; font-size:13px; color:#9ca3af; line-height:1.7;">
                    This is an automated security notification sent to protect your account activity.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
