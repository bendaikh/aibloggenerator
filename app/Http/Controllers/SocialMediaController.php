<?php

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SocialMediaController extends Controller
{
    /**
     * Display the social media management page.
     */
    public function index($websiteId)
    {
        $website = Website::findOrFail($websiteId);
        
        return Inertia::render('SuperAdmin/SocialMedia', [
            'currentWebsite' => $website,
            'websites' => Website::where('user_id', auth()->id())
                ->withCount(['articles', 'categories'])
                ->get(),
            'socialMedia' => $website->social_media ?? [],
        ]);
    }

    /**
     * Update social media connection.
     */
    public function update(Request $request, $websiteId)
    {
        $website = Website::findOrFail($websiteId);
        
        $validated = $request->validate([
            'platform' => 'required|string|in:twitter,facebook,linkedin,pinterest,instagram,youtube,tiktok',
            'url' => 'nullable|url|max:500',
        ]);

        $socialMedia = $website->social_media ?? [];
        
        if (empty($validated['url'])) {
            // Disconnect - remove the platform
            unset($socialMedia[$validated['platform']]);
        } else {
            // Connect - add/update the platform URL
            $socialMedia[$validated['platform']] = $validated['url'];
        }

        $website->update(['social_media' => $socialMedia]);

        return back()->with('success', ucfirst($validated['platform']) . ' connection updated successfully!');
    }

    /**
     * Disconnect a social media platform.
     */
    public function disconnect(Request $request, $websiteId, $platform)
    {
        $website = Website::findOrFail($websiteId);
        
        $socialMedia = $website->social_media ?? [];
        unset($socialMedia[$platform]);
        
        $website->update(['social_media' => $socialMedia]);

        return back()->with('success', ucfirst($platform) . ' disconnected successfully!');
    }
}

