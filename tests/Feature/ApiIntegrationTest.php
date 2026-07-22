<?php

namespace Tests\Feature;

use App\Services\InstagramService;
use App\Services\TikTokService;
use App\Services\TwitterService;
use App\Services\YouTubeService;
use Tests\TestCase;

class ApiIntegrationTest extends TestCase
{
    /**
     * Test YouTube API - Search functionality
     */
    public function test_youtube_api_search()
    {
        $this->markTestSkipped('Enable this test to check YouTube API');

        $youtubeService = app(YouTubeService::class);

        try {
            $results = $youtubeService->search('AI technology', 5);

            $this->assertIsArray($results);
            $this->assertNotEmpty($results, 'YouTube API should return results');
            $this->assertArrayHasKey('content', $results[0] ?? []);
            $this->assertArrayHasKey('author', $results[0] ?? []);

            echo "\n✅ YouTube API Working!\n";
            echo 'Found '.count($results)." videos\n";
            echo 'Sample: '.($results[0]['content'] ?? 'N/A')."\n";

        } catch (\Exception $e) {
            $this->fail('YouTube API Error: '.$e->getMessage());
        }
    }

    /**
     * Test Twitter API - Search functionality
     */
    public function test_twitter_api_search()
    {
        $this->markTestSkipped('Enable this test to check Twitter API');

        $twitterService = app(TwitterService::class);

        try {
            $results = $twitterService->search('AI technology', 5);

            $this->assertIsArray($results);
            $this->assertNotEmpty($results, 'Twitter API should return results');
            $this->assertArrayHasKey('content', $results[0] ?? []);
            $this->assertArrayHasKey('author', $results[0] ?? []);

            echo "\n✅ Twitter API Working!\n";
            echo 'Found '.count($results)." tweets\n";
            echo 'Sample: '.($results[0]['content'] ?? 'N/A')."\n";

        } catch (\Exception $e) {
            $this->fail('Twitter API Error: '.$e->getMessage());
        }
    }

    /**
     * Test TikTok Service - Mock data
     */
    public function test_tiktok_service_returns_mock_data()
    {
        $tiktokService = app(TikTokService::class);

        $results = $tiktokService->search('fashion', 5);

        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $this->assertArrayHasKey('content', $results[0]);
        $this->assertArrayHasKey('sentiment', $results[0]);

        echo "\n✅ TikTok Service Working (Mock Data)!\n";
        echo 'Generated '.count($results)." mock posts\n";
    }

    /**
     * Test Instagram Service - Mock data
     */
    public function test_instagram_service_returns_mock_data()
    {
        $instagramService = app(InstagramService::class);

        $results = $instagramService->search('fashion', 5);

        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $this->assertArrayHasKey('content', $results[0]);
        $this->assertArrayHasKey('sentiment', $results[0]);

        echo "\n✅ Instagram Service Working (Mock Data)!\n";
        echo 'Generated '.count($results)." mock posts\n";
    }
}
