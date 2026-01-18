<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Log;

class SubscriberController extends Controller
{
    use AuthorizesRequests;

    /**
     * Get common data for views (websites list and current website)
     */
    private function getCommonData(Website $website): array
    {
        return [
            'currentWebsite' => $website,
            'websites' => auth()->user()->websites()
                ->withCount(['articles', 'categories'])
                ->get(),
        ];
    }

    /**
     * Display the subscribers listing.
     */
    public function index(Website $website): Response
    {
        $this->authorize('view', $website);

        $subscribers = Subscriber::where('website_id', $website->id)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $stats = [
            'total' => Subscriber::where('website_id', $website->id)->count(),
            'active' => Subscriber::where('website_id', $website->id)->where('is_active', true)->count(),
            'this_month' => Subscriber::where('website_id', $website->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return Inertia::render('SuperAdmin/Subscribers/Index', array_merge(
            $this->getCommonData($website),
            [
                'subscribers' => $subscribers,
                'stats' => $stats,
            ]
        ));
    }

    /**
     * Delete a subscriber.
     */
    public function destroy(Website $website, Subscriber $subscriber)
    {
        $this->authorize('view', $website);

        if ($subscriber->website_id !== $website->id) {
            abort(404);
        }

        $subscriber->delete();

        return redirect()->route('superadmin.subscribers.index', ['website' => $website->id])
            ->with('success', 'Subscriber deleted successfully.');
    }

    /**
     * Export subscribers as CSV.
     */
    public function export(Request $request, Website $website)
    {
        $this->authorize('view', $website);

        $subscribers = Subscriber::where('website_id', $website->id)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get(['email', 'name', 'source', 'subscribed_at', 'created_at']);

        $filename = 'subscribers-' . $website->subdomain . '-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($subscribers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Email', 'Name', 'Source', 'Subscribed At', 'Created At']);

            foreach ($subscribers as $subscriber) {
                fputcsv($file, [
                    $subscriber->email,
                    $subscriber->name ?? '',
                    $subscriber->source,
                    $subscriber->subscribed_at?->format('Y-m-d H:i:s'),
                    $subscriber->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Handle subscription from public website (via /site/{website}/subscribe).
     */
    public function subscribe(Request $request, Website $website)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:50',
        ]);

        try {
            // Check if already subscribed
            $existing = Subscriber::where('website_id', $website->id)
                ->where('email', $validated['email'])
                ->first();

            if ($existing) {
                if (!$existing->is_active) {
                    // Reactivate
                    $existing->update([
                        'is_active' => true,
                        'unsubscribed_at' => null,
                        'subscribed_at' => now(),
                    ]);
                    return response()->json(['success' => true, 'message' => 'Welcome back! You have been resubscribed.']);
                }
                return response()->json(['success' => true, 'message' => 'You are already subscribed!']);
            }

            // Create new subscriber
            Subscriber::create([
                'website_id' => $website->id,
                'email' => $validated['email'],
                'name' => $validated['name'] ?? null,
                'source' => $validated['source'] ?? 'website',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'subscribed_at' => now(),
                'is_active' => true,
            ]);

            Log::info('New subscriber', [
                'website_id' => $website->id,
                'email' => $validated['email'],
                'source' => $validated['source'] ?? 'website',
            ]);

            return response()->json(['success' => true, 'message' => 'Thank you for subscribing!']);

        } catch (\Exception $e) {
            Log::error('Failed to subscribe', [
                'error' => $e->getMessage(),
                'email' => $validated['email'] ?? 'unknown',
            ]);

            return response()->json(['success' => false, 'message' => 'Something went wrong. Please try again.'], 500);
        }
    }

    /**
     * Handle subscription from subdomain/custom domain (via middleware-identified website).
     */
    public function subscribeByDomain(Request $request)
    {
        // Get website from request (set by identify.website middleware)
        $website = $request->get('website');
        
        if (!$website) {
            // Try to find website by domain
            $host = $request->getHost();
            $website = Website::where('domain', $host)
                ->orWhere('subdomain', explode('.', $host)[0])
                ->first();
        }

        if (!$website) {
            return response()->json(['success' => false, 'message' => 'Website not found.'], 404);
        }

        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:50',
        ]);

        try {
            // Check if already subscribed
            $existing = Subscriber::where('website_id', $website->id)
                ->where('email', $validated['email'])
                ->first();

            if ($existing) {
                if (!$existing->is_active) {
                    // Reactivate
                    $existing->update([
                        'is_active' => true,
                        'unsubscribed_at' => null,
                        'subscribed_at' => now(),
                    ]);
                    return response()->json(['success' => true, 'message' => 'Welcome back! You have been resubscribed.']);
                }
                return response()->json(['success' => true, 'message' => 'You are already subscribed!']);
            }

            // Create new subscriber
            Subscriber::create([
                'website_id' => $website->id,
                'email' => $validated['email'],
                'name' => $validated['name'] ?? null,
                'source' => $validated['source'] ?? 'website',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'subscribed_at' => now(),
                'is_active' => true,
            ]);

            Log::info('New subscriber (domain)', [
                'website_id' => $website->id,
                'email' => $validated['email'],
                'source' => $validated['source'] ?? 'website',
            ]);

            return response()->json(['success' => true, 'message' => 'Thank you for subscribing!']);

        } catch (\Exception $e) {
            Log::error('Failed to subscribe (domain)', [
                'error' => $e->getMessage(),
                'email' => $validated['email'] ?? 'unknown',
            ]);

            return response()->json(['success' => false, 'message' => 'Something went wrong. Please try again.'], 500);
        }
    }
}
