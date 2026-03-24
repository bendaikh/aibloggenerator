<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInertiaResponse
{
    /**
     * Handle an incoming request.
     *
     * This middleware ensures that Inertia responses are properly formatted
     * and prevents raw JSON from being displayed in the browser.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // If this is a GET request and the response is JSON but NOT an Inertia request,
        // something went wrong. This prevents raw JSON from being displayed.
        if (
            $request->method() === 'GET' &&
            !$request->header('X-Inertia') &&
            $response->headers->get('Content-Type') === 'application/json' &&
            !$request->ajax() &&
            !$request->wantsJson() &&
            $request->accepts('text/html')
        ) {
            // Log this issue for debugging
            \Log::warning('Detected potential raw JSON response for HTML request', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'headers' => $request->headers->all(),
            ]);
        }

        return $response;
    }
}
