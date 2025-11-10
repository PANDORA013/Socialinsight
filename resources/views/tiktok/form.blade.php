@extends('layouts.app')

@section('title', 'TikTok Analysis')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h4 class="mb-0">🎵 Analyze TikTok Comments</h4>
            </div>
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" fill="currentColor" class="bi bi-tiktok text-muted" viewBox="0 0 16 16">
                        <path d="M9 0h1.98c.144.715.54 1.617 1.235 2.512C12.895 3.389 13.797 4 15 4v2c-1.753 0-3.07-.814-4-1.829V11a5 5 0 1 1-5-5v2a3 3 0 1 0 3 3V0Z"/>
                    </svg>
                </div>
                <h3 class="mb-3">Coming Soon!</h3>
                <p class="text-muted mb-4">
                    TikTok integration is currently under development. This feature will allow you to analyze comments and engagement from TikTok videos.
                </p>
                <p class="text-muted small">
                    <strong>Note:</strong> TikTok API access requires a developer account and app approval. We're working on implementing this feature.
                </p>
                <a href="{{ route('dashboard') }}" class="btn btn-primary mt-3">
                    Back to Dashboard
                </a>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">🔜 Upcoming Features</h5>
                <ul>
                    <li>Analyze TikTok video comments</li>
                    <li>Track video performance metrics</li>
                    <li>Sentiment analysis for comments</li>
                    <li>Trending content analysis</li>
                    <li>Engagement rate tracking</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
