<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SentimentService
{
    protected $apiKey;

    protected $model = 'gpt-3.5-turbo';

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
    }

    /**
     * Analyze sentiment of text using OpenAI
     */
    public function analyze($text)
    {
        if (empty($this->apiKey)) {
            // Fallback to basic sentiment analysis
            return $this->basicSentiment($text);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a sentiment analysis assistant. Analyze the sentiment of the given text and respond with only one word: positive, negative, or neutral.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $text,
                    ],
                ],
                'temperature' => 0.3,
                'max_tokens' => 10,
            ]);

            if ($response->successful()) {
                $sentiment = strtolower(trim($response->json('choices.0.message.content')));

                return [
                    'label' => in_array($sentiment, ['positive', 'negative', 'neutral']) ? $sentiment : 'neutral',
                    'score' => $this->calculateScore($sentiment),
                ];
            }
        } catch (\Exception $e) {
            // Fallback to basic sentiment
        }

        return $this->basicSentiment($text);
    }

    /**
     * Basic sentiment analysis (fallback)
     */
    protected function basicSentiment($text)
    {
        $text = strtolower($text);

        $positive = ['good', 'great', 'excellent', 'amazing', 'love', 'best', 'awesome', 'fantastic'];
        $negative = ['bad', 'terrible', 'awful', 'hate', 'worst', 'horrible', 'disgusting', 'poor'];

        $positiveCount = 0;
        $negativeCount = 0;

        foreach ($positive as $word) {
            $positiveCount += substr_count($text, $word);
        }

        foreach ($negative as $word) {
            $negativeCount += substr_count($text, $word);
        }

        if ($positiveCount > $negativeCount) {
            return ['label' => 'positive', 'score' => 0.7];
        } elseif ($negativeCount > $positiveCount) {
            return ['label' => 'negative', 'score' => 0.3];
        }

        return ['label' => 'neutral', 'score' => 0.5];
    }

    /**
     * Calculate sentiment score
     */
    protected function calculateScore($sentiment)
    {
        switch ($sentiment) {
            case 'positive':
                return 0.8;
            case 'negative':
                return 0.2;
            default:
                return 0.5;
        }
    }
}
