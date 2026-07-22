<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YouTubeService
{
    protected $apiKey;

    protected $baseUrl = 'https://www.googleapis.com/youtube/v3';

    public function __construct()
    {
        $this->apiKey = config('services.youtube.api_key');
    }

    /**
     * Extract video ID from YouTube URL
     */
    public function extractVideoId($url)
    {
        $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/';
        preg_match($pattern, $url, $matches);

        if (! isset($matches[1])) {
            throw new \Exception('Invalid YouTube URL');
        }

        return $matches[1];
    }

    /**
     * Get comments from a YouTube video
     */
    public function getComments($videoId, $maxResults = 100)
    {
        $response = Http::timeout(10)->retry(2, 250)->get("{$this->baseUrl}/commentThreads", [
            'key' => $this->apiKey,
            'videoId' => $videoId,
            'part' => 'snippet',
            'maxResults' => $maxResults,
            'textFormat' => 'plainText',
        ]);

        if (! $response->successful()) {
            throw new \Exception('Failed to fetch YouTube comments: '.$response->body());
        }

        $data = $response->json();
        $comments = [];

        foreach ($data['items'] ?? [] as $item) {
            $snippet = $item['snippet']['topLevelComment']['snippet'];
            $comments[] = [
                'id' => $item['id'],
                'author' => $snippet['authorDisplayName'],
                'text' => $snippet['textDisplay'],
                'likes' => $snippet['likeCount'],
                'published_at' => $snippet['publishedAt'],
            ];
        }

        return $comments;
    }

    /**
     * Search for videos by topic/keyword
     */
    public function search($topic, $maxResults = 10)
    {
        try {
            $response = Http::timeout(10)->retry(2, 250)->get("{$this->baseUrl}/search", [
                'key' => $this->apiKey,
                'q' => $topic,
                'part' => 'snippet',
                'maxResults' => $maxResults,
                'type' => 'video',
                'order' => 'relevance',
            ]);

            if (! $response->successful()) {
                return [];
            }

            $data = $response->json();
            $results = [];

            foreach ($data['items'] ?? [] as $item) {
                $videoId = $item['id']['videoId'] ?? null;
                if (! $videoId) {
                    continue;
                }

                $statsResponse = Http::timeout(10)->retry(2, 250)->get("{$this->baseUrl}/videos", [
                    'key' => $this->apiKey,
                    'id' => $videoId,
                    'part' => 'statistics,snippet',
                ]);

                if ($statsResponse->successful()) {
                    $videoData = $statsResponse->json()['items'][0] ?? null;
                    if ($videoData) {
                        $results[] = [
                            'platform' => 'youtube',  // ← Added platform identifier
                            'content' => $videoData['snippet']['title'] ?? 'No title',
                            'author' => $videoData['snippet']['channelTitle'] ?? 'Unknown',
                            'likes' => (int) ($videoData['statistics']['likeCount'] ?? 0),
                            'comments' => (int) ($videoData['statistics']['commentCount'] ?? 0),
                            'views' => (int) ($videoData['statistics']['viewCount'] ?? 0),
                            'external_id' => $videoId,
                            'sentiment' => 'neutral', // Will be analyzed later
                            'score' => 0.5,
                            'raw' => $videoData,
                        ];
                    }
                }
            }

            return $results;
        } catch (\Exception $e) {
            Log::error('YouTube search error: '.$e->getMessage());

            return [];
        }
    }
}
