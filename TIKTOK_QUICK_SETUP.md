# ⚡ TikTok OAuth - Quick Start Guide

## ✅ Credentials Configured

```env
TIKTOK_CLIENT_KEY=awffy1d8f2lhad6l
TIKTOK_CLIENT_SECRET=AzmcyRQovHRsr2Ln9l2FhoQReoXfURru
```

---

## 🚀 Setup Steps (5 Minutes)

### Step 1: Install & Start ngrok

**Download ngrok:**
- Windows: https://ngrok.com/download
- Extract to: `C:\ngrok\`

**Start ngrok:**
```bash
cd C:\ngrok
ngrok http 8000
```

**You will see output like:**
```
Forwarding  https://abc123xyz.ngrok.io -> http://localhost:8000
```

**Copy the HTTPS URL:** `https://abc123xyz.ngrok.io`

---

### Step 2: Register Redirect URI

**Go to TikTok Developer Portal:**

1. Open: https://developers.tiktok.com/apps
2. Select your app (Client Key: `awffy1d8f2lhad6l`)
3. Go to **"Login Kit"** tab
4. Click **"Add redirect URI"**
5. Enter: `https://abc123xyz.ngrok.io/auth/tiktok/callback`
   ⚠️ Replace `abc123xyz` with YOUR ngrok subdomain
6. Click **"Save"**

---

### Step 3: Update .env File

Edit `.env` file:

```env
# Replace with YOUR ngrok URL
TIKTOK_REDIRECT_URI=https://abc123xyz.ngrok.io/auth/tiktok/callback
```

**Then reload config:**
```bash
php artisan config:clear
```

---

### Step 4: Test OAuth Flow

**Terminal 1 - Start Laravel:**
```bash
cd C:\xampp\htdocs\socialinsight
php artisan serve
```

**Terminal 2 - Keep ngrok running:**
```bash
cd C:\ngrok
ngrok http 8000
```

**Open in browser:**
```
https://abc123xyz.ngrok.io
```

**Click:** "Connect TikTok" button

**Expected flow:**
1. ✅ Redirects to TikTok login page
2. ✅ Login with TikTok account
3. ✅ Authorize app permissions
4. ✅ Redirects back to your app
5. ✅ See success message: "Connected to TikTok!"

---

## 🐛 Troubleshooting

### ❌ Error: "Redirect URI mismatch"

**Problem:** URI doesn't match registered URI

**Fix:**
```bash
# 1. Check your ngrok URL
#    Terminal should show: https://abc123xyz.ngrok.io

# 2. Update .env
TIKTOK_REDIRECT_URI=https://abc123xyz.ngrok.io/auth/tiktok/callback

# 3. Clear config
php artisan config:clear

# 4. Verify in TikTok portal
#    https://developers.tiktok.com/apps
#    Check "Login Kit" > Redirect URIs
```

### ❌ Error: "Invalid client key"

**Problem:** Credentials mismatch

**Fix:**
```bash
# 1. Verify credentials in .env match TikTok portal
TIKTOK_CLIENT_KEY=awffy1d8f2lhad6l
TIKTOK_CLIENT_SECRET=AzmcyRQovHRsr2Ln9l2FhoQReoXfURru

# 2. No spaces or extra characters
# 3. Clear config
php artisan config:clear
```

### ❌ Error: "This site can't provide a secure connection"

**Problem:** Using HTTP instead of HTTPS

**Fix:**
- ✅ Use ngrok URL: `https://abc123xyz.ngrok.io`
- ❌ Don't use: `http://localhost:8000`
- TikTok OAuth requires HTTPS!

---

## 📋 Complete Setup Checklist

- [x] ✅ TikTok credentials configured in `.env`
- [ ] ⏳ Download & install ngrok
- [ ] ⏳ Start ngrok: `ngrok http 8000`
- [ ] ⏳ Copy ngrok HTTPS URL
- [ ] ⏳ Register redirect URI in TikTok portal
- [ ] ⏳ Update `.env` with ngrok URL
- [ ] ⏳ Run `php artisan config:clear`
- [ ] ⏳ Test OAuth flow

---

## 🎯 Current Status

**Credentials:** ✅ Configured  
**Client Key:** `awffy1d8f2lhad6l`  
**Client Secret:** `AzmcyRQovHRsr2Ln9l2FhoQReoXfURru`

**Next:** Setup ngrok & register redirect URI

---

## 📞 Need Help?

**Check logs:**
```bash
tail -f storage/logs/laravel.log
```

**Test configuration:**
```bash
php artisan tinker
>>> config('services.tiktok.client_key')
=> "awffy1d8f2lhad6l"
```

**Full documentation:**
- `docs/api/TIKTOK_LOGIN_KIT_SETUP.md`

---

**Quick Commands:**

```bash
# Start ngrok
cd C:\ngrok && ngrok http 8000

# Start Laravel (new terminal)
cd C:\xampp\htdocs\socialinsight && php artisan serve

# Clear config
php artisan config:clear

# View logs
tail -f storage/logs/laravel.log
```

---

**Last Updated:** November 10, 2025  
**Ready to test!** Just setup ngrok and register redirect URI! 🚀
