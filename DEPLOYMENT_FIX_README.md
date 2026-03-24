# Fix for Raw JSON Display Issue

## Problem
The application was sometimes displaying raw JSON data instead of the rendered HTML page. This occurred when browsers accessed Inertia.js pages directly in production.

## Root Cause
The issue was caused by Inertia.js responses being returned as JSON when they should have been rendered as HTML. This happens when:

1. The browser makes a direct GET request (not an Inertia navigation)
2. The server incorrectly treats it as an Inertia/AJAX request
3. The middleware returns JSON props instead of the full HTML page

## Changes Made

### 1. Updated HandleInertiaRequests Middleware
**File**: `app/Http/Middleware/HandleInertiaRequests.php`

- Added detection for direct browser requests vs Inertia navigation
- Ensured direct browser requests always get HTML responses
- Added proper header handling to force HTML content type
- Added error logging to catch future occurrences
- Fixed session handling to prevent errors on public routes

### 2. Updated PublicWebsiteController
**File**: `app/Http/Controllers/PublicWebsiteController.php`

- Added explicit `toArray()` calls to ensure proper data serialization
- Added null coalescing for `articleImages` collection to prevent errors
- Ensured all data passed to Inertia is properly formatted

### 3. Created Cache Clearing Command
**File**: `app/Console/Commands/ClearAllCaches.php`

- New artisan command: `php artisan cache:clear-all`
- Clears all types of Laravel caches in one command

### 4. Created EnsureInertiaResponse Middleware
**File**: `app/Http/Middleware/EnsureInertiaResponse.php`

- Additional safety layer to log potential issues
- Can be enabled in `bootstrap/app.php` if needed

## Deployment Instructions

### Step 1: Deploy Code Changes
```bash
# Pull the latest changes
git pull origin main

# Install/update dependencies (if needed)
composer install --no-dev --optimize-autoloader
npm ci && npm run build
```

### Step 2: Clear All Caches
```bash
# Use the new cache clearing command
php artisan cache:clear-all

# Or manually clear each cache
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan clear-compiled
```

### Step 3: Optimize for Production
```bash
# Cache routes and config for better performance
php artisan route:cache
php artisan config:cache
php artisan view:cache
```

### Step 4: Restart Services
```bash
# Restart PHP-FPM (exact command depends on your server setup)
sudo systemctl restart php8.2-fpm

# Or restart your web server
sudo systemctl restart nginx
# or
sudo systemctl restart apache2

# If using Laravel Octane/Queue workers
php artisan octane:reload
# or
php artisan queue:restart
```

### Step 5: Clear Browser Cache
Instruct users to:
- Hard refresh their browsers (Ctrl+Shift+R or Cmd+Shift+R)
- Clear browser cache
- Try in incognito/private mode to test

### Step 6: Clear CDN Cache (if applicable)
If you're using a CDN (Cloudflare, etc.):
- Purge the CDN cache for your domain
- This ensures the CDN serves fresh HTML responses

## Testing

After deployment, test the following:

1. **Direct URL Access**: Open a fresh incognito window and visit an article URL directly
2. **Navigation**: Click through the site to ensure Inertia navigation works
3. **Multiple Devices**: Test on desktop and mobile browsers
4. **Different Article Types**: Test both recipe and regular article URLs

## Prevention

The fixes include:

1. **Proper Request Detection**: The middleware now correctly identifies browser requests vs Inertia navigation
2. **Data Serialization**: All Eloquent models are explicitly converted to arrays
3. **Error Logging**: Any future occurrences will be logged to `storage/logs/laravel.log`

## Monitoring

Check the logs regularly for these messages:
```bash
tail -f storage/logs/laravel.log | grep "Inertia returned JSON"
```

If you see this error, it indicates the issue is still occurring and needs further investigation.

## Rollback Plan

If issues persist:

1. Check `storage/logs/laravel.log` for error messages
2. Verify Vite assets are building correctly: `npm run build`
3. Ensure the `resources/views/app.blade.php` file exists
4. Check that JavaScript is loading: View source and verify script tags are present

## Additional Notes

- The fix is backward compatible and won't affect existing functionality
- Performance should improve due to better request handling
- The changes follow Laravel and Inertia.js best practices

## Support

If the issue persists after deployment:

1. Check server error logs: `/var/log/nginx/error.log` or `/var/log/apache2/error.log`
2. Check Laravel logs: `storage/logs/laravel.log`
3. Verify PHP version: Should be 8.1 or higher
4. Check Inertia.js version: Should be compatible with Laravel 10/11
