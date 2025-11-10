<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\YouTubeService;
use App\Services\SentimentService;
use Illuminate\Http\Request;

class YouTubeController extends Controller
{
    protected $youtubeService;
    protected $sentimentService;

    public function __construct(YouTubeService $youtubeService, SentimentService $sentimentService)
    {
        $this->youtubeService = $youtubeService;
        $this->sentimentService = $sentimentService;
    }

    /**
     * Get and analyze YouTube comments
     */
    public function analyze(Request $request)
    {
        $request->validate([
            'video_url' => 'required|url',
        ]);

        try {
            $videoId = $this->youtubeService->extractVideoId($request->video_url);
            $comments = $this->youtubeService->getComments($videoId);

            $analyzed = array_map(function ($comment) {
                $sentiment = $this->sentimentService->analyze($comment['text']);
                return [
                    'id' => $comment['id'],
                    'author' => $comment['author'],
                    'text' => $comment['text'],
                    'sentiment' => $sentiment['label'],
                    'score' => $sentiment['score'],
                ];
            }, $comments);

            return response()->json([
                'success' => true,
                'data' => $analyzed,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
