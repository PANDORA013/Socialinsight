<?php

namespace App\Services;

class InstagramService
{
    /**
     * Search Instagram posts by topic (Mock implementation)
     */
    public function search($topic, $maxResults = 10)
    {
        // Mock data for demonstration
        return $this->getMockData($topic, $maxResults);
    }

    /**
     * Placeholder for Instagram API integration
     */
    public function getComments($postUrl)
    {
        throw new \Exception('Instagram integration coming soon');
    }

    /**
     * Generate mock Instagram data for testing
     */
    private function getMockData($topic, $count)
    {
        $results = [];
        $templates = [
            "Beautiful {$topic} post! 😍",
            "Obsessed with this {$topic} aesthetic",
            "{$topic} goals! 💯",
            "Amazing {$topic} content",
            "Not impressed with {$topic}",
            "Love the {$topic} vibe ✨",
            "{$topic} is trending!",
            "Great {$topic} photography",
            "This {$topic} feed is everything",
            "Meh, seen better {$topic}",
        ];

        for ($i = 0; $i < min($count, count($templates)); $i++) {
            $sentiment = $i < 6 ? 'positive' : ($i === 4 || $i === 9 ? 'negative' : 'neutral');
            $score = $sentiment === 'positive' ? rand(75, 98) / 100 : ($sentiment === 'negative' ? rand(15, 35) / 100 : rand(45, 65) / 100);
            $username = 'instauser' . rand(1, 999);

            $results[] = [
                'platform' => 'instagram',
                'post_id' => $this->generateInstagramShortcode(), // Instagram shortcode format
                'content' => $templates[$i],
                'author' => '@' . $username,
                'likes' => rand(500, 100000),
                'comments' => rand(20, 10000),
                'views' => rand(50000, 5000000),
                'external_id' => 'ig_' . uniqid(),
                'sentiment' => $sentiment,
                'score' => $score,
                'created_at' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days')),
                'raw' => ['mock' => true],
            ];
        }

        return $results;
    }

    /**
     * Generate Instagram shortcode format (like: CXJmQoJg7Uo)
     */
    private function generateInstagramShortcode()
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';
        $shortcode = '';
        for ($i = 0; $i < 11; $i++) {
            $shortcode .= $chars[rand(0, strlen($chars) - 1)];
        }
        return $shortcode;
    }
}
