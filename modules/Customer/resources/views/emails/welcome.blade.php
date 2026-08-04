<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Welcome to World Art Atlas') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            color: #111827;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            padding: 28px 24px 32px;
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.12);
        }
        .email-header {
            margin-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 12px;
        }
        .email-title {
            font-size: 22px;
            margin: 0;
            font-weight: 700;
            letter-spacing: 0.02em;
        }
        .brand {
            font-size: 13px;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: 0.18em;
            margin-bottom: 4px;
        }
        .email-body {
            font-size: 14px;
            line-height: 1.7;
            margin-top: 10px;
        }
        .highlight-box {
            margin: 20px 0;
            padding: 16px 18px;
            border-radius: 10px;
            background: linear-gradient(135deg, #111827, #020617);
            color: #e5e7eb;
        }
        .highlight-title {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .highlight-text {
            font-size: 13px;
            margin: 0;
        }
        .list {
            padding-left: 18px;
            margin: 14px 0;
        }
        .list li {
            margin-bottom: 6px;
        }
        .cta {
            margin-top: 22px;
        }
        .cta-button {
            display: inline-block;
            padding: 10px 18px;
            border-radius: 999px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            background: #111827;
            color: #f9fafb;
        }
        .footer {
            margin-top: 26px;
            font-size: 11px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
<div class="email-wrapper">
    <div class="email-header">
        <div class="brand">{{ __('WORLD ART ATLAS') }}</div>
        <h1 class="email-title">
            {{ __('Welcome to World Art Atlas') }}
        </h1>
    </div>

    <div class="email-body">
        <p>
            {{ __('Hello') }}
            {{ $customer?->name ?: '' }}{{ $customer?->surname ? ' ' . $customer->surname : '' }},
        </p>

        <p>
            {{ __('Your email has been successfully verified, and your account is now active.') }}
        </p>

        <div class="highlight-box">
            <div class="highlight-title">
                {{ __('You are now part of the global art & sound map.') }}
            </div>
            <p class="highlight-text">
                {{ __('Claim your squares on the world map, upload your photos and music, and share your culture with the world.') }}
            </p>
        </div>

        <p>
            {{ __('With your account you can:') }}
        </p>
        <ul class="list">
            <li>{{ __('Manage your artist profile and media gallery.') }}</li>
            <li>{{ __('Reserve and purchase squares on the world map.') }}</li>
            <li>{{ __('Upload photos and audio that represent your culture and creativity.') }}</li>
        </ul>

        <div class="cta">
            <span style="font-size: 13px; margin-right: 8px;">
                {{ __('You can now sign in anytime using your email and password.') }}
            </span>
        </div>

        <p style="margin-top: 20px;">
            {{ __('Thank you for joining,') }}<br>
            {{ __('World Art Atlas Team') }}
        </p>
    </div>

    <div class="footer">
        {{ __('This is an automated message, please do not reply to this email.') }}
    </div>
</div>
</body>
</html>
