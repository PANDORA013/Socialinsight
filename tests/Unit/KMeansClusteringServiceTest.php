<?php

namespace Tests\Unit;

use App\Services\KMeansClusteringService;
use Tests\TestCase;

class KMeansClusteringServiceTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new KMeansClusteringService;
    }

    public function test_can_cluster_posts()
    {
        $posts = [
            ['content' => 'Music is great with good melody'],
            ['content' => 'The beat is amazing and rhythm perfect'],
            ['content' => 'Lyrics are meaningful and touching'],
            ['content' => 'The words tell a beautiful story'],
            ['content' => 'Artist voice is incredible'],
            ['content' => 'Performance on stage was fantastic'],
        ];

        $result = $this->service->setK(3)->cluster($posts);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('clusters', $result);
        $this->assertArrayHasKey('cluster_count', $result);
        $this->assertEquals(3, $result['cluster_count']);
    }

    public function test_can_set_k_value()
    {
        $k = 5;
        $this->service->setK($k);

        $posts = array_fill(0, 10, ['content' => 'Test content']);
        $result = $this->service->cluster($posts);

        $this->assertLessThanOrEqual($k, $result['cluster_count']);
    }

    public function test_handles_small_dataset()
    {
        $posts = [
            ['content' => 'Only one post'],
        ];

        $result = $this->service->setK(3)->cluster($posts);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, $result['cluster_count']);
    }

    public function test_handles_empty_dataset()
    {
        $posts = [];

        $result = $this->service->setK(3)->cluster($posts);

        $this->assertIsArray($result);
        // Empty dataset may return 0 or 1 cluster
        $this->assertGreaterThanOrEqual(0, $result['cluster_count']);
        $this->assertLessThanOrEqual(1, $result['cluster_count']);
    }

    public function test_clusters_have_keywords()
    {
        $posts = [
            ['content' => 'Music melody rhythm beat'],
            ['content' => 'Lyrics words story meaning'],
            ['content' => 'Artist singer voice performance'],
        ];

        $result = $this->service->setK(3)->cluster($posts);

        foreach ($result['clusters'] as $cluster) {
            $this->assertArrayHasKey('keywords', $cluster);
            $this->assertIsArray($cluster['keywords']);
        }
    }

    public function test_cluster_sizes_sum_to_total()
    {
        $posts = array_fill(0, 20, ['content' => 'Test post content']);

        $result = $this->service->setK(4)->cluster($posts);

        $totalSize = array_sum(array_column($result['clusters'], 'size'));
        $this->assertEquals(count($posts), $totalSize);
    }

    public function test_returns_cluster_metadata()
    {
        $posts = [
            ['content' => 'Test content 1'],
            ['content' => 'Test content 2'],
            ['content' => 'Test content 3'],
        ];

        $result = $this->service->setK(2)->cluster($posts);

        // Check basic structure
        $this->assertArrayHasKey('clusters', $result);
        $this->assertArrayHasKey('cluster_count', $result);
        $this->assertGreaterThan(0, $result['cluster_count']);
    }
}
