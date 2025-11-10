@extends('layouts.app')

@section('title', 'Twitter/X Analysis')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header text-white" style="background-color: #1DA1F2;">
                <h4 class="mb-0">🐦 Analyze Twitter/X Replies</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('twitter.analyze') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="tweet_url" class="form-label fw-bold">Twitter/X Tweet URL</label>
                        <input 
                            type="url" 
                            class="form-control @error('tweet_url') is-invalid @enderror" 
                            id="tweet_url" 
                            name="tweet_url" 
                            placeholder="https://twitter.com/username/status/..."
                            value="{{ old('tweet_url') }}"
                            required
                        >
                        @error('tweet_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Paste the full URL of a Twitter/X tweet to analyze its replies.
                        </small>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-lg" style="background-color: #1DA1F2; color: white;">
                            🔍 Analyze Replies
                        </button>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">ℹ️ How it works</h5>
                <ol class="mb-0">
                    <li>Paste a Twitter/X tweet URL in the form above</li>
                    <li>We'll fetch the replies to that tweet</li>
                    <li>Each reply will be analyzed for sentiment (positive, negative, or neutral)</li>
                    <li>Results will be saved and displayed in your dashboard</li>
                </ol>
                
                <div class="alert alert-info mt-3 mb-0">
                    <strong>Note:</strong> Twitter API requires elevated access to fetch all replies. 
                    If you have basic access, you'll see limited results.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
