<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Event Badge</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 650px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            background-color: #ffffff;
            border-radius: 15px;
            padding: 0;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            text-align: center;
            padding: 40px 20px;
            color: white;
        }
        .logo {
            max-width: 180px;
            margin-bottom: 20px;
            background: white;
            padding: 15px;
            border-radius: 10px;
        }
        h1 {
            color: #ffffff;
            margin: 0;
            font-size: 32px;
            font-weight: bold;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .subtitle {
            color: rgba(255,255,255,0.95);
            font-size: 16px;
            margin-top: 10px;
        }
        .content {
            padding: 40px;
        }
        .greeting {
            font-size: 20px;
            margin-bottom: 20px;
            color: #1a202c;
            font-weight: 600;
        }
        .info-box {
            background: linear-gradient(135deg, #f6f8fb 0%, #e9f0f5 100%);
            border-left: 5px solid #667eea;
            padding: 25px;
            margin: 25px 0;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .info-box h3 {
            color: #667eea;
            margin-top: 0;
            font-size: 20px;
            margin-bottom: 15px;
        }
        .info-item {
            margin: 12px 0;
            padding: 8px 0;
            border-bottom: 1px solid rgba(102, 126, 234, 0.1);
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .info-label {
            color: #667eea;
            font-weight: 600;
            display: inline-block;
            min-width: 140px;
        }
        .info-value {
            color: #2d3748;
            font-weight: 500;
        }
        .reception-box {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-left: 5px solid #f59e0b;
            padding: 25px;
            margin: 30px 0;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.15);
        }
        .reception-box h3 {
            color: #92400e;
            margin-top: 0;
            font-size: 22px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .icon {
            font-size: 28px;
            margin-right: 10px;
        }
        .reception-box ul {
            margin: 15px 0;
            padding-left: 25px;
            list-style: none;
        }
        .reception-box li {
            margin: 12px 0;
            color: #78350f;
            position: relative;
            padding-left: 25px;
            font-size: 15px;
            line-height: 1.6;
        }
        .reception-box li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #f59e0b;
            font-weight: bold;
            font-size: 18px;
        }
        .highlight-box {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            border-left: 5px solid #10b981;
            padding: 20px;
            margin: 25px 0;
            border-radius: 8px;
            text-align: center;
        }
        .highlight-box strong {
            color: #065f46;
            font-size: 18px;
            display: block;
            margin-bottom: 10px;
        }
        .badge-type {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            margin: 10px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 14px;
        }
        .qr-notice {
            background: #e0e7ff;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
        .qr-notice p {
            margin: 8px 0;
            color: #3730a3;
            font-weight: 500;
        }
        .footer {
            background: #f9fafb;
            text-align: center;
            padding: 30px 20px;
            color: #6b7280;
            font-size: 14px;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            margin: 8px 0;
        }
        .contact-info {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 2px dashed #d1d5db;
            text-align: center;
        }
        @media only screen and (max-width: 600px) {
            .container {
                border-radius: 0;
            }
            .content {
                padding: 25px;
            }
            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if(config('app.logo'))
                <img src="{{ config('app.logo') }}" alt="Logo" class="logo">
            @endif
            <h1>🎫 Your Event Badge is Ready!</h1>
            <p class="subtitle">Welcome to {{ $event->name }}</p>
        </div>

        <div class="content">
            <p class="greeting">Hello {{ $attendee->name }},</p>

            <p style="font-size: 16px; color: #4a5568;">
                Thank you for registering! Your personalized event badge is attached to this email. Please review the instructions below for check-in on the day of the event.
            </p>

            <div class="info-box">
                <h3>📋 Event Information</h3>
                <div class="info-item">
                    <span class="info-label">Event Name:</span>
                    <span class="info-value">{{ $event->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Date:</span>
                    <span class="info-value">
                        {{ $event->date->format('F d, Y') }}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Location:</span>
                    <span class="info-value">{{ $event->location ?? 'To Be Announced' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Your Badge Type:</span>
                    <span class="badge-type">{{ ucfirst($attendee->type ?? $attendee->category ?? 'Attendee') }}</span>
                </div>
            </div>

            <div class="highlight-box">
                <strong>📎 Your Badge is Attached</strong>
                <p style="margin: 10px 0 0 0; color: #065f46;">
                    File: <strong>{{ $attendee->name }}_Badge.pdf</strong>
                </p>
            </div>

            <div class="reception-box">
                <h3><span class="icon">🎯</span> IMPORTANT: Check-In Instructions</h3>
                <ul>
                    <li><strong>Print Your Badge:</strong> Download and print the attached PDF on A4 paper (cardstock recommended for durability)</li>
                    <li><strong>Arrive at the Event:</strong> Present your printed badge at the registration/reception desk upon arrival</li>
                    <li><strong>Alternative:</strong> You may also show the badge PDF on your mobile device if printing is not possible</li>
                    <li><strong>QR Code Verification:</strong> Reception staff will scan your QR code to verify your registration and grant access</li>
                    <li><strong>Keep it Visible:</strong> Wear or display your badge throughout the event for identification and access to sessions</li>
                </ul>
            </div>

            <div class="qr-notice">
                <p><strong>🔒 Your Unique QR Code</strong></p>
                <p>Your badge contains a unique QR code linked to your registration. This ensures secure and quick check-in at the event.</p>
            </div>

            <div class="contact-info">
                <p style="margin: 0; color: #374151; font-weight: 600;">Need Assistance?</p>
                <p style="margin: 8px 0 0 0; color: #6b7280;">
                    If you have any questions about your badge or event registration, please contact our support team before the event day.
                </p>
            </div>

            <p style="font-size: 17px; font-weight: 600; color: #1a202c; margin-top: 30px;">
                We look forward to seeing you at {{ $event->name }}!
            </p>

            <p style="color: #718096; margin-top: 20px;">
                Best regards,<br>
                <strong>{{ config('app.name', 'Creative Hub') }} Team</strong>
            </p>
        </div>

        <div class="footer">
            <p style="font-weight: 600; margin-bottom: 5px;">{{ config('app.name', 'Creative Hub') }}</p>
            <p>This is an automated email. Please do not reply to this message.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'Creative Hub') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
