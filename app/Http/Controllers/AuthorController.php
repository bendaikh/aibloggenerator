<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AuthorController extends Controller
{
    public function index(Website $website)
    {
        $authors = Author::where('website_id', $website->id)
            ->orderBy('name')
            ->get();

        return Inertia::render('SuperAdmin/Authors', [
            'currentWebsite' => $website,
            'authors' => $authors,
            'websites' => Website::where('user_id', auth()->id())->get(),
        ]);
    }

    public function store(Request $request, Website $website)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $directory = public_path('uploads/images/authors');
            
            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
            
            $file->move($directory, $filename);
            $validated['image'] = asset('uploads/images/authors/' . $filename);
        }

        $website->authors()->create($validated);

        return back()->with('success', 'Author created successfully.');
    }

    public function update(Request $request, Website $website, Author $author)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image_file')) {
            // Delete old image if it exists and is local
            if ($author->image && Str::contains($author->image, asset('uploads/images/authors/'))) {
                $oldPath = public_path(str_replace(asset(''), '', $author->image));
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            $file = $request->file('image_file');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $directory = public_path('uploads/images/authors');
            
            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
            
            $file->move($directory, $filename);
            $validated['image'] = asset('uploads/images/authors/' . $filename);
        }

        $author->update($validated);

        return back()->with('success', 'Author updated successfully.');
    }

    public function destroy(Website $website, Author $author)
    {
        // Delete image if it exists and is local
        if ($author->image && Str::contains($author->image, asset('uploads/images/authors/'))) {
            $oldPath = public_path(str_replace(asset(''), '', $author->image));
            if (File::exists($oldPath)) {
                File::delete($oldPath);
            }
        }

        $author->delete();

        return back()->with('success', 'Author deleted successfully.');
    }
}
