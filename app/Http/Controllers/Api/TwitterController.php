<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TwitterService;
use App\Services\SentimentService;
use Illuminate\Http\Request;

class TwitterController extends Controller
{
    protected $twitterService;
    protected $sentimentService;

    public function __construct(TwitterService $twitterService, SentimentService $sentimentService)
    {
        $this->twitterService = $twitterService;
        $this->sentimentService = $sentimentService;
    }

    /**
     * Get and analyze Twitter/X replies
     */
    public function analyze(Request $request)
    {
        $request->validate([
            'tweet_url' => 'required|url',
        ]);

        try {
            $tweetId = $this->twitterService->extractTweetId($request->tweet_url);
            $replies = $this->twitterService->getReplies($tweetId);

            $analyzed = array_map(function ($reply) {
                $sentiment = $this->sentimentService->analyze($reply['text']);
                return [
                    'id' => $reply['id'],
                    'author' => $reply['author'],
                    'username' => $reply['username'] ?? '',
                    'text' => $reply['text'],
                    'sentiment' => $sentiment['label'],
                    'score' => $sentiment['score'],
                    'likes' => $reply['likes'] ?? 0,
                ];
            }, $replies);

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
