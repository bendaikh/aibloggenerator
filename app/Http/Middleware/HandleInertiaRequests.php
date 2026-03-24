<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Symfony\Component\HttpFoundation\Response;
use Closure;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Handle the incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Detect if this is a direct browser request (not AJAX, not Inertia navigation)
        // This fixes the issue where browsers show raw JSON instead of HTML
        $isDirectBrowserRequest = $request->method() === 'GET' 
            && !$request->header('X-Inertia') 
            && !$request->ajax()
            && !$request->wantsJson()
            && $request->accepts('text/html');

        if ($isDirectBrowserRequest) {
            // Ensure this request is treated as a full page load, not an Inertia visit
            $request->headers->remove('X-Inertia');
            $request->headers->remove('X-Inertia-Version');
            $request->headers->remove('X-Requested-With');
            
            // Set Accept header to ensure HTML response
            $request->headers->set('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');
        }

        $response = parent::handle($request, $next);

        // Double-check: if we have a direct browser request but the response is JSON, 
        // something went wrong. Log it for debugging.
        if ($isDirectBrowserRequest && $response->headers->get('Content-Type') === 'application/json') {
            \Log::error('Inertia returned JSON for direct browser request', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $response;
    }

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $sharedData = [
            ...parent::share($request),
            'app' => [
                'name' => config('app.name'),
                'url' => config('app.url'),
                'base_domain' => config('app.base_domain'),
            ],
        ];

        // Only include auth and session data if user is authenticated
        // This prevents issues with public routes
        if ($request->user()) {
            $sharedData['auth'] = [
                'user' => $request->user(),
            ];
            $sharedData['isImpersonating'] = $request->session()->has('impersonator_id');
        }

        return $sharedData;
    }
}
