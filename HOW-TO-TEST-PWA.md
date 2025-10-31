# How to Test PWA Installation

## Quick Test Steps

### Method 1: Use the Test Page
1. **Open the test page**: https://qrch.online/pwa-test.html
2. This page will automatically check all PWA requirements
3. Look for green "pass" badges on all checks
4. If the install button appears, click it!

### Method 2: Test on Main Site
1. **Clear your browser cache completely**:
   - Chrome: Press `Ctrl+Shift+Delete` (or `Cmd+Shift+Delete` on Mac)
   - Select "All time"
   - Check "Cached images and files"
   - Click "Clear data"

2. **Visit the main site**: https://qrch.online

3. **Open Chrome DevTools** (Press F12):
   - Go to **Console** tab
   - You should see: `✅ Service Worker registered: /`
   - If you see errors, let me know what they say

4. **Check Application tab** in DevTools:
   - Click **Manifest** on the left
   - Should show all app details and icons
   - Click **Service Workers** on the left
   - Should show sw.js as "activated and running"

5. **Look for the install button**:
   - Chrome Desktop: Look for ⊕ icon in address bar
   - Chrome Mobile: Menu → "Install app"

## Why Install Prompt Might Not Show

### Chrome requires ALL of these:
- ✅ Valid manifest (we have this)
- ✅ Service worker registered (we have this)
- ✅ HTTPS (we have this)
- ✅ Valid icons (we have this)
- ⚠️ **User engagement** - You need to interact with the site first!

### To trigger the install prompt:
1. Visit the site
2. Click around, navigate to different pages
3. Spend at least 30 seconds on the site
4. Chrome tracks engagement before showing prompt

## Debugging Steps

### If Service Worker Fails:
```javascript
// Open Console (F12) and run:
navigator.serviceWorker.getRegistrations().then(registrations => {
    console.log('Registrations:', registrations);
});
```

### If you see any errors in console:
1. Take a screenshot
2. Share the error message
3. I can help fix it

### Force Unregister (if needed):
```javascript
// Run in Console:
navigator.serviceWorker.getRegistrations().then(registrations => {
    registrations.forEach(r => r.unregister());
    console.log('All service workers unregistered');
});
```

Then refresh and let it re-register.

## Mobile Testing

### Android Chrome:
1. Visit https://qrch.online on Chrome
2. Tap menu (⋮)
3. Look for "Install app" or "Add to Home screen"
4. If you don't see it, interact with the site for 30+ seconds
5. Close Chrome and reopen
6. Try menu again

### iOS Safari (Limited Support):
1. Visit https://qrch.online
2. Tap Share button (□↑)
3. Scroll and tap "Add to Home Screen"
4. This adds a shortcut (not full PWA)

## Expected Behavior After Install

Once installed, you should:
1. See the app icon on desktop/home screen
2. App opens in standalone window (no browser UI)
3. Works offline for cached pages
4. Fast loading from cache
5. Right-click icon shows app shortcuts (Scan QR, Dashboard)

## Common Issues

### "Already installed"
- If you already installed it, you won't see the prompt
- Uninstall first: Chrome → Apps → Right-click QRMH → Uninstall

### "Criteria not met" in DevTools
- Check DevTools → Application → Manifest for warnings
- Make sure you're on HTTPS (we are ✓)
- Interact with site for 30+ seconds

### No service worker in DevTools
- Hard refresh: `Ctrl+Shift+R` (or `Cmd+Shift+R`)
- Check console for errors
- Try the test page: https://qrch.online/pwa-test.html

## Verification Checklist

Run through this checklist:
- [ ] Visited https://qrch.online
- [ ] Cleared cache (Ctrl+Shift+Delete)
- [ ] Opened DevTools (F12)
- [ ] Checked Console - see "Service Worker registered"
- [ ] Checked Application → Manifest - sees QRMH details
- [ ] Checked Application → Service Workers - sees sw.js active
- [ ] Interacted with site (clicked around for 30+ seconds)
- [ ] Closed and reopened browser
- [ ] Looked for install button in address bar

## Still Not Working?

If after all this you still can't see the install prompt:

1. **Test the test page first**: https://qrch.online/pwa-test.html
   - This will show exactly what's working/failing

2. **Share these details**:
   - Browser name and version
   - Screenshot of DevTools → Application → Manifest
   - Screenshot of Console (any errors?)
   - Screenshot of address bar (is there an install icon?)

3. **Try incognito mode**:
   - Sometimes extensions block PWA features
   - Open Chrome Incognito
   - Visit https://qrch.online
   - Check if install prompt appears

---

**The PWA is fully configured and should work!**

Most likely you just need to:
1. Clear your cache
2. Interact with the site for 30 seconds
3. Look for the install icon

Try it now! 🚀
