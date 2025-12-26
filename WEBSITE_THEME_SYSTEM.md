# AI Blog Generator - Website Theme System

## Overview

This system allows users to create multiple websites with a beautiful default theme inspired by Solushcooks.com. Users can generate AI-powered or manual articles that automatically populate their created websites.

## Features Implemented

### 1. **Database Structure**
- **Websites Table**: Stores website configurations, themes, and settings
- **Categories Table**: Organizes content by category (e.g., "Comfort Classics", "Party Appetizers")
- **Articles Table**: Stores blog posts (both AI-generated and manual)

### 2. **Default Theme - Solushcooks**
A modern, clean, and responsive theme perfect for food blogs and recipe websites featuring:
- **Clean Header** with logo, navigation, and search bar
- **Category Grid** with beautiful card layouts
- **Article Cards** with featured images and excerpts
- **Newsletter Signup** sections
- **Social Media Integration** (Instagram, Pinterest, Facebook)
- **Responsive Design** that works on all devices
- **SEO-Friendly** structure

### 3. **Website Management**
- Create unlimited websites
- Each website gets:
  - Auto-generated slug for URLs
  - Default categories (Comfort Classics, Party Appetizers, Breakfast & Brunch, etc.)
  - Custom theme settings
  - Social media links
  - Logo and branding options

### 4. **Article Management**
- Create articles manually or via AI
- Articles automatically appear on their respective websites
- Support for:
  - Featured images
  - Categories
  - SEO meta tags
  - Draft/Published/Scheduled statuses
  - View tracking

## How It Works

### Creating a Website

1. **Navigate to Websites** (Superadmin → Websites)
2. **Click "Create New Website"**
3. **Fill in the details:**
   - Website Name (e.g., "My Food Blog")
   - Description
   - Logo URL (optional)
   - Custom domain (optional)
   - Social media links
4. **Click "Create Website"**

The system automatically:
- Generates a unique slug (e.g., `/site/my-food-blog`)
- Creates default categories
- Applies the Solushcooks theme
- Sets up theme settings

### Adding Articles

1. **From Website Dashboard**, click "Add New Article"
2. **Or navigate to** Superadmin → Articles → Create
3. **Fill in article details:**
   - Title
   - Content (rich text editor)
   - Featured image
   - Category
   - Status (Draft/Published)
   - Generation type (Manual/AI)
4. **Publish** and the article appears on the website instantly

### Viewing the Website

- **From Admin**: Click "View Live Site" on any website card
- **Public URL**: `/site/{website-slug}`
- **Article URL**: `/site/{website-slug}/article/{article-slug}`
- **Category URL**: `/site/{website-slug}/category/{category-slug}`

## File Structure

```
app/
├── Models/
│   ├── Website.php          # Website model with relationships
│   ├── Article.php          # Article model
│   └── Category.php         # Category model
├── Http/Controllers/
│   ├── WebsiteController.php       # Admin CRUD for websites
│   ├── ArticleController.php       # Admin CRUD for articles
│   └── PublicWebsiteController.php # Public-facing pages
├── Policies/
│   ├── WebsitePolicy.php    # Authorization for websites
│   └── ArticlePolicy.php    # Authorization for articles

resources/js/
├── Layouts/
│   └── PublicWebsiteLayout.vue     # Main layout for public sites
├── Pages/
│   ├── SuperAdmin/Websites/
│   │   ├── Index.vue        # List all websites
│   │   ├── Create.vue       # Create new website
│   │   └── Show.vue         # Website dashboard
│   └── Public/Website/
│       ├── Home.vue          # Homepage with categories & articles
│       ├── Article.vue       # Single article view
│       └── Category.vue      # Category archive

database/migrations/
├── 2024_12_25_000001_create_websites_table.php
├── 2024_12_25_000002_create_categories_table.php
└── 2024_12_25_000003_create_articles_table.php
```

## Routes

### Admin Routes (Protected)
```
GET  /superadmin/websites           - List all websites
GET  /superadmin/websites/create    - Create website form
POST /superadmin/websites           - Store new website
GET  /superadmin/websites/{id}      - View website dashboard
GET  /superadmin/websites/{id}/edit - Edit website
PUT  /superadmin/websites/{id}      - Update website
DELETE /superadmin/websites/{id}    - Delete website

GET  /superadmin/articles           - List all articles
GET  /superadmin/articles/create    - Create article form
POST /superadmin/articles           - Store new article
GET  /superadmin/articles/{id}      - View article
GET  /superadmin/articles/{id}/edit - Edit article
PUT  /superadmin/articles/{id}      - Update article
DELETE /superadmin/articles/{id}    - Delete article
```

### Public Routes
```
GET /site/{website}                      - Website homepage
GET /site/{website}/category/{category}  - Category archive
GET /site/{website}/article/{article}    - Single article
```

## Theme Customization

Each website has a `theme_settings` JSON field that stores:

```json
{
  "primary_color": "#1e293b",
  "secondary_color": "#10b981",
  "header_text": "Website Name",
  "newsletter_enabled": true,
  "comments_enabled": true,
  "show_banner": true,
  "banner_text": "30-MINUTE MEALS! Get the email series now →"
}
```

## Social Media Integration

Configure social media links for each website:

```json
{
  "instagram": "https://instagram.com/yourpage",
  "pinterest": "https://pinterest.com/yourpage",
  "facebook": "https://facebook.com/yourpage"
}
```

These links appear in:
- Header banner
- Footer
- Article sharing sections

## Default Categories

When a website is created, these categories are auto-generated:
1. Comfort Classics
2. Party Appetizers
3. Breakfast & Brunch
4. Desserts Baking
5. Healthy Recipes
6. Quick Dinners

You can add, edit, or remove categories later.

## Next Steps / Future Enhancements

1. **AI Article Generation**: Integrate OpenAI/Claude API for automated content
2. **Custom Domains**: Allow users to map custom domains
3. **Theme Editor**: Visual theme customization interface
4. **Multiple Themes**: Add more theme options
5. **Comments System**: Enable reader comments on articles
6. **Analytics Dashboard**: Track views, popular articles, etc.
7. **SEO Optimization**: Advanced meta tags, sitemaps, etc.
8. **Image Management**: Built-in image upload and management
9. **Newsletter Integration**: Connect to Mailchimp/ConvertKit
10. **Export/Import**: Backup and migrate websites

## Testing the System

1. **Start the development server:**
   ```bash
   php artisan serve
   npm run dev
   ```

2. **Login to your account**

3. **Navigate to Superadmin → Websites**

4. **Create your first website:**
   - Name: "My Awesome Food Blog"
   - Description: "Delicious recipes and cooking tips"
   - Add social media links

5. **Create an article:**
   - Go to the website dashboard
   - Click "Add New Article"
   - Title: "Best Chocolate Chip Cookies"
   - Content: Add your recipe
   - Select a category
   - Add a featured image URL
   - Publish

6. **View your live website:**
   - Click "View Live Site"
   - See your article displayed beautifully!

## Technical Notes

- **Framework**: Laravel 11 with Inertia.js and Vue 3
- **Styling**: Tailwind CSS
- **Icons**: Font Awesome 6
- **Database**: SQLite (can be changed to MySQL/PostgreSQL)
- **Authentication**: Laravel Breeze
- **Authorization**: Policy-based with Gates

## Support

For questions or issues, refer to:
- Laravel Documentation: https://laravel.com/docs
- Inertia.js Documentation: https://inertiajs.com
- Vue 3 Documentation: https://vuejs.org

---

**Enjoy building beautiful websites with AI-powered content! 🚀**

