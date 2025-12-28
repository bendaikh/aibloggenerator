<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Website;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index(): Response
    {
        $user = auth()->user();
        
        // Get all categories for the user's websites
        $categories = Category::whereHas('website', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with('website')
        ->orderBy('created_at', 'desc')
        ->get();

        // Get user's websites for the create form
        $websites = Website::where('user_id', $user->id)
            ->orderBy('name')
            ->get();

        return Inertia::render('SuperAdmin/Categories', [
            'categories' => $categories,
            'websites' => $websites
        ]);
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'website_id' => 'required|exists:websites,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        // Verify the website belongs to the authenticated user
        $website = Website::findOrFail($validated['website_id']);
        if ($website->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $category = Category::create($validated);

        return redirect()->route('superadmin.categories.index')
            ->with('success', 'Category created successfully!');
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category)
    {
        // Verify the category's website belongs to the authenticated user
        if ($category->website->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        return redirect()->route('superadmin.categories.index')
            ->with('success', 'Category updated successfully!');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category)
    {
        // Verify the category's website belongs to the authenticated user
        if ($category->website->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $category->delete();

        return redirect()->route('superadmin.categories.index')
            ->with('success', 'Category deleted successfully!');
    }
}

