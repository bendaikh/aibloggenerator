# Home Decor Theme - Command Reference

Quick reference for common operations with the home decor theme.

## Database Commands

### Check Available Themes
```sql
SELECT id, name, slug, show_recipe_sections, is_active 
FROM themes 
ORDER BY id;
```

### Check Which Theme a Website Uses
```sql
SELECT 
    w.id,
    w.name as website_name,
    w.slug as website_slug,
    t.name as theme_name,
    t.slug as theme_slug
FROM websites w
LEFT JOIN themes t ON w.theme_id = t.id
WHERE w.id = YOUR_WEBSITE_ID;
```

### Apply Home Decor Theme (SQL)
```sql
UPDATE websites 
SET theme_id = (SELECT id FROM themes WHERE slug = 'home-decor')
WHERE id = YOUR_WEBSITE_ID;
```

### Apply Recipe Theme (SQL)
```sql
UPDATE websites 
SET theme_id = (SELECT id FROM themes WHERE slug = 'recipe')
WHERE id = YOUR_WEBSITE_ID;
```

### Remove Theme (Use Default)
```sql
UPDATE websites 
SET theme_id = NULL
WHERE id = YOUR_WEBSITE_ID;
```

## Laravel Artisan Commands

### Open Tinker Console
```bash
php artisan tinker
```

### Apply Theme via Tinker
```php
// Get website
$website = \App\Models\Website::find(1);
// or by slug
$website = \App\Models\Website::where('slug', 'your-slug')->first();

// Apply home decor theme
$theme = \App\Models\Theme::where('slug', 'home-decor')->first();
$website->theme_id = $theme->id;
$website->save();

// Verify
echo "Theme: " . $website->theme->name . "\n";
echo "URL: " . $website->url . "\n";
```

### Check All Websites and Their Themes
```php
\App\Models\Website::with('theme')->get()->map(function($w) {
    return [
        'id' => $w->id,
        'name' => $w->name,
        'theme' => $w->theme->name ?? 'None',
    ];
});
```

### Seed Themes
```bash
php artisan db:seed --class=SetDefaultThemesSeeder
```

## Theme Customization Commands

### Update Banner Text
```sql
UPDATE websites 
SET theme_settings = JSON_SET(
    COALESCE(theme_settings, '{}'),
    '$.banner_text',
    'NEW IDEAS EVERY DAY! Join our community →'
)
WHERE id = YOUR_WEBSITE_ID;
```

### Add Hero Image
```sql
UPDATE websites 
SET theme_settings = JSON_SET(
    COALESCE(theme_settings, '{}'),
    '$.hero_image',
    '/uploads/images/hero.jpg'
)
WHERE id = YOUR_WEBSITE_ID;
```

### Customize Newsletter CTA
```sql
UPDATE websites 
SET theme_settings = JSON_SET(
    COALESCE(theme_settings, '{}'),
    '$.newsletter_cta_title', 'GET INSPIRED',
    '$.newsletter_cta_subtitle', 'WEEKLY TIPS',
    '$.newsletter_cta_button', 'JOIN NOW'
)
WHERE id = YOUR_WEBSITE_ID;
```

### View Current Theme Settings
```sql
SELECT 
    id,
    name,
    theme_settings
FROM websites
WHERE id = YOUR_WEBSITE_ID;
```

## Development Commands

### Build Assets
```bash
npm run build
```

### Watch for Changes (Development)
```bash
npm run dev
```

### Clear Application Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Check Routes
```bash
php artisan route:list --path=site
```

## Testing Commands

### Test Website with Home Decor Theme
```bash
# 1. Apply theme
php artisan tinker
$website = \App\Models\Website::first();
$theme = \App\Models\Theme::where('slug', 'home-decor')->first();
$website->theme_id = $theme->id;
$website->save();
exit

# 2. Visit website
echo "Visit: http://localhost:8000/site/" . $website->slug
```

### Create Test Categories for Home Decor
```php
php artisan tinker

$website = \App\Models\Website::find(YOUR_ID);

// Create home decor categories
$categories = [
    ['name' => 'Textiles', 'slug' => 'textiles'],
    ['name' => 'Greenery', 'slug' => 'greenery'],
    ['name' => 'Ceramics', 'slug' => 'ceramics'],
    ['name' => 'DIY Projects', 'slug' => 'diy-projects'],
];

foreach ($categories as $cat) {
    \App\Models\Category::create([
        'website_id' => $website->id,
        'name' => $cat['name'],
        'slug' => $cat['slug'],
        'is_active' => true,
        'order' => 0,
    ]);
}
```

## Bulk Operations

### Apply Home Decor Theme to All Websites
```sql
UPDATE websites 
SET theme_id = (SELECT id FROM themes WHERE slug = 'home-decor');
```

### Apply Recipe Theme to All Websites
```sql
UPDATE websites 
SET theme_id = (SELECT id FROM themes WHERE slug = 'recipe');
```

### Find All Websites Using Home Decor Theme
```sql
SELECT w.id, w.name, w.slug, w.url
FROM websites w
INNER JOIN themes t ON w.theme_id = t.id
WHERE t.slug = 'home-decor';
```

## Debugging Commands

### Check if Theme is Loaded
```bash
php artisan tinker
```
```php
$website = \App\Models\Website::find(1);
$website->load('theme');
print_r($website->theme->toArray());
```

### Check Built Assets
```bash
ls -la public/build/assets/ | grep -i home
```

Expected output:
```
HomeDecor-*.js
HomeDecorLayout-*.js
ThemeAwareLayout-*.js
```

### View Manifest
```bash
cat public/build/manifest.json | grep -i home
```

### Check for JavaScript Errors
```bash
# Start development server
npm run dev

# In another terminal, visit site and check browser console
```

## Backup Commands

### Backup Theme Settings
```bash
# Backup to JSON file
php artisan tinker
```
```php
$settings = \App\Models\Website::find(1)->theme_settings;
file_put_contents('theme_settings_backup.json', json_encode($settings, JSON_PRETTY_PRINT));
```

### Restore Theme Settings
```php
$settings = json_decode(file_get_contents('theme_settings_backup.json'), true);
$website = \App\Models\Website::find(1);
$website->theme_settings = $settings;
$website->save();
```

## Migration Commands

### Create New Theme
```bash
php artisan tinker
```
```php
\App\Models\Theme::create([
    'name' => 'Your Theme Name',
    'slug' => 'your-theme-slug',
    'description' => 'Theme description',
    'show_recipe_sections' => false,
    'is_active' => true,
]);
```

### Deactivate Theme
```sql
UPDATE themes 
SET is_active = 0 
WHERE slug = 'home-decor';
```

### Reactivate Theme
```sql
UPDATE themes 
SET is_active = 1 
WHERE slug = 'home-decor';
```

## Quick Copy-Paste Scripts

### One-Command Theme Switch
```bash
# Switch to Home Decor
php artisan tinker <<EOF
\$website = \\App\\Models\\Website::find(1);
\$theme = \\App\\Models\\Theme::where('slug', 'home-decor')->first();
\$website->theme_id = \$theme->id;
\$website->save();
echo "Switched to Home Decor theme\\n";
exit
EOF
```

```bash
# Switch to Recipe
php artisan tinker <<EOF
\$website = \\App\\Models\\Website::find(1);
\$theme = \\App\\Models\\Theme::where('slug', 'recipe')->first();
\$website->theme_id = \$theme->id;
\$website->save();
echo "Switched to Recipe theme\\n";
exit
EOF
```

## Environment-Specific

### Local Development
```bash
# Full URL with path
http://localhost:8000/site/your-website-slug
```

### Production (Custom Domain)
```bash
# Direct domain
https://your-domain.com
```

### Production (Subdomain)
```bash
# Subdomain
https://your-subdomain.yourdomain.com
```

## Verification Checklist

After applying theme, verify:

```bash
# 1. Check database
php artisan tinker
$website = \App\Models\Website::find(1);
echo "Theme: " . ($website->theme->slug ?? 'none') . "\n";
exit

# 2. Check assets exist
ls public/build/assets/ | grep -i homedecor

# 3. Visit site
# Open browser to website URL

# 4. Check browser console
# Open Developer Tools (F12) → Console tab
# Look for any errors

# 5. Test navigation
# Click through: Home → Category → Article
# Verify theme is consistent
```

## Common Issues & Fixes

### Issue: Theme Not Showing
```bash
# Solution 1: Clear cache
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# Solution 2: Rebuild assets
npm run build

# Solution 3: Hard refresh browser
# Press Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
```

### Issue: Old Theme Still Showing
```bash
# Check theme is actually applied
php artisan tinker
$website = \App\Models\Website::find(1);
echo $website->theme->slug;

# If it shows 'home-decor' but site looks wrong:
# 1. Clear browser cache completely
# 2. Try incognito/private window
# 3. Check browser console for asset loading errors
```

### Issue: Colors Not Right
```bash
# Rebuild assets
npm run build

# Check tailwind config
cat tailwind.config.js
```

---

**Need more help?** See:
- `QUICK_START_HOME_DECOR_THEME.md` - Setup guide
- `HOME_DECOR_THEME_GUIDE.md` - Full documentation
- `THEME_IMPLEMENTATION_SUMMARY.md` - Technical details
