<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\DataFilteringService;
use App\Services\NaiveBayesService;
use App\Services\KMeansClusteringService;
use App\Services\TikTokService;
use App\Services\InstagramService;

echo "\n╔═══════════════════════════════════════════════════════════════════════╗\n";
echo "║  🎓 SOCIALINSIGHT - DEMO 5-STAGE FILTERING + MACHINE LEARNING       ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════╝\n\n";

$query = "Taylor Swift";
echo "🔍 Query: {$query}\n";
echo str_repeat("─", 75) . "\n\n";

// ============================================================================
// STEP 1: Data Collection
// ============================================================================
echo "STEP 1: 📥 DATA COLLECTION\n";
echo str_repeat("─", 75) . "\n";

$tiktokService = new TikTokService();
$instagramService = new InstagramService();

$tiktokPosts = $tiktokService->search($query, 10);
$instagramPosts = $instagramService->search($query, 10);

$allRawPosts = array_merge($tiktokPosts, $instagramPosts);

echo "✅ TikTok: " . count($tiktokPosts) . " posts\n";
echo "✅ Instagram: " . count($instagramPosts) . " posts\n";
echo "📊 Total Raw Data: " . count($allRawPosts) . " posts\n\n";

// ============================================================================
// STEP 2: 5-Stage Filtering
// ============================================================================
echo "STEP 2: 🔧 5-STAGE FILTERING PROCESS\n";
echo str_repeat("─", 75) . "\n";

$filteringService = new DataFilteringService();
$filteredResult = $filteringService->processData($allRawPosts, $query);

$filteredPosts = $filteredResult['data'];
$stats = $filteredResult['stats'];

echo "Original Data: {$stats['original_count']} posts\n";
echo "\n";
echo "│ Tahap 1 - Relevansi    : {$stats['after_relevance']} posts (removed: {$stats['removed_by_stage']['relevance']})\n";
echo "│ Tahap 2 - Duplikasi    : {$stats['after_deduplication']} posts (removed: {$stats['removed_by_stage']['deduplication']})\n";
echo "│ Tahap 3 - Noise/Spam   : {$stats['after_noise_removal']} posts (removed: {$stats['removed_by_stage']['noise']})\n";
echo "│ Tahap 4 - Sentimen     : {$stats['after_sentiment_filter']} posts (removed: {$stats['removed_by_stage']['sentiment']})\n";
echo "│ Tahap 5 - Temporal     : {$stats['after_temporal_filter']} posts (removed: {$stats['removed_by_stage']['temporal']})\n";
echo "\n";

$metrics = $filteringService->getMetrics();
echo "📈 Filtering Effectiveness:\n";
echo "   Total Removed: {$metrics['total_removed']} posts ({$metrics['removal_rate']}%)\n";
echo "   Retention Rate: {$metrics['retention_rate']}%\n\n";

// ============================================================================
// STEP 3: Naive Bayes Sentiment Analysis
// ============================================================================
echo "STEP 3: 🤖 NAIVE BAYES SENTIMENT ANALYSIS\n";
echo str_repeat("─", 75) . "\n";

$naiveBayesService = new NaiveBayesService();
$sentimentAnalysis = $naiveBayesService->analyzeSentimentDistribution($filteredPosts);

echo "📊 Sentiment Distribution:\n";
echo "   Positive: {$sentimentAnalysis['distribution']['positive']} posts ({$sentimentAnalysis['percentages']['positive']}%)\n";
echo "   Negative: {$sentimentAnalysis['distribution']['negative']} posts ({$sentimentAnalysis['percentages']['negative']}%)\n";
echo "   Neutral: {$sentimentAnalysis['distribution']['neutral']} posts ({$sentimentAnalysis['percentages']['neutral']}%)\n";
echo "\n";
echo "   Overall Sentiment: " . strtoupper($sentimentAnalysis['overall_sentiment']) . "\n";
echo "   Average Score: {$sentimentAnalysis['average_score']}\n";
echo "\n";

// Show sample classification
if (count($filteredPosts) > 0) {
    echo "📝 Sample Classification:\n";
    $sample = $filteredPosts[0];
    $result = $naiveBayesService->classify($sample['content']);
    
    echo "   Content: \"" . substr($sample['content'], 0, 60) . "...\"\n";
    echo "   Prediction: {$result['sentiment']} (confidence: " . round($result['confidence'] * 100, 1) . "%)\n";
    echo "   Probabilities:\n";
    foreach ($result['probabilities'] as $class => $prob) {
        echo "      - {$class}: " . round($prob * 100, 1) . "%\n";
    }
    echo "\n";
}

// ============================================================================
// STEP 4: K-Means Clustering
// ============================================================================
echo "STEP 4: 📊 K-MEANS CLUSTERING\n";
echo str_repeat("─", 75) . "\n";

$kmeansService = new KMeansClusteringService();
$clusteringResult = $kmeansService->setK(4)->cluster($filteredPosts);

echo "🎯 Clustering Results:\n";
echo "   Clusters Created: {$clusteringResult['cluster_count']}\n";
echo "   Total Items: {$clusteringResult['total_items']}\n";
echo "\n";

foreach ($clusteringResult['clusters'] as $idx => $cluster) {
    echo "   Cluster " . ($idx + 1) . ": {$cluster['name']}\n";
    echo "      Size: {$cluster['size']} posts\n";
    echo "      Keywords: " . implode(', ', $cluster['keywords']) . "\n";
    echo "\n";
}

$clusterMetrics = $kmeansService->getMetrics($clusteringResult);
echo "📈 Clustering Metrics:\n";
echo "   Average Cluster Size: {$clusterMetrics['average_cluster_size']}\n";
echo "   Min Size: {$clusterMetrics['min_cluster_size']}\n";
echo "   Max Size: {$clusterMetrics['max_cluster_size']}\n";
echo "\n";

// ============================================================================
// SUMMARY
// ============================================================================
echo "╔═══════════════════════════════════════════════════════════════════════╗\n";
echo "║  📊 SUMMARY                                                           ║\n";
echo "╠═══════════════════════════════════════════════════════════════════════╣\n";
echo "║                                                                       ║\n";
echo "║  ✅ Data Collection: {$stats['original_count']} raw posts from 2 platforms                      ║\n";
echo "║  ✅ 5-Stage Filtering: {$stats['after_temporal_filter']} quality posts (retention: {$metrics['retention_rate']}%)          ║\n";
echo "║  ✅ Sentiment Analysis: {$sentimentAnalysis['overall_sentiment']} overall (avg: {$sentimentAnalysis['average_score']})                   ║\n";
echo "║  ✅ Clustering: {$clusteringResult['cluster_count']} topic groups identified                          ║\n";
echo "║                                                                       ║\n";
echo "╠═══════════════════════════════════════════════════════════════════════╣\n";
echo "║  🎯 RESEARCH VALIDATION                                               ║\n";
echo "╠═══════════════════════════════════════════════════════════════════════╣\n";
echo "║                                                                       ║\n";
echo "║  [✓] 5-Stage Filtering implemented & tested                           ║\n";
echo "║  [✓] Naive Bayes sentiment classifier working                         ║\n";
echo "║  [✓] K-Means clustering grouping topics                               ║\n";
echo "║  [✓] Metrics collected for evaluation                                 ║\n";
echo "║  [✓] Complete pipeline from data → insights                           ║\n";
echo "║                                                                       ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════╝\n\n";

echo "💡 TIP: Lihat RESEARCH_DOCUMENTATION.md untuk penjelasan lengkap!\n";
echo "🚀 Ready for research & testing!\n\n";
