<?php

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class AdsController extends Controller
{
    /**
     * Update HBAgency configuration for a website
     */
    public function updateHBAgency(Request $request, $websiteId)
    {
        $website = Website::findOrFail($websiteId);

        // Validate the request
        $validated = $request->validate([
            'hbagency_script' => 'nullable|string',
            'hbagency_placements' => 'nullable|array',
            'hbagency_placements.in_image' => 'nullable|string',
            'hbagency_placements.in_article_1' => 'nullable|string',
            'hbagency_placements.in_article_2' => 'nullable|string',
            'hbagency_placements.sidebar' => 'nullable|string',
            'hbagency_placements.sticky_footer' => 'nullable|string',
            'hbagency_placements.top_banner' => 'nullable|string',
            'hbagency_placements.bottom_banner' => 'nullable|string',
            'hbagency_active' => 'nullable|boolean',
        ]);

        // If activating HBAgency, deactivate Google Ads
        if (!empty($validated['hbagency_active'])) {
            $website->update(['google_ads_active' => false]);
        }

        // Update the website
        $website->update([
            'hbagency_script' => $validated['hbagency_script'] ?? null,
            'hbagency_placements' => $validated['hbagency_placements'] ?? null,
            'hbagency_active' => $validated['hbagency_active'] ?? false,
        ]);

        return Redirect::back()->with('success', 'HBAgency configuration updated successfully!');
    }

    /**
     * Update Google Ads configuration for a website
     */
    public function updateGoogleAds(Request $request, $websiteId)
    {
        $website = Website::findOrFail($websiteId);

        // Validate the request
        $validated = $request->validate([
            'google_adsense_id' => 'nullable|string',
            'ads_txt' => 'nullable|string',
            'google_ads_placements' => 'nullable|array',
            'google_ads_placements.in_image' => 'nullable|string',
            'google_ads_placements.in_article_1' => 'nullable|string',
            'google_ads_placements.in_article_2' => 'nullable|string',
            'google_ads_placements.sidebar' => 'nullable|string',
            'google_ads_placements.sticky_footer' => 'nullable|string',
            'google_ads_placements.top_banner' => 'nullable|string',
            'google_ads_placements.bottom_banner' => 'nullable|string',
            'google_ads_active' => 'nullable|boolean',
        ]);

        // If activating Google Ads, deactivate HBAgency
        if (!empty($validated['google_ads_active'])) {
            $website->update(['hbagency_active' => false]);
        }

        // Update the website
        $website->update([
            'google_adsense_id' => $validated['google_adsense_id'] ?? null,
            'ads_txt' => $validated['ads_txt'] ?? null,
            'google_ads_placements' => $validated['google_ads_placements'] ?? null,
            'google_ads_active' => $validated['google_ads_active'] ?? false,
        ]);

        return Redirect::back()->with('success', 'Google Ads configuration updated successfully!');
    }
}
