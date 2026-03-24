<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DiagnosticsController extends Controller
{
    /**
     * Display diagnostics information to help debug Inertia issues.
     */
    public function index(Request $request)
    {
        $diagnostics = [
            'request_info' => [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'is_ajax' => $request->ajax(),
                'wants_json' => $request->wantsJson(),
                'accepts_html' => $request->accepts('text/html'),
            ],
            'headers' => [
                'X-Inertia' => $request->header('X-Inertia'),
                'X-Inertia-Version' => $request->header('X-Inertia-Version'),
                'X-Requested-With' => $request->header('X-Requested-With'),
                'Accept' => $request->header('Accept'),
                'User-Agent' => $request->userAgent(),
            ],
            'environment' => [
                'app_env' => config('app.env'),
                'app_debug' => config('app.debug'),
                'app_url' => config('app.url'),
                'base_domain' => config('app.base_domain'),
            ],
            'detection' => [
                'is_direct_browser_request' => $request->method() === 'GET' 
                    && !$request->header('X-Inertia') 
                    && !$request->ajax()
                    && !$request->wantsJson()
                    && $request->accepts('text/html'),
                'is_inertia_navigation' => (bool) $request->header('X-Inertia'),
            ],
            'timestamp' => now()->toDateTimeString(),
        ];

        // Log the diagnostics
        Log::info('Inertia Diagnostics', $diagnostics);

        // Return as JSON for easy viewing
        return response()->json([
            'status' => 'OK',
            'message' => 'Diagnostics complete. This endpoint helps verify Inertia request detection.',
            'diagnostics' => $diagnostics,
        ], 200, [], JSON_PRETTY_PRINT);
    }
}
