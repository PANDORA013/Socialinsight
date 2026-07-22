<?php

namespace App\Support\SocialInsight;

use Illuminate\Support\Arr;

class SocialPlatform
{
    public const ALL = ['youtube', 'twitter', 'tiktok', 'instagram'];

    public const ACTIVE = ['youtube'];

    public const MAINTENANCE = ['twitter', 'tiktok', 'instagram'];

    public static function normalizeItem(array $item, string $platform, string $status = 'real'): array
    {
        $engagement = [
            'likes' => (int) Arr::get($item, 'engagement.likes', $item['likes'] ?? 0),
            'comments' => (int) Arr::get($item, 'engagement.comments', $item['comments'] ?? 0),
            'views' => (int) Arr::get($item, 'engagement.views', $item['views'] ?? 0),
            'shares' => (int) Arr::get($item, 'engagement.shares', $item['shares'] ?? 0),
        ];

        return [
            'platform' => strtolower((string) ($item['platform'] ?? $platform)),
            'external_id' => isset($item['external_id']) ? (string) $item['external_id'] : null,
            'author' => $item['author'] ?? null,
            'content' => (string) ($item['content'] ?? $item['title'] ?? ''),
            'url' => $item['url'] ?? $item['link'] ?? null,
            'link' => $item['url'] ?? $item['link'] ?? null,
            'published_at' => $item['published_at'] ?? null,
            'created_at' => $item['created_at'] ?? $item['published_at'] ?? null,
            'engagement' => $engagement,
            'likes' => $engagement['likes'],
            'comments' => $engagement['comments'],
            'views' => $engagement['views'],
            'shares' => $engagement['shares'],
            'sentiment' => $item['sentiment'] ?? null,
            'sentiment_score' => isset($item['sentiment_score']) ? (float) $item['sentiment_score'] : null,
            'source_status' => $status,
        ];
    }

    public static function demoItems(string $platform, string $topic): array
    {
        return [
            self::normalizeItem([
                'platform' => $platform,
                'external_id' => 'demo-'.$platform.'-1',
                'author' => 'demo_creator',
                'content' => 'Contoh percakapan bagus tentang '.$topic.' yang bikin audiens suka!',
                'likes' => 240,
                'comments' => 31,
                'views' => 5200,
                'link' => 'https://example.test/demo/'.$platform,
            ], $platform, 'demo'),
        ];
    }
}
