<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductRebuildFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads_as_creator_tool(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('SocialInsight')
            ->assertSee('Analisis')
            ->assertSee('Asisten tren untuk creator dan UMKM')
            ->assertSee('YouTube')
            ->assertSee('TikTok')
            ->assertSee('Instagram')
            ->assertSee('Twitter')
            ->assertSee('Coming Soon')
            ->assertSee('Maintenance');
    }

    public function test_analyze_requires_topic(): void
    {
        $this->from('/')
            ->post(route('analyze.trend'), [
                'topic' => '',
                'platforms' => ['youtube'],
            ])
            ->assertRedirect('/')
            ->assertSessionHasErrors('topic');
    }

    public function test_analyze_rejects_unknown_platform(): void
    {
        $this->from('/')
            ->post(route('analyze.trend'), [
                'topic' => 'kopi susu',
                'platforms' => ['youtube', 'unknown'],
            ])
            ->assertRedirect('/')
            ->assertSessionHasErrors('platforms.1');
    }

    public function test_non_youtube_platforms_are_maintenance_when_submitted_manually(): void
    {
        $this->post(route('analyze.trend'), [
            'topic' => 'kopi susu',
            'platforms' => ['youtube', 'twitter', 'tiktok', 'instagram'],
        ])
            ->assertOk()
            ->assertSee('YouTube')
            ->assertSee('Twitter/X')
            ->assertSee('MAINTENANCE')
            ->assertSee('Coming Soon');
    }

    public function test_analysis_route_uses_named_throttle(): void
    {
        $route = app('router')->getRoutes()->getByName('analyze.trend');

        $this->assertContains('throttle:trend-analysis', $route->gatherMiddleware());
    }

    public function test_export_route_uses_named_throttle(): void
    {
        $route = app('router')->getRoutes()->getByName('export.trend');

        $this->assertContains('throttle:trend-export', $route->gatherMiddleware());
    }

    public function test_export_requires_existing_result_id(): void
    {
        $this->get(route('export.trend'))
            ->assertRedirect('/')
            ->assertSessionHas('error');
    }

    public function test_export_returns_bounded_safe_csv(): void
    {
        $store = app(\App\Services\TrendResultStore::class);

        $id = $store->put([
            'topic' => 'kopi susu',
            'items' => [[
                'platform' => 'youtube',
                'author' => '=bad',
                'content' => '+formula',
                'sentiment' => 'positive',
                'sentiment_score' => 0.91,
                'engagement' => ['likes' => 5, 'comments' => 2, 'views' => 100, 'shares' => 0],
                'url' => 'https://example.test/video',
            ]],
        ]);

        $response = $this->get(route('export.trend', ['id' => $id]))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();

        $this->assertStringContainsString("'=bad", $csv);
        $this->assertStringContainsString("'+formula", $csv);
    }

    public function test_result_view_renders_insight_first_contract(): void
    {
        $result = [
            'id' => 'test-result',
            'topic' => 'kopi susu',
            'generated_at' => now()->toIso8601String(),
            'summary' => [
                'headline' => 'Kopi susu sedang kuat untuk konten praktis.',
                'trend' => 'Percakapan fokus pada harga, rasa, dan ide menu.',
                'dominant_sentiment' => 'positive',
                'audience_mood' => 'Penasaran dan siap mencoba.',
                'best_angle' => 'Menu hemat untuk creator kuliner.',
                'risk_note' => 'Hindari klaim rasa yang terlalu berlebihan.',
            ],
            'actions' => [[
                'title' => 'Buat video resep 30 detik',
                'description' => 'Tunjukkan bahan, harga, dan hasil akhir.',
                'steps' => ['Hook visual', 'Harga bahan', 'CTA komentar'],
            ]],
            'charts' => [
                'sentiment' => ['positive' => 1, 'negative' => 0, 'neutral' => 0],
                'platforms' => ['youtube' => 1],
            ],
            'source_statuses' => [
                'youtube' => ['status' => 'demo', 'message' => 'Demo data'],
            ],
            'items' => [[
                'platform' => 'youtube',
                'author' => 'creator',
                'content' => 'Konten kopi susu',
                'url' => 'https://example.test',
                'sentiment' => 'positive',
                'engagement' => ['likes' => 10, 'comments' => 2, 'views' => 100, 'shares' => 0],
            ]],
        ];

        $this->view('result', ['result' => $result])
            ->assertSee('Jawaban cepat')
            ->assertSee('Rekomendasi aksi')
            ->assertSee('Bukti dari platform')
            ->assertSee('Kopi susu sedang kuat');
    }

    public function test_dashboard_is_status_overview_not_raw_post_history(): void
    {
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Status platform')
            ->assertSee('Cara kerja analisis')
            ->assertDontSee('Recent Posts');
    }
}
