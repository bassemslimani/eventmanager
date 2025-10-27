<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Setting;

class DynamicConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load mail settings from database if available
        try {
            if (\Schema::hasTable('settings')) {
                $mailSettings = [
                    'mail.mailers.smtp.host' => Setting::get('mail_host'),
                    'mail.mailers.smtp.port' => Setting::get('mail_port'),
                    'mail.mailers.smtp.username' => Setting::get('mail_username'),
                    'mail.mailers.smtp.password' => Setting::get('mail_password'),
                    'mail.mailers.smtp.encryption' => Setting::get('mail_encryption'),
                    'mail.from.address' => Setting::get('mail_from_address'),
                    'mail.from.name' => Setting::get('mail_from_name'),
                ];

                foreach ($mailSettings as $key => $value) {
                    if ($value !== null) {
                        config([$key => $value]);
                    }
                }

                // Set app logo if available
                $appLogo = Setting::get('app_logo');
                if ($appLogo) {
                    config(['app.logo' => asset('storage/' . $appLogo)]);
                }

                // Set app name
                $appName = Setting::get('app_name');
                if ($appName) {
                    config(['app.name' => $appName]);
                }
            }
        } catch (\Exception $e) {
            // If settings table doesn't exist yet or any error, just continue
            // This can happen during initial migration
        }
    }
}
