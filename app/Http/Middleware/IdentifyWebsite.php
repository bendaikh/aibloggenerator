<?php

namespace App\Http\Middleware;

use App\Models\Website;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyWebsite
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $baseDomain = config('app.base_domain');
        
        // Skip if it's the main domain (websaasmanager.com) - let other routes handle it
        if ($host === $baseDomain || $host === 'www.' . $baseDomain) {
            return $next($request);
        }
        
        // Check if it's a subdomain of the base domain
        if (str_ends_with($host, '.' . $baseDomain)) {
            // Extract subdomain
            $subdomain = str_replace('.' . $baseDomain, '', $host);
            
            // Find website by subdomain
            $website = Website::where('subdomain', $subdomain)
                ->where('is_active', true)
                ->first();
            
            if ($website) {
                // Share website with all views
                $request->merge(['website' => $website]);
                view()->share('website', $website);
                
                return $next($request);
            }
        }
        
        // Check if it's a custom domain (with or without www)
        $domainToCheck = $host;
        
        // Try with and without www prefix
        $website = Website::where('is_active', true)
            ->where(function ($query) use ($host) {
                $query->where('domain', $host)
                    ->orWhere('domain', 'www.' . $host)
                    ->orWhere('domain', ltrim($host, 'www.'));
            })
            ->first();
        
        if ($website) {
            $request->merge(['website' => $website]);
            view()->share('website', $website);
            
            return $next($request);
        }
        
        // No website found for this domain
        abort(404, 'Website not found');
    }
}

