<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    /**
     * Get statistics for dashboard
     */
    public function stats()
    {
        $stats = [
            'total' => Post::count(),
            'positive' => Post::where('sentiment', 'positive')->count(),
            'negative' => Post::where('sentiment', 'negative')->count(),
            'neutral' => Post::where('sentiment', 'neutral')->count(),
            'by_platform' => Post::selectRaw('platform, count(*) as count')
                ->groupBy('platform')
                ->pluck('count', 'platform')
                ->toArray(),
            'recent' => Post::latest()->take(5)->get(['id', 'platform', 'author', 'sentiment', 'created_at']),
        ];

        return response()->json($stats);
    }

    /**
     * Get all posts with pagination
     */
    public function posts(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $platform = $request->input('platform');
        $sentiment = $request->input('sentiment');

        $query = Post::query()->latest();

        if ($platform) {
            $query->where('platform', $platform);
        }

        if ($sentiment) {
            $query->where('sentiment', $sentiment);
        }

        return response()->json($query->paginate($perPage));
    }

    /**
     * Export posts as CSV
     */
    public function export()
    {
        $posts = Post::all();

        $csv = "Platform,External ID,Author,Content,Sentiment,Score,Views,Likes,Comments,Created At\n";

        foreach ($posts as $post) {
            $content = str_replace(["\n", "\r", '"'], [' ', ' ', '""'], substr($post->content ?? '', 0, 200));
            $csv .= sprintf(
                '%s,%s,%s,"%s",%s,%s,%s,%s,%s,%s'."\n",
                $post->platform,
                $post->external_id,
                $post->author ?? '',
                $content,
                $post->sentiment ?? 'neutral',
                $post->sentiment_score ?? 0,
                $post->views ?? 0,
                $post->likes ?? 0,
                $post->comments ?? 0,
                $post->created_at
            );
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="socialinsight-posts-'.date('Y-m-d').'.csv"',
        ]);
    }
}
