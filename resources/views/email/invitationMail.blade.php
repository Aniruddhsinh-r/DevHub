<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invitation Mail</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #334155; max-width: 550px; margin: 40px auto; padding: 20px; background-color: #f8fafc;">
    <div style="background-color: #ffffff; padding: 40px; rounded-radius: 12px; border: 1px solid #e2e8f0; border-radius: 12px; shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);">
        <h2 style="color: #0f172a; font-size: 22px; font-weight: 700; margin-top: 0; margin-bottom: 12px; tracking-tight: -0.025em;">You have been invited!</h2>

        <p style="margin-top: 0; margin-bottom: 24px; font-size: 15px; color: #475569;">
            An administrator has generated an invitation link for you to set up your profile and join the platform.
        </p>

        <p style="margin-bottom: 32px; font-size: 14px; color: #64748b; padding: 10px 14px; background-color: #f1f5f9; border-left: 4px solid #475569; border-radius: 4px;">
            <strong>Important Security Notice:</strong> For security purposes, this dynamic activation link is strictly valid for the next <strong>30 minutes</strong>. After this window closes, the token will expire.
        </p>

        <div style="margin-bottom: 32px; text-align: left;">
            <a href="{{ $msg }}" style="background-color: #0f172a; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px; display: inline-block;">
                Accept Invitation & Setup Account
            </a>
        </div>
        
        <hr style="border: none; border-top: 1px solid #e2e8f0; margin-top: 32px; margin-bottom: 20px;">
        <p style="font-size: 12px; color: #94a3b8; margin-top: 0; margin-bottom: 8px;">If you are having trouble clicking the button above, copy and paste the raw URL configuration below directly into your web browser:</p>
        <p style="font-size: 12px; color: #0284c7; word-break: break-all; margin: 0; font-family: monospace;">
            {{ $msg }}
        </p>
    </div>
</body>
</html>