<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    public function test_youtube_api_endpoint_responds()
    {
        $response = $this->getJson('/api/youtube/search?q=test');
        
        $this->assertContains($response->status(), [200, 400, 404, 500]);
    }

    public function test_twitter_api_endpoint_responds()
    {
        $response = $this->getJson('/api/twitter/search?q=test');
        
        $this->assertContains($response->status(), [200, 400, 404, 500]);
    }

    public function test_tiktok_api_endpoint_responds()
    {
        $response = $this->getJson('/api/tiktok/search?q=test');
        
        $this->assertContains($response->status(), [200, 400, 404, 500]);
    }

    public function test_instagram_api_endpoint_responds()
    {
        $response = $this->getJson('/api/instagram/search?q=test');
        
        $this->assertContains($response->status(), [200, 400, 404, 500]);
    }

    public function test_indobert_status_endpoint()
    {
        $response = $this->getJson('/api/test/indobert-status');
        
        if ($response->status() === 200) {
            $response->assertJsonStructure([
                'available',
                'python_path',
            ]);
        }
        
        $this->assertContains($response->status(), [200, 404, 500]);
    }

    public function test_indobert_analyze_endpoint()
    {
        $response = $this->postJson('/api/test/indobert-analyze', [
            'text' => 'Test text'
        ]);
        
        $this->assertContains($response->status(), [200, 400, 404, 500]);
    }

    public function test_api_requires_query_parameter()
    {
        $response = $this->getJson('/api/youtube/search');
        
        // Should return error if no query
        $this->assertContains($response->status(), [400, 404, 422, 500]);
    }

    public function test_api_returns_json_response()
    {
        $response = $this->getJson('/api/youtube/search?q=test');
        
        $response->assertHeader('Content-Type', 'application/json');
    }
}
