<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\TikTokService;
use App\Services\InstagramService;
use App\Services\DataFilteringService;
use App\Services\NaiveBayesService;
use App\Services\KMeansClusteringService;
use App\Services\IndoBERTService;
use App\Services\AIInsightsService;

echo "\n╔══════════════════════════════════════════════════════════════════════════╗\n";
echo "║  🎵 SOCIALINSIGHT - AI INSIGHTS DEMO                                    ║\n";
echo "║  Example: Analisis Komprehensif untuk 'Pop Music'                       ║\n";
echo "╚══════════════════════════════════════════════════════════════════════════╝\n\n";

$query = "pop music";
echo "🔍 Query: {$query}\n";
echo str_repeat("─", 78) . "\n\n";

// Step 1: Collect Data
echo "📥 Collecting data from social media...\n";
$tiktokService = new TikTokService();
$instagramService = new InstagramService();

$tiktokPosts = $tiktokService->search($query, 15);
$instagramPosts = $instagramService->search($query, 15);
$allRawPosts = array_merge($tiktokPosts, $instagramPosts);

echo "✅ Collected: " . count($allRawPosts) . " posts\n\n";

// Step 2: Filter Data
echo "🔧 Applying 5-stage filtering...\n";
$filteringService = new DataFilteringService();
$filteredResult = $filteringService->processData($allRawPosts, $query);
$filteredPosts = $filteredResult['data'];
$filteringStats = $filteredResult['stats'];

echo "✅ Quality posts: " . count($filteredPosts) . " (retention: " . 
     round((count($filteredPosts) / count($allRawPosts)) * 100, 1) . "%)\n\n";

// Step 3: Sentiment Analysis
echo "🤖 Analyzing sentiment with Naive Bayes...\n";
$naiveBayesService = new NaiveBayesService();
$sentimentAnalysis = $naiveBayesService->analyzeSentimentDistribution($filteredPosts);

// Update posts with sentiment
foreach ($filteredPosts as &$post) {
    $result = $naiveBayesService->classify($post['content']);
    $post['sentiment'] = $result['sentiment'];
    $post['sentiment_score'] = $result['score'];
    $post['sentiment_confidence'] = $result['confidence'];
}

echo "✅ Sentiment: {$sentimentAnalysis['overall_sentiment']} " .
     "({$sentimentAnalysis['percentages']['positive']}% positive)\n\n";

// Step 4: Clustering
echo "📊 Clustering topics with K-Means...\n";
$kmeansService = new KMeansClusteringService();
$clusteringResult = $kmeansService->setK(4)->cluster($filteredPosts);

echo "✅ Created: {$clusteringResult['cluster_count']} topic clusters\n\n";

// Step 5: Generate AI Insights
echo "╔══════════════════════════════════════════════════════════════════════════╗\n";
echo "║  🎯 AI-GENERATED COMPREHENSIVE INSIGHTS                                  ║\n";
echo "╚══════════════════════════════════════════════════════════════════════════╝\n\n";

$indoBERTService = new IndoBERTService();
$aiInsightsService = new AIInsightsService($naiveBayesService, $kmeansService, $indoBERTService);
$aiInsights = $aiInsightsService->generateInsights(
    $query,
    $filteredPosts,
    $sentimentAnalysis,
    $clusteringResult,
    $filteringStats
);

// Display Overview
echo "📋 OVERVIEW\n";
echo str_repeat("─", 78) . "\n";
echo "Title: {$aiInsights['overview']['title']}\n\n";
echo wordwrap($aiInsights['overview']['summary'], 75) . "\n\n";

echo "Key Statistics:\n";
foreach ($aiInsights['overview']['key_stats'] as $key => $value) {
    echo "  • " . ucwords(str_replace('_', ' ', $key)) . ": {$value}\n";
}
echo "\n";

// Display Content Analysis
echo "🎵 CONTENT ANALYSIS (Genre, Tone, Style)\n";
echo str_repeat("─", 78) . "\n";

$content = $aiInsights['content_analysis'];

echo "Genre Detection:\n";
echo "  • Primary: {$content['genre']['primary']} ({$content['genre']['confidence']}% confidence)\n";
if ($content['genre']['subgenre']) {
    echo "  • Subgenre: {$content['genre']['subgenre']}\n";
}
echo "\n";
echo "  📝 Description:\n";
echo "  " . wordwrap($content['genre']['description'], 73, "\n  ") . "\n\n";

echo "Tone & Mood:\n";
echo "  • Primary Tone: {$content['tone']['primary']} (strength: {$content['tone']['strength']}%)\n";
echo "  • Description: " . wordwrap($content['tone']['description'], 62, "\n                 ") . "\n";
if (!empty($content['tone']['all_tones'])) {
    echo "  • Detected Tones: " . implode(', ', $content['tone']['all_tones']) . "\n";
}
echo "\n";

echo "Language Style:\n";
echo "  • Overall Style: {$content['style']['style']}\n";
echo "  • Emoji Usage: {$content['style']['emoji_usage']}\n";
echo "  • Hashtag Usage: {$content['style']['hashtag_usage']}\n";
echo "  • Expressiveness: {$content['style']['expressiveness']}\n\n";

// Display Themes
if (!empty($content['themes'])) {
    echo "Common Themes:\n";
    foreach (array_slice($content['themes'], 0, 3) as $theme) {
        echo "  • {$theme['name']} - {$theme['relevance']}% relevance ({$theme['mentions']} mentions)\n";
    }
    echo "\n";
}

// Display Sentiment Insights
echo "💭 SENTIMENT INSIGHTS\n";
echo str_repeat("─", 78) . "\n";
echo wordwrap($aiInsights['sentiment_insights']['summary'], 75) . "\n\n";

echo "Distribution:\n";
echo "  • Positive: {$aiInsights['sentiment_insights']['percentages']['positive']}%\n";
echo "  • Negative: {$aiInsights['sentiment_insights']['percentages']['negative']}%\n";
echo "  • Neutral: {$aiInsights['sentiment_insights']['percentages']['neutral']}%\n\n";

// Display Topic Themes
echo "🎯 TOPIC THEMES (From K-Means Clustering)\n";
echo str_repeat("─", 78) . "\n";
foreach ($aiInsights['topic_themes'] as $theme) {
    echo "Cluster: {$theme['name']} ({$theme['size']} posts)\n";
    echo "  Keywords: " . implode(', ', $theme['keywords']) . "\n";
    echo "  " . wordwrap($theme['description'], 73, "\n  ") . "\n\n";
}

// Display Trend Analysis
echo "📈 TREND ANALYSIS\n";
echo str_repeat("─", 78) . "\n";
$trends = $aiInsights['trends'];
echo "Trending Status: {$trends['is_trending']}\n";
echo "Virality Score: {$trends['virality_score']}\n";
echo "Engagement Level: {$trends['engagement_level']['level']} " .
     "(avg: {$trends['engagement_level']['average']})\n";
echo "\n";
echo "Prediction:\n";
echo "  " . wordwrap($trends['prediction'], 73, "\n  ") . "\n\n";

// Display Personalized Notes
if (isset($aiInsights['personalized_notes']) && !empty($aiInsights['personalized_notes'])) {
    $notes = $aiInsights['personalized_notes'];
    echo "{$notes['icon']} PERSONALIZED NOTES - {$notes['category']}\n";
    echo str_repeat("─", 78) . "\n";
    echo "Berdasarkan analisis data, berikut rekomendasi spesifik untuk konten Anda:\n\n";
    
    // Music-specific notes
    if (isset($notes['genre_preference'])) {
        echo "🎸 Genre Preference:\n";
        echo "   Detected: {$notes['genre_preference']['detected']}\n";
        echo "   " . wordwrap($notes['genre_preference']['note'], 73, "\n   ") . "\n\n";
    }
    
    if (isset($notes['tempo_preference'])) {
        echo "🎵 Tempo/BPM Recommendation:\n";
        echo "   Recommended BPM: {$notes['tempo_preference']['recommended_bpm']}\n";
        echo "   Energy Level: {$notes['tempo_preference']['energy_level']}\n";
        echo "   " . wordwrap($notes['tempo_preference']['note'], 73, "\n   ") . "\n\n";
    }
    
    if (isset($notes['mood_preference'])) {
        echo "😊 Mood/Tone Preference:\n";
        echo "   Primary Mood: {$notes['mood_preference']['primary_mood']}\n";
        echo "   Strength: {$notes['mood_preference']['strength']}\n";
        echo "   " . wordwrap($notes['mood_preference']['note'], 73, "\n   ") . "\n\n";
    }
    
    if (isset($notes['lyrical_themes'])) {
        echo "📝 Lyrical Themes:\n";
        echo "   Top Theme: {$notes['lyrical_themes']['top_theme']}\n";
        echo "   Relevance: {$notes['lyrical_themes']['relevance']}\n";
        echo "   " . wordwrap($notes['lyrical_themes']['note'], 73, "\n   ") . "\n\n";
    }
    
    if (isset($notes['harmony_preference'])) {
        echo "🎹 Harmony Preference:\n";
        echo "   Type: {$notes['harmony_preference']['type']}\n";
        echo "   " . wordwrap($notes['harmony_preference']['note'], 73, "\n   ") . "\n\n";
    }
    
    if (isset($notes['instrument_suggestions'])) {
        echo "🎸 Instrument Suggestions:\n";
        echo "   Primary: " . implode(', ', $notes['instrument_suggestions']['primary']) . "\n";
        echo "   " . wordwrap($notes['instrument_suggestions']['note'], 73, "\n   ") . "\n\n";
    }
    
    if (isset($notes['vocal_style'])) {
        echo "🎤 Vocal Style:\n";
        echo "   Recommended: {$notes['vocal_style']['recommended']}\n";
        echo "   " . wordwrap($notes['vocal_style']['note'], 73, "\n   ") . "\n\n";
    }
    
    // Fashion-specific notes
    if (isset($notes['style_preference'])) {
        echo "👗 Style Preference:\n";
        echo "   Mood: {$notes['style_preference']['mood']}\n";
        echo "   " . wordwrap($notes['style_preference']['note'], 73, "\n   ") . "\n\n";
    }
    
    if (isset($notes['color_palette'])) {
        echo "🎨 Color Palette:\n";
        echo "   Primary: " . implode(', ', $notes['color_palette']['primary']) . "\n";
        echo "   " . wordwrap($notes['color_palette']['note'], 73, "\n   ") . "\n\n";
    }
    
    // Food-specific notes
    if (isset($notes['flavor_profile'])) {
        echo "🍽️ Flavor Profile:\n";
        echo "   Preference: {$notes['flavor_profile']['preference']}\n";
        echo "   " . wordwrap($notes['flavor_profile']['note'], 73, "\n   ") . "\n\n";
    }
    
    // Tech-specific notes
    if (isset($notes['tech_focus'])) {
        echo "💻 Tech Focus:\n";
        echo "   Area: {$notes['tech_focus']['area']}\n";
        echo "   " . wordwrap($notes['tech_focus']['note'], 73, "\n   ") . "\n\n";
    }
    
    // General notes
    if (isset($notes['content_style'])) {
        echo "📝 Content Style:\n";
        echo "   Tone: {$notes['content_style']['tone']}\n";
        echo "   " . wordwrap($notes['content_style']['note'], 73, "\n   ") . "\n\n";
    }
}

// Display Recommendations
echo "💡 ACTIONABLE RECOMMENDATIONS\n";
echo str_repeat("─", 78) . "\n";
foreach ($aiInsights['recommendations'] as $idx => $rec) {
    echo ($idx + 1) . ". [{$rec['type']}] {$rec['title']}\n";
    echo "   " . wordwrap($rec['description'], 72, "\n   ") . "\n";
    
    // Display evidence if available
    if (isset($rec['evidence'])) {
        echo "\n   📊 BUKTI KONKRIT:\n";
        
        // Top performing posts
        if (isset($rec['evidence']['top_performing_posts']) && !empty($rec['evidence']['top_performing_posts'])) {
            echo "   ├─ Top Performing Posts:\n";
            foreach (array_slice($rec['evidence']['top_performing_posts'], 0, 2) as $i => $post) {
                echo "   │  " . ($i + 1) . ". {$post['platform']} | @{$post['author']}\n";
                echo "   │     Content: " . substr($post['content'], 0, 70) . "\n";
                echo "   │     🔗 {$post['link']}\n";
                echo "   │     ❤️ {$post['engagement']['likes']} likes, 💬 {$post['engagement']['comments']} comments\n";
                if ($i < 1) echo "   │\n";
            }
        }
        
        // Supporting comments
        if (isset($rec['evidence']['supporting_comments']) && !empty($rec['evidence']['supporting_comments'])) {
            echo "   ├─ Supporting Comments:\n";
            foreach (array_slice($rec['evidence']['supporting_comments'], 0, 2) as $i => $comment) {
                echo "   │  " . ($i + 1) . ". @{$comment['author']} ({$comment['platform']}):\n";
                echo "   │     \"" . substr($comment['comment'], 0, 65) . "\"\n";
                echo "   │     🔗 {$comment['link']}\n";
                if ($i < 1) echo "   │\n";
            }
        }
        
        // Viral examples
        if (isset($rec['evidence']['viral_examples']) && !empty($rec['evidence']['viral_examples'])) {
            echo "   ├─ Viral Content Examples:\n";
            foreach (array_slice($rec['evidence']['viral_examples'], 0, 2) as $i => $viral) {
                echo "   │  " . ($i + 1) . ". Virality Score: {$viral['virality_score']} 🔥\n";
                echo "   │     {$viral['platform']} | @{$viral['author']}\n";
                echo "   │     🔗 {$viral['link']}\n";
                echo "   │     Why: {$viral['why_viral']}\n";
                if ($i < 1) echo "   │\n";
            }
        }
        
        // Best platforms
        if (isset($rec['evidence']['best_performing_platforms']) && !empty($rec['evidence']['best_performing_platforms'])) {
            echo "   ├─ Best Performing Platforms:\n";
            foreach ($rec['evidence']['best_performing_platforms'] as $i => $platform) {
                echo "   │  " . ($i + 1) . ". {$platform['platform']}: {$platform['avg_engagement']} avg engagement/post\n";
            }
        }
        
        // Critical posts
        if (isset($rec['evidence']['critical_posts']) && !empty($rec['evidence']['critical_posts'])) {
            echo "   ├─ Critical Feedback Examples:\n";
            foreach (array_slice($rec['evidence']['critical_posts'], 0, 2) as $i => $post) {
                echo "   │  " . ($i + 1) . ". {$post['platform']} | @{$post['author']}\n";
                echo "   │     " . substr($post['content'], 0, 65) . "\n";
                echo "   │     🔗 {$post['link']}\n";
                if ($i < 1) echo "   │\n";
            }
        }
    }
    
    // Display action items
    if (isset($rec['action_items']) && !empty($rec['action_items'])) {
        echo "\n   ✅ ACTION ITEMS:\n";
        foreach ($rec['action_items'] as $i => $action) {
            echo "   │  " . ($i + 1) . ". {$action}\n";
        }
    }
    
    echo "\n";
}

echo "╔══════════════════════════════════════════════════════════════════════════╗\n";
echo "║  ✅ COMPLETE AI ANALYSIS FINISHED                                        ║\n";
echo "╠══════════════════════════════════════════════════════════════════════════╣\n";
echo "║  Data dari media sosial telah dianalisis 100% dan menghasilkan:         ║\n";
echo "║                                                                          ║\n";
echo "║  ✓ Genre & subgenre detection                                           ║\n";
echo "║  ✓ Tone & mood analysis                                                 ║\n";
echo "║  ✓ Language style assessment                                            ║\n";
echo "║  ✓ Theme extraction                                                     ║\n";
echo "║  ✓ Sentiment distribution                                               ║\n";
echo "║  ✓ Topic clustering                                                     ║\n";
echo "║  ✓ Trend prediction                                                     ║\n";
echo "║  ✓ Actionable recommendations                                           ║\n";
echo "║                                                                          ║\n";
echo "╚══════════════════════════════════════════════════════════════════════════╝\n\n";

echo "💡 TIP: Gunakan query berbeda untuk hasil analisis yang berbeda!\n";
echo "   Contoh: 'rock music', 'fashion style', 'food recipe', etc.\n\n";
