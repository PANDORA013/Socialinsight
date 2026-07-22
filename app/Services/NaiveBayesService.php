<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Naive Bayes Classifier untuk Analisis Sentimen
 * Mengklasifikasi teks menjadi: Positif, Negatif, atau Netral
 */
class NaiveBayesService
{
    protected $vocabulary = [];

    protected $priors = [];

    protected $likelihoods = [];

    // Training data - bisa diganti dengan data dari database
    protected $trainingData = [
        'positive' => [
            'love this amazing product',
            'great quality best purchase',
            'excellent service very happy',
            'suka banget keren mantap',
            'bagus sekali sangat puas',
            'amazing beautiful wonderful',
        ],
        'negative' => [
            'hate this terrible product',
            'bad quality worst purchase',
            'awful service very disappointed',
            'jelek banget buruk parah',
            'mengecewakan tidak suka',
            'terrible horrible disgusting',
        ],
        'neutral' => [
            'this is a product',
            'received the item today',
            'came in package normal',
            'ini adalah produk',
            'sudah diterima hari ini',
            'biasa saja standar',
        ],
    ];

    public function __construct()
    {
        // Auto-train on initialization
        $this->train();
    }

    /**
     * Train Naive Bayes model
     */
    public function train()
    {
        $totalDocs = 0;
        $classCounts = [];
        $classWordCounts = [];
        $classWords = [];

        // Count documents per class
        foreach ($this->trainingData as $class => $documents) {
            $classCounts[$class] = count($documents);
            $totalDocs += count($documents);
            $classWords[$class] = [];

            foreach ($documents as $doc) {
                $words = $this->tokenize($doc);
                $classWords[$class] = array_merge($classWords[$class], $words);

                foreach ($words as $word) {
                    if (! isset($this->vocabulary[$word])) {
                        $this->vocabulary[$word] = 0;
                    }
                    $this->vocabulary[$word]++;
                }
            }

            $classWordCounts[$class] = count($classWords[$class]);
        }

        // Calculate priors P(class)
        foreach ($classCounts as $class => $count) {
            $this->priors[$class] = $count / $totalDocs;
        }

        // Calculate likelihoods P(word|class)
        $vocabularySize = count($this->vocabulary);

        foreach ($this->trainingData as $class => $documents) {
            $this->likelihoods[$class] = [];

            foreach ($this->vocabulary as $word => $count) {
                $wordCountInClass = 0;
                foreach ($classWords[$class] as $w) {
                    if ($w === $word) {
                        $wordCountInClass++;
                    }
                }

                // Laplace smoothing
                $this->likelihoods[$class][$word] =
                    ($wordCountInClass + 1) / ($classWordCounts[$class] + $vocabularySize);
            }
        }

        Log::info('Naive Bayes model trained', [
            'vocabulary_size' => $vocabularySize,
            'classes' => array_keys($this->priors),
        ]);
    }

    /**
     * Classify text sentiment
     */
    public function classify($text)
    {
        $words = $this->tokenize($text);
        $scores = [];

        foreach ($this->priors as $class => $prior) {
            $score = log($prior);

            foreach ($words as $word) {
                if (isset($this->likelihoods[$class][$word])) {
                    $score += log($this->likelihoods[$class][$word]);
                } else {
                    // Unknown word - use small probability
                    $vocabularySize = count($this->vocabulary);
                    $score += log(1 / ($vocabularySize + count($this->vocabulary)));
                }
            }

            $scores[$class] = $score;
        }

        // Get class with highest score
        arsort($scores);
        $prediction = array_key_first($scores);

        // Convert scores to probabilities
        $maxScore = max($scores);
        $expScores = array_map(fn ($s) => exp($s - $maxScore), $scores);
        $sumExp = array_sum($expScores);
        $probabilities = array_map(fn ($s) => $s / $sumExp, $expScores);

        return [
            'sentiment' => $prediction,
            'confidence' => $probabilities[$prediction],
            'probabilities' => $probabilities,
            'score' => $this->mapSentimentToScore($prediction),
        ];
    }

    /**
     * Batch classify multiple texts
     */
    public function classifyBatch($texts)
    {
        $results = [];

        foreach ($texts as $text) {
            $results[] = $this->classify($text);
        }

        return $results;
    }

    /**
     * Analyze sentiment distribution
     */
    public function analyzeSentimentDistribution($data)
    {
        $sentiments = [
            'positive' => 0,
            'negative' => 0,
            'neutral' => 0,
        ];

        $totalScore = 0;
        $count = 0;

        foreach ($data as $item) {
            $content = $item['content'] ?? '';
            if (empty($content)) {
                continue;
            }

            $result = $this->classify($content);
            $sentiments[$result['sentiment']]++;
            $totalScore += $result['score'];
            $count++;

            // Update item with sentiment analysis
            $item['sentiment'] = $result['sentiment'];
            $item['sentiment_score'] = $result['score'];
            $item['sentiment_confidence'] = $result['confidence'];
        }

        $avgScore = $count > 0 ? $totalScore / $count : 0.5;

        return [
            'distribution' => $sentiments,
            'percentages' => [
                'positive' => $count > 0 ? round(($sentiments['positive'] / $count) * 100, 2) : 0,
                'negative' => $count > 0 ? round(($sentiments['negative'] / $count) * 100, 2) : 0,
                'neutral' => $count > 0 ? round(($sentiments['neutral'] / $count) * 100, 2) : 0,
            ],
            'average_score' => round($avgScore, 3),
            'total_analyzed' => $count,
            'overall_sentiment' => $this->getOverallSentiment($sentiments),
        ];
    }

    /**
     * Tokenize text into words
     */
    protected function tokenize($text)
    {
        // Convert to lowercase
        $text = strtolower($text);

        // Remove URLs
        $text = preg_replace('/https?:\/\/[^\s]+/', '', $text);

        // Remove mentions and hashtags symbols (keep the words)
        $text = str_replace(['@', '#'], '', $text);

        // Remove special characters except spaces
        $text = preg_replace('/[^a-z0-9\s]/', ' ', $text);

        // Split into words
        $words = preg_split('/\s+/', $text);

        // Remove stopwords
        $stopwords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'is', 'this',
            'yang', 'dan', 'atau', 'di', 'ke', 'dari', 'untuk', 'ini', 'itu', 'dengan'];

        $words = array_filter($words, function ($word) use ($stopwords) {
            return strlen($word) > 2 && ! in_array($word, $stopwords);
        });

        return array_values($words);
    }

    /**
     * Map sentiment to numerical score
     */
    protected function mapSentimentToScore($sentiment)
    {
        return match ($sentiment) {
            'positive' => 1.0,
            'negative' => 0.0,
            'neutral' => 0.5,
            default => 0.5
        };
    }

    /**
     * Get overall sentiment from distribution
     */
    protected function getOverallSentiment($sentiments)
    {
        $max = max($sentiments);

        foreach ($sentiments as $sentiment => $count) {
            if ($count === $max) {
                return $sentiment;
            }
        }

        return 'neutral';
    }

    /**
     * Get model statistics
     */
    public function getModelStats()
    {
        return [
            'vocabulary_size' => count($this->vocabulary),
            'classes' => array_keys($this->priors),
            'priors' => $this->priors,
            'training_samples' => array_map('count', $this->trainingData),
        ];
    }
}
