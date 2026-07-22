<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyzeTrendRequest;
use App\Models\Post;
use App\Services\AIInsightsService;
use App\Services\DataFilteringService;
use App\Services\InstagramService;
use App\Services\KMeansClusteringService;
use App\Services\NaiveBayesService;
use App\Services\TikTokService;
use App\Services\TrendAnalysisService;
use App\Services\TrendResultStore;
use App\Services\TwitterService;
use App\Services\YouTubeService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TrendController extends Controller
{
    protected $youtubeService;

    protected $twitterService;

    protected $tiktokService;

    protected $instagramService;

    protected $filteringService;

    protected $naiveBayesService;

    protected $kmeansService;

    protected $aiInsightsService;

    public function __construct(
        YouTubeService $youtube,
        TwitterService $twitter,
        TikTokService $tiktok,
        InstagramService $instagram,
        DataFilteringService $filtering,
        NaiveBayesService $naiveBayes,
        KMeansClusteringService $kmeans,
        AIInsightsService $aiInsights
    ) {
        $this->youtubeService = $youtube;
        $this->twitterService = $twitter;
        $this->tiktokService = $tiktok;
        $this->instagramService = $instagram;
        $this->filteringService = $filtering;
        $this->naiveBayesService = $naiveBayes;
        $this->kmeansService = $kmeans;
        $this->aiInsightsService = $aiInsights;
    }

    /**
     * Show home page with search bar
     */
    public function home()
    {
        return view('home');
    }

    /**
     * Analyze trend across all platforms with 5-stage filtering and ML
     */
    public function analyzeTrend(
        AnalyzeTrendRequest $request,
        TrendAnalysisService $analysis,
        TrendResultStore $store
    ) {
        $data = $request->validated();
        $result = $analysis->analyze($data['topic'], $data['platforms']);
        $result['id'] = $store->put($result);

        return view('result', ['result' => $result]);
    }

    /**
     * Detect platform from post data
     */
    protected function detectPlatform($post)
    {
        // Use platform field directly if available
        if (isset($post['platform'])) {
            return $post['platform'];
        }

        // Fallback to detection logic
        $content = json_encode($post);

        if (stripos($content, 'video') !== false && stripos($content, 'tiktok') === false) {
            return 'youtube';
        }
        if (stripos($post['author'] ?? '', '@') === 0 && stripos($content, 'tiktok') === false) {
            return 'twitter';
        }
        if (stripos($content, 'tiktok') !== false) {
            return 'tiktok';
        }

        return 'instagram';
    }

    /**
     * Calculate platform summary
     */
    private function calculatePlatformSummary($allPosts)
    {
        return [
            'YouTube' => count($allPosts['youtube']),
            'Twitter' => count($allPosts['twitter']),
            'TikTok' => count($allPosts['tiktok']),
            'Instagram' => count($allPosts['instagram']),
        ];
    }

    /**
     * Export trend analysis as CSV
     */
    public function exportTrend(Request $request, TrendResultStore $store)
    {
        $id = (string) $request->query('id', '');
        $result = $id !== '' ? $store->get($id) : null;

        if (! $result) {
            return redirect()->route('home')->with('error', 'Hasil analisis sudah kedaluwarsa. Jalankan analisis lagi untuk export.');
        }

        $topic = Str::slug((string) ($result['topic'] ?? 'trend')) ?: 'trend';
        $filename = 'socialinsight-'.$topic.'-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($result) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Platform',
                'Author',
                'Content',
                'Sentiment',
                'Score',
                'Likes',
                'Comments',
                'Views',
                'Shares',
                'URL',
            ]);

            foreach (array_slice($result['items'] ?? [], 0, 100) as $item) {
                fputcsv($file, [
                    $item['platform'] ?? '',
                    $this->safeCsvCell($item['author'] ?? ''),
                    $this->safeCsvCell($item['content'] ?? ''),
                    $item['sentiment'] ?? '',
                    $item['sentiment_score'] ?? '',
                    $item['engagement']['likes'] ?? 0,
                    $item['engagement']['comments'] ?? 0,
                    $item['engagement']['views'] ?? 0,
                    $item['engagement']['shares'] ?? 0,
                    $item['url'] ?? '',
                ]);
            }

            fclose($file);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function safeCsvCell(mixed $value): string
    {
        $text = (string) $value;

        return preg_match('/^[=+\-@]/', $text) ? "'".$text : $text;
    }
}
