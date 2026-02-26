# Quick Start Guide - Testing the Home Decor Theme

## Step 1: Ensure Themes are in Database

Run this command to make sure the themes are seeded:

```bash
php artisan db:seed --class=SetDefaultThemesSeeder
```

Or manually insert via SQL:

```sql
INSERT INTO themes (name, slug, description, show_recipe_sections, is_active, created_at, updated_at)
VALUES 
('Recipe Theme', 'recipe', 'Full recipe theme with ingredients and instructions cards', 1, 1, NOW(), NOW()),
('Home Decor Theme', 'home-decor', 'Warm, bohemian-inspired theme for home decor blogs', 0, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    name = VALUES(name),
    description = VALUES(description),
    show_recipe_sections = VALUES(show_recipe_sections),
    is_active = VALUES(is_active),
    updated_at = NOW();
```

## Step 2: Apply Theme to Your Website

### Option A: Using Laravel Tinker (Recommended)

```bash
php artisan tinker
```

Then run:

```php
// Get your website (replace with your actual website ID or slug)
$website = \App\Models\Website::find(1); // or use ->where('slug', 'your-slug')->first()

// Get the home decor theme
$theme = \App\Models\Theme::where('slug', 'home-decor')->first();

// Apply the theme
$website->theme_id = $theme->id;
$website->save();

// Verify it worked
echo "Theme applied! Visit: " . $website->url . "\n";
```

### Option B: Using Direct SQL

```sql
-- First, find your website ID
SELECT id, name, slug FROM websites;

-- Then apply the theme (replace YOUR_WEBSITE_ID with the actual ID)
UPDATE websites 
SET theme_id = (SELECT id FROM themes WHERE slug = 'home-decor')
WHERE id = YOUR_WEBSITE_ID;

-- Verify the change
SELECT w.id, w.name, t.name as theme_name, t.slug as theme_slug
FROM websites w
LEFT JOIN themes t ON w.theme_id = t.id
WHERE w.id = YOUR_WEBSITE_ID;
```

## Step 3: Visit Your Website

After applying the theme, visit your website:

### If using path-based URLs:
```
http://localhost:8000/site/your-website-slug
```

### If using custom domain:
```
http://your-custom-domain.com
```

### If using subdomain (production):
```
http://your-subdomain.yourdomain.com
```

## Step 4: What to Check

✅ **Homepage:**
- Warm beige/cream background (`#F5F1ED`)
- Terracotta accent buttons (`#FF6B4A`)
- "Living Warm & Styling Wild" hero section
- Circular category icons (Curated Collections)
- Latest Journal article cards
- Dark footer with social icons

✅ **Article Pages:**
- Same warm color scheme
- Home decor themed navigation
- Cleaner article layouts (no recipe sections)

✅ **Category Pages:**
- Themed article grid
- Warm colors throughout

## Step 5: Switch Back to Recipe Theme (Optional)

If you want to switch back to the recipe theme:

```bash
php artisan tinker
```

```php
$website = \App\Models\Website::find(1);
$theme = \App\Models\Theme::where('slug', 'recipe')->first();
$website->theme_id = $theme->id;
$website->save();
```

## Troubleshooting

### Theme not showing?

1. **Clear your browser cache** (Ctrl+Shift+R or Cmd+Shift+R)

2. **Check if theme is applied:**
   ```bash
   php artisan tinker
   ```
   ```php
   $website = \App\Models\Website::find(1);
   $website->theme; // Should show the theme object
   ```

3. **Check if assets are built:**
   ```bash
   ls -la public/build/assets/ | grep Home
   ```
   You should see files like:
   - `HomeDecor-*.js`
   - `HomeDecorLayout-*.js`
   - `ThemeAwareLayout-*.js`

4. **Rebuild assets if needed:**
   ```bash
   npm run build
   ```

5. **Check browser console for errors:**
   - Open Developer Tools (F12)
   - Look for any JavaScript errors in the Console tab

### Colors not showing correctly?

- Make sure you're not caching old CSS
- Try opening in incognito/private mode
- Check if `tailwind.config.js` is properly configured

### Still using old theme?

The theme detection logic is in:
- `app/Http/Controllers/PublicWebsiteController.php` (line ~577)
- Look for the `renderHome()` method

It checks: `if ($website->theme && $website->theme->slug === 'home-decor')`

## Customization

### Change Banner Text

```sql
UPDATE websites 
SET theme_settings = JSON_SET(
    COALESCE(theme_settings, '{}'),
    '$.banner_text',
    'YOUR CUSTOM TEXT →'
)
WHERE id = YOUR_WEBSITE_ID;
```

### Add Hero Image

```sql
UPDATE websites 
SET theme_settings = JSON_SET(
    COALESCE(theme_settings, '{}'),
    '$.hero_image',
    '/uploads/images/your-hero-image.jpg'
)
WHERE id = YOUR_WEBSITE_ID;
```

## Need Help?

Check the detailed guide: `HOME_DECOR_THEME_GUIDE.md`
