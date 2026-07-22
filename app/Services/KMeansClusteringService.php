<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * K-Means Clustering Service untuk Pengelompokan Tren
 * Mengelompokkan topik berdasarkan kata kunci dan hashtag
 */
class KMeansClusteringService
{
    protected $k = 4; // Number of clusters (default: 4)

    protected $maxIterations = 100;

    protected $centroids = [];

    protected $clusters = [];

    protected $vocabulary = []; // Vocabulary for TF-IDF

    /**
     * Set number of clusters
     */
    public function setK($k)
    {
        $this->k = $k;

        return $this;
    }

    /**
     * Perform K-Means clustering on data
     */
    public function cluster($data)
    {
        if (count($data) < $this->k) {
            Log::warning('Not enough data for K-Means clustering', [
                'data_count' => count($data),
                'k' => $this->k,
            ]);

            // Return single cluster if not enough data
            return [
                'clusters' => [
                    [
                        'id' => 0,
                        'name' => 'All Data',
                        'items' => $data,
                        'keywords' => $this->extractTopKeywords($data, 5),
                        'size' => count($data),
                    ],
                ],
                'cluster_count' => 1,
                'total_items' => count($data),
                'iterations' => 0,
            ];
        }

        // Extract features (TF-IDF vectors)
        $vectors = $this->extractFeatures($data);

        // Initialize centroids randomly
        $this->initializeCentroids($vectors);

        $iteration = 0;
        $changed = true;

        while ($changed && $iteration < $this->maxIterations) {
            $changed = false;

            // Assign points to nearest centroid
            $newClusters = array_fill(0, $this->k, []);

            foreach ($vectors as $idx => $vector) {
                $nearestCentroid = $this->findNearestCentroid($vector);
                $newClusters[$nearestCentroid][] = $idx;
            }

            // Check if clusters changed
            if ($newClusters != $this->clusters) {
                $changed = true;
                $this->clusters = $newClusters;
            }

            // Update centroids
            $this->updateCentroids($vectors);

            $iteration++;
        }

        // Prepare result with cluster names and keywords
        $result = $this->prepareClusterResult($data, $vectors);

        Log::info('K-Means clustering completed', [
            'iterations' => $iteration,
            'cluster_count' => count($result['clusters']),
            'sizes' => array_map('count', array_column($result['clusters'], 'items')),
        ]);

        return $result;
    }

    /**
     * Extract TF-IDF features from data
     */
    protected function extractFeatures($data)
    {
        $documents = [];
        $vocabulary = [];

        // Extract all words and build vocabulary
        foreach ($data as $item) {
            $content = $item['content'] ?? '';
            $words = $this->tokenize($content);
            $documents[] = $words;

            foreach ($words as $word) {
                if (! isset($vocabulary[$word])) {
                    $vocabulary[$word] = 0;
                }
                $vocabulary[$word]++;
            }
        }

        // Calculate IDF
        $totalDocs = count($documents);
        $idf = [];

        foreach ($vocabulary as $word => $df) {
            $idf[$word] = log($totalDocs / $df);
        }

        // Calculate TF-IDF vectors
        $vectors = [];

        foreach ($documents as $doc) {
            $vector = [];
            $wordCount = array_count_values($doc);
            $totalWords = count($doc);

            foreach ($vocabulary as $word => $df) {
                $tf = ($wordCount[$word] ?? 0) / max($totalWords, 1);
                $vector[$word] = $tf * $idf[$word];
            }

            // Store vocabulary reference
            if (empty($vectors)) {
                $this->vocabulary = array_keys($vocabulary);
            }

            $vectors[] = $vector;
        }

        return $vectors;
    }

    /**
     * Initialize centroids randomly from data points
     */
    protected function initializeCentroids($vectors)
    {
        $indices = array_rand($vectors, $this->k);

        if (! is_array($indices)) {
            $indices = [$indices];
        }

        $this->centroids = [];
        foreach ($indices as $idx) {
            $this->centroids[] = $vectors[$idx];
        }
    }

    /**
     * Find nearest centroid for a vector
     */
    protected function findNearestCentroid($vector)
    {
        $minDistance = PHP_FLOAT_MAX;
        $nearestCentroid = 0;

        foreach ($this->centroids as $idx => $centroid) {
            $distance = $this->euclideanDistance($vector, $centroid);

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearestCentroid = $idx;
            }
        }

        return $nearestCentroid;
    }

    /**
     * Update centroids based on cluster members
     */
    protected function updateCentroids($vectors)
    {
        foreach ($this->clusters as $clusterIdx => $members) {
            if (empty($members)) {
                continue;
            }

            $newCentroid = [];
            $vocabulary = array_keys($vectors[0]);

            foreach ($vocabulary as $word) {
                $sum = 0;
                foreach ($members as $memberIdx) {
                    $sum += $vectors[$memberIdx][$word] ?? 0;
                }
                $newCentroid[$word] = $sum / count($members);
            }

            $this->centroids[$clusterIdx] = $newCentroid;
        }
    }

    /**
     * Calculate Euclidean distance between two vectors
     */
    protected function euclideanDistance($vector1, $vector2)
    {
        $sum = 0;

        foreach ($vector1 as $word => $value1) {
            $value2 = $vector2[$word] ?? 0;
            $sum += pow($value1 - $value2, 2);
        }

        return sqrt($sum);
    }

    /**
     * Prepare cluster result with names and keywords
     */
    protected function prepareClusterResult($data, $vectors)
    {
        $clusterData = [];
        $clusterNames = ['Fashion & Lifestyle', 'Music & Entertainment', 'Drama & News', 'Technology & Innovation'];

        foreach ($this->clusters as $idx => $members) {
            if (empty($members)) {
                continue;
            }

            $items = [];
            foreach ($members as $memberIdx) {
                if (isset($data[$memberIdx])) {
                    $items[] = $data[$memberIdx];
                }
            }

            // Extract top keywords for cluster
            $keywords = $this->extractTopKeywords($items, 5);

            // Determine cluster name based on keywords
            $clusterName = $this->determineClusterName($keywords, $clusterNames[$idx] ?? 'Cluster '.($idx + 1));

            $clusterData[] = [
                'id' => $idx,
                'name' => $clusterName,
                'keywords' => $keywords,
                'items' => $items,
                'size' => count($items),
            ];
        }

        // Sort clusters by size (largest first)
        usort($clusterData, fn ($a, $b) => $b['size'] - $a['size']);

        return [
            'clusters' => $clusterData,
            'cluster_count' => count($clusterData),
            'total_items' => count($data),
            'iterations' => $this->maxIterations,
        ];
    }

    /**
     * Extract top keywords from cluster items
     */
    protected function extractTopKeywords($items, $topN = 5)
    {
        $wordFreq = [];

        foreach ($items as $item) {
            $content = $item['content'] ?? '';
            $words = $this->tokenize($content);

            foreach ($words as $word) {
                if (! isset($wordFreq[$word])) {
                    $wordFreq[$word] = 0;
                }
                $wordFreq[$word]++;
            }
        }

        arsort($wordFreq);

        return array_slice(array_keys($wordFreq), 0, $topN);
    }

    /**
     * Determine cluster name based on keywords
     */
    protected function determineClusterName($keywords, $defaultName)
    {
        $categories = [
            'fashion' => ['fashion', 'style', 'outfit', 'dress', 'clothing', 'model', 'beauty'],
            'music' => ['music', 'song', 'concert', 'singer', 'album', 'band', 'performance'],
            'drama' => ['drama', 'news', 'gossip', 'scandal', 'rumor', 'controversy'],
            'tech' => ['tech', 'technology', 'app', 'software', 'gadget', 'innovation', 'ai'],
            'food' => ['food', 'recipe', 'cooking', 'restaurant', 'delicious', 'meal'],
            'sports' => ['sport', 'game', 'player', 'team', 'match', 'champion'],
            'travel' => ['travel', 'trip', 'destination', 'vacation', 'journey', 'explore'],
            'lifestyle' => ['life', 'lifestyle', 'daily', 'routine', 'vlog', 'inspiration'],
        ];

        foreach ($categories as $category => $categoryKeywords) {
            foreach ($keywords as $keyword) {
                if (in_array(strtolower($keyword), $categoryKeywords)) {
                    return ucfirst($category);
                }
            }
        }

        // If no category matched, use top keyword
        return ! empty($keywords) ? ucfirst($keywords[0]) : $defaultName;
    }

    /**
     * Tokenize text
     */
    protected function tokenize($text)
    {
        $text = strtolower($text);
        $text = preg_replace('/https?:\/\/[^\s]+/', '', $text);
        $text = str_replace(['@', '#'], '', $text);
        $text = preg_replace('/[^a-z0-9\s]/', ' ', $text);
        $words = preg_split('/\s+/', $text);

        $stopwords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'is', 'this',
            'yang', 'dan', 'atau', 'di', 'ke', 'dari', 'untuk', 'ini', 'itu', 'dengan'];

        $words = array_filter($words, function ($word) use ($stopwords) {
            return strlen($word) > 2 && ! in_array($word, $stopwords);
        });

        return array_values($words);
    }

    /**
     * Get clustering metrics
     */
    public function getMetrics($result)
    {
        $clusters = $result['clusters'];
        $totalItems = $result['total_items'];

        // Calculate metrics
        $sizes = array_column($clusters, 'size');
        $avgSize = $totalItems > 0 ? array_sum($sizes) / count($clusters) : 0;
        $minSize = min($sizes);
        $maxSize = max($sizes);

        return [
            'cluster_count' => count($clusters),
            'total_items' => $totalItems,
            'average_cluster_size' => round($avgSize, 2),
            'min_cluster_size' => $minSize,
            'max_cluster_size' => $maxSize,
            'cluster_distribution' => array_map(function ($cluster) use ($totalItems) {
                return [
                    'name' => $cluster['name'],
                    'size' => $cluster['size'],
                    'percentage' => round(($cluster['size'] / $totalItems) * 100, 2),
                ];
            }, $clusters),
        ];
    }
}
