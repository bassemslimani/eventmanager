<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $appName = \App\Models\Setting::get('app_name', config('app.name', 'QRCH'));
            $brandColor = \App\Models\Setting::get('brand_color', '#10b981');
            $favicon = \App\Models\Setting::get('app_favicon');
            $pwaIcon = \App\Models\Setting::get('pwa_icon');
        @endphp

        <title inertia>{{ $appName }}</title>

        <!-- Favicon -->
        @if($favicon)
            <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $favicon) }}">
        @endif

        <!-- PWA Meta Tags -->
        <meta name="application-name" content="{{ $appName }}">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="{{ $appName }}">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="theme-color" content="{{ $brandColor }}">
        <link rel="manifest" href="/build/manifest.webmanifest">
        @if($pwaIcon)
            <link rel="apple-touch-icon" href="{{ asset('storage/' . $pwaIcon) }}">
        @else
            <link rel="apple-touch-icon" href="/pwa-192x192.png">
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.ts', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead

        <!-- PWA Service Worker Registration -->
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/build/sw.js', { scope: '/' })
                        .then(registration => {
                            console.log('✅ Service Worker registered:', registration.scope);
                        })
                        .catch(error => {
                            console.error('❌ Service Worker registration failed:', error);
                        });
                });
            }
        </script>
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
