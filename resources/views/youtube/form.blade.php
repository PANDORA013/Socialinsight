@extends('layouts.app')

@section('title', 'YouTube Analysis')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0">📺 Analyze YouTube Comments</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('youtube.analyze') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="video_url" class="form-label fw-bold">YouTube Video URL</label>
                        <input 
                            type="url" 
                            class="form-control @error('video_url') is-invalid @enderror" 
                            id="video_url" 
                            name="video_url" 
                            placeholder="https://www.youtube.com/watch?v=..."
                            value="{{ old('video_url') }}"
                            required
                        >
                        @error('video_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Paste the full URL of a YouTube video to analyze its comments.
                        </small>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger btn-lg">
                            🔍 Analyze Comments
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
                    <li>Paste a YouTube video URL in the form above</li>
                    <li>We'll fetch the comments from that video</li>
                    <li>Each comment will be analyzed for sentiment (positive, negative, or neutral)</li>
                    <li>Results will be saved and displayed in your dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection
