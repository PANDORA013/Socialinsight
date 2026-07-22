<?php

namespace Tests\Unit;

use App\Services\AIInsightsService;
use App\Services\DataFilteringService;
use App\Services\IndoBERTService;
use App\Services\InstagramService;
use App\Services\KMeansClusteringService;
use App\Services\NaiveBayesService;
use App\Services\TikTokService;
use App\Services\TrendAnalysisService;
use App\Services\TwitterService;
use App\Services\YouTubeService;
use Tests\TestCase;

class TrendAnalysisServiceTest extends TestCase
{
    public function test_analyze_returns_insight_first_result_contract(): void
    {
        $service = new TrendAnalysisService(
            $this->fakePlatformService(YouTubeService::class, 'youtube'),
            $this->fakePlatformService(TwitterService::class, 'twitter'),
            $this->fakePlatformService(TikTokService::class, 'tiktok'),
            $this->fakePlatformService(InstagramService::class, 'instagram'),
            new DataFilteringService,
            new NaiveBayesService,
            new KMeansClusteringService,
            $this->fakeInsightsService()
        );

        $result = $service->analyze('kopi susu', ['youtube', 'tiktok']);

        $this->assertSame('kopi susu', $result['topic']);
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('actions', $result);
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('source_statuses', $result);
        $this->assertContains($result['source_statuses']['youtube']['status'], ['real', 'demo', 'failed']);
    }

    public function test_platform_services_do_not_disable_tls_verification(): void
    {
        $files = [
            app_path('Services/YouTubeService.php'),
            app_path('Services/TwitterService.php'),
            app_path('Services/TikTokService.php'),
            app_path('Services/InstagramService.php'),
        ];

        foreach ($files as $file) {
            $this->assertStringNotContainsString('withoutVerifying()', file_get_contents($file), $file);
        }
    }

    public function test_main_application_code_does_not_persist_raw_social_posts(): void
    {
        $paths = [
            app_path('Http'),
            app_path('Services'),
        ];

        foreach ($paths as $path) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

            foreach ($files as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }

                $contents = file_get_contents($file->getPathname());

                $this->assertStringNotContainsString('Post::create', $contents, $file->getPathname());
                $this->assertStringNotContainsString('Post::updateOrCreate', $contents, $file->getPathname());
            }
        }
    }

    private function fakePlatformService(string $class, string $platform): object
    {
        $mock = $this->createMock($class);
        $mock->method('search')->willReturn([[
            'platform' => $platform,
            'external_id' => $platform.'-1',
            'author' => 'creator_'.$platform,
            'content' => 'Konten bagus tentang kopi susu yang bikin audiens suka!',
            'likes' => 100,
            'comments' => 12,
            'views' => 1000,
            'link' => 'https://example.test/'.$platform,
        ]]);

        return $mock;
    }

    private function fakeInsightsService(): AIInsightsService
    {
        return new class(new NaiveBayesService, new KMeansClusteringService, app(IndoBERTService::class)) extends AIInsightsService
        {
            public function generateInsights($query, $filteredData, $sentimentAnalysis, $clusteringResult, $filteringStats): array
            {
                return [
                    'overview' => [
                        'summary' => 'Topik '.$query.' sedang punya sinyal positif untuk dicoba.',
                        'dominant_sentiment' => 'positive',
                    ],
                    'recommendations' => [[
                        'title' => 'Buat konten edukasi singkat',
                        'description' => 'Gunakan angle praktis yang mudah dicoba.',
                        'action_items' => ['Buat video 30 detik', 'Gunakan hook di 3 detik pertama'],
                    ]],
                ];
            }
        };
    }
}
