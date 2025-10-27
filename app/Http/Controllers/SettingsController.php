<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Display the settings page
     */
    public function index()
    {
        $settings = [
            'mail' => Setting::getByGroup('mail')->toArray(),
            'branding' => Setting::getByGroup('branding')->toArray(),
            'seo' => Setting::getByGroup('seo')->toArray(),
            'general' => Setting::getByGroup('general')->toArray(),
        ];

        return Inertia::render('Settings/Index', [
            'settings' => $settings
        ]);
    }

    /**
     * Update mail settings
     */
    public function updateMail(Request $request)
    {
        $validated = $request->validate([
            'mail_mailer' => 'required|string',
            'mail_host' => 'required|string',
            'mail_port' => 'required|string',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'required|string',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value, 'string', 'mail');
        }

        return back()->with('success', 'Mail settings updated successfully');
    }

    /**
     * Update branding settings
     */
    public function updateBranding(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string',
            'brand_color' => 'required|string',
            'app_logo' => 'nullable|image|max:2048',
            'app_favicon' => 'nullable|image|max:1024',
        ]);

        // Handle logo upload
        if ($request->hasFile('app_logo')) {
            $path = $request->file('app_logo')->store('branding', 'public');
            Setting::set('app_logo', $path, 'string', 'branding');
        }

        // Handle favicon upload
        if ($request->hasFile('app_favicon')) {
            $path = $request->file('app_favicon')->store('branding', 'public');
            Setting::set('app_favicon', $path, 'string', 'branding');
        }

        Setting::set('app_name', $validated['app_name'], 'string', 'branding');
        Setting::set('brand_color', $validated['brand_color'], 'string', 'branding');

        return back()->with('success', 'Branding settings updated successfully');
    }

    /**
     * Update SEO settings
     */
    public function updateSeo(Request $request)
    {
        $validated = $request->validate([
            'seo_title' => 'required|string',
            'seo_description' => 'required|string',
            'seo_keywords' => 'required|string',
            'seo_image' => 'nullable|image|max:2048',
        ]);

        // Handle SEO image upload
        if ($request->hasFile('seo_image')) {
            $path = $request->file('seo_image')->store('seo', 'public');
            Setting::set('seo_image', $path, 'string', 'seo');
        }

        Setting::set('seo_title', $validated['seo_title'], 'string', 'seo');
        Setting::set('seo_description', $validated['seo_description'], 'text', 'seo');
        Setting::set('seo_keywords', $validated['seo_keywords'], 'text', 'seo');

        return back()->with('success', 'SEO settings updated successfully');
    }

    /**
     * Update general settings
     */
    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'timezone' => 'required|string',
            'date_format' => 'required|string',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value, 'string', 'general');
        }

        return back()->with('success', 'General settings updated successfully');
    }

    /**
     * Test email configuration
     */
    public function testEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        try {
            \Mail::raw('This is a test email from Creative Hub. Your SMTP configuration is working correctly!', function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('Test Email - Creative Hub');
            });

            return back()->with('success', 'Test email sent successfully! Check your inbox.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }
}
