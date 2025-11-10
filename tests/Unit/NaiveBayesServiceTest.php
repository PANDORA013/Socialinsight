<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\NaiveBayesService;

class NaiveBayesServiceTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new NaiveBayesService();
    }

    public function test_can_classify_positive_sentiment()
    {
        $result = $this->service->classify('Lagu ini bagus banget, saya suka!');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('sentiment', $result);
        $this->assertArrayHasKey('score', $result);
        $this->assertArrayHasKey('confidence', $result);
        $this->assertEquals('positive', $result['sentiment']);
        $this->assertGreaterThan(0.5, $result['confidence']);
    }

    public function test_can_classify_negative_sentiment()
    {
        $result = $this->service->classify('Jelek sekali, mengecewakan!');
        
        $this->assertIsArray($result);
        $this->assertEquals('negative', $result['sentiment']);
        $this->assertGreaterThan(0.5, $result['confidence']);
    }

    public function test_can_classify_neutral_sentiment()
    {
        $result = $this->service->classify('Biasa saja');
        
        $this->assertIsArray($result);
        $this->assertEquals('neutral', $result['sentiment']);
    }

    public function test_can_analyze_sentiment_distribution()
    {
        $posts = [
            ['content' => 'Bagus sekali!'],
            ['content' => 'Jelek banget!'],
            ['content' => 'Biasa saja'],
        ];

        $result = $this->service->analyzeSentimentDistribution($posts);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('distribution', $result);
        $this->assertArrayHasKey('percentages', $result);
        $this->assertArrayHasKey('overall_sentiment', $result);
        
        // Check distribution counts
        $this->assertArrayHasKey('positive', $result['distribution']);
        $this->assertArrayHasKey('negative', $result['distribution']);
        $this->assertArrayHasKey('neutral', $result['distribution']);
    }

    public function test_handles_empty_text()
    {
        $result = $this->service->classify('');
        
        $this->assertIsArray($result);
        // Empty text may return any sentiment, just check structure
        $this->assertArrayHasKey('sentiment', $result);
        $this->assertArrayHasKey('confidence', $result);
    }

    public function test_handles_only_emoji()
    {
        $result = $this->service->classify('😊😊😊');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('sentiment', $result);
    }

    public function test_can_detect_multiple_positive_words()
    {
        $result = $this->service->classify('Bagus, mantap, keren, recommended!');
        
        $this->assertEquals('positive', $result['sentiment']);
        $this->assertGreaterThan(0.7, $result['confidence']);
    }

    public function test_confidence_score_range()
    {
        $result = $this->service->classify('Sangat bagus');
        
        $this->assertGreaterThanOrEqual(0, $result['confidence']);
        $this->assertLessThanOrEqual(1, $result['confidence']);
    }
}
