<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Mail Settings
            ['key' => 'mail_mailer', 'value' => 'smtp', 'type' => 'string', 'group' => 'mail'],
            ['key' => 'mail_host', 'value' => 'smtp.mailtrap.io', 'type' => 'string', 'group' => 'mail'],
            ['key' => 'mail_port', 'value' => '2525', 'type' => 'string', 'group' => 'mail'],
            ['key' => 'mail_username', 'value' => '', 'type' => 'string', 'group' => 'mail'],
            ['key' => 'mail_password', 'value' => '', 'type' => 'string', 'group' => 'mail'],
            ['key' => 'mail_encryption', 'value' => 'tls', 'type' => 'string', 'group' => 'mail'],
            ['key' => 'mail_from_address', 'value' => 'noreply@creativehub.com', 'type' => 'string', 'group' => 'mail'],
            ['key' => 'mail_from_name', 'value' => 'Creative Hub', 'type' => 'string', 'group' => 'mail'],

            // Branding Settings
            ['key' => 'app_name', 'value' => 'Creative Hub', 'type' => 'string', 'group' => 'branding'],
            ['key' => 'app_logo', 'value' => '', 'type' => 'string', 'group' => 'branding'],
            ['key' => 'app_favicon', 'value' => '', 'type' => 'string', 'group' => 'branding'],
            ['key' => 'brand_color', 'value' => '#4F46E5', 'type' => 'string', 'group' => 'branding'],

            // SEO Settings
            ['key' => 'seo_title', 'value' => 'Creative Hub - Event Management', 'type' => 'string', 'group' => 'seo'],
            ['key' => 'seo_description', 'value' => 'Professional event management and badge printing system', 'type' => 'text', 'group' => 'seo'],
            ['key' => 'seo_keywords', 'value' => 'events, badges, registration, attendees', 'type' => 'text', 'group' => 'seo'],
            ['key' => 'seo_image', 'value' => '', 'type' => 'string', 'group' => 'seo'],

            // General Settings
            ['key' => 'timezone', 'value' => 'UTC', 'type' => 'string', 'group' => 'general'],
            ['key' => 'date_format', 'value' => 'Y-m-d', 'type' => 'string', 'group' => 'general'],
        ];

        foreach ($settings as $setting) {
            \App\Models\Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
