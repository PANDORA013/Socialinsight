<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║           🔧 API TEST - AFTER SSL FIX                         ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

$query = "Taylor Swift";
$servicesWorking = 0;
$servicesTotal = 4;

// ============================================
// Test 1: YouTube API
// ============================================
echo "┌─────────────────────────────────────────┐\n";
echo "│  1️⃣  YouTube API Test                  │\n";
echo "└─────────────────────────────────────────┘\n";

$youtubeService = app('App\Services\YouTubeService');

try {
    $results = $youtubeService->search($query, 3);
    
    if (count($results) > 0) {
        echo "✅ Status: WORKING!\n";
        echo "📊 Found: " . count($results) . " videos\n";
        echo "📺 Sample: " . substr($results[0]['content'], 0, 50) . "...\n";
        $servicesWorking++;
    } else {
        echo "⚠️  Status: No results (Check API Key or quota)\n";
        echo "🔑 API Key: " . substr(config('services.youtube.api_key'), 0, 20) . "...\n";
    }
} catch (Exception $e) {
    echo "❌ Status: ERROR\n";
    echo "🚨 Error: " . $e->getMessage() . "\n";
}

echo "\n";

// ============================================
// Test 2: Twitter API
// ============================================
echo "┌─────────────────────────────────────────┐\n";
echo "│  2️⃣  Twitter API Test                  │\n";
echo "└─────────────────────────────────────────┘\n";

$twitterService = app('App\Services\TwitterService');
$bearerToken = config('services.twitter.bearer_token');

if (empty($bearerToken)) {
    echo "❌ Status: NOT CONFIGURED\n";
    echo "⚠️  Twitter Bearer Token is EMPTY!\n";
    echo "📝 Action needed: Add bearer token to .env file\n";
    echo "    TWITTER_BEARER_TOKEN=your_actual_bearer_token\n";
} else {
    try {
        $results = $twitterService->search($query, 3);
        
        if (count($results) > 0) {
            echo "✅ Status: WORKING!\n";
            echo "📊 Found: " . count($results) . " tweets\n";
            echo "🐦 Sample: " . substr($results[0]['content'], 0, 50) . "...\n";
            $servicesWorking++;
        } else {
            echo "⚠️  Status: No results (Check bearer token or quota)\n";
            echo "🔑 Bearer: " . substr($bearerToken, 0, 30) . "...\n";
        }
    } catch (Exception $e) {
        echo "❌ Status: ERROR\n";
        echo "🚨 Error: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// ============================================
// Test 3: TikTok API (Mock)
// ============================================
echo "┌─────────────────────────────────────────┐\n";
echo "│  3️⃣  TikTok API Test (Mock)            │\n";
echo "└─────────────────────────────────────────┘\n";

$tiktokService = app('App\Services\TikTokService');

try {
    $results = $tiktokService->search($query, 3);
    
    if (count($results) > 0) {
        echo "✅ Status: WORKING (Mock Data)\n";
        echo "📊 Generated: " . count($results) . " videos\n";
        echo "🎵 Sample: " . substr($results[0]['content'], 0, 50) . "...\n";
        $servicesWorking++;
    } else {
        echo "❌ Status: Failed to generate mock data\n";
    }
} catch (Exception $e) {
    echo "❌ Status: ERROR\n";
    echo "🚨 Error: " . $e->getMessage() . "\n";
}

echo "\n";

// ============================================
// Test 4: Instagram API (Mock)
// ============================================
echo "┌─────────────────────────────────────────┐\n";
echo "│  4️⃣  Instagram API Test (Mock)         │\n";
echo "└─────────────────────────────────────────┘\n";

$instagramService = app('App\Services\InstagramService');

try {
    $results = $instagramService->search($query, 3);
    
    if (count($results) > 0) {
        echo "✅ Status: WORKING (Mock Data)\n";
        echo "📊 Generated: " . count($results) . " posts\n";
        echo "📸 Sample: " . substr($results[0]['content'], 0, 50) . "...\n";
        $servicesWorking++;
    } else {
        echo "❌ Status: Failed to generate mock data\n";
    }
} catch (Exception $e) {
    echo "❌ Status: ERROR\n";
    echo "🚨 Error: " . $e->getMessage() . "\n";
}

echo "\n";

// ============================================
// Final Results
// ============================================
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                   📊 FINAL RESULTS                             ║\n";
echo "╠════════════════════════════════════════════════════════════════╣\n";

$percentage = ($servicesWorking / $servicesTotal) * 100;

echo "║  Services Working: {$servicesWorking}/{$servicesTotal} ({$percentage}%)";
echo str_repeat(" ", 63 - strlen("║  Services Working: {$servicesWorking}/{$servicesTotal} ({$percentage}%)")) . "║\n";

if ($servicesWorking >= 3) {
    echo "║  Status: ✅ EXCELLENT - System Ready!";
} elseif ($servicesWorking >= 2) {
    echo "║  Status: ⚠️  GOOD - Core Features Working";
} else {
    echo "║  Status: ❌ NEEDS ATTENTION";
}

echo str_repeat(" ", 63 - strlen("║  Status: ")) . "║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";

echo "\n";

// ============================================
// Next Steps
// ============================================
if ($servicesWorking < 4) {
    echo "📝 NEXT STEPS:\n";
    echo "─────────────────────────────────────────\n";
    
    if (empty($bearerToken)) {
        echo "\n🔴 HIGH PRIORITY: Add Twitter Bearer Token\n";
        echo "   1. Visit: https://developer.twitter.com/en/portal/dashboard\n";
        echo "   2. Go to your App → Keys and Tokens\n";
        echo "   3. Generate Bearer Token\n";
        echo "   4. Add to .env:\n";
        echo "      TWITTER_BEARER_TOKEN=your_bearer_token_here\n";
        echo "   5. Run: php artisan config:clear\n";
    }
    
    echo "\n";
}

if ($servicesWorking >= 2) {
    echo "💡 TIP: System can work with {$servicesWorking} services!\n";
    echo "   - TikTok & Instagram provide realistic mock data\n";
    echo "   - Good enough for development and testing\n";
    echo "   - Add real APIs when ready for production\n";
}

echo "\n";
