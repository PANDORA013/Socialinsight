<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Service untuk 5 Tahap Filtering Data
 * Sesuai dengan arsitektur penelitian:
 * 1. Relevansi
 * 2. Duplikasi
 * 3. Noise
 * 4. Sentimen
 * 5. Temporal
 */
class DataFilteringService
{
    protected $stats = [
        'original_count' => 0,
        'after_relevance' => 0,
        'after_deduplication' => 0,
        'after_noise_removal' => 0,
        'after_sentiment_filter' => 0,
        'after_temporal_filter' => 0,
        'removed_by_stage' => [
            'relevance' => 0,
            'deduplication' => 0,
            'noise' => 0,
            'sentiment' => 0,
            'temporal' => 0,
        ]
    ];

    /**
     * Proses lengkap 5 tahap filtering
     */
    public function processData($data, $query, $options = [])
    {
        $this->stats['original_count'] = count($data);
        
        Log::info("Starting 5-stage filtering process", [
            'original_count' => $this->stats['original_count'],
            'query' => $query
        ]);

        // Tahap 1: Filter Relevansi
        $data = $this->filterRelevance($data, $query);
        $this->stats['after_relevance'] = count($data);
        $this->stats['removed_by_stage']['relevance'] = 
            $this->stats['original_count'] - $this->stats['after_relevance'];

        // Tahap 2: Remove Duplikasi
        $data = $this->removeDuplicates($data);
        $this->stats['after_deduplication'] = count($data);
        $this->stats['removed_by_stage']['deduplication'] = 
            $this->stats['after_relevance'] - $this->stats['after_deduplication'];

        // Tahap 3: Remove Noise & Spam
        $data = $this->removeNoise($data);
        $this->stats['after_noise_removal'] = count($data);
        $this->stats['removed_by_stage']['noise'] = 
            $this->stats['after_deduplication'] - $this->stats['after_noise_removal'];

        // Tahap 4: Filter berdasarkan Sentimen (opini only)
        $data = $this->filterBySentiment($data);
        $this->stats['after_sentiment_filter'] = count($data);
        $this->stats['removed_by_stage']['sentiment'] = 
            $this->stats['after_noise_removal'] - $this->stats['after_sentiment_filter'];

        // Tahap 5: Filter Temporal (berdasarkan waktu)
        $daysBack = $options['days_back'] ?? 30;
        $data = $this->filterTemporal($data, $daysBack);
        $this->stats['after_temporal_filter'] = count($data);
        $this->stats['removed_by_stage']['temporal'] = 
            $this->stats['after_sentiment_filter'] - $this->stats['after_temporal_filter'];

        Log::info("Filtering completed", $this->stats);

        return [
            'data' => $data,
            'stats' => $this->stats
        ];
    }

    /**
     * Tahap 1: Filter Relevansi
     * Memilih data yang sesuai dengan kata kunci/topik
     */
    protected function filterRelevance($data, $query)
    {
        $keywords = $this->extractKeywords($query);
        
        return array_filter($data, function($item) use ($keywords) {
            $content = strtolower($item['content'] ?? '');
            $author = strtolower($item['author'] ?? '');
            
            // Check if any keyword exists in content or author
            foreach ($keywords as $keyword) {
                if (str_contains($content, $keyword) || str_contains($author, $keyword)) {
                    return true;
                }
            }
            
            return false;
        });
    }

    /**
     * Tahap 2: Remove Duplikasi
     * Menghapus data yang sama berdasarkan content similarity
     */
    protected function removeDuplicates($data)
    {
        $unique = [];
        $hashes = [];
        
        foreach ($data as $item) {
            // Create hash dari content untuk detect duplikat
            $content = strtolower(trim($item['content'] ?? ''));
            $hash = md5($content);
            
            // Check similarity dengan existing items
            $isDuplicate = false;
            foreach ($hashes as $existingHash => $existingContent) {
                $similarity = $this->calculateSimilarity($content, $existingContent);
                if ($similarity > 0.85) { // 85% similar = duplicate
                    $isDuplicate = true;
                    break;
                }
            }
            
            if (!$isDuplicate) {
                $unique[] = $item;
                $hashes[$hash] = $content;
            }
        }
        
        return $unique;
    }

    /**
     * Tahap 3: Remove Noise & Spam
     * Menghapus spam, bot, dan teks tidak relevan
     */
    protected function removeNoise($data)
    {
        $spamPatterns = [
            '/(\w)\1{4,}/',  // Repeated characters (aaaaa)
            '/https?:\/\/bit\.ly/',  // Shortened links
            '/follow\s+me|follow\s+back|f4f|l4l/i',  // Follow spam
            '/win\s+free|click\s+here|earn\s+money/i',  // Common spam phrases
            '/^(.{1,10})$/u',  // Too short (< 10 chars)
            '/🎁|💰|💵|💸/',  // Spam emoji
        ];
        
        return array_filter($data, function($item) use ($spamPatterns) {
            $content = $item['content'] ?? '';
            
            // Check spam patterns
            foreach ($spamPatterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    return false;
                }
            }
            
            // Check if mostly URLs or hashtags
            $words = str_word_count($content, 0, '0123456789');
            $hashtagCount = substr_count($content, '#');
            if ($hashtagCount > $words * 0.5) { // More than 50% hashtags
                return false;
            }
            
            // Check bot indicators
            $author = strtolower($item['author'] ?? '');
            if (str_contains($author, 'bot') || str_contains($author, 'auto')) {
                return false;
            }
            
            return true;
        });
    }

    /**
     * Tahap 4: Filter berdasarkan Sentimen
     * Mempertahankan teks yang memiliki opini (bukan netral)
     */
    protected function filterBySentiment($data)
    {
        return array_filter($data, function($item) {
            $content = $item['content'] ?? '';
            
            // Check for opinion indicators
            $opinionIndicators = [
                // Positive indicators
                'love', 'amazing', 'great', 'excellent', 'good', 'best', 'awesome',
                'suka', 'bagus', 'keren', 'mantap', 'hebat',
                '❤️', '😍', '🔥', '👍', '✨',
                
                // Negative indicators
                'hate', 'bad', 'worst', 'terrible', 'awful', 'disgusting',
                'benci', 'jelek', 'buruk', 'parah', 'mengecewakan',
                '😡', '😤', '👎', '💔',
                
                // Sentiment words
                'feel', 'think', 'believe', 'rasa', 'pikir', 'menurut'
            ];
            
            $contentLower = strtolower($content);
            foreach ($opinionIndicators as $indicator) {
                if (str_contains($contentLower, $indicator)) {
                    return true;
                }
            }
            
            // Check for exclamation or question marks (indicates emotion/opinion)
            if (preg_match('/[!?]{1,}/', $content)) {
                return true;
            }
            
            return false;
        });
    }

    /**
     * Tahap 5: Filter Temporal
     * Memfilter data sesuai waktu posting
     */
    protected function filterTemporal($data, $daysBack = 30)
    {
        $cutoffDate = now()->subDays($daysBack);
        
        return array_filter($data, function($item) use ($cutoffDate) {
            $createdAt = $item['created_at'] ?? null;
            
            if (!$createdAt) {
                return true; // Keep if no date
            }
            
            try {
                $postDate = \Carbon\Carbon::parse($createdAt);
                return $postDate->gte($cutoffDate);
            } catch (\Exception $e) {
                return true; // Keep if date parse fails
            }
        });
    }

    /**
     * Helper: Extract keywords dari query
     */
    protected function extractKeywords($query)
    {
        $stopwords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
                      'yang', 'dan', 'atau', 'di', 'ke', 'dari', 'untuk'];
        
        $words = preg_split('/\s+/', strtolower($query));
        $keywords = array_diff($words, $stopwords);
        
        return array_values($keywords);
    }

    /**
     * Helper: Calculate text similarity (Levenshtein distance)
     */
    protected function calculateSimilarity($str1, $str2)
    {
        $len1 = strlen($str1);
        $len2 = strlen($str2);
        
        if ($len1 == 0 || $len2 == 0) {
            return 0;
        }
        
        $distance = levenshtein(
            substr($str1, 0, 255), 
            substr($str2, 0, 255)
        );
        
        $maxLen = max($len1, $len2);
        return 1 - ($distance / $maxLen);
    }

    /**
     * Get filtering statistics
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * Get filtering effectiveness metrics
     */
    public function getMetrics()
    {
        if ($this->stats['original_count'] == 0) {
            return [];
        }

        $total = $this->stats['original_count'];
        
        return [
            'total_removed' => $total - $this->stats['after_temporal_filter'],
            'removal_rate' => round((($total - $this->stats['after_temporal_filter']) / $total) * 100, 2),
            'retention_rate' => round(($this->stats['after_temporal_filter'] / $total) * 100, 2),
            'stage_effectiveness' => [
                'relevance' => round(($this->stats['removed_by_stage']['relevance'] / $total) * 100, 2),
                'deduplication' => round(($this->stats['removed_by_stage']['deduplication'] / $total) * 100, 2),
                'noise' => round(($this->stats['removed_by_stage']['noise'] / $total) * 100, 2),
                'sentiment' => round(($this->stats['removed_by_stage']['sentiment'] / $total) * 100, 2),
                'temporal' => round(($this->stats['removed_by_stage']['temporal'] / $total) * 100, 2),
            ]
        ];
    }
}
