<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ $event->name }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #4F46E5;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 15px;
        }
        h1 {
            color: #4F46E5;
            margin: 0;
            font-size: 28px;
        }
        .welcome-banner {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            margin: 20px 0;
        }
        .welcome-banner h2 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            margin: 30px 0;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #10b981;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .info-box strong {
            color: #059669;
            display: block;
            margin-bottom: 5px;
        }
        .highlight-box {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            padding: 20px;
            margin: 25px 0;
            border-radius: 5px;
        }
        .highlight-box h3 {
            color: #155724;
            margin-top: 0;
            font-size: 18px;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            color: #666;
            font-size: 14px;
        }
        .check-icon {
            font-size: 48px;
            margin: 10px 0;
        }
        .time-info {
            background-color: #e7f3ff;
            border-left: 4px solid #0066cc;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if($event->logo)
                <img src="{{ asset('storage/' . $event->logo) }}" alt="{{ $event->name }} Logo" class="logo">
            @elseif(config('app.logo'))
                <img src="{{ config('app.logo') }}" alt="Logo" class="logo">
            @endif
            <h1>Welcome to {{ $event->name }}!</h1>
        </div>

        <div class="welcome-banner">
            <div class="check-icon">&#10003;</div>
            <h2>Check-In Successful!</h2>
            <p style="margin: 10px 0 0 0;">You're all checked in and ready to go</p>
        </div>

        <div class="content">
            <p class="greeting">Hello {{ $attendee->name }}!</p>

            <p>Welcome to <strong>{{ $event->name }}</strong>! We're thrilled to have you here with us.</p>

            <div class="time-info">
                <strong style="color: #0066cc; font-size: 16px;">Checked in at:</strong>
                <p style="margin: 5px 0; font-size: 18px; font-weight: bold;">
                    {{ $attendee->checked_in_at->format('g:i A, F j, Y') }}
                </p>
            </div>

            <div class="info-box">
                <strong>Your Details:</strong>
                <p style="margin: 5px 0;">
                    <strong>Name:</strong> {{ $attendee->name }}<br>
                    <strong>Email:</strong> {{ $attendee->email }}<br>
                    <strong>Category:</strong> {{ ucfirst($attendee->category) }}<br>
                    @if($attendee->company)
                        <strong>Company:</strong> {{ $attendee->company }}<br>
                    @endif
                    @if($attendee->title)
                        <strong>Title:</strong> {{ $attendee->title }}<br>
                    @endif
                </p>
            </div>

            @if($attendee->category === 'exhibitor')
            <div class="highlight-box">
                <h3>🏢 Exhibitor Information</h3>
                <p>As an exhibitor, please remember:</p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Your booth location and setup details</li>
                    <li>Access to the exhibitor lounge for refreshments</li>
                    <li>Lead retrieval system for capturing visitor information</li>
                    <li>Event staff are available to assist you throughout the day</li>
                </ul>
            </div>
            @elseif($attendee->category === 'visitor')
            <div class="highlight-box">
                <h3>👤 Visitor Guide</h3>
                <p>Make the most of your visit:</p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Explore all exhibition halls and booths</li>
                    <li>Attend scheduled presentations and demos</li>
                    <li>Network with exhibitors and other visitors</li>
                    <li>Visit the information desk for event schedules</li>
                </ul>
            </div>
            @elseif($attendee->category === 'speaker')
            <div class="highlight-box">
                <h3>🎤 Speaker Instructions</h3>
                <p>Important reminders:</p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Visit the speaker lounge for final preparations</li>
                    <li>Check your session time and location</li>
                    <li>Technical support is available for your presentation</li>
                    <li>Thank you for sharing your expertise!</li>
                </ul>
            </div>
            @else
            <div class="highlight-box">
                <h3>✨ Event Information</h3>
                <p>Here's what you can do:</p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Explore the event venue and facilities</li>
                    <li>Connect with other attendees</li>
                    <li>Visit the information desk for assistance</li>
                    <li>Enjoy the refreshments and hospitality services</li>
                </ul>
            </div>
            @endif

            <div class="info-box">
                <strong>Event Details:</strong>
                <p style="margin: 5px 0;">
                    <strong>Event:</strong> {{ $event->name }}<br>
                    <strong>Date:</strong> {{ $event->date->format('F d, Y') }}
                    @if($event->end_date)
                        - {{ \Carbon\Carbon::parse($event->end_date)->format('F d, Y') }}
                    @endif
                    <br>
                    <strong>Location:</strong> {{ $event->location ?? 'TBA' }}
                </p>
            </div>

            <p>If you need any assistance during the event, please don't hesitate to approach our staff members or visit the information desk.</p>

            <p>Enjoy your time at {{ $event->name }}!</p>

            <p style="margin-top: 30px;">
                <strong>Best regards,</strong><br>
                The {{ config('app.name', 'Creative Hub') }} Team
            </p>
        </div>

        <div class="footer">
            <p>This is an automated email confirming your check-in.</p>
            <p>If you believe this is an error, please contact our support team.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'Creative Hub') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
