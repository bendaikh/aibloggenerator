#!/usr/bin/env php
<?php

/**
 * Google AdSense Configuration Checker
 * 
 * This script checks your Google AdSense configuration and helps
 * diagnose issues with ad serving.
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Website;

echo "\n=== Google AdSense Configuration Checker ===\n\n";

// Get all websites with Google Ads enabled
$websites = Website::where('google_ads_active', true)->get();

if ($websites->isEmpty()) {
    echo "❌ No websites found with Google Ads enabled.\n";
    echo "   Please enable Google Ads for at least one website in the CMS.\n\n";
    exit(1);
}

echo "✅ Found " . $websites->count() . " website(s) with Google Ads enabled:\n\n";

foreach ($websites as $website) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Website: {$website->name}\n";
    echo "Domain: {$website->domain}\n";
    echo "URL: {$website->url}\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    // Check AdSense ID
    echo "1. AdSense Publisher ID\n";
    if (empty($website->google_adsense_id)) {
        echo "   ❌ MISSING - No AdSense ID configured\n";
        echo "   → Add your AdSense ID (ca-pub-XXXXXXXXXXXXXXXX) in the CMS\n\n";
        continue;
    }
    
    // Validate AdSense ID format
    if (!preg_match('/^ca-pub-\d{16}$/', $website->google_adsense_id)) {
        echo "   ⚠️  WARNING - AdSense ID format may be incorrect: {$website->google_adsense_id}\n";
        echo "   → Expected format: ca-pub-XXXXXXXXXXXXXXXX (16 digits)\n\n";
    } else {
        echo "   ✅ {$website->google_adsense_id}\n\n";
    }
    
    // Check activation status
    echo "2. Activation Status\n";
    echo "   ✅ Google Ads Active: " . ($website->google_ads_active ? 'Yes' : 'No') . "\n";
    echo "   ℹ️  HBAgency Active: " . ($website->hbagency_active ? 'Yes (will override Google Ads)' : 'No') . "\n\n";
    
    // Check placements
    echo "3. Ad Placements\n";
    $placements = $website->google_ads_placements ?? [];
    
    if (empty($placements)) {
        echo "   ❌ No ad placements configured\n";
        echo "   → Add at least one ad placement in the CMS\n\n";
        continue;
    }
    
    $placementNames = [
        'top_banner' => 'Top Banner (728x90)',
        'in_article_1' => 'In-Article 1 (336x280)',
        'in_article_2' => 'In-Article 2 (336x280)',
        'sidebar' => 'Sidebar (300x600)',
        'sticky_footer' => 'Sticky Footer (728x90)',
        'bottom_banner' => 'Bottom Banner (728x90)',
    ];
    
    $hasValidPlacements = false;
    foreach ($placementNames as $key => $name) {
        if (!empty($placements[$key])) {
            echo "   ✅ {$name}: {$placements[$key]}\n";
            $hasValidPlacements = true;
            
            // Validate slot ID format (should be numeric)
            if (!is_numeric($placements[$key])) {
                echo "      ⚠️  WARNING - Ad slot should be numeric (e.g., 1234567890)\n";
            }
        } else {
            echo "   ⚪ {$name}: Not configured\n";
        }
    }
    
    if (!$hasValidPlacements) {
        echo "   ❌ No valid ad placements found\n\n";
        continue;
    }
    
    echo "\n";
    
    // Check ads.txt
    echo "4. Ads.txt File\n";
    if (!empty($website->ads_txt)) {
        $adsLines = explode("\n", trim($website->ads_txt));
        $hasGoogleLine = false;
        
        foreach ($adsLines as $line) {
            if (stripos($line, 'google.com') !== false && stripos($line, str_replace('ca-', '', $website->google_adsense_id)) !== false) {
                $hasGoogleLine = true;
                break;
            }
        }
        
        if ($hasGoogleLine) {
            echo "   ✅ ads.txt configured with Google AdSense\n";
        } else {
            echo "   ⚠️  WARNING - ads.txt exists but may not include your AdSense ID\n";
            echo "   → Expected line: google.com, " . str_replace('ca-', '', $website->google_adsense_id) . ", DIRECT, f08c47fec0942fa0\n";
        }
    } else {
        echo "   ⚠️  WARNING - No ads.txt configured\n";
        echo "   → Add ads.txt in the CMS for better ad serving\n";
    }
    
    echo "\n";
    
    // Summary
    echo "5. Configuration Summary\n";
    $issues = [];
    
    if (empty($website->google_adsense_id)) {
        $issues[] = "Missing AdSense ID";
    } elseif (!preg_match('/^ca-pub-\d{16}$/', $website->google_adsense_id)) {
        $issues[] = "Invalid AdSense ID format";
    }
    
    if (empty($placements)) {
        $issues[] = "No ad placements configured";
    }
    
    if (!$hasValidPlacements) {
        $issues[] = "No valid ad slots configured";
    }
    
    if ($website->hbagency_active) {
        $issues[] = "HBAgency is active (will override Google Ads)";
    }
    
    if (empty($issues)) {
        echo "   ✅ Configuration looks good!\n\n";
        echo "   Next Steps:\n";
        echo "   1. Rebuild your assets: npm run build\n";
        echo "   2. Clear cache: php artisan cache:clear\n";
        echo "   3. Deploy to production\n";
        echo "   4. Test on production domain (ads don't work on localhost)\n";
        echo "   5. Wait 10-20 minutes for ads to appear\n\n";
        echo "   ⚠️  Remember:\n";
        echo "   - Your domain must be approved in Google AdSense\n";
        echo "   - Ad slots must exist in your AdSense account\n";
        echo "   - Users must accept advertising cookies\n";
        echo "   - Ads may not show on every page load (normal behavior)\n\n";
    } else {
        echo "   ❌ Issues found:\n";
        foreach ($issues as $issue) {
            echo "      • {$issue}\n";
        }
        echo "\n   → Fix these issues and run this script again\n\n";
    }
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "For more help, see: GOOGLE_ADS_TROUBLESHOOTING.md\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
