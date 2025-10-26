<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BadgeTemplate extends Model
{
    protected $fillable = [
        'type',
        'name',
        'background_image',
        'overlay_color',
        'overlay_opacity',
        'glass_effect',
        'gradient_direction',
        'font_family',
        'layout_config',
        'css_overrides',
        'is_active',
    ];

    protected $casts = [
        'layout_config' => 'array',
        'glass_effect' => 'boolean',
        'is_active' => 'boolean',
        'overlay_opacity' => 'integer',
    ];
}
