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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            border-left: 4px solid #4F46E5;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .info-box strong {
            color: #4F46E5;
            display: block;
            margin-bottom: 5px;
        }
        .role-specific {
            background-color: #e7f3ff;
            border-left: 4px solid #0066cc;
            padding: 20px;
            margin: 25px 0;
            border-radius: 5px;
        }
        .role-specific h3 {
            color: #0066cc;
            margin-top: 0;
            font-size: 18px;
        }
        .role-specific ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .role-specific li {
            margin: 8px 0;
        }
        .next-steps {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            padding: 20px;
            margin: 25px 0;
            border-radius: 5px;
        }
        .next-steps h3 {
            color: #155724;
            margin-top: 0;
            font-size: 18px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #4F46E5;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
            text-align: center;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            color: #666;
            font-size: 14px;
        }
        .badge-icon {
            font-size: 48px;
            margin: 10px 0;
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
            <div class="badge-icon">🎉</div>
            <h2>Registration Confirmed!</h2>
            <p style="margin: 10px 0 0 0;">You're all set for an amazing experience</p>
        </div>

        <div class="content">
            <p class="greeting">Dear {{ $attendee->name }},</p>

            <p>Congratulations! Your registration for <strong>{{ $event->name }}</strong> has been successfully confirmed.</p>

            <div class="info-box">
                <strong>Your Registration Details:</strong>
                <p style="margin: 5px 0;">
                    <strong>Name:</strong> {{ $attendee->name }}<br>
                    <strong>Email:</strong> {{ $attendee->email }}<br>
                    <strong>Registration Type:</strong> {{ ucfirst($attendee->category) }}<br>
                    @if($attendee->company)
                        <strong>Company:</strong> {{ $attendee->company }}<br>
                    @endif
                    @if($attendee->title)
                        <strong>Title:</strong> {{ $attendee->title }}<br>
                    @endif
                </p>
            </div>

            <div class="info-box">
                <strong>Event Information:</strong>
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

            @if($attendee->category === 'exhibitor')
            <div class="role-specific">
                <h3>🏢 Exhibitor Benefits</h3>
                <p>As an exhibitor, you'll enjoy exclusive benefits:</p>
                <ul>
                    <li>Dedicated booth space for showcasing your products/services</li>
                    <li>Access to all event sessions and networking opportunities</li>
                    <li>Lead retrieval system for connecting with attendees</li>
                    <li>Marketing materials in the event guide</li>
                    <li>Early access to the venue for setup</li>
                </ul>
                <p><strong>Next:</strong> Our team will contact you soon regarding booth allocation and setup details.</p>
            </div>
            @elseif($attendee->category === 'visitor')
            <div class="role-specific">
                <h3>👤 Visitor Access</h3>
                <p>As a visitor, you'll have access to:</p>
                <ul>
                    <li>All exhibition halls and booths</li>
                    <li>Networking sessions and meet-and-greet opportunities</li>
                    <li>Product demonstrations and presentations</li>
                    <li>Event materials and resources</li>
                    <li>Refreshments and dining areas</li>
                </ul>
                <p><strong>Tip:</strong> Come prepared with questions for exhibitors and bring plenty of business cards!</p>
            </div>
            @elseif($attendee->category === 'speaker')
            <div class="role-specific">
                <h3>🎤 Speaker Information</h3>
                <p>Thank you for being part of our speaker lineup:</p>
                <ul>
                    <li>VIP access to all areas and sessions</li>
                    <li>Dedicated speaker lounge with refreshments</li>
                    <li>Technical support and rehearsal time</li>
                    <li>Recording of your session (upon request)</li>
                    <li>Enhanced networking opportunities</li>
                </ul>
                <p><strong>Important:</strong> Our event coordinator will reach out with your session details and technical requirements.</p>
            </div>
            @else
            <div class="role-specific">
                <h3>✨ Guest Access</h3>
                <p>We're delighted to have you as our guest:</p>
                <ul>
                    <li>Access to designated event areas</li>
                    <li>Networking opportunities with other attendees</li>
                    <li>Refreshments and hospitality services</li>
                </ul>
            </div>
            @endif

            <div class="next-steps">
                <h3>📋 Next Steps:</h3>
                <ol style="margin: 10px 0; padding-left: 20px;">
                    <li><strong>Check your email</strong> for your event badge (arriving separately)</li>
                    <li><strong>Print your badge</strong> or save it to your mobile device</li>
                    <li><strong>Mark your calendar</strong> for {{ $event->date->format('F d, Y') }}@if($event->end_date) - {{ \Carbon\Carbon::parse($event->end_date)->format('F d, Y') }}@endif</li>
                </ol>
            </div>

            <p>If you have any questions or need assistance before the event, please don't hesitate to reach out to our support team.</p>

            <p>We're excited to see you at {{ $event->name }}!</p>

            <p style="margin-top: 30px;">
                <strong>Best regards,</strong><br>
                The {{ config('app.name', 'Creative Hub') }} Team
            </p>
        </div>

        <div class="footer">
            <p>This is an automated email. Please do not reply to this message.</p>
            <p>If you need to update your registration, please contact our support team.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'Creative Hub') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
