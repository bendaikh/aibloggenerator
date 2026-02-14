# System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                         USER CONFIGURATION                           │
│  ┌────────────────────────┐        ┌────────────────────────────┐  │
│  │  Full AI Mode          │   OR   │  Hybrid Rewrite Mode       │  │
│  │  ─────────────         │        │  ───────────────────       │  │
│  │  • Every article via AI│        │  • 1 master via AI         │  │
│  │  • Max uniqueness      │        │  • N variations locally    │  │
│  │  • Higher cost         │        │  • 90% cost savings        │  │
│  └────────────────────────┘        └────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────┘
                                  ↓
┌─────────────────────────────────────────────────────────────────────┐
│                    ARTICLE GENERATION TRIGGER                        │
│                  User selects 10 websites to publish                 │
└─────────────────────────────────────────────────────────────────────┘
                                  ↓
┌─────────────────────────────────────────────────────────────────────┐
│                  GenerateGlobalAIArticleJob                          │
│                                                                       │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │  Mode Detection                                              │   │
│  │  if (user.article_generation_mode === 'hybrid_rewrite')     │   │
│  │      → handleHybridMode()                                    │   │
│  │  else                                                         │   │
│  │      → handleFullAIMode()                                    │   │
│  └─────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────┘
                    ↓                              ↓
         ┌──────────────────┐          ┌──────────────────────┐
         │   Full AI Mode   │          │  Hybrid Rewrite Mode │
         └──────────────────┘          └──────────────────────┘
                    ↓                              ↓
    ┌───────────────────────────┐    ┌────────────────────────────────┐
    │  FOR EACH WEBSITE (10):   │    │  STEP 1: Master Generation     │
    │  ├─ Call OpenAI API       │    │  ├─ Call OpenAI API (1x)       │
    │  ├─ Generate unique content│   │  ├─ Parse master content       │
    │  ├─ Parse content          │   │  └─ Store master data          │
    │  └─ Store article          │   │                                 │
    │                             │   │  STEP 2: Variation Creation    │
    │  Result:                    │   │  FOR EACH WEBSITE 2-10 (9):   │
    │  • 10 API calls            │    │  ├─ RewritingService:          │
    │  • 10 unique articles      │    │  │   • Rewrite title           │
    │  • Cost: ~$1.00            │    │  │   • Rewrite meta            │
    │  • Time: ~120s             │    │  │   • Rewrite content         │
    │                             │   │  │   • Rewrite headings        │
    │                             │   │  ├─ VariationEngine:           │
    │                             │   │  │   • Shuffle sections        │
    │                             │   │  │   • Vary tags/notes         │
    │                             │   │  │   • Calculate uniqueness    │
    │                             │   │  └─ Store variation            │
    │                             │   │                                 │
    │                             │   │  Result:                        │
    │                             │   │  • 1 API call                  │
    │                             │   │  • 1 master + 9 variations    │
    │                             │   │  • Cost: ~$0.10                │
    │                             │   │  • Time: ~15s                  │
    └───────────────────────────┘    └────────────────────────────────┘
                    ↓                              ↓
    ┌─────────────────────────────────────────────────────────────────┐
    │                    DATABASE: articles TABLE                      │
    │                                                                   │
    │  Full AI Articles:                  Hybrid Articles:             │
    │  ┌──────────────────────┐          ┌──────────────────────────┐│
    │  │ id: 1                │          │ id: 11 (MASTER)          ││
    │  │ title: "..."         │          │ title: "Ultimate ..."    ││
    │  │ generation_mode:     │          │ generation_mode:         ││
    │  │   full_ai            │          │   hybrid_rewrite         ││
    │  │ master_article_id:   │          │ master_article_id: NULL  ││
    │  │   NULL               │          │ variation_index: NULL    ││
    │  └──────────────────────┘          └──────────────────────────┘│
    │  ┌──────────────────────┐          ┌──────────────────────────┐│
    │  │ id: 2                │          │ id: 12 (VARIATION 1)     ││
    │  │ title: "..."         │          │ title: "Perfect ..."     ││
    │  │ generation_mode:     │          │ generation_mode:         ││
    │  │   full_ai            │          │   hybrid_rewrite         ││
    │  │ master_article_id:   │          │ master_article_id: 11    ││
    │  │   NULL               │          │ variation_index: 1       ││
    │  └──────────────────────┘          └──────────────────────────┘│
    │         ... ×10                            ... ×9                │
    └─────────────────────────────────────────────────────────────────┘
                                  ↓
    ┌─────────────────────────────────────────────────────────────────┐
    │                      ARTICLE RELATIONSHIPS                       │
    │                                                                   │
    │     Master Article (id: 11)                                      │
    │            ↓                                                      │
    │     masterArticle() / variations()                               │
    │            ↓                                                      │
    │     ┌──────┴──────┬──────┬──────┬──────┐                       │
    │     ↓             ↓      ↓      ↓      ↓                       │
    │  Variation 1  Variation 2  ...  Variation 9                     │
    │  (id: 12)     (id: 13)          (id: 20)                        │
    │                                                                   │
    │  Each variation links back via master_article_id                 │
    └─────────────────────────────────────────────────────────────────┘


╔══════════════════════════════════════════════════════════════════════╗
║                         SERVICE LAYER                                 ║
╚══════════════════════════════════════════════════════════════════════╝

┌─────────────────────────────────────────────────────────────────────┐
│                       RewritingService.php                           │
│  ┌───────────────────────────────────────────────────────────────┐ │
│  │  • rewriteContent($content, $index)                           │ │
│  │    ├─ Parse HTML with DOMDocument                             │ │
│  │    ├─ Rewrite paragraphs (synonym replacement)                │ │
│  │    ├─ Rewrite list items                                      │ │
│  │    ├─ Shuffle sections                                        │ │
│  │    └─ Return unique HTML                                      │ │
│  │                                                                 │ │
│  │  • rewriteTitle($title, $index)                               │ │
│  │    ├─ Prepend descriptive words                               │ │
│  │    ├─ Replace adjectives                                      │ │
│  │    └─ Rephrase structure                                      │ │
│  │                                                                 │ │
│  │  • rewriteMetaDescription($desc, $index)                      │ │
│  │    ├─ Shuffle sentences                                       │ │
│  │    └─ Apply synonyms                                          │ │
│  └───────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────┘
                                  ↓ uses
┌─────────────────────────────────────────────────────────────────────┐
│                       VariationEngine.php                            │
│  ┌───────────────────────────────────────────────────────────────┐ │
│  │  • createVariation($masterData, $index)                       │ │
│  │    ├─ Rewrite title                                           │ │
│  │    ├─ Rewrite meta_title                                      │ │
│  │    ├─ Rewrite meta_description                                │ │
│  │    ├─ Rewrite excerpt                                         │ │
│  │    ├─ Vary tags                                               │ │
│  │    ├─ Vary notes                                              │ │
│  │    ├─ Rewrite content                                         │ │
│  │    ├─ Vary ingredients order                                  │ │
│  │    └─ Vary instructions                                       │ │
│  │                                                                 │ │
│  │  • calculateUniquenessScore($article1, $article2)             │ │
│  │    ├─ Title similarity (15% weight)                           │ │
│  │    ├─ Content similarity (60% weight)                         │ │
│  │    ├─ Meta similarity (15% weight)                            │ │
│  │    ├─ Tags similarity (10% weight)                            │ │
│  │    └─ Return uniqueness % (100 - similarity)                  │ │
│  │                                                                 │ │
│  │  • isVariationUnique($variation, $master, $minScore = 40)    │ │
│  │    └─ Validate >= 40% difference                             │ │
│  └───────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────┘


╔══════════════════════════════════════════════════════════════════════╗
║                        DATA FLOW DIAGRAM                              ║
╚══════════════════════════════════════════════════════════════════════╝

   OpenAI API                    Laravel Backend              Database
       │                               │                          │
       │  1. Generate Master           │                          │
       │◄──────────────────────────────│                          │
       │                               │                          │
       │  2. Return JSON               │                          │
       ├──────────────────────────────►│                          │
       │                               │                          │
       │                               │  3. Store Master         │
       │                               ├─────────────────────────►│
       │                               │                          │
       │                               │  4. Create Variations    │
       │                               │     (Local Processing)   │
       │                               │  ┌─────────────────────┐ │
       │                               │  │ RewritingService    │ │
       │                               │  │       ↓             │ │
       │                               │  │ VariationEngine     │ │
       │                               │  └─────────────────────┘ │
       │                               │                          │
       │                               │  5. Store Variations     │
       │                               ├─────────────────────────►│
       │                               │                          │
       │                               │  6. Link to Master       │
       │                               ├─────────────────────────►│
       │                               │                          │
       ✗ No more API calls              │         DONE            │


╔══════════════════════════════════════════════════════════════════════╗
║                     COST COMPARISON CHART                             ║
╚══════════════════════════════════════════════════════════════════════╝

  Websites │ Full AI Cost │ Hybrid Cost │ Savings
  ─────────┼──────────────┼─────────────┼─────────
     1     │    $0.10     │   $0.10     │   0%
     5     │    $0.50     │   $0.10     │  80%
    10     │    $1.00     │   $0.10     │  90%
    20     │    $2.00     │   $0.10     │  95%
    50     │    $5.00     │   $0.10     │  98%
   100     │   $10.00     │   $0.10     │  99%

  📊 Visual:
  Full AI:  $$$$$$$$$$  (10 API calls)
  Hybrid:   $           (1 API call)
  Savings:  █████████   (90% saved)


╔══════════════════════════════════════════════════════════════════════╗
║                    UNIQUENESS GUARANTEE                               ║
╚══════════════════════════════════════════════════════════════════════╝

  Master Article                    Variation Article
  ──────────────                    ─────────────────
  
  Title: "Easy Chocolate Cake"  →   Title: "Perfect Chocolate Cake Recipe"
                                    (prepended "Perfect", added "Recipe")
  
  Meta: "Learn how to make..."  →   Meta: "Discover how to create..."
                                    (synonyms: learn→discover, make→create)
  
  Content:                          Content:
  <h2>Introduction</h2>         →   <h2>Getting Started</h2>
  <p>This cake is delicious</p> →   <p>This cake is mouthwatering</p>
  <h2>Tips</h2>                 →   <h2>Storage</h2> (section shuffled)
  <h2>Storage</h2>              →   <h2>Tips</h2>
  
  Tags: [chocolate, cake, easy] →   Tags: [chocolate, cake, simple]
                                    (easy → simple)
  
  Uniqueness Score: 42.7%
  ✅ PASSED (≥40% required)


╔══════════════════════════════════════════════════════════════════════╗
║                      QUICK REFERENCE                                  ║
╚══════════════════════════════════════════════════════════════════════╝

  Enable Hybrid Mode:
  ──────────────────
  UPDATE users 
  SET article_generation_mode = 'hybrid_rewrite'
  WHERE id = YOUR_USER_ID;

  Query Master Articles:
  ─────────────────────
  Article::masterOnly()->get();

  Query Variations:
  ────────────────
  $master->variations;

  Check Uniqueness:
  ────────────────
  $variationEngine->calculateUniquenessScore($v1, $v2);

  Cost Analysis:
  ─────────────
  $saved = Article::byGenerationMode('hybrid_rewrite')->count() * 0.09;
  echo "Saved: $" . $saved;
```

---

**Legend:**
- `→` : Data flow
- `↓` : Process flow
- `├─` : Tree branch
- `◄─` : API request
- `─►` : API response
- `✓` : Success
- `✗` : Not used
- `█` : Savings bar
