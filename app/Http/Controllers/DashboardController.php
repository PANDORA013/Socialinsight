<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        $posts = Post::latest()->take(10)->get();
        
        $stats = [
            'total_posts' => Post::count(),
            'positive' => Post::where('sentiment', 'positive')->count(),
            'negative' => Post::where('sentiment', 'negative')->count(),
            'neutral' => Post::where('sentiment', 'neutral')->count(),
        ];

        // Data for charts - format untuk Chart.js
        $sentimentData = [
            'labels' => ['Positive', 'Negative', 'Neutral'],
            'data' => [$stats['positive'], $stats['negative'], $stats['neutral']]
        ];

        $platformCounts = Post::selectRaw('platform, count(*) as count')
            ->groupBy('platform')
            ->pluck('count', 'platform')
            ->toArray();

        $platformData = [
            'labels' => array_keys($platformCounts),
            'data' => array_values($platformCounts)
        ];

        // Recent activity
        $recentActivity = Post::with([])
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard-tailwind', compact('posts', 'stats', 'sentimentData', 'platformData', 'recentActivity'));
    }
}
