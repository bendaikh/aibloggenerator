# Deployment Checklist - Raw JSON Fix

## Pre-Deployment Checklist

- [ ] Review all changed files in the commit
- [ ] Ensure you have database backups
- [ ] Ensure you have a rollback plan
- [ ] Test the changes in a staging environment if available
- [ ] Schedule deployment during low-traffic period

## Deployment Steps

### 1. Preparation
- [ ] SSH into your production server
- [ ] Navigate to your Laravel project directory
- [ ] Verify you're on the correct branch: `git branch`

### 2. Backup (Critical!)
```bash
# Backup database
php artisan backup:run  # If you have backup package
# OR manually backup your database

# Backup current code
tar -czf backup-$(date +%Y%m%d-%H%M%S).tar.gz .
```
- [ ] Database backup completed
- [ ] Code backup completed

### 3. Deploy Code

#### Option A: Using the deployment script
```bash
./deploy-fix.sh
```
- [ ] Script executed successfully
- [ ] No errors reported

#### Option B: Manual deployment
```bash
# Put site in maintenance mode
php artisan down
```
- [ ] Site in maintenance mode

```bash
# Pull latest changes
git pull origin main
```
- [ ] Code pulled successfully
- [ ] No merge conflicts

```bash
# Update dependencies
composer install --no-dev --optimize-autoloader
```
- [ ] Composer dependencies installed

```bash
# Build frontend assets
npm ci
npm run build
```
- [ ] NPM dependencies installed
- [ ] Assets built successfully
- [ ] `public/build` directory exists

```bash
# Clear all caches
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan clear-compiled
```
- [ ] All caches cleared

```bash
# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
- [ ] Application optimized

```bash
# Restart services (choose appropriate for your setup)
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
# OR
php artisan octane:reload
# OR
php artisan queue:restart
```
- [ ] PHP-FPM restarted
- [ ] Web server restarted
- [ ] Queue workers restarted (if applicable)

```bash
# Bring site back online
php artisan up
```
- [ ] Site is online

## Post-Deployment Testing

### 4. Immediate Tests (Within 5 minutes)

#### Test 1: Direct URL Access
```bash
# Open in incognito/private browser window
# Visit: https://yourdomain.com/recipes/some-article
```
- [ ] Page loads as HTML (not raw JSON)
- [ ] Page renders correctly
- [ ] All styles applied
- [ ] Images load

#### Test 2: Navigation
```bash
# From the article page, click links to navigate
```
- [ ] Navigation works smoothly
- [ ] No page refreshes (Inertia navigation)
- [ ] No console errors in browser DevTools (F12)

#### Test 3: Multiple Article Types
- [ ] Recipe article loads correctly (`/recipes/article-name`)
- [ ] Regular article loads correctly (`/article-name`)
- [ ] Category pages load correctly (`/category/category-name`)
- [ ] Home page loads correctly (`/`)

#### Test 4: Mobile Browser
- [ ] Open site on mobile device or mobile view in DevTools
- [ ] Pages load correctly
- [ ] No raw JSON displayed

#### Test 5: Different Browsers
- [ ] Chrome/Edge works
- [ ] Firefox works
- [ ] Safari works (if applicable)

### 5. Check Diagnostics

Visit the diagnostics endpoint:
```
https://yourdomain.com/diagnostics/inertia
```

Expected output:
```json
{
  "status": "OK",
  "diagnostics": {
    "detection": {
      "is_direct_browser_request": true,
      "is_inertia_navigation": false
    }
  }
}
```

- [ ] Diagnostics endpoint returns correct detection
- [ ] `is_direct_browser_request` is `true` when accessing directly
- [ ] `is_inertia_navigation` is `false` for direct access

### 6. Monitor Logs

```bash
# Watch logs in real-time
tail -f storage/logs/laravel.log
```

Look for:
- [ ] No errors in Laravel logs
- [ ] No "Inertia returned JSON" error messages
- [ ] No PHP errors or warnings

Check web server logs:
```bash
tail -f /var/log/nginx/error.log
# OR
tail -f /var/log/apache2/error.log
```
- [ ] No web server errors

### 7. Clear External Caches

#### Browser Cache
- [ ] Hard refresh on all tested pages (Ctrl+Shift+R / Cmd+Shift+R)
- [ ] Test in incognito mode to verify no cache issues

#### CDN Cache (if using Cloudflare, etc.)
- [ ] Login to CDN dashboard
- [ ] Purge entire cache for the domain
- [ ] Wait 2-3 minutes for propagation
- [ ] Re-test the site

### 8. User Testing (Within 1 hour)

#### Ask users to test:
- [ ] Can access articles directly via URL
- [ ] Can navigate between pages
- [ ] Can sign up for newsletter
- [ ] Can use Pinterest features
- [ ] No JSON is visible anywhere

### 9. Performance Check

```bash
# Check response times
curl -w "\nTime: %{time_total}s\n" -o /dev/null -s https://yourdomain.com
```
- [ ] Response time is acceptable (< 2 seconds)
- [ ] No significant performance degradation

### 10. Final Verification (Within 24 hours)

- [ ] No user complaints about JSON display
- [ ] Analytics show normal traffic patterns
- [ ] Error logs remain clean
- [ ] All features working as expected

## Rollback Procedure (If Needed)

If critical issues occur:

```bash
# 1. Put site in maintenance mode
php artisan down

# 2. Restore previous code
git reset --hard HEAD~1  # Go back one commit
# OR restore from backup
tar -xzf backup-YYYYMMDD-HHMMSS.tar.gz

# 3. Restore dependencies
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# 4. Clear caches
php artisan cache:clear-all

# 5. Bring site back online
php artisan up
```

## Common Issues & Solutions

### Issue: Still seeing JSON after deployment
**Solution**:
1. Clear browser cache completely
2. Clear CDN cache
3. Verify assets built correctly: `ls -la public/build/`
4. Check browser DevTools console for JavaScript errors

### Issue: JavaScript not loading
**Solution**:
1. Verify Vite manifest: `cat public/build/manifest.json`
2. Check file permissions: `ls -la public/build/`
3. Rebuild assets: `npm run build`

### Issue: 500 Server Error
**Solution**:
1. Check Laravel logs: `tail -100 storage/logs/laravel.log`
2. Check web server logs
3. Verify file permissions: `chmod -R 755 storage bootstrap/cache`
4. Clear all caches again

### Issue: Styles not applied
**Solution**:
1. Hard refresh browser (Ctrl+Shift+R)
2. Check if CSS files exist: `ls -la public/build/assets/`
3. Rebuild assets: `npm run build`
4. Clear CDN cache

## Success Criteria

All of these should be true:
- ✓ No raw JSON visible on any page
- ✓ All pages load as proper HTML
- ✓ Navigation works smoothly
- ✓ No console errors
- ✓ No server errors in logs
- ✓ Users can access the site normally
- ✓ All features work (newsletter, Pinterest, etc.)

## Post-Deployment Cleanup (Optional)

After verifying everything works for 1-2 days:

```bash
# Remove diagnostics route
# Edit routes/web.php and remove the diagnostics route

# Remove backup files
rm backup-*.tar.gz
```

## Documentation

After successful deployment:
- [ ] Update internal documentation
- [ ] Document any issues encountered
- [ ] Note the deployment date/time
- [ ] Archive this checklist with notes

## Contact Information

If you need help:
- Check `DEPLOYMENT_FIX_README.md` for detailed information
- Check `FIX_SUMMARY.md` for technical details
- Review Laravel logs
- Check web server logs

---

**Deployment Date**: _________________

**Deployed By**: _________________

**Deployment Start Time**: _________________

**Deployment End Time**: _________________

**Status**: ☐ Success  ☐ Issues (document below)  ☐ Rolled Back

**Notes**:
_____________________________________________
_____________________________________________
_____________________________________________
_____________________________________________
