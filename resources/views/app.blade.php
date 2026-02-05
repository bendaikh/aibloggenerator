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

        <!-- HBAgency Ads Script (loaded after consent) -->
        @if(isset($website) && $website->hbagency_script)
        <script>
            // Store HBAgency script configuration for consent-based loading
            window.__HBAGENCY_CONFIG__ = {
                script: @json($website->hbagency_script),
                enabled: true,
                loaded: false
            };
            
            // Function to load HBAgency script after consent
            window.loadHBAgencyScript = function() {
                if (window.__HBAGENCY_CONFIG__.loaded) {
                    console.log('[CMP] HBAgency script already loaded');
                    return;
                }
                
                console.log('[CMP] Loading HBAgency script after consent...');
                
                // Create a temporary container to parse the script
                var tempDiv = document.createElement('div');
                tempDiv.innerHTML = window.__HBAGENCY_CONFIG__.script;
                
                // Find and execute all script tags
                var scripts = tempDiv.getElementsByTagName('script');
                for (var i = 0; i < scripts.length; i++) {
                    var script = document.createElement('script');
                    
                    // Copy all attributes
                    for (var j = 0; j < scripts[i].attributes.length; j++) {
                        var attr = scripts[i].attributes[j];
                        script.setAttribute(attr.name, attr.value);
                    }
                    
                    // Copy inline script content if any
                    if (scripts[i].innerHTML) {
                        script.innerHTML = scripts[i].innerHTML;
                    }
                    
                    document.head.appendChild(script);
                }
                
                window.__HBAGENCY_CONFIG__.loaded = true;
                console.log('[CMP] HBAgency script loaded successfully');
            };
            
            // Check for existing consent on page load
            document.addEventListener('DOMContentLoaded', function() {
                try {
                    var consent = localStorage.getItem('cookie_consent');
                    if (consent) {
                        var parsed = JSON.parse(consent);
                        if (parsed.advertising === true) {
                            console.log('[CMP] Advertising consent found, loading HBAgency script...');
                            window.loadHBAgencyScript();
                        }
                    }
                } catch (e) {
                    console.error('[CMP] Error checking consent:', e);
                }
            });
            
            // Listen for consent events
            window.addEventListener('consent_accepted_all', function() {
                window.loadHBAgencyScript();
            });
            
            window.addEventListener('consent_custom', function(e) {
                if (e.detail && e.detail.advertising === true) {
                    window.loadHBAgencyScript();
                }
            });
            
            window.addEventListener('ads_consent_granted', function() {
                window.loadHBAgencyScript();
            });
        </script>
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
