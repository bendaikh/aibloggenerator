# Quick Start Guide: Hybrid Article Rewriting

## 🚀 Getting Started in 3 Steps

### Step 1: Run Migrations

```bash
php artisan migrate
```

This creates the necessary database fields for the cost-reduction system.

### Step 2: Configure Settings via UI

Navigate to **Settings** in your dashboard:

1. Go to **Organization → Settings** (or **Global Settings**)
2. Scroll to the **Cost Reduction Strategy** section
3. Select your preferred mode:
   - **Full AI Mode**: Every article via AI (default, maximum quality)
   - **Hybrid Rewrite Mode**: 1 master + variations (90% cost savings)
4. If using Hybrid Mode, set **Maximum Variations** (1-20, default: 5)
5. Click **Save Settings**

**That's it!** No database queries needed - everything is configurable through the UI.

### Step 3: Generate Articles

Simply use the existing article generation workflow. The system will automatically:
- Detect your preferred mode from settings
- Generate 1 master article via AI (if hybrid mode)
- Create variations locally (if hybrid mode)
- Save 90% on API costs!

---

## 💰 Cost Savings Example

### Before (Full AI Mode)
```
10 websites × $0.10 per article = $1.00
Generation time: ~120 seconds
```

### After (Hybrid Mode)
```
1 master article ($0.10) + 9 variations ($0.00) = $0.10
Generation time: ~15 seconds
Savings: 90% cost + 8x faster
```

---

## 📊 Verify It's Working

After generating articles in hybrid mode:

```php
// Check the master article
$master = Article::masterOnly()->latest()->first();
echo "Master: " . $master->title;
echo "Variations: " . $master->getVariationCount();

// Check variations
$variations = $master->variations;
foreach ($variations as $variation) {
    echo "Variation {$variation->variation_index}: {$variation->title}\n";
}
```

---

## 🎯 Best Use Cases

### Use Hybrid Mode When:
- Generating the same topic for multiple websites
- Budget is a concern
- Speed is important
- Content variety is acceptable

### Use Full AI Mode When:
- Generating for a single website
- Maximum uniqueness is critical
- Different topics per website
- Premium quality is required

---

## ⚙️ Configuration Options

Configure via the **Settings UI**:

1. Navigate to **Organization → Settings**
2. Find the **Cost Reduction Strategy** section
3. Choose your mode and adjust settings

| Setting | Values | Default | Description |
|---------|--------|---------|-------------|
| Article Generation Mode | Full AI / Hybrid Rewrite | Full AI | Generation strategy |
| Maximum Variations | 1-20 | 5 | Max variations in hybrid mode |

The UI provides:
- ✅ Visual mode comparison
- ✅ Real-time cost savings display  
- ✅ Slider for max variations
- ✅ Helpful tooltips and explanations

---

## 🔍 Monitoring

Check the generation mode in your articles:

```php
// Count articles by mode
$fullAI = Article::byGenerationMode('full_ai')->count();
$hybrid = Article::byGenerationMode('hybrid_rewrite')->count();

echo "Full AI: {$fullAI}\n";
echo "Hybrid: {$hybrid}\n";
echo "Savings: " . ($hybrid * 0.90) . " in API costs\n";
```

---

## 🎓 Tips for Success

1. **Start with 5 variations** - Test with `max_variations = 5` first
2. **Review the first batch** - Check quality before scaling up
3. **Monitor uniqueness** - Variations should be 40%+ different
4. **Adjust as needed** - Increase/decrease variations based on results
5. **Mix modes** - Use hybrid for bulk, full AI for premium content

---

## 📞 Need Help?

- Read the full documentation: `COST_REDUCTION_STRATEGY.md`
- Check logs: `storage/logs/laravel.log`
- Verify migrations: `php artisan migrate:status`

---

**That's it! You're now saving 90% on AI article generation costs.** 🎉
