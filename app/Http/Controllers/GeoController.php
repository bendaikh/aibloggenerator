<?php

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class GeoController extends Controller
{
    /**
     * Show the GEO (Generative Engine Optimization) settings page
     */
    public function index($websiteId)
    {
        $website = Website::findOrFail($websiteId);
        
        return Inertia::render('SuperAdmin/GEO', [
            'currentWebsite' => $website
        ]);
    }

    /**
     * Update GEO settings for a website
     */
    public function update(Request $request, $websiteId)
    {
        $website = Website::findOrFail($websiteId);

        // Validate the request
        $validated = $request->validate([
            'geo_settings' => 'nullable|array',
            'geo_settings.structured_data_enhanced' => 'nullable|boolean',
            'geo_settings.ai_readability_optimized' => 'nullable|boolean',
            'geo_settings.citation_format' => 'nullable|string',
            'geo_settings.fact_checking_enabled' => 'nullable|boolean',
            'geo_settings.author_credentials' => 'nullable|string',
            'geo_settings.expertise_areas' => 'nullable|array',
            'geo_settings.ai_summary' => 'nullable|string',
            'geo_settings.key_facts' => 'nullable|array',
            'geo_settings.conversational_queries' => 'nullable|array',
            'geo_settings.technical_depth' => 'nullable|string',
            'geo_settings.api_access_enabled' => 'nullable|boolean',
            'geo_settings.llm_training_opt_out' => 'nullable|boolean',
            'geo_settings.preferred_llms' => 'nullable|array',
            'geo_settings.content_attribution' => 'nullable|string',
            'geo_settings.entity_definitions' => 'nullable|array',
        ]);

        // Update the website
        $website->update([
            'geo_settings' => $validated['geo_settings'] ?? null,
        ]);

        return Redirect::back()->with('success', 'GEO settings updated successfully!');
    }
}
