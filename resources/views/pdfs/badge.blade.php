<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Badge - {{ $attendee->name }}</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 20mm;
        }
        .badge-container {
            width: 85mm;
            height: 125mm;
            margin: 0 auto;
            border: 2px solid #667eea;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            background: linear-gradient(135deg, #f6f8fb 0%, #e9f0f5 100%);
        }
        .badge-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .event-logo {
            max-width: 60mm;
            max-height: 30mm;
            margin-bottom: 10px;
        }
        .event-name {
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
        }
        .attendee-name {
            font-size: 24px;
            font-weight: bold;
            color: #1a202c;
            margin: 15px 0;
            min-height: 30px;
        }
        .attendee-company {
            font-size: 14px;
            color: #4a5568;
            margin: 10px 0;
            min-height: 20px;
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
            font-size: 12px;
        }
        .qr-code {
            margin: 15px auto;
            text-align: center;
        }
        .qr-code img {
            width: 50mm;
            height: 50mm;
        }
        .instructions {
            font-size: 10px;
            color: #718096;
            margin-top: 15px;
            padding: 10px;
            background: white;
            border-radius: 5px;
            text-align: left;
        }
        .footer {
            margin-top: 15px;
            font-size: 9px;
            color: #a0aec0;
        }
    </style>
</head>
<body>
    <div class="badge-container">
        <div class="badge-header">
            @if($event->logo)
                <img src="{{ public_path('storage/' . $event->logo) }}" alt="Event Logo" class="event-logo">
            @endif
            <div class="event-name">{{ $event->name }}</div>
        </div>

        <div class="attendee-name">{{ $attendee->name }}</div>

        @if($attendee->company)
            <div class="attendee-company">{{ $attendee->company }}</div>
        @endif

        <div class="badge-type">{{ ucfirst($attendee->type ?? 'Attendee') }}</div>

        <div class="qr-code">
            {!! $qrCode !!}
        </div>

        <div class="instructions">
            <strong>Instructions:</strong><br>
            • Present this badge at reception<br>
            • Keep visible during event<br>
            • QR code required for entry
        </div>

        <div class="footer">
            {{ $event->name }}<br>
            {{ \Carbon\Carbon::parse($event->start_date)->format('F d, Y') }}
        </div>
    </div>
</body>
</html>
