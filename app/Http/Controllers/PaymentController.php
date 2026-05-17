<?php

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $websites = $user->accessibleWebsitesQuery()
            ->withCount(['articles', 'categories'])
            ->get();

        return Inertia::render('Organization/Payments', [
            'settings' => [
                'payments_enabled' => $user->payments_enabled,
                'stripe_configured' => $user->hasStripeConfigured(),
                'stripe_publishable_key_masked' => $user->stripe_publishable_key 
                    ? 'pk_....' . substr($user->stripe_publishable_key, -4) 
                    : null,
                'stripe_secret_key_masked' => $user->stripe_secret_key 
                    ? 'sk_....' . substr($user->stripe_secret_key, -4) 
                    : null,
                'stripe_webhook_secret_set' => !empty($user->stripe_webhook_secret),
                'paypal_configured' => $user->hasPaypalConfigured(),
                'paypal_client_id_masked' => $user->paypal_client_id 
                    ? substr($user->paypal_client_id, 0, 8) . '....' . substr($user->paypal_client_id, -4) 
                    : null,
                'paypal_client_secret_set' => !empty($user->paypal_client_secret),
                'paypal_mode' => $user->paypal_mode ?? 'sandbox',
            ],
            'websites' => $websites,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'payments_enabled' => 'boolean',
            'stripe_publishable_key' => 'nullable|string',
            'stripe_secret_key' => 'nullable|string',
            'stripe_webhook_secret' => 'nullable|string',
            'paypal_client_id' => 'nullable|string',
            'paypal_client_secret' => 'nullable|string',
            'paypal_mode' => 'nullable|in:sandbox,live',
        ]);

        $updateData = [
            'payments_enabled' => $validated['payments_enabled'] ?? false,
        ];

        if (!empty($validated['stripe_publishable_key'])) {
            $updateData['stripe_publishable_key'] = $validated['stripe_publishable_key'];
        }

        if (!empty($validated['stripe_secret_key'])) {
            $updateData['stripe_secret_key'] = $validated['stripe_secret_key'];
        }

        if (!empty($validated['stripe_webhook_secret'])) {
            $updateData['stripe_webhook_secret'] = $validated['stripe_webhook_secret'];
        }

        if (!empty($validated['paypal_client_id'])) {
            $updateData['paypal_client_id'] = $validated['paypal_client_id'];
        }

        if (!empty($validated['paypal_client_secret'])) {
            $updateData['paypal_client_secret'] = $validated['paypal_client_secret'];
        }

        if (isset($validated['paypal_mode'])) {
            $updateData['paypal_mode'] = $validated['paypal_mode'];
        }

        $user->update($updateData);

        return redirect()->back()->with('success', 'Payment settings updated successfully!');
    }

    public function testStripe()
    {
        $user = Auth::user();

        if (!$user->hasStripeConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Stripe is not configured. Please add your API keys.',
            ]);
        }

        try {
            \Stripe\Stripe::setApiKey($user->stripe_secret_key);
            $balance = \Stripe\Balance::retrieve();

            return response()->json([
                'success' => true,
                'message' => 'Stripe connection successful! Your account is ready to accept payments.',
            ]);
        } catch (\Stripe\Exception\AuthenticationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Stripe API key. Please check your credentials.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Stripe connection failed: ' . $e->getMessage(),
            ]);
        }
    }

    public function testPaypal()
    {
        $user = Auth::user();

        if (!$user->hasPaypalConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'PayPal is not configured. Please add your API credentials.',
            ]);
        }

        try {
            $baseUrl = $user->paypal_mode === 'live' 
                ? 'https://api-m.paypal.com' 
                : 'https://api-m.sandbox.paypal.com';

            $client = new \GuzzleHttp\Client();
            $response = $client->post($baseUrl . '/v1/oauth2/token', [
                'auth' => [$user->paypal_client_id, $user->paypal_client_secret],
                'form_params' => [
                    'grant_type' => 'client_credentials',
                ],
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['access_token'])) {
                return response()->json([
                    'success' => true,
                    'message' => 'PayPal connection successful! Your ' . ucfirst($user->paypal_mode) . ' account is ready.',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Unexpected response from PayPal.',
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $errorBody = $e->getResponse()->getBody()->getContents();
            $errorData = json_decode($errorBody, true);
            $errorMessage = $errorData['error_description'] ?? $e->getMessage();

            return response()->json([
                'success' => false,
                'message' => 'PayPal authentication failed: ' . $errorMessage,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'PayPal connection failed: ' . $e->getMessage(),
            ]);
        }
    }

    public function disconnectStripe()
    {
        $user = Auth::user();

        $user->update([
            'stripe_publishable_key' => null,
            'stripe_secret_key' => null,
            'stripe_webhook_secret' => null,
        ]);

        return redirect()->back()->with('success', 'Stripe disconnected successfully.');
    }

    public function disconnectPaypal()
    {
        $user = Auth::user();

        $user->update([
            'paypal_client_id' => null,
            'paypal_client_secret' => null,
            'paypal_mode' => 'sandbox',
        ]);

        return redirect()->back()->with('success', 'PayPal disconnected successfully.');
    }
}
