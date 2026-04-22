<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\IpGeolocationService;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Jenssegers\Agent\Agent;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        // Check if user account is pending
        if ($user->isPending()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('account.pending');
        }

        // Check if user account is declined
        if ($user->isDeclined()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('account.declined');
        }

        // Security Alert: Check if superadmin is logging in
        if ($user->isSuperAdmin()) {
            $this->sendSuperAdminLoginAlert($request, $user);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Send WhatsApp alert when superadmin logs in.
     *
     * @param Request $request
     * @param \App\Models\User $user
     * @return void
     */
    protected function sendSuperAdminLoginAlert(Request $request, $user): void
    {
        try {
            // Check if security alerts are enabled
            if (!($user->security_alerts_enabled ?? true)) {
                return;
            }

            $ipAddress = $request->ip();
            
            // Get IP geolocation data
            $geoService = new IpGeolocationService();
            $locationData = $geoService->getLocationData($ipAddress);

            // Get browser and platform information
            $agent = new Agent();
            $agent->setUserAgent($request->userAgent());
            
            $browser = $agent->browser();
            $browserVersion = $agent->version($browser);
            $platform = $agent->platform();
            $platformVersion = $agent->version($platform);

            // Prepare login data
            $loginData = [
                'username' => $user->name,
                'email' => $user->email,
                'ip_address' => $ipAddress,
                'city' => $locationData['city'],
                'region' => $locationData['region'],
                'country' => $locationData['country'],
                'isp' => $locationData['isp'],
                'browser' => "{$browser} {$browserVersion}",
                'platform' => "{$platform} {$platformVersion}",
                'timestamp' => now()->format('Y-m-d H:i:s T'),
            ];

            // Send WhatsApp notification
            $whatsappService = new WhatsAppNotificationService();
            $whatsappService->sendSuperAdminLoginAlert($loginData);
            
        } catch (\Exception $e) {
            // Log the error but don't block the login process
            \Illuminate\Support\Facades\Log::error('Failed to send superadmin login alert', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
