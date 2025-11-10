<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $samplePosts = [
            [
                'platform' => 'youtube',
                'content' => 'This is an amazing video! I learned so much.',
                'author' => 'John Doe',
                'sentiment' => 'positive',
                'sentiment_score' => 0.85,
                'external_id' => 'sample_1',
            ],
            [
                'platform' => 'youtube',
                'content' => 'Not what I expected. Very disappointing.',
                'author' => 'Jane Smith',
                'sentiment' => 'negative',
                'sentiment_score' => 0.25,
                'external_id' => 'sample_2',
            ],
            [
                'platform' => 'youtube',
                'content' => 'Thanks for sharing this information.',
                'author' => 'Bob Johnson',
                'sentiment' => 'neutral',
                'sentiment_score' => 0.50,
                'external_id' => 'sample_3',
            ],
        ];

        foreach ($samplePosts as $post) {
            Post::create($post);
        }
    }
}
