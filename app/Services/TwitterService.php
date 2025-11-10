<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwitterService
{
    protected $bearerToken;
    protected $baseUrl = 'https://api.twitter.com/2';

    public function __construct()
    {
        $this->bearerToken = config('services.twitter.bearer_token');
    }

    /**
     * Extract tweet ID from Twitter/X URL
     */
    public function extractTweetId($url)
    {
        // Match patterns like:
        // https://twitter.com/username/status/1234567890
        // https://x.com/username/status/1234567890
        $pattern = '/(?:twitter\.com|x\.com)\/[^\/]+\/status\/(\d+)/';
        preg_match($pattern, $url, $matches);
        
        if (!isset($matches[1])) {
            throw new \Exception('Invalid Twitter/X URL');
        }
        
        return $matches[1];
    }

    /**
     * Get replies/comments for a tweet
     * Note: Twitter API v2 requires elevated access for conversation search
     */
    public function getReplies($tweetId, $maxResults = 100)
    {
        if (empty($this->bearerToken)) {
            throw new \Exception('Twitter Bearer Token not configured');
        }

        // Get the original tweet first (SSL verification disabled for Windows/XAMPP)
        $tweetResponse = Http::withoutVerifying()->withHeaders([
            'Authorization' => 'Bearer ' . $this->bearerToken,
        ])->get("{$this->baseUrl}/tweets/{$tweetId}", [
            'tweet.fields' => 'author_id,created_at,public_metrics',
            'expansions' => 'author_id',
            'user.fields' => 'username,name',
        ]);

        if (!$tweetResponse->successful()) {
            throw new \Exception('Failed to fetch tweet: ' . $tweetResponse->body());
        }

        $tweetData = $tweetResponse->json();
        $authorUsername = $tweetData['includes']['users'][0]['username'] ?? 'unknown';

        // Search for replies (requires elevated access, SSL verification disabled)
        $response = Http::withoutVerifying()->withHeaders([
            'Authorization' => 'Bearer ' . $this->bearerToken,
        ])->get("{$this->baseUrl}/tweets/search/recent", [
            'query' => "conversation_id:{$tweetId}",
            'max_results' => min($maxResults, 100),
            'tweet.fields' => 'author_id,created_at,public_metrics',
            'expansions' => 'author_id',
            'user.fields' => 'username,name',
        ]);

        if (!$response->successful()) {
            // If we don't have elevated access, return the original tweet only
            if ($response->status() === 403) {
                return [[
                    'id' => $tweetId,
                    'author' => $authorUsername,
                    'text' => $tweetData['data']['text'],
                    'likes' => $tweetData['data']['public_metrics']['like_count'] ?? 0,
                    'created_at' => $tweetData['data']['created_at'] ?? now(),
                ]];
            }
            throw new \Exception('Failed to fetch replies: ' . $response->body());
        }

        $data = $response->json();
        $replies = [];

        // Map users for quick lookup
        $users = [];
        foreach ($data['includes']['users'] ?? [] as $user) {
            $users[$user['id']] = $user;
        }

        foreach ($data['data'] ?? [] as $tweet) {
            $author = $users[$tweet['author_id']] ?? null;
            $replies[] = [
                'id' => $tweet['id'],
                'author' => $author['name'] ?? 'Unknown',
                'username' => $author['username'] ?? 'unknown',
                'text' => $tweet['text'],
                'likes' => $tweet['public_metrics']['like_count'] ?? 0,
                'retweets' => $tweet['public_metrics']['retweet_count'] ?? 0,
                'created_at' => $tweet['created_at'],
            ];
        }

        return $replies;
    }

    /**
     * Get a single tweet
     */
    public function getTweet($tweetId)
    {
        if (empty($this->bearerToken)) {
            throw new \Exception('Twitter Bearer Token not configured');
        }

        // SSL verification disabled for Windows/XAMPP
        $response = Http::withoutVerifying()->withHeaders([
            'Authorization' => 'Bearer ' . $this->bearerToken,
        ])->get("{$this->baseUrl}/tweets/{$tweetId}", [
            'tweet.fields' => 'author_id,created_at,public_metrics',
            'expansions' => 'author_id',
            'user.fields' => 'username,name',
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch tweet: ' . $response->body());
        }

        $data = $response->json();
        $tweet = $data['data'];
        $author = $data['includes']['users'][0] ?? null;

        return [
            'id' => $tweet['id'],
            'author' => $author['name'] ?? 'Unknown',
            'username' => $author['username'] ?? 'unknown',
            'text' => $tweet['text'],
            'likes' => $tweet['public_metrics']['like_count'] ?? 0,
            'retweets' => $tweet['public_metrics']['retweet_count'] ?? 0,
            'replies' => $tweet['public_metrics']['reply_count'] ?? 0,
            'created_at' => $tweet['created_at'],
        ];
    }

    /**
     * Search tweets by topic/keyword
     */
    public function search($topic, $maxResults = 10)
    {
        try {
            // Check if bearer token is configured
            if (empty($this->bearerToken)) {
                Log::warning('Twitter Bearer Token not configured');
                return [];
            }

            // SSL verification disabled for Windows/XAMPP
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => "Bearer {$this->bearerToken}",
            ])->get("{$this->baseUrl}/tweets/search/recent", [
                'query' => $topic,
                'max_results' => min($maxResults, 100),
                'tweet.fields' => 'public_metrics,created_at,author_id',
                'expansions' => 'author_id',
                'user.fields' => 'username,name',
            ]);

            if (!$response->successful()) {
                Log::error('Twitter search failed: ' . $response->body());
                return [];
            }

            $data = $response->json();
            $results = [];

            // Create users lookup array
            $users = [];
            foreach ($data['includes']['users'] ?? [] as $user) {
                $users[$user['id']] = $user;
            }

            foreach ($data['data'] ?? [] as $tweet) {
                $authorId = $tweet['author_id'] ?? null;
                $author = $users[$authorId] ?? ['username' => 'unknown'];

                $results[] = [
                    'platform' => 'twitter',  // ← Added platform identifier
                    'content' => $tweet['text'] ?? '',
                    'author' => '@' . ($author['username'] ?? 'unknown'),
                    'likes' => (int)($tweet['public_metrics']['like_count'] ?? 0),
                    'comments' => (int)($tweet['public_metrics']['reply_count'] ?? 0),
                    'views' => (int)($tweet['public_metrics']['impression_count'] ?? 0),
                    'external_id' => $tweet['id'] ?? uniqid('tweet_'),
                    'sentiment' => 'neutral',
                    'score' => 0.5,
                    'raw' => $tweet,
                ];
            }

            return $results;
        } catch (\Exception $e) {
            Log::error('Twitter search error: ' . $e->getMessage());
            return [];
        }
    }
}
