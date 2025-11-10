# 🎵 TikTok Login Kit for Web - Setup Guide

## 📋 Prerequisites

### 1. Register Your App on TikTok

1. Go to **TikTok for Developers**: https://developers.tiktok.com
2. Click **"Manage apps"**
3. Create a new app or select existing app
4. Note your **Client Key** and **Client Secret**

### 2. Configure Redirect URI

Redirect URI must be registered in Login Kit product configuration.

**Requirements:**
- ✅ Maximum 10 URIs
- ✅ Each URI < 512 characters
- ✅ Must be absolute and begin with `https://`
- ✅ Must be static (no parameters)
- ✅ Cannot include fragment (#)

**Examples:**

```
✅ Correct: https://dev.example.com/auth/callback/
❌ Incorrect: dev.example.com/auth/callback/

✅ Correct: https://dev.example.com/auth/callback/
❌ Incorrect: https://dev.example.com/auth/callback/?id=1

✅ Correct: https://dev.example.com/auth/callback/
❌ Incorrect: https://dev.example.com/auth/callback/#100
```

**For Development:**
- Use ngrok for HTTPS: `https://your-domain.ngrok.io/auth/tiktok/callback`
- Register this URL in TikTok Developer Portal

**For Production:**
- Use your domain: `https://yourdomain.com/auth/tiktok/callback`

---

## 🔧 Implementation Guide

### Step 1: Configure Environment Variables

Edit `.env` file:

```env
# TikTok OAuth Configuration
TIKTOK_CLIENT_KEY=your_client_key_here
TIKTOK_CLIENT_SECRET=your_client_secret_here
TIKTOK_REDIRECT_URI=https://your-ngrok-url.ngrok.io/auth/tiktok/callback
```

**Get your credentials:**
1. Login to https://developers.tiktok.com
2. Go to **Manage apps** → Select your app
3. Copy **Client Key** and **Client Secret**

---

### Step 2: Frontend Integration

File already implemented: `resources/views/home.blade.php`

```html
<!-- TikTok Connect Button -->
<a href="{{ url('/auth/tiktok') }}" 
   class="px-4 py-2 bg-black text-white rounded-lg">
    Connect TikTok
</a>
```

---

### Step 3: Backend OAuth Flow

Already implemented in `TikTokAuthController.php`:

#### A. Redirect to TikTok Authorization

```php
public function redirectToTikTok()
{
    // 1. Generate CSRF state token
    $state = Str::random(40);
    Session::put('tiktok_oauth_state', $state);
    
    // 2. Build authorization URL
    $authUrl = 'https://www.tiktok.com/v2/auth/authorize/'
        . '?client_key=' . $this->clientKey
        . '&scope=user.info.basic,video.list'
        . '&response_type=code'
        . '&redirect_uri=' . urlencode($this->redirectUri)
        . '&state=' . $state;
    
    // 3. Redirect user
    return redirect()->away($authUrl);
}
```

#### B. Handle Callback

```php
public function handleTikTokCallback(Request $request)
{
    // 1. Verify CSRF state
    $state = Session::get('tiktok_oauth_state');
    if ($state !== $request->query('state')) {
        return redirect('/')->with('error', 'Invalid state');
    }
    
    // 2. Get authorization code
    $code = $request->query('code');
    
    // 3. Exchange code for access token
    $response = Http::asForm()->post('https://open.tiktokapis.com/v2/oauth/token/', [
        'client_key' => $this->clientKey,
        'client_secret' => $this->clientSecret,
        'code' => $code,
        'grant_type' => 'authorization_code',
        'redirect_uri' => $this->redirectUri,
    ]);
    
    // 4. Store access token
    $data = $response->json();
    Session::put('tiktok_access_token', $data['access_token']);
    Session::put('tiktok_refresh_token', $data['refresh_token']);
    
    return redirect('/')->with('success', 'Connected to TikTok!');
}
```

---

## 🔐 Security Best Practices

### 1. CSRF Protection

**State Token:**
```php
// Generate random state
$state = Str::random(40);
Session::put('tiktok_oauth_state', $state);

// Verify on callback
if ($state !== $request->query('state')) {
    abort(403, 'Invalid CSRF state');
}
```

### 2. Secure Storage

**Never expose in frontend:**
- ❌ Client Secret in JavaScript
- ❌ Access Token in HTML
- ❌ Refresh Token in cookies (without httpOnly)

**Store securely:**
- ✅ Session (server-side)
- ✅ Database (encrypted)
- ✅ Environment variables

### 3. Token Management

```php
// Access token expires in 24 hours
// Refresh token expires in 1 year

// Refresh access token before expiry
public function refreshAccessToken()
{
    $refreshToken = Session::get('tiktok_refresh_token');
    
    $response = Http::asForm()->post('https://open.tiktokapis.com/v2/oauth/token/', [
        'client_key' => $this->clientKey,
        'client_secret' => $this->clientSecret,
        'grant_type' => 'refresh_token',
        'refresh_token' => $refreshToken,
    ]);
    
    $data = $response->json();
    Session::put('tiktok_access_token', $data['access_token']);
    Session::put('tiktok_refresh_token', $data['refresh_token']);
}
```

---

## 📊 OAuth Flow Diagram

```
1. User clicks "Connect TikTok"
   ↓
2. Server generates CSRF state token
   ↓
3. Server redirects to TikTok authorization page
   https://www.tiktok.com/v2/auth/authorize/
   ↓
4. User logs in to TikTok (if not already)
   ↓
5. User grants permissions
   ↓
6. TikTok redirects back with authorization code
   https://yourapp.com/auth/tiktok/callback?code=xxx&state=yyy
   ↓
7. Server verifies CSRF state
   ↓
8. Server exchanges code for access token
   POST https://open.tiktokapis.com/v2/oauth/token/
   ↓
9. Server stores access token in session
   ↓
10. User can now fetch TikTok data
```

---

## 🧪 Testing

### 1. Start ngrok

```bash
ngrok http 8000
```

Copy the HTTPS URL (e.g., `https://abc123.ngrok.io`)

### 2. Update .env

```env
TIKTOK_REDIRECT_URI=https://abc123.ngrok.io/auth/tiktok/callback
```

### 3. Register Redirect URI

1. Go to https://developers.tiktok.com
2. Select your app
3. Go to **Login Kit** settings
4. Add redirect URI: `https://abc123.ngrok.io/auth/tiktok/callback`
5. Save

### 4. Clear Config Cache

```bash
php artisan config:clear
php artisan cache:clear
```

### 5. Test OAuth Flow

1. Start Laravel server:
   ```bash
   php artisan serve
   ```

2. Open ngrok URL in browser:
   ```
   https://abc123.ngrok.io
   ```

3. Click **"Connect TikTok"** button

4. Complete TikTok login/authorization

5. Should redirect back to your app with success message

---

## 🐛 Troubleshooting

### Error: "Redirect URI mismatch"

**Cause:** Redirect URI in code doesn't match registered URI

**Fix:**
1. Check `.env` TIKTOK_REDIRECT_URI
2. Ensure it matches exactly in TikTok Developer Portal
3. Run `php artisan config:clear`

### Error: "Invalid client credentials"

**Cause:** Wrong Client Key or Secret

**Fix:**
1. Verify credentials in TikTok Developer Portal
2. Copy-paste carefully (no spaces)
3. Update `.env`
4. Run `php artisan config:clear`

### Error: "Invalid state parameter"

**Cause:** CSRF state mismatch

**Fix:**
1. Ensure cookies are enabled
2. Check session driver (`SESSION_DRIVER=database` in `.env`)
3. Run `php artisan session:table && php artisan migrate`

### Error: "Authorization request failed"

**Cause:** TikTok API down or network issue

**Fix:**
1. Check TikTok API status
2. Verify internet connection
3. Check firewall settings

---

## 📚 API Endpoints Reference

### Authorization URL

```
GET https://www.tiktok.com/v2/auth/authorize/
```

**Query Parameters:**
- `client_key`: Your app's client key
- `scope`: Comma-separated scopes (e.g., `user.info.basic,video.list`)
- `response_type`: Always `code`
- `redirect_uri`: Your registered redirect URI
- `state`: CSRF protection token
- `disable_auto_auth`: (Optional) `0` or `1`

### Token Exchange

```
POST https://open.tiktokapis.com/v2/oauth/token/
```

**Body (form-urlencoded):**
- `client_key`: Your client key
- `client_secret`: Your client secret
- `code`: Authorization code from callback
- `grant_type`: `authorization_code`
- `redirect_uri`: Same redirect URI

**Response:**
```json
{
  "access_token": "act.xxx",
  "refresh_token": "rft.xxx",
  "expires_in": 86400,
  "token_type": "Bearer"
}
```

### Token Refresh

```
POST https://open.tiktokapis.com/v2/oauth/token/
```

**Body:**
- `client_key`: Your client key
- `client_secret`: Your client secret
- `grant_type`: `refresh_token`
- `refresh_token`: User's refresh token

---

## ✅ Quick Setup Checklist

- [ ] Register app on https://developers.tiktok.com
- [ ] Get Client Key and Client Secret
- [ ] Start ngrok: `ngrok http 8000`
- [ ] Register redirect URI in TikTok portal
- [ ] Update `.env` with credentials
- [ ] Run `php artisan config:clear`
- [ ] Test OAuth flow
- [ ] Verify access token received
- [ ] Test API calls with access token

---

## 🔗 Official Documentation

- **TikTok for Developers**: https://developers.tiktok.com
- **Login Kit Guide**: https://developers.tiktok.com/doc/login-kit-web
- **API Reference**: https://developers.tiktok.com/doc/tiktok-api-v2-overview

---

**Last Updated:** November 10, 2025  
**Version:** 2.0 (Using TikTok API v2)
