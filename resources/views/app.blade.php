<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

        <!-- HBAgency Script - inserted directly in head as required by HBAgency -->
        <!-- HBAgency's script will automatically inject their CMP (Consent Management Platform) -->
        @if(isset($website) && $website->hbagency_script && $website->hbagency_active)
        {!! $website->hbagency_script !!}
        @endif

        <!-- Google AdSense Script - loaded when Google Ads are active -->
        @if(isset($website) && $website->google_adsense_id && $website->google_ads_active)
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $website->google_adsense_id }}"
                crossorigin="anonymous"></script>
        @endif

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
