# Settings UI Guide

## How to Access

1. Log in to your dashboard
2. Click **Organization** or **Settings** in the navigation
3. You'll see the **Global Settings** page

---

## Settings Page Layout

### Section 1: AI Content Generation
- **OpenAI API Key** - Enter your API key (encrypted storage)
- **Test Connection** - Verify your API key works
- **Default AI Model** - Choose GPT-4o, GPT-4 Turbo, or GPT-3.5
- **Default Content Tone** - Select conversational, professional, etc.

### Section 2: Cost Reduction Strategy ⭐ NEW
This section controls how articles are generated across multiple websites.

#### **Full AI Mode** (Left Option)
```
┌─────────────────────────────────────┐
│ ○ Full AI Mode      [Default]       │
│                                      │
│ Every article generated via OpenAI  │
│                                      │
│ ✓ Maximum uniqueness                │
│ ✓ Premium quality                   │
│ ✗ Higher API costs                  │
└─────────────────────────────────────┘
```

#### **Hybrid Rewrite Mode** (Right Option) 
```
┌─────────────────────────────────────┐
│ ○ Hybrid Rewrite Mode [90% savings] │
│                                      │
│ 1 master AI article + local         │
│ variations                           │
│                                      │
│ ✓ 90% cost reduction                │
│ ✓ 8x faster generation              │
│ ✓ 40%+ uniqueness guaranteed        │
└─────────────────────────────────────┘
```

#### **Maximum Variations** (Shows when Hybrid is selected)
```
Maximum Variations
[────────●─────────────] 5

Generate up to 5 article variations from 
one master article when publishing to 
multiple websites
```

A slider control from 1 to 20 that lets you choose how many variations to create.

#### **Info Box**
A blue information box explains:
- How Hybrid Mode works
- Step-by-step process
- Cost example: "10 websites = 1 API call ($0.10) instead of 10 calls ($1.00)"

---

## Usage Flow

### Scenario 1: User Wants Maximum Quality (Default)
1. Go to Settings
2. **Full AI Mode** is already selected
3. Click "Save Settings"
4. Done! Every article will be unique AI content

### Scenario 2: User Wants to Save Costs
1. Go to Settings
2. Select **Hybrid Rewrite Mode** radio button
3. Adjust slider to desired number of variations (e.g., 10)
4. Click "Save Settings"
5. Done! Now generating for multiple websites saves 90%

---

## Visual Design

### Colors & Styling
- **Full AI Mode**: Green accent (emerald-500)
- **Hybrid Mode**: Blue accent (blue-500)
- **Selected option**: Highlighted border and background
- **Info boxes**: Blue background with helpful tips
- **Slider**: Interactive with real-time value display

### Responsive Design
- Works on desktop and mobile
- Two-column layout on desktop
- Single column on mobile
- Touch-friendly controls

---

## What Happens When Settings Change

### If you switch FROM Full AI TO Hybrid:
- ✅ Immediately active for next generation
- ✅ Existing articles remain unchanged
- ✅ New generations use hybrid mode
- ✅ 90% cost savings start immediately

### If you switch FROM Hybrid TO Full AI:
- ✅ Immediately active for next generation
- ✅ Back to maximum uniqueness
- ✅ Higher quality per article
- ⚠️ Higher costs resume

### If you adjust Max Variations:
- ✅ Applies to next generation batch
- ✅ Can set from 1-20 variations
- ✅ Higher number = more websites supported per batch

---

## User Experience Flow

```
User logs in
    ↓
Navigate to Settings
    ↓
See two clear options:
    • Full AI (current default)
    • Hybrid (with savings badge)
    ↓
Read info box explanation
    ↓
Choose preferred mode
    ↓
[If Hybrid] Adjust slider for variations
    ↓
Click "Save Settings"
    ↓
Success message appears
    ↓
Settings now active for all future generations
```

---

## Success Messages

After saving:
```
┌──────────────────────────────────────────┐
│ ✓ Settings updated successfully!         │
└──────────────────────────────────────────┘
```

Green success banner appears at top of page.

---

## Error Handling

The form validates:
- ✅ Generation mode must be selected
- ✅ Max variations must be 1-20
- ✅ All fields required

If validation fails, red error messages appear below the field.

---

## Benefits of UI Approach

1. **User-Friendly**: No SQL queries needed
2. **Visual**: See cost comparison side-by-side
3. **Safe**: Validation prevents invalid settings
4. **Immediate**: Settings apply instantly
5. **Discoverable**: Users naturally find it in settings
6. **Educational**: Info boxes explain the feature
7. **Responsive**: Works on all devices

---

## Comparison: Database vs UI

### ❌ Old Way (Database Query)
```sql
UPDATE users SET article_generation_mode = 'hybrid_rewrite' WHERE id = 1;
```
- Requires SQL knowledge
- Risk of typos
- No validation
- Not user-friendly

### ✅ New Way (UI)
```
Click Settings → Choose Hybrid Mode → Save
```
- No technical knowledge needed
- Visual feedback
- Built-in validation
- Professional UX

---

This UI implementation makes the cost-reduction feature accessible to all users, not just developers!
