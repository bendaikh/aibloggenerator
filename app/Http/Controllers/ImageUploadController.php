<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImageUploadController extends Controller
{
    /**
     * Upload an image and return its URL
     * 
     * This stores images directly in public/uploads/ folder to avoid
     * symlink issues on shared hosting (like Hostinger)
     */
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            'type' => 'nullable|string|in:article,website,category',
        ]);

        $type = $request->input('type', 'general');
        $file = $request->file('image');
        
        // Generate unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        
        // Create directory if it doesn't exist
        $directory = public_path("uploads/images/{$type}");
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
        
        // Move file directly to public folder (no symlink needed!)
        $file->move($directory, $filename);
        
        // Path relative to public folder
        $relativePath = "uploads/images/{$type}/{$filename}";

        // Generate the full URL
        $url = asset($relativePath);

        return response()->json([
            'success' => true,
            'url' => $url,
            'path' => $relativePath,
        ]);
    }

    /**
     * Delete an uploaded image
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $path = $request->input('path');

        // Security check - only allow deleting from uploads/images folder
        if (!Str::startsWith($path, 'uploads/images/')) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid path',
            ], 400);
        }

        $fullPath = public_path($path);

        if (File::exists($fullPath)) {
            File::delete($fullPath);
            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Image not found',
        ], 404);
    }
}

