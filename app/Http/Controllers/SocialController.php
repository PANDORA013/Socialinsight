<?php

namespace App\Http\Controllers;

use App\Services\SentimentService;
use App\Services\TwitterService;
use App\Services\YouTubeService;
use Illuminate\Http\Request;

class SocialController extends Controller
{
    protected $youtubeService;

    protected $twitterService;

    protected $sentimentService;

    public function __construct(
        YouTubeService $youtubeService,
        TwitterService $twitterService,
        SentimentService $sentimentService
    ) {
        $this->youtubeService = $youtubeService;
        $this->twitterService = $twitterService;
        $this->sentimentService = $sentimentService;
    }

    /**
     * Show YouTube form
     */
    public function youtubeForm()
    {
        return view('youtube.form');
    }

    /**
     * Analyze YouTube comments
     */
    public function analyzeYouTube(Request $request)
    {
        $request->validate([
            'video_url' => 'required|url',
        ]);

        try {
            $videoId = $this->youtubeService->extractVideoId($request->video_url);
            $comments = $this->youtubeService->getComments($videoId);

            $analyzed = collect($comments)->map(function ($comment) {
                $sentiment = $this->sentimentService->analyze($comment['text']);

                return [
                    'platform' => 'youtube',
                    'content' => $comment['text'],
                    'author' => $comment['author'],
                    'sentiment' => $sentiment['label'],
                    'sentiment_score' => $sentiment['score'],
                    'external_id' => $comment['id'],
                ];
            });

            return redirect()->route('dashboard')
                ->with('success', $analyzed->count().' YouTube comments analyzed without permanent raw storage.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: '.$e->getMessage());
        }
    }

    /**
     * Show Twitter form
     */
    public function twitterForm()
    {
        return view('twitter.form');
    }

    /**
     * Analyze Twitter replies
     */
    public function analyzeTwitter(Request $request)
    {
        $request->validate([
            'tweet_url' => 'required|url',
        ]);

        try {
            $tweetId = $this->twitterService->extractTweetId($request->tweet_url);
            $replies = $this->twitterService->getReplies($tweetId);

            $analyzed = collect($replies)->map(function ($reply) {
                $sentiment = $this->sentimentService->analyze($reply['text']);

                return [
                    'platform' => 'twitter',
                    'content' => $reply['text'],
                    'author' => $reply['author'],
                    'sentiment' => $sentiment['label'],
                    'sentiment_score' => $sentiment['score'],
                    'external_id' => $reply['id'],
                ];
            });

            return redirect()->route('dashboard')
                ->with('success', $analyzed->count().' Twitter/X replies analyzed without permanent raw storage.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: '.$e->getMessage());
        }
    }

    /**
     * Show Instagram form (placeholder)
     */
    public function instagramForm()
    {
        return view('instagram.form');
    }

    /**
     * Show TikTok form (placeholder)
     */
    public function tiktokForm()
    {
        return view('tiktok.form');
    }
}
