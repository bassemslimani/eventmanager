<?php

namespace Database\Seeders;

use App\Models\BadgeTemplate;
use Illuminate\Database\Seeder;

class BadgeTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'type' => 'exhibitor',
                'name' => 'Exhibitor Badge',
                'overlay_color' => '#10B981',
                'overlay_opacity' => 85,
                'glass_effect' => true,
                'gradient_direction' => '135deg',
                'font_family' => 'Inter',
                'is_active' => true,
            ],
            [
                'type' => 'guest',
                'name' => 'Guest Badge',
                'overlay_color' => '#3B82F6',
                'overlay_opacity' => 85,
                'glass_effect' => true,
                'gradient_direction' => '135deg',
                'font_family' => 'Inter',
                'is_active' => true,
            ],
            [
                'type' => 'organizer',
                'name' => 'Organizer Badge',
                'overlay_color' => '#EAB308',
                'overlay_opacity' => 85,
                'glass_effect' => true,
                'gradient_direction' => '135deg',
                'font_family' => 'Inter',
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            BadgeTemplate::create($template);
        }
    }
}
