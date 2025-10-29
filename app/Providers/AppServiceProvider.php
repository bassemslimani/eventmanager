<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Configure timezone from database
        $this->configureTimezoneFromSettings();

        // Configure mail settings from database
        $this->configureMailFromSettings();
    }

    /**
     * Configure timezone from database
     */
    protected function configureTimezoneFromSettings(): void
    {
        try {
            // Get timezone setting from database
            $timezone = \App\Models\Setting::get('timezone', config('app.timezone'));

            // Set application timezone
            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);
        } catch (\Exception $e) {
            // Silently fail if database is not available yet (during migrations, etc.)
            \Illuminate\Support\Facades\Log::warning('Could not load timezone setting from database: ' . $e->getMessage());
        }
    }

    /**
     * Configure mail settings from database
     */
    protected function configureMailFromSettings(): void
    {
        try {
            // Get mail settings from database
            $mailMailer = \App\Models\Setting::get('mail_mailer', config('mail.default'));
            $mailHost = \App\Models\Setting::get('mail_host', config('mail.mailers.smtp.host'));
            $mailPort = \App\Models\Setting::get('mail_port', config('mail.mailers.smtp.port'));
            $mailUsername = \App\Models\Setting::get('mail_username', config('mail.mailers.smtp.username'));
            $mailPassword = \App\Models\Setting::get('mail_password', config('mail.mailers.smtp.password'));
            $mailEncryption = \App\Models\Setting::get('mail_encryption', config('mail.mailers.smtp.encryption'));
            $mailFromAddress = \App\Models\Setting::get('mail_from_address', config('mail.from.address'));
            $mailFromName = \App\Models\Setting::get('mail_from_name', config('mail.from.name'));

            // Set mail configuration
            config([
                'mail.default' => $mailMailer,
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => $mailHost,
                'mail.mailers.smtp.port' => $mailPort,
                'mail.mailers.smtp.encryption' => $mailEncryption,
                'mail.mailers.smtp.username' => $mailUsername,
                'mail.mailers.smtp.password' => $mailPassword,
                'mail.from.address' => $mailFromAddress,
                'mail.from.name' => $mailFromName,
            ]);
        } catch (\Exception $e) {
            // Silently fail if database is not available yet (during migrations, etc.)
            \Illuminate\Support\Facades\Log::warning('Could not load mail settings from database: ' . $e->getMessage());
        }
    }
}
