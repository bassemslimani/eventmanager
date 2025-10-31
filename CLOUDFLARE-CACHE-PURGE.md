# ⚠️ URGENT: Cloudflare is Caching Old Service Worker

## The Problem

Cloudflare cached the old, broken service worker file. The new, fixed version is on the server, but Cloudflare is serving the old cached version.

## Quick Fix (Do this now!)

### Option 1: Purge Everything (Easiest)

1. **Login to Cloudflare**:
   - Go to https://dash.cloudflare.com
   - Select your domain `qrch.online`

2. **Purge Cache**:
   - Click **Caching** in the left menu
   - Click **Configuration** tab
   - Scroll down to "Purge Cache"
   - Click **"Purge Everything"** button
   - Confirm the purge

3. **Wait 30 seconds**, then test:
   - Visit https://qrch.online/pwa-test.html
   - Service worker should now register successfully!

### Option 2: Purge Specific Files (Faster)

1. **Login to Cloudflare** → Select `qrch.online`

2. **Purge by URL**:
   - Go to Caching → Configuration
   - Click "Custom Purge"
   - Select "Purge by URL"
   - Enter these URLs (one per line):
     ```
     https://qrch.online/build/sw.js
     https://qrch.online/build/manifest.webmanifest
     https://qrch.online/build/registerSW.js
     ```
   - Click "Purge"

3. **Test immediately**:
   - Visit https://qrch.online/pwa-test.html
   - Should see service worker register!

## Prevent This in Future

I've already added cache control headers to `.htaccess` that tell Cloudflare NOT to cache service worker files. After you purge the cache once, this won't happen again.

## How to Verify It's Fixed

After purging, run this command to verify you're getting the NEW service worker:

```bash
curl -s https://qrch.online/build/sw.js | grep -o "index.html"
```

- If you see "index.html" → Still cached, wait a bit longer
- If you see nothing → ✅ Fixed! New version is serving

Or visit the test page:
- https://qrch.online/pwa-test.html
- Service Worker section should show "✅ pass" status

## Alternative: Bypass Cache for Testing

If you can't purge cache right now, you can test the PWA directly by:

1. **Use browser bypass**:
   - Open https://qrch.online
   - Press `Ctrl+Shift+R` (hard refresh)
   - This bypasses Cloudflare cache for that request

2. **Test in DevTools**:
   - Open DevTools (F12)
   - Go to Network tab
   - Check "Disable cache"
   - Reload the page

## Why This Happened

- Cloudflare was caching the old service worker from when it had the `index.html` bug
- The new service worker is on your server (you can see it locally)
- But Cloudflare serves the old cached version to visitors
- Once purged, the new cache headers will prevent this

---

**Action Required**: Please purge Cloudflare cache now using Option 1 or Option 2 above, then test the PWA again!
