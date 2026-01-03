<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
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
     * Display a listing of the categories.
     */
    public function index(Website $website): Response
    {
        $this->authorize('view', $website);
        
        // Get all categories for this website
        $categories = Category::where('website_id', $website->id)
            ->withCount('articles')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('SuperAdmin/Categories', array_merge(
            $this->getCommonData($website),
            ['categories' => $categories]
        ));
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request, Website $website)
    {
        $this->authorize('view', $website);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $directory = public_path('uploads/images/categories');
            
            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
            
            $file->move($directory, $filename);
            $validated['image'] = asset('uploads/images/categories/' . $filename);
        }

        $validated['website_id'] = $website->id;

        Category::create($validated);

        return redirect()->route('superadmin.categories.index', ['website' => $website->id])
            ->with('success', 'Category created successfully!');
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Website $website, Category $category)
    {
        // Verify the category belongs to this website
        if ($category->website_id !== $website->id) {
            abort(403, 'Category does not belong to this website.');
        }

        $this->authorize('view', $website);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image_file')) {
            // Delete old image if it exists and is local
            if ($category->image && Str::contains($category->image, asset('uploads/images/categories/'))) {
                $oldPath = public_path(str_replace(asset(''), '', $category->image));
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            $file = $request->file('image_file');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $directory = public_path('uploads/images/categories');
            
            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
            
            $file->move($directory, $filename);
            $validated['image'] = asset('uploads/images/categories/' . $filename);
        }

        $category->update($validated);

        return redirect()->route('superadmin.categories.index', ['website' => $website->id])
            ->with('success', 'Category updated successfully!');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Website $website, Category $category)
    {
        // Verify the category belongs to this website
        if ($category->website_id !== $website->id) {
            abort(403, 'Category does not belong to this website.');
        }

        $this->authorize('view', $website);

        $category->delete();

        return redirect()->route('superadmin.categories.index', ['website' => $website->id])
            ->with('success', 'Category deleted successfully!');
    }
}
