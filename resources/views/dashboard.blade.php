@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1 class="display-4 fw-bold">Dashboard</h1>
        <p class="text-muted">Social media sentiment analysis overview</p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total Posts</h5>
                <h2 class="display-4">{{ $stats['total_posts'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Positive</h5>
                <h2 class="display-4">{{ $stats['positive'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h5 class="card-title">Negative</h5>
                <h2 class="display-4">{{ $stats['negative'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5 class="card-title">Neutral</h5>
                <h2 class="display-4">{{ $stats['neutral'] }}</h2>
            </div>
        </div>
    </div>
</div>

<!-- Recent Posts -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white">
                <h4 class="mb-0">Recent Posts</h4>
            </div>
            <div class="card-body">
                @if($posts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Platform</th>
                                    <th>Author</th>
                                    <th>Content</th>
                                    <th>Sentiment</th>
                                    <th>Score</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($posts as $post)
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary">{{ strtoupper($post->platform) }}</span>
                                        </td>
                                        <td>{{ $post->author }}</td>
                                        <td>{{ Str::limit($post->content, 50) }}</td>
                                        <td>
                                            <span class="badge sentiment-{{ $post->sentiment }}">
                                                {{ ucfirst($post->sentiment) }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($post->sentiment_score, 2) }}</td>
                                        <td>{{ $post->created_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <p class="text-muted">No posts analyzed yet. Start by analyzing some social media content!</p>
                        <a href="{{ route('youtube.form') }}" class="btn btn-primary">Analyze YouTube Comments</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
