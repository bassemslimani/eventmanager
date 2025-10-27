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
    public static function getDefaultLayout(): array
    {
        return [
            'textZones' => []
        ];
    }

    /**
     * Get default elements for badge designer
     */
    public static function getDefaultElements(): array
    {
        return [
            [
                'id' => 'event_name',
                'type' => 'text',
                'label' => 'Event Name',
                'field' => 'event.name',
                'x' => 4.25,
                'y' => 1.5,
                'fontSize' => 16,
                'fontWeight' => 'bold',
                'align' => 'center',
                'color' => '#1F2937',
                'visible' => true,
                'maxWidth' => 7
            ],
            [
                'id' => 'logo',
                'type' => 'logo',
                'label' => 'Event Logo',
                'x' => 4.25,
                'y' => 2.5,
                'width' => 3,
                'height' => 1.5,
                'visible' => true
            ],
            [
                'id' => 'attendee_name',
                'type' => 'text',
                'label' => 'Attendee Name',
                'field' => 'attendee.name',
                'x' => 4.25,
                'y' => 5.5,
                'fontSize' => 28,
                'fontWeight' => 'bold',
                'align' => 'center',
                'color' => '#000000',
                'visible' => true,
                'maxWidth' => 7
            ],
            [
                'id' => 'company',
                'type' => 'text',
                'label' => 'Company',
                'field' => 'attendee.company',
                'x' => 4.25,
                'y' => 7,
                'fontSize' => 18,
                'fontWeight' => 'normal',
                'align' => 'center',
                'color' => '#4B5563',
                'visible' => true,
                'maxWidth' => 7
            ],
            [
                'id' => 'category',
                'type' => 'text',
                'label' => 'Category',
                'field' => 'attendee.category',
                'x' => 4.25,
                'y' => 8.2,
                'fontSize' => 14,
                'fontWeight' => 'bold',
                'align' => 'center',
                'color' => '#059669',
                'visible' => true,
                'maxWidth' => 4
            ],
            [
                'id' => 'qr_code',
                'type' => 'qrcode',
                'label' => 'QR Code',
                'x' => 4.25,
                'y' => 9.5,
                'width' => 2.5,
                'height' => 2.5,
                'visible' => true
            ],
        ];
    }
}
