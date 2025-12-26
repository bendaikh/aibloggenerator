# Troubleshooting: White Page Issue

## The Problem
You're seeing a white page when accessing `/superadmin` after login.

## Most Common Cause
The frontend assets (JavaScript/CSS) are not being served properly.

## Solution

### Step 1: Make sure Vite development server is running

Open a **NEW terminal window** (keep your `php artisan serve` running) and run:

```bash
npm run dev
```

You should see output like:
```
VITE v7.x.x  ready in xxx ms

➜  Local:   http://localhost:5173/
➜  Network: use --host to expose
➜  press h + enter to show help

LARAVEL v11.x.x  plugin v1.x.x

➜  APP_URL: http://localhost:8000
```

### Step 2: Hard Refresh Your Browser

After starting `npm run dev`:
1. Go to your browser
2. Press `Ctrl + Shift + R` (Windows/Linux) or `Cmd + Shift + R` (Mac)
3. Or press `F12`, right-click the refresh button, and select "Empty Cache and Hard Reload"

### Step 3: Check Browser Console

1. Press `F12` to open Developer Tools
2. Click on the "Console" tab
3. Look for any errors (red text)
4. Look for any errors in the "Network" tab

## Alternative: Use Production Build

If `npm run dev` doesn't work, you can use the production build:

```bash
npm run build
```

Then in your `.env` file, make sure:
```
APP_ENV=production
APP_DEBUG=false
```

And run:
```bash
php artisan optimize
```

## Common Errors and Fixes

### Error: "Failed to fetch dynamically imported module"
**Fix:** Hard refresh the browser (Ctrl + Shift + R)

### Error: Connection refused to localhost:5173
**Fix:** Make sure `npm run dev` is running in a separate terminal

### Error: "Inertia page component not found"
**Fix:** Check the component exists and the path is correct

### Error: "500 Internal Server Error"
**Fix:** Check Laravel logs at `storage/logs/laravel.log`

## Quick Check Commands

```bash
# Check if npm dev server is running
netstat -ano | findstr :5173

# Check if PHP server is running  
netstat -ano | findstr :8000

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild frontend
npm run build
```

## Expected Setup

You should have **TWO terminals running**:

**Terminal 1:**
```bash
php artisan serve
```

**Terminal 2:**
```bash
npm run dev
```

Then access: `http://localhost:8000/login`

## Still Not Working?

Check these files:
1. `resources/js/app.js` - Make sure it exists
2. `vite.config.js` - Make sure configuration is correct
3. `resources/views/app.blade.php` - Make sure @vite directive is present

## Contact Support

If issue persists, provide:
1. Browser console errors (F12 → Console tab)
2. Network tab errors (F12 → Network tab)  
3. Laravel log errors (`storage/logs/laravel.log`)

