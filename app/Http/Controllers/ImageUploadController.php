<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageUploadController extends Controller
{
    /**
     * Upload an image and return its URL
     * 
     * This stores images directly in public/uploads/ folder to avoid
     * symlink issues on shared hosting (like Hostinger)
     * All images are automatically converted to WebP format for better performance
     */
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            'type' => 'nullable|string|in:article,website,category,product',
        ]);

        $type = $request->input('type', 'general');
        $file = $request->file('image');

        try {
            // Create directory if it doesn't exist
            $directory = public_path("uploads/images/{$type}");
            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            // Generate unique filename with .webp extension
            $filename = Str::uuid() . '.webp';
            $relativePath = "uploads/images/{$type}/{$filename}";

            try {
                // Create image manager instance
                $manager = new ImageManager(new Driver());

                // Load and process the image
                $image = $manager->read($file->getRealPath());

                // Convert to WebP format with quality optimization (85% quality is a good balance)
                // This will significantly reduce file size while maintaining good visual quality
                $image->toWebp(85)->save($directory . '/' . $filename);
            } catch (\Exception $e) {
                // Fallback: if WebP conversion fails, save original file
                // This ensures the upload still works even if image processing fails
                \Log::warning('WebP conversion failed, saving original: ' . $e->getMessage());

                $originalExtension = $file->getClientOriginalExtension() ?: 'jpg';
                $fallbackFilename = Str::uuid() . '.' . $originalExtension;
                $file->move($directory, $fallbackFilename);
                $relativePath = "uploads/images/{$type}/{$fallbackFilename}";
            }

            return response()->json([
                'success' => true,
                'url' => asset($relativePath),
                'path' => $relativePath,
            ]);
        } catch (\Exception $e) {
            \Log::error('Image upload failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Upload failed. Please try again.',
            ], 500);
        }
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

