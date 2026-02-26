# Bug Fix: "Attempt to read property 'slug' on string" Error

## Issue
When trying to view a website with the home decor theme, you get this error:
```
Attempt to read property "slug" on string at PublicWebsiteController.php:638
```

## Root Cause
The `websites` table has both a `theme` column (string) and a `theme_id` column (foreign key). The `theme` attribute in the `$fillable` array conflicts with the `theme()` relationship method, causing `$website->theme` to return the string value instead of the relationship object.

## Solution Applied
Updated `PublicWebsiteController.php` in the `renderHome()` method to safely handle the theme relationship:

```php
// Check if the website has a theme relationship loaded
// We need to handle the case where 'theme' attribute might conflict with theme() relationship
$websiteTheme = null;
if ($website->relationLoaded('theme')) {
    $websiteTheme = $website->getRelation('theme');
} elseif ($website->theme_id) {
    // Fallback: load theme by ID if not already loaded
    $websiteTheme = \App\Models\Theme::find($website->theme_id);
}

if ($websiteTheme && $websiteTheme->slug === 'home-decor') {
    $viewComponent = 'Public/Website/HomeDecor';
}
```

## Files Changed
- `app/Http/Controllers/PublicWebsiteController.php` (lines 638-650)

## How to Test the Fix

1. **Clear Laravel cache:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

2. **Try viewing your website again:**
   ```
   http://localhost:8000/site/your-website-slug
   ```

3. **Verify the theme is applied:**
   - You should see the warm beige/cream background
   - Terracotta accent colors
   - Home decor themed layout

## Alternative Fix (Optional)
If you continue having issues, you can remove the legacy `theme` column from the `$fillable` array in `Website.php` model:

```php
protected $fillable = [
    'user_id',
    'name',
    'slug',
    // ... other fields ...
    // 'theme', // REMOVE THIS LINE
    'theme_id', // Keep this
    // ... rest of fields ...
];
```

However, this might break backward compatibility if you have old code still using the `theme` column.

## Prevention
When using both a column and a relationship with similar names:
- ✅ Use `->relationLoaded()` and `->getRelation()` to safely access relationships
- ✅ Call the relationship method explicitly: `$website->theme()`
- ❌ Avoid direct property access when there's a naming conflict: `$website->theme`

## Related Files
- `app/Models/Website.php` (line 24 - theme in fillable)
- `app/Models/Website.php` (line 92-95 - theme() relationship)
- `database/migrations/2026_02_19_224447_add_theme_id_to_websites_table.php`

## Status
✅ **FIXED** - The error should no longer occur when viewing websites with the home decor theme.
