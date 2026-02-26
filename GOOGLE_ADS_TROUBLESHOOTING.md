# Google AdSense Troubleshooting Guide

## Issues Fixed

I've updated your Google AdSense implementation to address the following issues:

### 1. **Consent Management Integration**
- Google Ads now properly check for advertising consent before initializing
- Ads will only load after user gives consent through the CMP

### 2. **Improved Ad Initialization**
- Added proper error checking and logging
- Validates that ad slots have required attributes (`data-ad-client` and `data-ad-slot`)
- Prevents duplicate initialization

### 3. **Better Event Handling**
- Added dedicated event listener for Google Ads consent
- Multiple retry attempts with proper timing

## Common Issues and Solutions

### Issue 1: 400 Error from Google AdSense API

**Error Message:**
```
googleads.g.doubleclick.net/pagead/ads?client=ca-pub-XXXX... 400 (Bad Request)
```

**Possible Causes:**

1. **Invalid Ad Slot IDs**
   - Make sure the ad slot IDs in your database match the ones in your Google AdSense account
   - Ad slot IDs should be numeric (e.g., `1234567890`)
   - Verify in Google AdSense: Settings > Account > Sites > Ad units

2. **AdSense Account Not Approved**
   - Your AdSense account must be fully approved
   - Domain must be verified in AdSense
   - Check: https://www.google.com/adsense

3. **Site Not Added to AdSense**
   - Add your production domain to AdSense: Settings > Sites
   - Wait for approval (can take 24-48 hours)

4. **Policy Violations**
   - Ensure your site complies with AdSense policies
   - Check for policy notifications in your AdSense dashboard

### Issue 2: Ads Not Showing (No Errors)

**Possible Causes:**

1. **Consent Not Given**
   - Users must accept advertising cookies
   - Check console for: `[Google Ads] Waiting for advertising consent...`
   - Test by accepting all cookies

2. **Ad Blocker Active**
   - Disable ad blockers when testing
   - Use incognito mode without extensions

3. **Insufficient Content**
   - AdSense requires sufficient content to show ads
   - Minimum 300-500 words per page

4. **Fill Rate Issues**
   - AdSense might not have ads available for your niche/location
   - Check AdSense dashboard for fill rate

### Issue 3: DOM Node Errors (frame_start.js)

**Error Message:**
```
Uncaught NotFoundError: Failed to execute 'removeChild' on 'Node'
```

**Solution:**
- This is a known AdSense issue and usually doesn't prevent ads from showing
- Ensure ad containers exist before AdSense loads
- Fixed in the updated code with proper DOM checks

## Verification Steps

### Step 1: Check Console Logs

Open your browser console (F12) and look for these messages:

```
[Layout] Website Google Ads config: {hasAdsenseId: true, placements: Proxy(Object), active: true}
[Google Ads] Initializing Google AdSense ads
[Google Ads] AdSense ID: ca-pub-XXXXXXXXXX
[Google Ads] Found ad elements: X
[Google Ads] Initializing ad X - Client: ca-pub-XXXX, Slot: XXXX
[Google Ads] Initialized ad X
```

### Step 2: Verify Ad Slots in Database

Check your website configuration in the database:

```bash
php artisan tinker
```

Then run:
```php
$website = App\Models\Website::find(YOUR_WEBSITE_ID);
echo "AdSense ID: " . $website->google_adsense_id . "\n";
echo "Active: " . ($website->google_ads_active ? 'Yes' : 'No') . "\n";
print_r($website->google_ads_placements);
```

### Step 3: Verify Ad Elements on Page

Open browser console and run:
```javascript
document.querySelectorAll('.adsbygoogle').forEach((ad, i) => {
    console.log('Ad ' + (i+1) + ':', {
        client: ad.getAttribute('data-ad-client'),
        slot: ad.getAttribute('data-ad-slot'),
        status: ad.getAttribute('data-adsbygoogle-status')
    });
});
```

### Step 4: Check AdSense Script Loaded

In console:
```javascript
console.log('AdSense loaded:', typeof adsbygoogle !== 'undefined');
```

## Configuration Checklist

- [ ] AdSense account is approved
- [ ] Production domain is added to AdSense
- [ ] Ad units are created in AdSense dashboard
- [ ] Ad slot IDs are correctly entered in your CMS
- [ ] `google_ads_active` is set to `true` in database
- [ ] `google_adsense_id` matches your AdSense publisher ID (ca-pub-XXXX)
- [ ] Website is published and accessible
- [ ] No ad blockers are active during testing
- [ ] User has accepted advertising cookies

## Testing in Development

**Important:** AdSense typically doesn't serve ads on localhost or test domains.

To test AdSense properly:
1. Deploy to production domain
2. Ensure domain is verified in AdSense
3. Test from a real device/browser
4. Check after 24-48 hours of going live

## Debug Mode

To enable detailed logging, add this to your browser console:
```javascript
localStorage.setItem('debug_google_ads', 'true');
```

Then reload the page to see detailed ad initialization logs.

## Common AdSense Publisher ID Formats

Your AdSense Publisher ID should look like:
- `ca-pub-1234567890123456` (correct)
- NOT `pub-1234567890123456` (incorrect - missing 'ca-')
- NOT `ca-pub-1234567890123456/987654321` (incorrect - includes slot ID)

## Getting Help

If ads still don't show after following this guide:

1. **Check AdSense Dashboard**
   - Look for policy violations
   - Check earnings/impressions
   - Review account status

2. **Contact Google AdSense Support**
   - Use the "Help" section in AdSense dashboard
   - Provide your publisher ID and domain

3. **Common Wait Times**
   - New ad units: 10-20 minutes
   - New sites: 24-48 hours
   - Policy reviews: 1-2 weeks

## Updated Files

The following files have been updated to fix Google AdSense issues:

1. `resources/js/Layouts/PublicWebsiteLayout.vue`
   - Added proper consent checking
   - Improved ad initialization with error handling
   - Added validation for required ad attributes

2. `resources/js/Pages/Public/Website/Article.vue`
   - Enhanced ad initialization with retry logic
   - Better consent event handling
   - Detailed logging for troubleshooting

3. `resources/js/composables/useConsentManagement.js`
   - Added Google AdSense consent management
   - Separate initialization function for Google Ads

## Next Steps

1. **Rebuild Your Assets**
   ```bash
   npm run build
   ```
   or for development:
   ```bash
   npm run dev
   ```

2. **Clear Cache**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

3. **Deploy to Production**
   - Push changes to your production server
   - Restart your application server if needed

4. **Test on Production**
   - Visit your production website
   - Open browser console (F12)
   - Accept all cookies when prompted
   - Check for ad initialization logs
   - Wait 10-20 minutes for ads to appear

## Still Having Issues?

If you're still seeing the 400 error or ads aren't showing:

1. Double-check your AdSense account status
2. Verify your domain is approved in AdSense
3. Ensure ad slot IDs are correct (copy directly from AdSense dashboard)
4. Check that your site meets AdSense content policies
5. Wait 24-48 hours after adding a new site to AdSense

Remember: AdSense ads may not show on every page load, especially for new sites or low-traffic pages. This is normal behavior.
