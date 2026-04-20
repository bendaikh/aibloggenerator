<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index(int $websiteId): Response
    {
        $website = Website::findOrFail($websiteId);
        
        $products = $website->products()
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('SuperAdmin/Products/Index', [
            'currentWebsite' => $website,
            'websites' => Website::where('user_id', auth()->id())->withCount(['articles', 'categories'])->get(),
            'products' => $products,
        ]);
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(int $websiteId): Response
    {
        $website = Website::findOrFail($websiteId);

        return Inertia::render('SuperAdmin/Products/Create', [
            'currentWebsite' => $website,
            'websites' => Website::where('user_id', auth()->id())->withCount(['articles', 'categories'])->get(),
        ]);
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request, int $websiteId)
    {
        $website = Website::findOrFail($websiteId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'currency' => 'required|string|max:3',
            'featured_image' => 'nullable|string',
            'gallery_images' => 'nullable|array',
            'sku' => 'nullable|string|max:100',
            'stock_quantity' => 'nullable|integer|min:0',
            'track_stock' => 'boolean',
            'is_digital' => 'boolean',
            'digital_file_url' => 'nullable|string',
            'external_url' => 'nullable|url',
            'status' => 'required|in:draft,published,archived',
            'is_featured' => 'boolean',
            'order' => 'nullable|integer',
        ]);

        $validated['website_id'] = $website->id;
        $validated['user_id'] = auth()->id();
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['stock_quantity'] = $validated['stock_quantity'] ?? 0;
        $validated['order'] = $validated['order'] ?? 0;

        // Ensure unique slug within the website
        $baseSlug = $validated['slug'];
        $counter = 1;
        while (Product::where('website_id', $website->id)->where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $baseSlug . '-' . $counter;
            $counter++;
        }

        Product::create($validated);

        return redirect()->route('superadmin.products.index', ['website' => $websiteId])
            ->with('success', 'Product created successfully.');
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(int $websiteId, int $productId): Response
    {
        $website = Website::findOrFail($websiteId);
        $product = $website->products()->findOrFail($productId);

        return Inertia::render('SuperAdmin/Products/Edit', [
            'currentWebsite' => $website,
            'websites' => Website::where('user_id', auth()->id())->withCount(['articles', 'categories'])->get(),
            'product' => $product,
        ]);
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, int $websiteId, int $productId)
    {
        $website = Website::findOrFail($websiteId);
        $product = $website->products()->findOrFail($productId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'currency' => 'required|string|max:3',
            'featured_image' => 'nullable|string',
            'gallery_images' => 'nullable|array',
            'sku' => 'nullable|string|max:100',
            'stock_quantity' => 'nullable|integer|min:0',
            'track_stock' => 'boolean',
            'is_digital' => 'boolean',
            'digital_file_url' => 'nullable|string',
            'external_url' => 'nullable|url',
            'status' => 'required|in:draft,published,archived',
            'is_featured' => 'boolean',
            'order' => 'nullable|integer',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['stock_quantity'] = $validated['stock_quantity'] ?? 0;
        $validated['order'] = $validated['order'] ?? 0;

        // Ensure unique slug within the website (excluding current product)
        $baseSlug = $validated['slug'];
        $counter = 1;
        while (Product::where('website_id', $website->id)
            ->where('slug', $validated['slug'])
            ->where('id', '!=', $product->id)
            ->exists()) {
            $validated['slug'] = $baseSlug . '-' . $counter;
            $counter++;
        }

        $product->update($validated);

        return redirect()->route('superadmin.products.index', ['website' => $websiteId])
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product.
     */
    public function destroy(int $websiteId, int $productId)
    {
        $website = Website::findOrFail($websiteId);
        $product = $website->products()->findOrFail($productId);

        $product->delete();

        return redirect()->route('superadmin.products.index', ['website' => $websiteId])
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Update product order.
     */
    public function updateOrder(Request $request, int $websiteId)
    {
        $website = Website::findOrFail($websiteId);

        $validated = $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|integer',
            'products.*.order' => 'required|integer',
        ]);

        foreach ($validated['products'] as $item) {
            $website->products()->where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['success' => true]);
    }
}
