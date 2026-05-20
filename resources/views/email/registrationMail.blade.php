<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? config('app.name') }}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f9fafb; margin: 0; padding: 40px 16px; color: #111827;">
    <div style="max-w: 550px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 24px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div style="padding: 32px 32px 24px 32px; text-align: center; border-b: 1px solid #f3f4f6;">
            <span style="font-size: 20px; font-weight: 900; tracking-tight: -0.05em; color: #4f46e5;">
                {{ config('app.name', 'MyPlatform') }}
            </span>
        </div>

        <div style="padding: 32px; font-size: 15px; line-height: 1.6; color: #374151;">
            <div>
                <h2 style="font-size: 20px; font-weight: 800; color: #111827; margin-top: 0; margin-bottom: 12px; tracking-tight: -0.02em;">
                    Hi {{ $msg }},
                </h2>

                <p>Thank you for signing up! We are incredibly thrilled to welcome you to our growing community ecosystem.</p>
                <div style="margin: 28px 0; text-align: center;">
                    <a href="{{ url('http://devhub.test/home') }}" style="background-color: #4f46e5; color: #ffffff; padding: 12px 28px; border-radius: 12px; font-size: 14px; font-weight: 700; text-decoration: none; display: inline-block; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);">
                        Get Started & Explore
                    </a>
                </div>
                <p style="margin-bottom: 0;">If you have any questions navigating the platform, just reply to this email directly—our support group is always standing by to help.</p>
            </div>
        </div>

        <div style="padding: 24px 32px; background-color: #fafafa; border-top: 1px solid #f3f4f6; text-align: center; font-size: 12px; color: #6b7280;">
            <p style="margin: 0 0 8px 0; font-weight: 600;">© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p style="margin: 0;">You are receiving this security notification because you hold an active account.</p>
        </div>
    </div>
</body>
</html>
