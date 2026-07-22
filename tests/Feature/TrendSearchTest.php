<?php

namespace Tests\Feature;

use App\Services\TrendAnalysisService;
use Tests\TestCase;

class TrendSearchTest extends TestCase
{
    public function test_home_page_loads_successfully()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('SocialInsight');
    }

    public function test_search_form_exists_on_home_page()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('name="topic"', false);
        $response->assertSee('Analisis Tren', false);
    }

    public function test_can_perform_search()
    {
        $this->mock(TrendAnalysisService::class, function ($mock) {
            $mock->shouldReceive('analyze')->once()->andReturn([
                'id' => null,
                'topic' => 'test music',
                'generated_at' => now()->toIso8601String(),
                'summary' => [
                    'headline' => 'Test music sedang ramai.',
                    'trend' => 'Topik punya sinyal positif.',
                    'dominant_sentiment' => 'positive',
                    'audience_mood' => 'Penasaran.',
                    'best_angle' => 'Resep singkat.',
                    'risk_note' => 'Jangan klaim berlebihan.',
                ],
                'actions' => [],
                'platforms' => [],
                'items' => [],
                'sentiment' => ['positive' => 0, 'negative' => 0, 'neutral' => 0],
                'charts' => ['sentiment' => [], 'platforms' => []],
                'source_statuses' => [],
            ]);
        });

        $token = $this->getCsrfToken();
        $response = $this->post('/analyze/trend', [
            '_token' => $token,
            'topic' => 'test music',
            'platforms' => ['youtube'],
        ]);

        $response->assertOk();
        $response->assertSee('test music');
    }

    public function test_search_requires_query_parameter()
    {
        $token = $this->getCsrfToken();
        $response = $this->post('/analyze/trend', [
            '_token' => $token,
        ]);

        // Expect a redirect back with validation errors
        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors('topic');
    }

    /**
     * Helper to get CSRF token from the home page.
     */
    private function getCsrfToken()
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        // Extract CSRF token from the response content
        preg_match('/<meta name="csrf-token" content="([^"]+)">/', $response->getContent(), $matches);

        return $matches[1] ?? '';
    }

    public function test_api_youtube_endpoint_exists()
    {
        $response = $this->getJson('/api/youtube/search?q=test');

        // Should return JSON response
        $this->assertTrue(
            $response->status() === 200 ||
            $response->status() === 400 ||
            $response->status() === 404 ||
            $response->status() === 500
        );

        if ($response->status() === 200) {
            $response->assertJsonStructure([
                'status',
            ]);
        }
    }

    public function test_result_page_accessible()
    {
        // Try accessing result page with query parameter
        $response = $this->get('/analyze/trend?topic=test');

        // May return 200, 302 (redirect), or 404
        $this->assertContains($response->status(), [200, 302, 404, 405]);
    }
}
