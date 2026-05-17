<?php

namespace App\Http\Controllers;

use App\Models\DomainRequest;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class DomainRequestController extends Controller
{
    /**
     * Display the user's domain onboarding page.
     */
    public function index()
    {
        $user = Auth::user();
        
        $domainRequests = DomainRequest::where('user_id', $user->id)
            ->with(['website:id,name,slug'])
            ->orderBy('created_at', 'desc')
            ->get();

        $websites = $user->accessibleWebsitesQuery()
            ->withCount(['articles', 'categories'])
            ->get();

        return Inertia::render('Organization/DomainOnboarding', [
            'domainRequests' => $domainRequests,
            'websites' => $websites,
        ]);
    }

    /**
     * Store a new domain request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'domain' => [
                'required',
                'string',
                'max:255',
                'regex:/^(?!:\/\/)([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/',
                Rule::unique('domain_requests', 'domain'),
                Rule::unique('websites', 'domain'),
            ],
        ], [
            'domain.regex' => 'Please enter a valid domain name (e.g., example.com)',
            'domain.unique' => 'This domain has already been submitted or is already in use.',
        ]);

        // Clean the domain (remove http/https, www, trailing slash)
        $domain = strtolower(trim($validated['domain']));
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = preg_replace('#^www\.#', '', $domain);
        $domain = rtrim($domain, '/');

        DomainRequest::create([
            'user_id' => Auth::id(),
            'domain' => $domain,
            'status' => DomainRequest::STATUS_PENDING,
        ]);

        return back()->with('success', 'Domain request submitted successfully. Please wait for approval.');
    }

    /**
     * Cancel/delete a pending domain request.
     */
    public function destroy(DomainRequest $domainRequest)
    {
        // Only allow users to delete their own pending requests
        if ($domainRequest->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$domainRequest->isPending()) {
            return back()->with('error', 'You can only cancel pending domain requests.');
        }

        $domainRequest->delete();

        return back()->with('success', 'Domain request cancelled.');
    }

    // ==========================================
    // SUPERADMIN METHODS
    // ==========================================

    /**
     * Display all pending domains for superadmin.
     */
    public function adminIndex()
    {
        $user = Auth::user();
        
        if (!$user->isSuperAdmin()) {
            abort(403);
        }

        $pendingDomains = DomainRequest::with(['user:id,name,email', 'website:id,name,slug,user_id'])
            ->whereIn('status', [
                DomainRequest::STATUS_PENDING,
                DomainRequest::STATUS_PARKING,
                DomainRequest::STATUS_PARKED,
            ])
            ->orderBy('created_at', 'asc')
            ->get();

        $allDomains = DomainRequest::with(['user:id,name,email', 'website:id,name,slug,user_id', 'approver:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get all websites (superadmin can assign domain to any website)
        $websites = Website::select('id', 'name', 'slug', 'user_id')
            ->with('user:id,name')
            ->get();

        return Inertia::render('Organization/PendingDomains', [
            'pendingDomains' => $pendingDomains,
            'allDomains' => $allDomains,
            'websites' => $websites,
        ]);
    }

    /**
     * Mark a domain as being parked (superadmin started parking process).
     */
    public function markParking(DomainRequest $domainRequest)
    {
        $user = Auth::user();
        
        if (!$user->isSuperAdmin()) {
            abort(403);
        }

        if (!$domainRequest->isPending()) {
            return back()->with('error', 'This domain is not in pending status.');
        }

        $domainRequest->markAsParking();

        return back()->with('success', 'Domain marked as parking in progress.');
    }

    /**
     * Mark a domain as parked (superadmin completed parking with Hostinger).
     */
    public function markParked(Request $request, DomainRequest $domainRequest)
    {
        $user = Auth::user();
        
        if (!$user->isSuperAdmin()) {
            abort(403);
        }

        if (!$domainRequest->isPending() && !$domainRequest->isParking()) {
            return back()->with('error', 'This domain cannot be marked as parked.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $domainRequest->update([
            'status' => DomainRequest::STATUS_PARKED,
            'parked_at' => now(),
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Domain marked as parked. Ready for approval.');
    }

    /**
     * Approve a domain request and optionally assign to a website.
     */
    public function approve(Request $request, DomainRequest $domainRequest)
    {
        $user = Auth::user();
        
        if (!$user->isSuperAdmin()) {
            abort(403);
        }

        if (!$domainRequest->isParked()) {
            return back()->with('error', 'Domain must be parked before it can be approved.');
        }

        $validated = $request->validate([
            'website_id' => 'nullable|exists:websites,id',
        ]);

        $websiteId = $validated['website_id'] ?? null;

        // If assigning to a website, update the website's domain
        if ($websiteId) {
            $website = Website::find($websiteId);
            $website->update(['domain' => $domainRequest->domain]);
        }

        $domainRequest->approve($user->id, $websiteId);

        return back()->with('success', 'Domain approved successfully.' . ($websiteId ? ' Domain assigned to website.' : ''));
    }

    /**
     * Reject a domain request.
     */
    public function reject(Request $request, DomainRequest $domainRequest)
    {
        $user = Auth::user();
        
        if (!$user->isSuperAdmin()) {
            abort(403);
        }

        if ($domainRequest->isApproved()) {
            return back()->with('error', 'Cannot reject an already approved domain.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $domainRequest->reject($validated['rejection_reason']);

        return back()->with('success', 'Domain request rejected.');
    }

    /**
     * Update notes for a domain request.
     */
    public function updateNotes(Request $request, DomainRequest $domainRequest)
    {
        $user = Auth::user();
        
        if (!$user->isSuperAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $domainRequest->update(['notes' => $validated['notes']]);

        return back()->with('success', 'Notes updated.');
    }
}
