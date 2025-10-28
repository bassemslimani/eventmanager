<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $campaign->subject }}</title>
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
            font-size: 24px;
        }
        .content {
            margin: 30px 0;
            font-size: 16px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if($event && $event->logo)
                <img src="{{ asset('storage/' . $event->logo) }}" alt="{{ $event->name }}" class="logo">
            @endif
            <h1>{{ $campaign->name }}</h1>
        </div>

        <div class="content">
            <p class="greeting">Dear {{ $attendee->name }},</p>

            <div>
                {!! nl2br(e($campaign->body)) !!}
            </div>

            @if(!empty($campaignAttachments))
            <div style="background-color: #f8f9fa; border-left: 4px solid #4F46E5; padding: 15px; margin: 20px 0; border-radius: 5px;">
                <strong>📎 Attachments:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    @foreach($campaignAttachments as $attachment)
                        <li>{{ basename($attachment) }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        <div class="footer">
            <p>This email was sent to you because you registered for one of our events.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'Creative Hub') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
