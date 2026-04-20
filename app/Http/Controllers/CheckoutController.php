<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    private function resolveWebsiteAndProduct(Request $request, $websiteParam, $productParam)
    {
        $website = $request->get('website');
        
        if (!$website) {
            $website = Website::where('slug', $websiteParam)
                ->where('is_active', true)
                ->firstOrFail();
        }

        $product = Product::where('website_id', $website->id)
            ->where('slug', $productParam)
            ->where('status', 'published')
            ->firstOrFail();

        return [$website, $product];
    }

    public function show(Request $request, $website, $product)
    {
        [$website, $product] = $this->resolveWebsiteAndProduct($request, $website, $product);
        
        $user = $website->user;

        if (!$user->hasPaymentsConfigured()) {
            abort(404, 'Payments are not enabled for this website.');
        }

        $website->load([
            'categories' => function ($query) {
                $query->where('is_active', true)->orderBy('order');
            },
            'pages' => function ($query) {
                $query->where('is_active', true)->where('show_in_menu', true)->orderBy('order');
            },
            'theme'
        ]);

        return Inertia::render('Public/Checkout', [
            'website' => $website,
            'product' => $product,
            'paymentMethods' => [
                'stripe' => $user->hasStripeConfigured(),
                'paypal' => $user->hasPaypalConfigured(),
            ],
            'stripePublishableKey' => $user->hasStripeConfigured() ? $user->stripe_publishable_key : null,
            'paypalClientId' => $user->hasPaypalConfigured() ? $user->paypal_client_id : null,
            'paypalMode' => $user->paypal_mode ?? 'sandbox',
        ]);
    }

    public function createStripeSession(Request $request, $websiteParam, $productParam)
    {
        [$website, $product] = $this->resolveWebsiteAndProduct($request, $websiteParam, $productParam);
        $user = $website->user;

        if (!$user->hasStripeConfigured()) {
            return response()->json(['error' => 'Stripe is not configured.'], 400);
        }

        $validated = $request->validate([
            'customer_email' => 'required|email',
            'customer_name' => 'nullable|string|max:255',
        ]);

        try {
            \Stripe\Stripe::setApiKey($user->stripe_secret_key);

            $price = $product->is_on_sale ? $product->sale_price : $product->price;

            $order = Order::create([
                'website_id' => $website->id,
                'product_id' => $product->id,
                'customer_email' => $validated['customer_email'],
                'customer_name' => $validated['customer_name'] ?? null,
                'payment_provider' => 'stripe',
                'payment_status' => 'pending',
                'amount' => $price,
                'currency' => $product->currency,
                'product_snapshot' => [
                    'name' => $product->name,
                    'price' => $price,
                    'currency' => $product->currency,
                    'featured_image' => $product->featured_image,
                ],
                'status' => 'pending',
            ]);

            $successUrl = $website->getUrlForPath('checkout/' . $product->slug . '/success?order=' . $order->order_number . '&session_id={CHECKOUT_SESSION_ID}');
            $cancelUrl = $website->getUrlForPath('checkout/' . $product->slug . '?cancelled=true');

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($product->currency),
                        'product_data' => [
                            'name' => $product->name,
                            'description' => $product->short_description ?? null,
                            'images' => $product->processed_featured_image ? [$product->processed_featured_image] : [],
                        ],
                        'unit_amount' => (int) ($price * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'customer_email' => $validated['customer_email'],
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'product_id' => $product->id,
                    'website_id' => $website->id,
                ],
            ]);

            $order->update([
                'payment_intent_id' => $session->id,
            ]);

            return response()->json([
                'sessionId' => $session->id,
                'url' => $session->url,
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe checkout error', [
                'error' => $e->getMessage(),
                'product_id' => $product->id,
                'website_id' => $website->id,
            ]);

            return response()->json(['error' => 'Failed to create checkout session.'], 500);
        }
    }

    public function createPaypalOrder(Request $request, $websiteParam, $productParam)
    {
        [$website, $product] = $this->resolveWebsiteAndProduct($request, $websiteParam, $productParam);
        $user = $website->user;

        if (!$user->hasPaypalConfigured()) {
            return response()->json(['error' => 'PayPal is not configured.'], 400);
        }

        $validated = $request->validate([
            'customer_email' => 'required|email',
            'customer_name' => 'nullable|string|max:255',
        ]);

        try {
            $price = $product->is_on_sale ? $product->sale_price : $product->price;

            $order = Order::create([
                'website_id' => $website->id,
                'product_id' => $product->id,
                'customer_email' => $validated['customer_email'],
                'customer_name' => $validated['customer_name'] ?? null,
                'payment_provider' => 'paypal',
                'payment_status' => 'pending',
                'amount' => $price,
                'currency' => $product->currency,
                'product_snapshot' => [
                    'name' => $product->name,
                    'price' => $price,
                    'currency' => $product->currency,
                    'featured_image' => $product->featured_image,
                ],
                'status' => 'pending',
            ]);

            $baseUrl = $user->paypal_mode === 'live' 
                ? 'https://api-m.paypal.com' 
                : 'https://api-m.sandbox.paypal.com';

            $client = new \GuzzleHttp\Client();

            $tokenResponse = $client->post($baseUrl . '/v1/oauth2/token', [
                'auth' => [$user->paypal_client_id, $user->paypal_client_secret],
                'form_params' => [
                    'grant_type' => 'client_credentials',
                ],
            ]);

            $tokenData = json_decode($tokenResponse->getBody()->getContents(), true);
            $accessToken = $tokenData['access_token'];

            $orderResponse = $client->post($baseUrl . '/v2/checkout/orders', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [[
                        'reference_id' => $order->order_number,
                        'description' => $product->name,
                        'amount' => [
                            'currency_code' => $product->currency,
                            'value' => number_format($price, 2, '.', ''),
                        ],
                    ]],
                    'application_context' => [
                        'return_url' => $website->getUrlForPath('checkout/' . $product->slug . '/paypal-return?order=' . $order->order_number),
                        'cancel_url' => $website->getUrlForPath('checkout/' . $product->slug . '?cancelled=true'),
                        'brand_name' => $website->name,
                        'user_action' => 'PAY_NOW',
                    ],
                ],
            ]);

            $orderData = json_decode($orderResponse->getBody()->getContents(), true);

            $order->update([
                'payment_id' => $orderData['id'],
            ]);

            return response()->json([
                'orderId' => $orderData['id'],
                'orderNumber' => $order->order_number,
            ]);
        } catch (\Exception $e) {
            Log::error('PayPal order creation error', [
                'error' => $e->getMessage(),
                'product_id' => $product->id,
                'website_id' => $website->id,
            ]);

            return response()->json(['error' => 'Failed to create PayPal order.'], 500);
        }
    }

    public function capturePaypalOrder(Request $request, $websiteParam, $productParam)
    {
        [$website, $product] = $this->resolveWebsiteAndProduct($request, $websiteParam, $productParam);
        $user = $website->user;

        if (!$user->hasPaypalConfigured()) {
            return response()->json(['error' => 'PayPal is not configured.'], 400);
        }

        $validated = $request->validate([
            'paypal_order_id' => 'required|string',
            'order_number' => 'required|string',
        ]);

        $order = Order::where('order_number', $validated['order_number'])
            ->where('website_id', $website->id)
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found.'], 404);
        }

        try {
            $baseUrl = $user->paypal_mode === 'live' 
                ? 'https://api-m.paypal.com' 
                : 'https://api-m.sandbox.paypal.com';

            $client = new \GuzzleHttp\Client();

            $tokenResponse = $client->post($baseUrl . '/v1/oauth2/token', [
                'auth' => [$user->paypal_client_id, $user->paypal_client_secret],
                'form_params' => [
                    'grant_type' => 'client_credentials',
                ],
            ]);

            $tokenData = json_decode($tokenResponse->getBody()->getContents(), true);
            $accessToken = $tokenData['access_token'];

            $captureResponse = $client->post($baseUrl . '/v2/checkout/orders/' . $validated['paypal_order_id'] . '/capture', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
            ]);

            $captureData = json_decode($captureResponse->getBody()->getContents(), true);

            if ($captureData['status'] === 'COMPLETED') {
                $captureId = $captureData['purchase_units'][0]['payments']['captures'][0]['id'] ?? null;

                $order->markAsPaid($captureId, $validated['paypal_order_id']);

                return response()->json([
                    'success' => true,
                    'orderNumber' => $order->order_number,
                    'redirectUrl' => $website->getUrlForPath('checkout/' . $product->slug . '/success?order=' . $order->order_number),
                ]);
            }

            return response()->json(['error' => 'Payment was not completed.'], 400);
        } catch (\Exception $e) {
            Log::error('PayPal capture error', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
            ]);

            return response()->json(['error' => 'Failed to capture payment.'], 500);
        }
    }

    public function success(Request $request, $websiteParam, $productParam)
    {
        [$website, $product] = $this->resolveWebsiteAndProduct($request, $websiteParam, $productParam);
        
        $orderNumber = $request->query('order');
        $sessionId = $request->query('session_id');

        $order = Order::where('order_number', $orderNumber)
            ->where('website_id', $website->id)
            ->first();

        if (!$order) {
            abort(404, 'Order not found.');
        }

        if ($sessionId && $order->payment_provider === 'stripe' && !$order->isPaid()) {
            $user = $website->user;
            
            try {
                \Stripe\Stripe::setApiKey($user->stripe_secret_key);
                $session = \Stripe\Checkout\Session::retrieve($sessionId);

                if ($session->payment_status === 'paid') {
                    $order->markAsPaid($session->payment_intent, $sessionId);
                }
            } catch (\Exception $e) {
                Log::error('Stripe session verification error', [
                    'error' => $e->getMessage(),
                    'order_id' => $order->id,
                ]);
            }
        }

        return Inertia::render('Public/CheckoutSuccess', [
            'website' => $website,
            'product' => $product,
            'order' => $order,
        ]);
    }

    public function paypalReturn(Request $request, $websiteParam, $productParam)
    {
        [$website, $product] = $this->resolveWebsiteAndProduct($request, $websiteParam, $productParam);
        
        return redirect($website->getUrlForPath('checkout/' . $product->slug) . '?paypal_pending=true&order=' . $request->query('order'));
    }

    public function stripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        Log::info('Stripe webhook received', ['payload_length' => strlen($payload)]);

        try {
            $orders = Order::where('payment_provider', 'stripe')
                ->where('payment_status', 'pending')
                ->whereNotNull('payment_intent_id')
                ->get();

            foreach ($orders as $order) {
                $website = $order->website;
                $user = $website->user;

                if (!$user->stripe_webhook_secret) {
                    continue;
                }

                try {
                    $event = \Stripe\Webhook::constructEvent(
                        $payload,
                        $sigHeader,
                        $user->stripe_webhook_secret
                    );

                    if ($event->type === 'checkout.session.completed') {
                        $session = $event->data->object;

                        if (isset($session->metadata->order_number) && $session->metadata->order_number === $order->order_number) {
                            $order->markAsPaid($session->payment_intent, $session->id);
                            Log::info('Order marked as paid via webhook', ['order_id' => $order->id]);
                        }
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
