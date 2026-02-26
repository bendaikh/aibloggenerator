# Home Decor Theme - "The Boho Artisan"

## Overview

A new warm, bohemian-inspired theme for home decor blogs with a beige/cream color palette and earthy accents.

## Design Features

### Color Palette
- **Background**: `#F5F1ED` (Warm beige/cream)
- **Primary Accent**: `#FF6B4A` (Terracotta/coral)
- **Secondary Accent**: `#D4A574` (Golden beige)
- **Text Primary**: `#2D2D2D` (Dark charcoal)
- **Text Secondary**: `#5D5D5D` (Medium gray)
- **Borders**: `#E5D5C3` (Light beige)

### Layout Components

1. **Header**
   - Sticky navigation with warm color scheme
   - Logo placement with decorative icon fallback
   - Navigation: blog, DIY Projects (dropdown), shop, About
   - Mobile-responsive hamburger menu

2. **Hero Section**
   - Large "Living Warm & Styling Wild" headline
   - Featured image container with decorative elements
   - Call-to-action buttons (Start Exploring, Watch Tutorial)
   - Diamond and circular decorative shapes

3. **Curated Collections**
   - Circular category icons (4 main categories)
   - Hover effects with coral accent ring
   - Clean typography with category names

4. **Latest Journal**
   - Blog-style article cards
   - Author information display
   - Category tags and publish dates
   - Smooth hover animations

5. **Featured Quote Section**
   - Dark background with centered quote
   - Decorative quote icon
   - Attribution styling

6. **Reader Favorites**
   - Grid of popular articles
   - Square image thumbnails
   - Compact layout for featured content

7. **Newsletter Section**
   - "Join the Artisan Community" heading
   - Envelope icon
   - Email subscription form
   - Clean white background

8. **Footer**
   - Dark (`#2D2D2D`) background
   - Four columns: Logo, Explore, Support, From the Shop
   - Social media icons
   - "From the Shop" section with product preview grid

## File Structure

```
resources/js/
├── Layouts/
│   ├── PublicWebsiteLayout.vue (Recipe/Food theme - green)
│   └── HomeDecorLayout.vue (New - Home decor theme - beige)
│
└── Pages/Public/Website/
    ├── Home.vue (Recipe/Food theme)
    └── HomeDecor.vue (New - Home decor theme)
```

## Theme Activation

### In Database

The `home-decor` theme is already seeded in the `themes` table:

```sql
SELECT * FROM themes WHERE slug = 'home-decor';
```

### Assign Theme to Website

To use the home decor theme for a website:

1. **Via Database**:
   ```sql
   UPDATE websites 
   SET theme_id = (SELECT id FROM themes WHERE slug = 'home-decor') 
   WHERE id = YOUR_WEBSITE_ID;
   ```

2. **Via Laravel Tinker**:
   ```php
   php artisan tinker
   $website = Website::find(YOUR_WEBSITE_ID);
   $theme = Theme::where('slug', 'home-decor')->first();
   $website->theme_id = $theme->id;
   $website->save();
   ```

3. **Via UI** (if implemented):
   - Go to Website Settings → Appearance → Theme
   - Select "Home Decor Theme"

## Testing the Theme

### Prerequisites
1. Ensure you have at least one website in your system
2. Ensure you have some published articles
3. Ensure you have categories created

### Steps to Test

1. **Assign the theme**:
   ```bash
   php artisan tinker
   ```
   ```php
   $website = \App\Models\Website::first();
   $theme = \App\Models\Theme::where('slug', 'home-decor')->first();
   $website->theme_id = $theme->id;
   $website->save();
   ```

2. **Visit your website**:
   - If using subdomain: `http://your-subdomain.yourdomain.com`
   - If using custom domain: `http://your-custom-domain.com`
   - If using path-based: `http://localhost:8000/site/your-website-slug`

3. **What to verify**:
   - ✅ Warm beige/cream background
   - ✅ Terracotta accent colors
   - ✅ "Living Warm & Styling Wild" hero section
   - ✅ Circular category icons in Curated Collections
   - ✅ Latest Journal article cards
   - ✅ Featured quote section
   - ✅ Dark footer with social icons
   - ✅ Newsletter signup section

## Theme Settings (Optional Customization)

You can customize the theme via `theme_settings` JSON field in the `websites` table:

```json
{
  "banner_text": "NEW IDEAS EVERY DAY! Join our community →",
  "hero_image": "/uploads/images/hero-image.jpg",
  "subscription_popup_title": "JOIN OUR COMMUNITY",
  "subscription_popup_subtitle": "GET WEEKLY INSPIRATION",
  "subscription_popup_description": "Home decor ideas delivered to your inbox"
}
```

## Switching Themes

The controller (`PublicWebsiteController::renderHome()`) automatically detects the theme:

```php
if ($website->theme && $website->theme->slug === 'home-decor') {
    $viewComponent = 'Public/Website/HomeDecor';
} else {
    $viewComponent = 'Public/Website/Home'; // Default recipe theme
}
```

## Custom Categories for Home Decor

Suggested category names that fit the theme:
- Textiles
- Greenery
- Ceramics
- DIY Projects
- Living Spaces
- Outdoor Decor
- Vintage Finds
- Minimalism

## Article Types

While the recipe theme shows recipe sections (ingredients, instructions), the home decor theme:
- Hides recipe-specific fields
- Shows cleaner article layouts
- Focuses on imagery and inspiration
- Displays author information prominently

## Browser Compatibility

- ✅ Chrome/Edge (Chromium)
- ✅ Firefox
- ✅ Safari
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

## Performance

- Optimized images with lazy loading
- Smooth CSS transitions
- Responsive grid layouts
- Mobile-first approach

## Future Enhancements

Potential additions:
- [ ] Image galleries for projects
- [ ] Pinterest-style masonry layout option
- [ ] Color palette customizer
- [ ] Shop integration for home decor products
- [ ] Before/After sliders for DIY projects
- [ ] Room planner/mood board feature

## Support

For issues or questions about the home decor theme:
1. Check that `theme_id` is properly set in the `websites` table
2. Verify the build compiled successfully (`npm run build`)
3. Clear browser cache if styles aren't loading
4. Check console for JavaScript errors

## Credits

Design inspired by "The Boho Artisan" aesthetic with warm, earthy tones and modern bohemian styling.
