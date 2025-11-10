<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TikTokAuthController extends Controller
{
    // TikTok OAuth Credentials (Sandbox for Testing)
    // GANTI dengan kredensial dari TikTok Developer Dashboard
    private $clientKey;
    private $clientSecret;
    private $redirectUri;

    public function __construct()
    {
        // Load dari .env untuk keamanan
        $this->clientKey = env('TIKTOK_CLIENT_KEY', 'your_client_key_here');
        $this->clientSecret = env('TIKTOK_CLIENT_SECRET', 'your_client_secret_here');
        $this->redirectUri = env('TIKTOK_REDIRECT_URI', 'http://localhost:8000/auth/tiktok/callback');
    }

    /**
     * STEP 1: Redirect user to TikTok OAuth authorization page
     * 
     * User clicks "Login with TikTok" → redirects to TikTok login page
     */
    public function redirectToTikTok()
    {
        try {
            // Scopes yang diperlukan untuk trending data
            // HARUS SAMA dengan yang disubmit untuk review
            $scopes = 'user.info.basic,video.list';
            
            // Generate random state untuk security (CSRF protection)
            $state = Str::random(40);
            Session::put('tiktok_oauth_state', $state);

            // Build TikTok authorization URL
            $authUrl = 'https://www.tiktok.com/v2/auth/authorize/'
                . '?client_key=' . $this->clientKey
                . '&scope=' . urlencode($scopes)
                . '&response_type=code'
                . '&redirect_uri=' . urlencode($this->redirectUri)
                . '&state=' . $state;

            Log::info('TikTok OAuth: Redirecting to TikTok', [
                'redirect_uri' => $this->redirectUri,
                'scopes' => $scopes
            ]);

            // Redirect browser ke TikTok login page
            return redirect()->away($authUrl);

        } catch (\Exception $e) {
            Log::error('TikTok OAuth Redirect Error: ' . $e->getMessage());
            return redirect('/')
                ->with('error', 'Unable to connect to TikTok. Please check your configuration.');
        }
    }

    /**
     * STEP 2: Handle callback from TikTok after user authorizes
     * 
     * TikTok redirects back → brings authorization code → exchange for access token
     */
    public function handleTikTokCallback(Request $request)
    {
        try {
            // 1. Verify state parameter (CSRF protection)
            $state = Session::get('tiktok_oauth_state');
            if (!$state || $state !== $request->query('state')) {
                Log::error('TikTok OAuth: Invalid state parameter');
                return redirect('/')
                    ->with('error', 'Invalid OAuth state. Please try again.');
            }

            // 2. Get authorization code from URL
            if (!$request->has('code')) {
                Log::error('TikTok OAuth: No authorization code received');
                return redirect('/')
                    ->with('error', 'No authorization code received from TikTok.');
            }

            $code = $request->query('code');
            Log::info('TikTok OAuth: Authorization code received');

            // 3. Exchange authorization code for access token
            // This happens server-to-server (backend) - SECURE
            $response = Http::asForm()->post('https://open.tiktokapis.com/v2/oauth/token/', [
                'client_key' => $this->clientKey,
                'client_secret' => $this->clientSecret,
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $this->redirectUri,
            ]);

            if ($response->failed()) {
                Log::error('TikTok OAuth: Token exchange failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return redirect('/')
                    ->with('error', 'Failed to exchange authorization code: ' . $response->body());
            }

            $tokenData = $response->json();
            $accessToken = $tokenData['access_token'] ?? null;
            $openId = $tokenData['open_id'] ?? null;
            $refreshToken = $tokenData['refresh_token'] ?? null;
            $expiresIn = $tokenData['expires_in'] ?? 86400;

            if (!$accessToken || !$openId) {
                Log::error('TikTok OAuth: Missing access token or open_id');
                return redirect('/')
                    ->with('error', 'Invalid token response from TikTok.');
            }

            Log::info('TikTok OAuth: Access token received', ['open_id' => $openId]);

            // 4. Get user info using access token
            $userResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])->get('https://open.tiktokapis.com/v2/user/info/', [
                'fields' => 'open_id,union_id,avatar_url,display_name'
            ]);

            $userData = $userResponse->successful() ? $userResponse->json()['data']['user'] ?? [] : [];
            $displayName = $userData['display_name'] ?? 'TikTok User';
            $avatarUrl = $userData['avatar_url'] ?? '';

            // 5. Store credentials in session
            // IMPORTANT: In production, store in database
            Session::put('tiktok_connected', true);
            Session::put('tiktok_access_token', $accessToken); // For API calls
            Session::put('tiktok_open_id', $openId); // User's unique ID
            Session::put('tiktok_user', [
                'open_id' => $openId,
                'display_name' => $displayName,
                'avatar_url' => $avatarUrl,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'expires_in' => $expiresIn,
                'token_created_at' => now(),
            ]);

            // Clear OAuth state
            Session::forget('tiktok_oauth_state');

            Log::info('TikTok Connected Successfully: ' . $displayName);

            // Redirect to dashboard to show user's videos
            return redirect()->route('tiktok.dashboard')
                ->with('success', 'TikTok account connected successfully! Here are your videos.');

        } catch (\Exception $e) {
            Log::error('TikTok OAuth Callback Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect('/')
                ->with('error', 'Failed to connect TikTok account: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect TikTok account
     */
    public function disconnect()
    {
        Session::forget('tiktok_connected');
        Session::forget('tiktok_user');
        Session::forget('tiktok_access_token');
        Session::forget('tiktok_open_id');

        return redirect('/')
            ->with('success', 'TikTok account disconnected. You can still use the system with sample data.');
    }

    /**
     * Dashboard: Show user's TikTok videos using access token
     * 
     * This demonstrates Manage User Access Tokens endpoint
     */
    public function showDashboard()
    {
        // Get token from session
        $accessToken = Session::get('tiktok_access_token');
        $displayName = Session::get('tiktok_user.display_name', 'User');

        if (!$accessToken) {
            // If no token, force login
            return redirect()->route('tiktok.login')
                ->with('error', 'Please connect your TikTok account first.');
        }

        try {
            // Define fields we want to retrieve
            $fields = [
                'id',
                'create_time',
                'cover_image_url',
                'share_url',
                'video_description',
                'duration',
                'height',
                'width',
                'title',
                'embed_html',
                'embed_link',
                'like_count',
                'comment_count',
                'share_count',
                'view_count',
            ];

            // Call TikTok Video List API
            // Documentation: https://developers.tiktok.com/doc/query-video-list-api
            $response = Http::withToken($accessToken)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post('https://open.tiktokapis.com/v2/video/list/', [
                    'max_count' => 20, // Max videos to fetch
                ]);

            if ($response->failed()) {
                Log::error('TikTok Video API Failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                // If token expired, clear session
                if ($response->status() == 401) {
                    Session::forget('tiktok_access_token');
                    return redirect()->route('tiktok.login')
                        ->with('error', 'Your TikTok session has expired. Please login again.');
                }

                return view('tiktok-dashboard', [
                    'display_name' => $displayName,
                    'videos' => [],
                    'error' => 'Failed to fetch videos: ' . $response->body(),
                ]);
            }

            $videoData = $response->json();
            $videos = $videoData['data']['videos'] ?? [];

            Log::info('TikTok Videos Fetched', [
                'count' => count($videos),
                'user' => $displayName
            ]);

            return view('tiktok-dashboard', [
                'display_name' => $displayName,
                'videos' => $videos,
                'error' => null,
            ]);

        } catch (\Exception $e) {
            Log::error('TikTok Dashboard Error: ' . $e->getMessage());
            
            return view('tiktok-dashboard', [
                'display_name' => $displayName,
                'videos' => [],
                'error' => 'An error occurred: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Check if TikTok is connected (JSON API endpoint)
     */
    public function checkConnection()
    {
        $connected = Session::get('tiktok_connected', false);
        $user = Session::get('tiktok_user', null);

        return response()->json([
            'connected' => $connected,
            'user' => $user ? [
                'display_name' => $user['display_name'] ?? 'TikTok User',
                'avatar_url' => $user['avatar_url'] ?? '',
                'open_id' => $user['open_id'] ?? '',
            ] : null,
        ]);
    }
}
