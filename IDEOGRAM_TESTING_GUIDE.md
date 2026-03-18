# Ideogram Integration - Testing Guide

## Quick Test Steps

### 1. Get Ideogram API Key
1. Visit: https://ideogram.ai/api
2. Sign up if you don't have an account
3. Go to API Dashboard
4. Create a new API key
5. Copy the key (starts with `idg_`)

### 2. Configure in System
1. Start your Laravel app: `php artisan serve`
2. Login to your account
3. Navigate to: `Organization > API Keys`
4. Find the "Ideogram AI" section
5. Paste your API key
6. Select "Ideogram (Recommended for Home Decor)" from the dropdown
7. Click "Save Settings"

### 3. Test Image Generation

#### Option A: Generate a New Article
1. Go to Home Decor website dashboard
2. Click "Generate Article" or "AI Article"
3. Enter a topic like: "26 Luxe Home Decor Ideas"
4. Select "Home Decor" theme/category
5. Click Generate
6. Wait for article generation
7. Images should generate automatically with Ideogram

#### Option B: Generate Images for Existing Article
1. Go to Articles list
2. Find a Home Decor article without images
3. Click "Generate Images" button
4. System will use Ideogram provider
5. Check progress in background jobs

### 4. Verify Success

**Check API Usage Logs:**
```sql
SELECT * FROM api_usage_logs 
WHERE provider = 'ideogram' 
ORDER BY created_at DESC 
LIMIT 10;
```

**Check Generated Images:**
- Look in `public/uploads/images/ai-generated/`
- Images should be in WebP format
- Verify images appear in article

**Check Costs:**
- Fast rendering: ~$0.08 per image
- Slow/HD rendering: ~$0.20 per image
- Significantly cheaper than DALL-E HD ($0.80)

### 5. Expected Results

✅ **Success Indicators:**
- API key saves successfully
- Provider shows as "Ideogram" in settings
- Status card shows "✓ Connected"
- Images generate without errors
- Images appear in articles
- Cost is tracked in usage logs
- Images are high quality and relevant to home decor

❌ **Common Issues:**

**Issue**: "Ideogram API key not configured"
- **Fix**: Add API key in settings first

**Issue**: "Invalid API key"
- **Fix**: Verify key is correct and active in Ideogram dashboard

**Issue**: Images not generating
- **Fix**: Check queue worker is running: `php artisan queue:work`
- **Fix**: Check Laravel logs: `tail -f storage/logs/laravel.log`

**Issue**: Images low quality
- **Fix**: Try "HD" quality setting (though standard is usually excellent)

## Manual API Test

You can test the Ideogram API directly from command line:

```bash
php artisan tinker
```

```php
// Get a user with Ideogram key configured
$user = App\Models\User::find(1);

// Set Ideogram as provider
$user->image_generation_provider = 'ideogram';
$user->save();

// Create service
$service = new App\Services\AIImageService($user);

// Generate test image
$result = $service->generateImage(
    'A beautiful modern living room with large windows, natural light, contemporary furniture, and indoor plants. Professional interior design photography, 8k resolution.',
    '1024x1024',
    'standard',
    'natural'
);

// Check result
print_r($result);

// Result should contain:
// - url: Temporary URL to download image
// - cost: ~0.08 for standard
// - provider: 'ideogram'
// - revised_prompt: Enhanced prompt by Ideogram
```

## Background Job Testing

Make sure queue worker is running:

```bash
# Terminal 1: Start queue worker
php artisan queue:work --tries=3

# Terminal 2: Generate article with images
# Then watch Terminal 1 for job processing
```

## Database Verification

```sql
-- Check if Ideogram key is stored
SELECT id, name, email, 
       CASE WHEN ideogram_api_key IS NOT NULL THEN 'YES' ELSE 'NO' END as has_ideogram_key,
       image_generation_provider
FROM users 
WHERE id = YOUR_USER_ID;

-- Check API usage
SELECT provider, model, operation, estimated_cost, created_at
FROM api_usage_logs
WHERE provider = 'ideogram'
ORDER BY created_at DESC;

-- Check generated images
SELECT ai.article_id, ai.image_path, ai.provider, ai.created_at, a.title
FROM article_images ai
JOIN articles a ON a.id = ai.article_id
WHERE ai.provider = 'ideogram'
ORDER BY ai.created_at DESC;
```

## Performance Benchmarks

Expected generation times:
- Single image (fast): 3-5 seconds
- Single image (HD): 8-12 seconds
- 10 images (fast): 30-50 seconds
- 26 images (fast): 130-150 seconds (with delays)

## Cost Analysis for Home Decor Article

**Scenario**: "26 Luxe Home Decor Ideas" article

| Provider | Quality | Per Image | Total (26 images) |
|----------|---------|-----------|-------------------|
| DALL-E | Standard | $0.040 | $1.04 |
| DALL-E | HD | $0.080 | $2.08 |
| Ideogram | Fast | $0.08 | $2.08 |
| Ideogram | Slow (HD) | $0.20 | $5.20 |

**Recommendation**: Use Ideogram Fast for cost-effective, high-quality home decor images.

## Troubleshooting Commands

```bash
# Check migration status
php artisan migrate:status

# View Laravel logs in real-time
tail -f storage/logs/laravel.log

# Check queue jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear cache if needed
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Check file permissions
ls -la public/uploads/images/ai-generated/
```

## Success Criteria

✅ All checks passed when:
1. Ideogram API key saves and shows as connected
2. Provider selection updates in database
3. Test image generates successfully
4. Image downloads and stores locally
5. Cost logs correctly (~$0.08 or ~$0.20)
6. Images appear in article view
7. No errors in Laravel logs
8. Image quality meets expectations

## Next Steps After Testing

1. Generate multiple test articles with different topics
2. Compare image quality: Ideogram vs DALL-E
3. Monitor costs over time
4. Adjust prompts if needed for better results
5. Consider making Ideogram the default for Home Decor theme

---

**Ready to Test!** 🚀

Start with step 1 above and work through each test to verify the integration.
