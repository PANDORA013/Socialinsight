<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\YouTubeService;
use App\Services\TwitterService;
use App\Services\TikTokService;
use App\Services\InstagramService;
use App\Services\DataFilteringService;
use App\Services\NaiveBayesService;
use App\Services\KMeansClusteringService;
use App\Services\AIInsightsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
    public function analyzeTrend(Request $request)
    {
        $request->validate([
            'topic' => 'required|string|max:255',
        ]);

        $topic = $request->input('topic');

        // === STEP 1: Fetch raw data from all platforms ===
        
        // YouTube: Always use REAL API (if key available)
        $youtubePosts = $this->youtubeService->search($topic, 10);
        
        // Twitter: Use API if available
        $twitterPosts = $this->twitterService->search($topic, 10);
        
        // TikTok: Check if user logged in via OAuth
        $tiktokConnected = session('tiktok_connected', false);
        $tiktokPosts = $this->tiktokService->search($topic, 10);
        
        // Instagram: Mock data for now
        $instagramPosts = $this->instagramService->search($topic, 10);

        // Combine all posts
        $allRawPosts = array_merge($youtubePosts, $twitterPosts, $tiktokPosts, $instagramPosts);
        
        // === STEP 2: Apply 5-stage filtering ===
        $filteredResult = $this->filteringService->processData($allRawPosts, $topic);
        $filteredPosts = $filteredResult['data'];
        $filteringStats = $filteredResult['stats'];

        // === STEP 3: Apply Naive Bayes sentiment analysis ===
        $sentimentAnalysis = $this->naiveBayesService->analyzeSentimentDistribution($filteredPosts);
        
        // Update posts with NB sentiment
        foreach ($filteredPosts as &$post) {
            $result = $this->naiveBayesService->classify($post['content']);
            $post['sentiment'] = $result['sentiment'];
            $post['sentiment_score'] = $result['score'];
            $post['sentiment_confidence'] = $result['confidence'];
        }

        // === STEP 4: Apply K-Means clustering ===
        $clusteringResult = $this->kmeansService->setK(4)->cluster($filteredPosts);
        
        // Ensure all posts have platform field and separate by platform
        $allPosts = [
            'youtube' => [],
            'twitter' => [],
            'tiktok' => [],
            'instagram' => [],
        ];
        
        foreach ($filteredPosts as $post) {
            $platform = strtolower($post['platform'] ?? 'unknown');
            if (isset($allPosts[$platform])) {
                $allPosts[$platform][] = $post;
            }
        }

        // Save to database
        foreach ($filteredPosts as $post) {
            $platform = $this->detectPlatform($post);
            
            Post::updateOrCreate(
                [
                    'platform' => $platform,
                    'external_id' => $post['external_id'] ?? uniqid(),
                ],
                [
                    'content' => $post['content'],
                    'author' => $post['author'],
                    'likes' => $post['likes'] ?? 0,
                    'comments' => $post['comments'] ?? 0,
                    'views' => $post['views'] ?? 0,
                    'sentiment' => $post['sentiment'],
                    'sentiment_score' => $post['sentiment_score'],
                    'raw' => json_encode($post['raw'] ?? []),
                ]
            );
        }

        // Prepare data for view
        $platforms = [
            [
                'name' => 'youtube',
                'icon' => '/img/youtube.svg',
                'posts' => $allPosts['youtube'],
                'color' => 'red'
            ],
            [
                'name' => 'twitter',
                'icon' => '/img/twitter.svg',
                'posts' => $allPosts['twitter'],
                'color' => 'blue'
            ],
            [
                'name' => 'tiktok',
                'icon' => '/img/tiktok.svg',
                'posts' => $allPosts['tiktok'],
                'color' => 'purple'
            ],
            [
                'name' => 'instagram',
                'icon' => '/img/instagram.svg',
                'posts' => $allPosts['instagram'],
                'color' => 'pink'
            ],
        ];

        // Calculate summaries
        $sentimentSummary = $sentimentAnalysis['distribution'];
        $platformSummary = $this->calculatePlatformSummary($allPosts);
        $totalPosts = count($filteredPosts);

        // === STEP 5: Generate comprehensive AI insights ===
        $aiInsights = $this->aiInsightsService->generateInsights(
            $topic,
            $filteredPosts,
            $sentimentAnalysis,
            $clusteringResult,
            $filteringStats
        );
        
        // Get ML metrics
        $filteringMetrics = $this->filteringService->getMetrics();
        $clusteringMetrics = $this->kmeansService->getMetrics($clusteringResult);

        return view('result', compact(
            'topic',
            'platforms',
            'sentimentSummary',
            'platformSummary',
            'totalPosts',
            'aiInsights',
            'filteringStats',
            'filteringMetrics',
            'sentimentAnalysis',
            'clusteringResult',
            'clusteringMetrics'
        ));
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
    public function exportTrend(Request $request)
    {
        $topic = $request->query('topic', 'trend');
        
        $posts = Post::orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        $filename = "socialinsight-trend-{$topic}-" . date('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($posts) {
            $file = fopen('php://output', 'w');
            
            // CSV header
            fputcsv($file, [
                'Platform',
                'Author',
                'Content',
                'Sentiment',
                'Score',
                'Likes',
                'Comments',
                'Views',
                'Created At'
            ]);

            // CSV rows
            foreach ($posts as $post) {
                fputcsv($file, [
                    $post->platform,
                    $post->author,
                    $post->content,
                    $post->sentiment,
                    $post->sentiment_score,
                    $post->likes,
                    $post->comments,
                    $post->views,
                    $post->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
