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
        'terms_and_conditions',
        'badge_size',
        'badge_width',
        'badge_height',
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
        'badge_width' => 'integer',
        'badge_height' => 'integer',
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
     * Get default layout configuration
     */
    public static function getDefaultLayout(): array
    {
        return [
            'elements' => [
                'name' => [
                    'x' => 50,
                    'y' => 200,
                    'fontSize' => 24,
                    'fontWeight' => 'bold',
                    'align' => 'center',
                    'color' => '#000000',
                    'show' => true,
                ],
                'company' => [
                    'x' => 50,
                    'y' => 240,
                    'fontSize' => 16,
                    'fontWeight' => 'normal',
                    'align' => 'center',
                    'color' => '#666666',
                    'show' => true,
                ],
                'category' => [
                    'x' => 50,
                    'y' => 100,
                    'fontSize' => 14,
                    'fontWeight' => 'bold',
                    'align' => 'center',
                    'color' => '#ffffff',
                    'backgroundColor' => '#10B981',
                    'show' => true,
                ],
                'qr_code' => [
                    'x' => 150,
                    'y' => 400,
                    'size' => 120,
                    'show' => true,
                ],
                'logo' => [
                    'x' => 150,
                    'y' => 30,
                    'width' => 100,
                    'height' => 50,
                    'show' => true,
                ],
            ],
        ];
    }
}
