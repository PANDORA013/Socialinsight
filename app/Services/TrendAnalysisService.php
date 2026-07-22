<?php

namespace App\Services;

use App\Support\SocialInsight\SocialPlatform;
use Illuminate\Support\Facades\Log;

class TrendAnalysisService
{
    public function __construct(
        private YouTubeService $youtubeService,
        private TwitterService $twitterService,
        private TikTokService $tiktokService,
        private InstagramService $instagramService,
        private DataFilteringService $filteringService,
        private NaiveBayesService $naiveBayesService,
        private KMeansClusteringService $kmeansService,
        private AIInsightsService $aiInsightsService
    ) {}

    public function analyze(string $topic, array $platforms): array
    {
        $platforms = array_values(array_intersect($platforms ?: SocialPlatform::ALL, SocialPlatform::ALL));
        $platforms = $platforms ?: SocialPlatform::ACTIVE;

        [$rawItems, $sourceStatuses] = $this->fetchPlatforms($topic, $platforms);

        $filteredResult = $this->filteringService->processData($rawItems, $topic);
        $items = array_values($filteredResult['data']);
        $filteringStats = $filteredResult['stats'];

        foreach ($items as &$item) {
            $sentiment = $this->naiveBayesService->classify($item['content'] ?? '');
            $item['sentiment'] = $sentiment['sentiment'];
            $item['sentiment_score'] = $sentiment['score'];
            $item['sentiment_confidence'] = $sentiment['confidence'];
        }
        unset($item);

        $sentimentAnalysis = $this->naiveBayesService->analyzeSentimentDistribution($items);
        $clusteringResult = $this->kmeansService->setK(4)->cluster($items);
        $clusteringMetrics = empty($items) ? [] : $this->safeClusteringMetrics($clusteringResult);
        $aiInsights = $this->aiInsightsService->generateInsights(
            $topic,
            $items,
            $sentimentAnalysis,
            $clusteringResult,
            $filteringStats
        );

        $sentimentSummary = $sentimentAnalysis['distribution'] ?? ['positive' => 0, 'negative' => 0, 'neutral' => 0];
        $platformSummary = $this->platformSummary($items, $platforms);

        return [
            'id' => null,
            'topic' => $topic,
            'generated_at' => now()->toIso8601String(),
            'summary' => $this->summary($topic, $sentimentAnalysis, $aiInsights),
            'actions' => $this->actions($aiInsights),
            'platforms' => $this->platformBuckets($items, $platforms),
            'items' => $items,
            'sentiment' => $sentimentSummary,
            'charts' => [
                'sentiment' => $sentimentSummary,
                'platforms' => $platformSummary,
            ],
            'source_statuses' => $sourceStatuses,
            'filtering' => $filteringStats,
            'clustering' => $clusteringMetrics,
        ];
    }

    private function fetchPlatforms(string $topic, array $platforms): array
    {
        $services = [
            'youtube' => $this->youtubeService,
            'twitter' => $this->twitterService,
            'tiktok' => $this->tiktokService,
            'instagram' => $this->instagramService,
        ];
        $items = [];
        $statuses = [];

        foreach ($platforms as $platform) {
            if (in_array($platform, SocialPlatform::MAINTENANCE, true)) {
                $statuses[$platform] = [
                    'status' => 'maintenance',
                    'message' => 'Coming Soon. Platform ini sedang maintenance dan belum dipakai untuk analisis.',
                ];

                continue;
            }

            try {
                $result = $services[$platform]->search($topic, 10);
                $status = empty($result) ? 'demo' : 'real';
                $message = $status === 'real' ? 'Data real tersedia.' : 'Mode demo karena data real tidak tersedia.';
                $platformItems = empty($result) ? SocialPlatform::demoItems($platform, $topic) : $result;
            } catch (\Throwable $exception) {
                Log::warning('Social platform fetch failed', [
                    'platform' => $platform,
                    'message' => $exception->getMessage(),
                ]);
                $status = 'failed';
                $message = 'Platform gagal diambil. Hasil lain tetap ditampilkan.';
                $platformItems = [];
            }

            $statuses[$platform] = ['status' => $status, 'message' => $message];

            foreach ($platformItems as $item) {
                $items[] = SocialPlatform::normalizeItem($item, $platform, $status);
            }
        }

        return [$items, $statuses];
    }

    private function summary(string $topic, array $sentimentAnalysis, array $aiInsights): array
    {
        $overview = $aiInsights['overview'] ?? [];
        $dominant = strtolower((string) ($overview['dominant_sentiment'] ?? $sentimentAnalysis['overall_sentiment'] ?? 'neutral'));

        return [
            'headline' => 'Topik '.$topic.' punya sinyal '.($dominant ?: 'netral').' untuk dieksplor.',
            'trend' => $overview['summary'] ?? 'Percakapan menunjukkan peluang konten yang bisa diuji dengan format singkat dan jelas.',
            'dominant_sentiment' => $dominant ?: 'neutral',
            'audience_mood' => $this->audienceMood($dominant),
            'best_angle' => 'Buat konten praktis yang menjawab kebutuhan audiens dengan cepat.',
            'risk_note' => $dominant === 'negative'
                ? 'Perhatikan kritik yang berulang sebelum menjadikan topik ini kampanye utama.'
                : 'Tetap gunakan klaim yang realistis agar engagement positif tidak berubah menjadi kritik.',
        ];
    }

    private function actions(array $aiInsights): array
    {
        $recommendations = $aiInsights['recommendations'] ?? [];

        if ($recommendations === []) {
            return [[
                'title' => 'Uji satu konten pendek hari ini',
                'description' => 'Gunakan hook jelas, bukti visual, dan ajakan komentar untuk membaca respons audiens.',
                'steps' => ['Tulis hook 1 kalimat', 'Tampilkan contoh nyata', 'Ajak audiens memilih angle berikutnya'],
            ]];
        }

        return array_map(function (array $item): array {
            return [
                'title' => $item['title'] ?? 'Coba angle konten baru',
                'description' => $item['description'] ?? 'Gunakan insight ini sebagai eksperimen konten berikutnya.',
                'steps' => array_values($item['action_items'] ?? []),
            ];
        }, array_slice($recommendations, 0, 5));
    }

    private function audienceMood(string $dominant): string
    {
        return match ($dominant) {
            'positive' => 'Antusias dan siap mencoba.',
            'negative' => 'Kritis, perlu bukti dan penjelasan.',
            default => 'Masih beragam, cocok untuk eksperimen kecil.',
        };
    }

    private function platformBuckets(array $items, array $platforms): array
    {
        $buckets = array_fill_keys($platforms, []);

        foreach ($items as $item) {
            $platform = $item['platform'] ?? 'unknown';
            $buckets[$platform] ??= [];
            $buckets[$platform][] = $item;
        }

        return $buckets;
    }

    private function platformSummary(array $items, array $platforms): array
    {
        $summary = array_fill_keys($platforms, 0);

        foreach ($items as $item) {
            $platform = $item['platform'] ?? 'unknown';
            $summary[$platform] ??= 0;
            $summary[$platform]++;
        }

        return $summary;
    }

    private function safeClusteringMetrics(array $clusteringResult): array
    {
        if (empty($clusteringResult['clusters']) || empty($clusteringResult['total_items'])) {
            return [];
        }

        return $this->kmeansService->getMetrics($clusteringResult);
    }
}
