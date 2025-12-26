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
        
        // Check if it's a subdomain
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
        
        // Check if it's a custom domain
        $website = Website::where('domain', $host)
            ->where('is_active', true)
            ->first();
        
        if ($website) {
            $request->merge(['website' => $website]);
            view()->share('website', $website);
            
            return $next($request);
        }
        
        // No website found
        abort(404, 'Website not found');
    }
}

