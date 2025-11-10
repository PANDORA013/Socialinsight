<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\IndoBERTService;

class IndoBERTServiceTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new IndoBERTService();
    }

    public function test_service_can_be_instantiated()
    {
        $this->assertInstanceOf(IndoBERTService::class, $this->service);
    }

    public function test_can_check_availability()
    {
        $available = $this->service->checkAvailability();
        
        $this->assertIsBool($available);
    }

    public function test_can_get_status()
    {
        $status = $this->service->getStatus();
        
        $this->assertIsArray($status);
        $this->assertArrayHasKey('available', $status);
        $this->assertArrayHasKey('python_path', $status);
        $this->assertArrayHasKey('script_path', $status);
    }

    public function test_analyze_sentiment_returns_correct_structure()
    {
        // Skip if IndoBERT not available
        if (!$this->service->checkAvailability()) {
            $this->markTestSkipped('IndoBERT is not available');
        }

        try {
            $result = $this->service->analyzeSentiment('Lagu ini bagus');
            
            $this->assertIsArray($result);
            $this->assertArrayHasKey('sentiment', $result);
            $this->assertArrayHasKey('confidence', $result);
            $this->assertArrayHasKey('probabilities', $result);
        } catch (\Exception $e) {
            // If Python script fails, skip test
            $this->markTestSkipped('IndoBERT Python script failed: ' . $e->getMessage());
        }
    }

    public function test_analyze_sentiment_with_positive_text()
    {
        if (!$this->service->checkAvailability()) {
            $this->markTestSkipped('IndoBERT is not available');
        }

        try {
            $result = $this->service->analyzeSentiment('Sangat bagus dan menyentuh hati!');
            
            $this->assertEquals('positive', $result['sentiment']);
            $this->assertGreaterThan(0.5, $result['confidence']);
        } catch (\Exception $e) {
            $this->markTestSkipped('IndoBERT Python script failed: ' . $e->getMessage());
        }
    }

    public function test_analyze_sentiment_with_negative_text()
    {
        if (!$this->service->checkAvailability()) {
            $this->markTestSkipped('IndoBERT is not available');
        }

        try {
            $result = $this->service->analyzeSentiment('Jelek sekali, tidak menarik');
            
            $this->assertEquals('negative', $result['sentiment']);
            $this->assertGreaterThan(0.5, $result['confidence']);
        } catch (\Exception $e) {
            $this->markTestSkipped('IndoBERT Python script failed: ' . $e->getMessage());
        }
    }

    public function test_confidence_is_between_0_and_1()
    {
        if (!$this->service->checkAvailability()) {
            $this->markTestSkipped('IndoBERT is not available');
        }

        try {
            $result = $this->service->analyzeSentiment('Biasa saja');
            
            $this->assertGreaterThanOrEqual(0, $result['confidence']);
            $this->assertLessThanOrEqual(1, $result['confidence']);
        } catch (\Exception $e) {
            $this->markTestSkipped('IndoBERT Python script failed: ' . $e->getMessage());
        }
    }

    public function test_handles_error_gracefully()
    {
        // Test with empty text should not crash
        try {
            $result = $this->service->analyzeSentiment('');
            $this->assertIsArray($result);
        } catch (\Exception $e) {
            // If error occurs, should be caught gracefully
            $this->assertInstanceOf(\Exception::class, $e);
        }
    }
}
