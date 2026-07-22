<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard', [
            'platformStatuses' => [
                'youtube' => ['label' => 'YouTube', 'mode' => config('services.youtube.api_key') ? 'real' : 'demo'],
                'twitter' => ['label' => 'Twitter/X', 'mode' => 'maintenance'],
                'tiktok' => ['label' => 'TikTok', 'mode' => 'maintenance'],
                'instagram' => ['label' => 'Instagram', 'mode' => 'maintenance'],
            ],
            'pipelineSteps' => ['Ambil sinyal', 'Filter relevansi', 'Baca sentimen', 'Susun rekomendasi'],
            'demoMetrics' => ['platforms' => 4, 'maxEvidence' => 100, 'cacheMinutes' => 30],
        ]);
    }
}
