# 🚀 Complete Deployment Guide - All Fixes

## Issues Fixed in This Release

1. ✅ **Pinterest Verification** - Meta tag now in `<head>` (not in app div)
2. ✅ **Duplicate Articles** - Prevents creating 3x same article
3. ✅ **Jobs Reprocessing** - Jobs stay "completed" (don't go back to "processing")
4. ✅ **Missing Ingredients** - Recipe cards now show ingredients section

---

## 📋 Pre-Deployment Checklist

- [ ] **Backup database** (CRITICAL!)
  ```bash
  # Export database
  mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql
  
  # Or use your hosting provider's backup tool
  ```

- [ ] **Check current queue status**
  ```bash
  # See if any jobs are running
  php artisan tinker
  >>> ArticleGenerationJob::where('status', 'processing')->count()
  ```

- [ ] **Stop queue workers** (prevents issues during deployment)
  ```bash
  php artisan queue:restart
  ```

---

## 🔧 Deployment Steps

### Step 1: Deploy Code

```bash
# Pull latest code
git pull origin main

# If using Composer
composer install --no-dev --optimize-autoloader

# Generate optimized autoloader
composer dump-autoload -o
```

### Step 2: Run Database Migration

```bash
# CRITICAL: This adds ingredients and instructions columns
php artisan migrate

# Verify migration ran successfully
php artisan migrate:status
```

Expected output should show:
```
✓ 2026_02_22_233840_add_ingredients_instructions_to_articles_table
```

### Step 3: Clear All Caches

```bash
# Clear Laravel caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Clear compiled files
php artisan clear-compiled

# Regenerate optimized files (optional, for production)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 4: Restart Queue Workers

```bash
# Restart queue workers (CRITICAL - must load new job class)
php artisan queue:restart

# If using Supervisor
sudo supervisorctl restart all

# If using systemd
sudo systemctl restart laravel-worker

# Verify workers are running
ps aux | grep "queue:work"
```

### Step 5: Verify Deployment

Run these checks to ensure everything is working:

#### A. Check Database Schema
```bash
php artisan tinker
>>> Schema::hasColumn('articles', 'ingredients')
>>> Schema::hasColumn('articles', 'instructions')
```
Expected: Both should return `true`

#### B. Check Article Model
```bash
php artisan tinker
>>> $fillable = (new \App\Models\Article)->getFillable()
>>> in_array('ingredients', $fillable)
>>> in_array('instructions', $fillable)
```
Expected: Both should return `true`

#### C. Check Queue Status
```bash
php artisan queue:work --once --verbose
```
Should run without errors

---

## 🧪 Testing After Deployment

### Test 1: Generate Single Recipe (Hybrid Mode)

1. Go to **Global AI Articles**
2. Select **1 website**
3. Topic: `Test Recipe $(date +%s)`
4. Article Type: **Recipe**
5. Mode: **Hybrid Rewrite**
6. Click **Generate**

**Expected Results:**
- ✅ Job completes successfully
- ✅ Article created with ingredients
- ✅ Article created with instructions
- ✅ Recipe card shows all sections

**Verify in database:**
```bash
php artisan tinker
>>> $article = Article::latest()->first()
>>> $article->title
>>> count($article->ingredients ?? [])
>>> count($article->instructions ?? [])
```

Expected:
- `ingredients`: Array with 8-15 items
- `instructions`: Array with 8-12 items

### Test 2: Generate Multiple Recipes (3 websites)

1. Select **3 websites**
2. Generate same recipe topic
3. **Expected**: 3 unique articles (not 9 duplicates!)

**Verify:**
```bash
php artisan tinker
>>> Article::where('title', 'LIKE', '%Test Recipe%')
>>>     ->where('created_at', '>=', now()->subHour())
>>>     ->count()
```

Expected: **3 articles** (1 per website), not 9

### Test 3: Duplicate Prevention

1. Generate an article with topic "Duplicate Prevention Test"
2. **Immediately** try to generate again with same topic
3. **Expected**: Error message "A job for this topic is already being processed"

### Test 4: Job Completion (No Reprocessing)

1. Generate an article
2. Watch job status change from "Pending" → "Processing" → "Completed"
3. Wait 5 minutes
4. **Expected**: Status stays "Completed" (doesn't go back to "Processing")

**Monitor:**
```bash
# Watch logs in real-time
tail -f storage/logs/laravel.log | grep -i "already completed"
```

Should see logs like:
- "All jobs already completed, aborting"
- "Job X is already completed, skipping"

### Test 5: Pinterest Verification

1. Visit your production website
2. View page source (Ctrl+U or Cmd+Option+U)
3. Search for `p:domain_verify`
4. **Expected**: Meta tag appears in `<head>` section, NOT inside `<div id="app">`

```html
<head>
  ...
  <meta name="p:domain_verify" content="your-pinterest-code" />
  ...
</head>
```

### Test 6: Recipe Card Display

1. Visit any recipe article on your website
2. **Expected to see:**
   - ✅ Prep time: XX mins
   - ✅ Cook time: XX mins
   - ✅ Rest time: XX mins (if applicable)
   - ✅ Total time: XX mins
   - ✅ **Ingredients section with list**
   - ✅ **Instructions section with numbered steps**

---

## 🔍 Monitoring (First 24 Hours)

### Watch Logs
```bash
# Terminal 1: Watch all logs
tail -f storage/logs/laravel.log

# Terminal 2: Watch for errors
tail -f storage/logs/laravel.log | grep -i error

# Terminal 3: Watch queue worker
ps aux | grep queue:work
```

### Check Job Statistics
```bash
php artisan tinker
>>> # Jobs in last hour
>>> ArticleGenerationJob::where('created_at', '>=', now()->subHour())
>>>     ->selectRaw('status, count(*) as count')
>>>     ->groupBy('status')
>>>     ->get()
```

Expected healthy output:
```
status: completed, count: 10+
status: failed, count: 0-2 (acceptable)
status: processing, count: 0-5 (only active ones)
status: pending, count: 0-3 (waiting to process)
```

### Check for Duplicates
```bash
php artisan tinker
>>> # Find duplicate articles (same title, same website)
>>> Article::selectRaw('title, website_id, count(*) as count')
>>>     ->where('created_at', '>=', now()->subHour())
>>>     ->groupBy('title', 'website_id')
>>>     ->having('count', '>', 1)
>>>     ->get()
```

Expected: **Empty collection** (no duplicates)

### Check Ingredients Data
```bash
php artisan tinker
>>> # Check recent recipes have ingredients
>>> Article::where('article_type', 'recipe')
>>>     ->where('created_at', '>=', now()->subHour())
>>>     ->get()
>>>     ->map(function($a) {
>>>         return [
>>>             'id' => $a->id,
>>>             'title' => $a->title,
>>>             'has_ingredients' => !empty($a->ingredients),
>>>             'ingredient_count' => count($a->ingredients ?? []),
>>>         ];
>>>     })
```

Expected: All recipes should have `has_ingredients: true` and `ingredient_count: 8+`

---

## 🚨 Troubleshooting

### Issue: Migration Fails

**Symptom**: `php artisan migrate` shows error

**Solution**:
```bash
# Check current migrations
php artisan migrate:status

# Try migrating with force (production)
php artisan migrate --force

# If column already exists (unlikely), rollback and migrate
php artisan migrate:rollback --step=1
php artisan migrate
```

### Issue: Jobs Still Reprocessing

**Symptom**: Completed jobs go back to "processing"

**Solution**:
```bash
# 1. Clear cache (releases job uniqueness locks)
php artisan cache:clear

# 2. Restart ALL queue workers
php artisan queue:restart
ps aux | grep queue:work  # Should show only 1-2 processes

# 3. If multiple workers, kill extras
killall php  # Then restart properly

# 4. Restart queue worker properly
php artisan queue:work --daemon
```

### Issue: Ingredients Still Missing

**Symptom**: New articles don't have ingredients

**Solution**:
```bash
# 1. Verify migration ran
php artisan tinker
>>> Schema::hasColumn('articles', 'ingredients')

# 2. If false, run migration
php artisan migrate --force

# 3. Clear model cache
php artisan clear-compiled
composer dump-autoload -o

# 4. Restart queue workers
php artisan queue:restart
```

### Issue: Pinterest Meta Tag Still in Wrong Place

**Symptom**: Meta tag still in `<div id="app">`

**Solution**:
```bash
# Clear view cache
php artisan view:clear

# Clear route cache
php artisan route:clear

# Restart web server (if using PHP-FPM)
sudo systemctl restart php8.2-fpm

# Clear browser cache and test in incognito mode
```

### Issue: "Class not found" Error

**Symptom**: Queue worker shows "Class 'ShouldBeUnique' not found"

**Solution**:
```bash
# Regenerate autoloader
composer dump-autoload -o

# Clear compiled classes
php artisan clear-compiled

# Restart queue workers
php artisan queue:restart
```

---

## 📊 Success Metrics

After 24 hours, you should see:

| Metric | Target | Command |
|--------|--------|---------|
| Duplicate articles | 0 | Check with tinker query above |
| Jobs reprocessed | 0 | Check logs for "already completed" |
| Failed jobs | < 5% | `ArticleGenerationJob::where('status', 'failed')->count()` |
| Recipes with ingredients | 100% | Check with tinker query above |
| Queue worker uptime | 100% | `ps aux \| grep queue:work` |

---

## 🔄 Rollback Plan

If critical issues occur:

```bash
# 1. Stop queue workers
php artisan queue:restart

# 2. Rollback migration
php artisan migrate:rollback --step=1

# 3. Rollback code
git checkout previous-commit-hash

# 4. Clear caches
php artisan cache:clear
php artisan config:clear
composer dump-autoload -o

# 5. Restart queue workers
php artisan queue:restart
```

---

## 📞 Support Checklist

If you need help after deployment:

- [ ] Check `storage/logs/laravel.log` for errors
- [ ] Verify migration status: `php artisan migrate:status`
- [ ] Check queue worker is running: `ps aux | grep queue:work`
- [ ] Verify cache is clear: `php artisan cache:clear`
- [ ] Check database connection: `php artisan tinker` → `DB::connection()->getPdo()`
- [ ] Review documentation:
  - `MISSING_INGREDIENTS_FIX.md` - Ingredients issue details
  - `DUPLICATE_JOB_FIXES.md` - Job reprocessing details
  - `FIX_CHECKLIST.md` - Complete checklist

---

## 📝 Post-Deployment Notes

After successful deployment:

- [ ] Update team/clients about new features
- [ ] Monitor for 24-48 hours
- [ ] Verify all existing functionality still works
- [ ] Test edge cases (large batch generations, etc.)
- [ ] Consider regenerating high-priority old recipes (optional)

---

**Deployment Date**: _____________  
**Deployed By**: _____________  
**Verified By**: _____________  
**Status**: ☐ Success ☐ Rollback Required ☐ Issues Found

---

**All fixes are production-ready!** 🎉
