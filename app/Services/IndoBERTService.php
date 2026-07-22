<?php

namespace App\Services;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * IndoBERT Sentiment Analysis Service
 *
 * Service ini mengintegrasikan model Deep Learning IndoBERT
 * untuk analisis sentimen berbahasa Indonesia yang lebih akurat.
 *
 * Features:
 * - Deep Learning based sentiment analysis
 * - Support untuk teks panjang (auto truncation)
 * - Confidence score untuk setiap prediksi
 * - Fallback ke Naive Bayes jika IndoBERT tidak tersedia
 */
class IndoBERTService
{
    /**
     * Path ke Python interpreter
     *
     * Windows: 'python' atau 'python.exe' atau 'C:\Python\python.exe'
     * Linux/Mac: 'python3' atau '/usr/bin/python3'
     */
    private $pythonPath;

    /**
     * Path ke script analyze.py
     */
    private $scriptPath;

    /**
     * Timeout untuk eksekusi (dalam detik)
     */
    private $timeout;

    /**
     * Flag untuk check apakah IndoBERT available
     */
    private $isAvailable = null;

    public function __construct()
    {
        // Ambil konfigurasi dari .env atau gunakan default
        $this->pythonPath = env('PYTHON_PATH', 'python3');
        $this->scriptPath = storage_path('app/python/analyze.py');
        $this->timeout = env('INDOBERT_TIMEOUT', 60);
    }

    /**
     * Analisis sentimen menggunakan IndoBERT
     *
     * @param  string  $text  Teks yang akan dianalisis
     * @return array Result dengan format:
     *               [
     *               'status' => 'success'|'error',
     *               'label' => 'positive'|'neutral'|'negative',
     *               'score' => float (0-1),
     *               'details' => ['positive' => float, 'neutral' => float, 'negative' => float]
     *               ]
     */
    public function analyzeSentiment($text)
    {
        // Validasi input
        if (empty($text) || ! is_string($text)) {
            return [
                'status' => 'error',
                'message' => 'Text input tidak valid',
            ];
        }

        // Check apakah IndoBERT tersedia
        if (! $this->checkAvailability()) {
            return [
                'status' => 'error',
                'message' => 'IndoBERT tidak tersedia. Install Python dependencies: pip install transformers torch',
            ];
        }

        try {
            // Setup process
            $process = new Process([
                $this->pythonPath,
                $this->scriptPath,
                $text,
            ]);

            $process->setTimeout($this->timeout);

            // Jalankan process
            $process->mustRun();

            // Ambil output
            $output = $process->getOutput();

            // Parse JSON
            $result = json_decode($output, true);

            // Validasi hasil
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'status' => 'error',
                    'message' => 'Gagal mem-parsing output Python',
                    'raw_output' => $output,
                ];
            }

            return $result;

        } catch (ProcessFailedException $exception) {
            return [
                'status' => 'error',
                'message' => 'Proses Python gagal dieksekusi',
                'details' => $exception->getMessage(),
            ];
        } catch (\Exception $exception) {
            return [
                'status' => 'error',
                'message' => 'Error tidak terduga',
                'details' => $exception->getMessage(),
            ];
        }
    }

    /**
     * Batch analysis untuk multiple texts
     *
     * CATATAN: Ini akan LAMBAT karena setiap text memuat model dari awal.
     * Untuk production, gunakan Flask API approach.
     *
     * @param  array  $texts  Array of texts
     * @return array Array of results
     */
    public function analyzeBatch(array $texts)
    {
        $results = [];

        foreach ($texts as $text) {
            $results[] = $this->analyzeSentiment($text);
        }

        return $results;
    }

    /**
     * Check apakah IndoBERT tersedia dan bisa digunakan
     *
     * @return bool
     */
    public function checkAvailability()
    {
        // Cache result untuk menghindari check berulang
        if ($this->isAvailable !== null) {
            return $this->isAvailable;
        }

        try {
            // Check apakah script Python exists
            if (! file_exists($this->scriptPath)) {
                $this->isAvailable = false;

                return false;
            }

            // Test dengan teks sederhana
            $testText = 'Test';
            $process = new Process([
                $this->pythonPath,
                $this->scriptPath,
                $testText,
            ]);

            $process->setTimeout(10); // Short timeout untuk test
            $process->run();

            // Check apakah berhasil
            if ($process->isSuccessful()) {
                $output = $process->getOutput();
                $result = json_decode($output, true);

                $this->isAvailable = (
                    $result !== null &&
                    isset($result['status']) &&
                    $result['status'] === 'success'
                );
            } else {
                $this->isAvailable = false;
            }

        } catch (\Exception $e) {
            $this->isAvailable = false;
        }

        return $this->isAvailable;
    }

    /**
     * Get status IndoBERT
     *
     * @return array
     */
    public function getStatus()
    {
        $available = $this->checkAvailability();

        return [
            'available' => $available,
            'python_path' => $this->pythonPath,
            'script_path' => $this->scriptPath,
            'script_exists' => file_exists($this->scriptPath),
            'message' => $available
                ? 'IndoBERT siap digunakan'
                : 'IndoBERT tidak tersedia. Install: pip install transformers torch',
        ];
    }

    /**
     * Convert IndoBERT result ke format yang sama dengan Naive Bayes
     * (untuk kompatibilitas dengan kode existing)
     *
     * @param  array  $indoBertResult
     * @return string 'positive'|'negative'|'neutral'
     */
    public function convertToSimpleLabel($indoBertResult)
    {
        if ($indoBertResult['status'] === 'success') {
            return $indoBertResult['label'];
        }

        return 'neutral'; // Default fallback
    }
}
