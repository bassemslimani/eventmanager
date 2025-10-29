<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Badge - {{ $attendee->name }}</title>
    <style>
        @page {
            size: {{ $badgeWidthCm }}cm {{ $badgeHeightCm }}cm;
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            width: {{ $badgeWidthCm }}cm;
            height: {{ $badgeHeightCm }}cm;
            position: relative;
            overflow: hidden;
            font-family: {{ $template->font_family ?? 'Arial, Helvetica, sans-serif' }};
        }
        .badge-container {
            width: {{ $badgeWidthCm }}cm;
            height: {{ $badgeHeightCm }}cm;
            position: relative;
            background-color: white;
        }
        @if($template->front_template)
        .badge-background {
            position: absolute;
            top: 0;
            left: 0;
            width: {{ $badgeWidthCm }}cm;
            height: {{ $badgeHeightCm }}cm;
            z-index: 0;
        }
        .badge-background img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        @endif
        .element {
            position: absolute;
            z-index: 10;
        }
        .text-element {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .qr-element {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .qr-element svg {
            width: 100%;
            height: 100%;
        }
        .logo-element {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo-element img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <div class="badge-container">
        @if($compressedTemplate)
        <div class="badge-background">
            <img src="{{ $compressedTemplate }}" alt="Badge Background">
        </div>
        @elseif($template->front_template)
        <div class="badge-background">
            <img src="{{ public_path('storage/' . $template->front_template) }}" alt="Badge Background">
        </div>
        @endif

        @if($elements && is_array($elements))
            @foreach($elements as $element)
                @if(isset($element['visible']) && $element['visible'])
                    @php
                        // Convert object to array if needed
                        $elem = is_object($element) ? (array) $element : $element;

                        // Get field value for text elements
                        $fieldValue = '';
                        if ($elem['type'] === 'text' && isset($elem['field'])) {
                            $field = $elem['field'];
                            if (strpos($field, 'static:') === 0) {
                                $fieldValue = substr($field, 7);
                            } elseif ($field === 'event.name') {
                                $fieldValue = $event->name;
                            } elseif ($field === 'event.date') {
                                $fieldValue = $event->date->format('F d, Y');
                            } elseif ($field === 'event.location') {
                                $fieldValue = $event->location ?? '';
                            } elseif ($field === 'attendee.name') {
                                $fieldValue = $attendee->name;
                            } elseif ($field === 'attendee.company') {
                                $fieldValue = $attendee->company ?? '';
                            } elseif ($field === 'attendee.category') {
                                $fieldValue = ucfirst($attendee->type ?? 'Attendee');
                            } elseif ($field === 'attendee.email') {
                                $fieldValue = $attendee->email ?? '';
                            } elseif ($field === 'attendee.phone') {
                                $fieldValue = $attendee->phone ?? '';
                            } elseif ($field === 'attendee.role') {
                                $fieldValue = $attendee->role ?? '';
                                \Log::info("Processing attendee.role field for attendee {$attendee->id}: role value = '{$attendee->role}', fieldValue = '{$fieldValue}'");
                            }
                        }

                        // Calculate positioning - convert from cm
                        $x = floatval($elem['x'] ?? 0);
                        $y = floatval($elem['y'] ?? 0);
                        $width = floatval($elem['width'] ?? 2);
                        $height = floatval($elem['height'] ?? 2);
                        $fontSize = floatval($elem['fontSize'] ?? 12);
                        $fontWeight = $elem['fontWeight'] ?? 'normal';
                        $align = $elem['align'] ?? 'left';
                        $color = $elem['color'] ?? '#000000';
                        $maxWidth = floatval($elem['maxWidth'] ?? 10);

                        // For centered text, adjust x position
                        $leftPos = $x;
                        $textAlign = 'left';
                        if ($align === 'center') {
                            $textAlign = 'center';
                            $leftPos = $x - ($maxWidth / 2);
                        } elseif ($align === 'right') {
                            $textAlign = 'right';
                            $leftPos = $x - $maxWidth;
                        }

                        // For QR codes and logos, center them on the given position
                        if (in_array($elem['type'], ['qrcode', 'logo'])) {
                            $leftPos = $x - ($width / 2);
                            $topPos = $y - ($height / 2);
                        } else {
                            $topPos = $y;
                        }
                    @endphp

                    @if($elem['type'] === 'text')
                        <div class="element text-element" style="
                            left: {{ $leftPos }}cm;
                            top: {{ $topPos }}cm;
                            font-size: {{ $fontSize }}pt;
                            font-weight: {{ $fontWeight }};
                            color: {{ $color }};
                            text-align: {{ $textAlign }};
                            width: {{ $maxWidth }}cm;
                        ">
                            {{ $fieldValue }}
                        </div>

                    @elseif($elem['type'] === 'qrcode')
                        <div class="element qr-element" style="
                            left: {{ $leftPos }}cm;
                            top: {{ $topPos }}cm;
                            width: {{ $width }}cm;
                            height: {{ $height }}cm;
                        ">
                            <img src="{{ $qrCode }}" alt="QR Code" style="width: 100%; height: 100%; object-fit: contain;">
                        </div>

                    @elseif($elem['type'] === 'logo')
                        <div class="element logo-element" style="
                            left: {{ $leftPos }}cm;
                            top: {{ $topPos }}cm;
                            width: {{ $width }}cm;
                            height: {{ $height }}cm;
                        ">
                            @if($compressedLogo)
                                <img src="{{ $compressedLogo }}" alt="Event Logo">
                            @elseif($event->logo)
                                <img src="{{ public_path('storage/' . $event->logo) }}" alt="Event Logo">
                            @endif
                        </div>
                    @endif
                @endif
            @endforeach
        @endif
    </div>
</body>
</html>
