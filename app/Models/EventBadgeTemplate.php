<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventBadgeTemplate extends Model
{
    protected $fillable = [
        'event_id',
        'category',
        'front_template',
        'back_template',
        'front_layout',
        'back_layout',
        'elements',
        'terms_and_conditions',
        'badge_size',
        'badge_width',
        'badge_height',
        'badge_width_cm',
        'badge_height_cm',
        'measurement_unit',
        'font_family',
        'primary_color',
        'secondary_color',
        'show_qr_code',
        'show_logo',
        'show_category_badge',
        'is_active',
    ];

    protected $casts = [
        'front_layout' => 'array',
        'back_layout' => 'array',
        'elements' => 'array',
        'badge_width' => 'integer',
        'badge_height' => 'integer',
        'badge_width_cm' => 'decimal:2',
        'badge_height_cm' => 'decimal:2',
        'show_qr_code' => 'boolean',
        'show_logo' => 'boolean',
        'show_category_badge' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get default layout configuration (8.5cm x 12.5cm badge)
     * Positions are in cm
     */
    public static function getDefaultElements(): array
    {
        return [
            [
                'id' => 'logo',
                'type' => 'logo',
                'label' => 'Event Logo',
                'x' => 2.25,  // cm - centered (8.5/2 - 2 = 2.25)
                'y' => 0.5,
                'width' => 4,
                'height' => 2,
                'visible' => true,
                'locked' => false,
            ],
            [
                'id' => 'event_name',
                'type' => 'text',
                'label' => 'Event Name',
                'field' => 'event.name',
                'x' => 4.25,  // cm - centered
                'y' => 3,
                'fontSize' => 16,
                'fontWeight' => 'bold',
                'align' => 'center',
                'color' => '#1F2937',
                'visible' => true,
                'locked' => false,
            ],
            [
                'id' => 'event_date',
                'type' => 'text',
                'label' => 'Event Date',
                'field' => 'event.date',
                'x' => 4.25,
                'y' => 3.7,
                'fontSize' => 11,
                'fontWeight' => 'normal',
                'align' => 'center',
                'color' => '#6B7280',
                'visible' => true,
                'locked' => false,
            ],
            [
                'id' => 'event_location',
                'type' => 'text',
                'label' => 'Event Location',
                'field' => 'event.location',
                'x' => 4.25,
                'y' => 4.2,
                'fontSize' => 11,
                'fontWeight' => 'normal',
                'align' => 'center',
                'color' => '#6B7280',
                'visible' => true,
                'locked' => false,
            ],
            [
                'id' => 'attendee_name',
                'type' => 'text',
                'label' => 'Attendee Name',
                'field' => 'attendee.name',
                'x' => 4.25,
                'y' => 5.5,
                'fontSize' => 24,
                'fontWeight' => 'bold',
                'align' => 'center',
                'color' => '#000000',
                'visible' => true,
                'locked' => false,
            ],
            [
                'id' => 'company',
                'type' => 'text',
                'label' => 'Company',
                'field' => 'attendee.company',
                'x' => 4.25,
                'y' => 6.5,
                'fontSize' => 16,
                'fontWeight' => 'normal',
                'align' => 'center',
                'color' => '#4B5563',
                'visible' => true,
                'locked' => false,
            ],
            [
                'id' => 'category',
                'type' => 'text',
                'label' => 'Category',
                'field' => 'attendee.type',
                'x' => 4.25,
                'y' => 7.2,
                'fontSize' => 12,
                'fontWeight' => 'bold',
                'align' => 'center',
                'color' => '#059669',
                'visible' => true,
                'locked' => false,
            ],
            [
                'id' => 'qr_code',
                'type' => 'qrcode',
                'label' => 'QR Code',
                'x' => 3,
                'y' => 8.5,
                'width' => 2.5,
                'height' => 2.5,
                'visible' => true,
                'locked' => false,
            ],
            [
                'id' => 'qr_uuid',
                'type' => 'text',
                'label' => 'QR UUID',
                'field' => 'attendee.qr_uuid',
                'x' => 4.25,
                'y' => 11.2,
                'fontSize' => 10,
                'fontWeight' => 'normal',
                'align' => 'center',
                'color' => '#000000',
                'visible' => true,
                'locked' => false,
            ],
            [
                'id' => 'qr_helper',
                'type' => 'text',
                'label' => 'QR Helper Text',
                'field' => 'static:Scan QR or enter code manually',
                'x' => 4.25,
                'y' => 11.7,
                'fontSize' => 8,
                'fontWeight' => 'normal',
                'align' => 'center',
                'color' => '#9CA3AF',
                'visible' => true,
                'locked' => false,
            ],
        ];
    }
}
