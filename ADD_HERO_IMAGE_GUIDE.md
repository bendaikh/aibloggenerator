# How to Add a Hero Image to Home Decor Theme

## Method 1: Using Laravel Tinker (Recommended)

```bash
php artisan tinker
```

```php
// Get your website
$website = \App\Models\Website::find(YOUR_WEBSITE_ID);

// Add hero image URL to theme settings
$website->theme_settings = [
    'hero_image' => '/uploads/images/your-hero-image.jpg',
    // Keep other existing settings
    'banner_text' => $website->theme_settings['banner_text'] ?? 'NEW IDEAS EVERY DAY! Join our community →',
];

$website->save();

echo "Hero image added!\n";
```

## Method 2: Using SQL

```sql
UPDATE websites 
SET theme_settings = JSON_SET(
    COALESCE(theme_settings, '{}'),
    '$.hero_image',
    '/uploads/images/your-hero-image.jpg'
)
WHERE id = YOUR_WEBSITE_ID;
```

## Uploading the Image

### Option A: Via File Manager
1. Upload your image to `public/uploads/images/`
2. Make sure the file is accessible
3. Use the path `/uploads/images/your-image.jpg` in the settings

### Option B: Via Laravel Storage
1. Place image in `public/uploads/images/`
2. Or use Storage facade:
   ```php
   // In your controller
   $path = $request->file('hero_image')->store('uploads/images', 'public');
   ```

## Image Recommendations

### For Best Results:
- **Dimensions**: 800x1000px (aspect ratio 3:4)
- **Format**: JPG or PNG
- **Size**: Under 500KB (optimize for web)
- **Content**: Lifestyle/home decor scene that represents your brand
- **Style**: Clean, bright, professional photography

### Good Hero Image Examples:
- Styled living room with natural light
- Beautifully decorated bedroom
- Kitchen with warm tones
- Cozy reading nook with plants
- Minimalist home office setup

## Complete Example

```bash
# 1. Upload your image to public/uploads/images/hero-living-room.jpg

# 2. Apply to website
php artisan tinker
```

```php
$website = \App\Models\Website::find(1);

$website->theme_settings = [
    'hero_image' => '/uploads/images/hero-living-room.jpg',
    'banner_text' => 'NEW IDEAS EVERY DAY! Join our community →',
];

$website->save();

// Verify
echo "Hero image: " . $website->theme_settings['hero_image'] . "\n";
exit
```

## Testing

After adding the hero image:
1. Clear cache: `php artisan cache:clear`
2. Refresh your browser (Ctrl+Shift+R)
3. Visit your website homepage
4. You should see your hero image in the right section with:
   - Decorative elements (circles, borders)
   - White frame overlay in bottom-right corner
   - "Your Style" decorative element

## Troubleshooting

### Image Not Showing?
1. **Check file path**:
   ```bash
   ls -la public/uploads/images/your-hero-image.jpg
   ```

2. **Check permissions**:
   ```bash
   chmod 644 public/uploads/images/your-hero-image.jpg
   ```

3. **Verify in database**:
   ```sql
   SELECT theme_settings FROM websites WHERE id = YOUR_WEBSITE_ID;
   ```

4. **Clear all caches**:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

### Image Too Large?
Use an image optimizer:
- **Online**: TinyPNG, Squoosh
- **CLI**: ImageMagick
  ```bash
  convert input.jpg -resize 800x1000^ -gravity center -extent 800x1000 output.jpg
  ```

## Using External URLs

You can also use external image URLs:

```php
$website->theme_settings = [
    'hero_image' => 'https://images.unsplash.com/photo-xyz...',
];
```

**Note**: For best performance and reliability, host images locally.

## Multiple Theme Settings

You can set multiple theme customizations at once:

```php
$website->theme_settings = [
    'hero_image' => '/uploads/images/hero.jpg',
    'banner_text' => 'DESIGN YOUR DREAM HOME TODAY →',
    'subscription_popup_title' => 'GET INSPIRED',
    'subscription_popup_subtitle' => 'WEEKLY DECOR TIPS',
    'subscription_popup_image' => '/uploads/images/popup.jpg',
];

$website->save();
```

## Removing Hero Image

To go back to the gradient placeholder:

```php
$website = \App\Models\Website::find(1);
$settings = $website->theme_settings;
unset($settings['hero_image']);
$website->theme_settings = $settings;
$website->save();
```

Or via SQL:
```sql
UPDATE websites 
SET theme_settings = JSON_REMOVE(theme_settings, '$.hero_image')
WHERE id = YOUR_WEBSITE_ID;
```
