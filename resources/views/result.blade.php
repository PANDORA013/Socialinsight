@extends('layouts.app-tailwind')

@section('title', 'Hasil Analisis')

@section('content')
<div class="container mx-auto px-6 py-10">
  <!-- Header -->
  <div class="mb-8">
    <a href="{{ route('home') }}" class="inline-flex items-center text-blue-600 hover:text-blue-700 mb-4">
      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
      </svg>
      Kembali ke Home
    </a>
    
    <h2 class="text-4xl font-bold text-gray-800 mb-2">
      🔍 Hasil Analisis Tren
    </h2>
    <p class="text-xl text-gray-600">
      Topik: <span class="font-semibold text-blue-600">"{{ $topic }}"</span>
    </p>
    <p class="text-sm text-gray-500 mt-2">
      Ditemukan <strong>{{ $totalPosts }}</strong> hasil dari {{ count($platforms) }} platform
    </p>

    <!-- Data Source Indicator -->
    <div class="mt-4 flex flex-wrap gap-2">
      <!-- YouTube Status -->
      <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-100 border border-red-300 rounded-full text-xs font-semibold text-red-800">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        📺 YouTube: Real-time API Data ✨
      </div>
      
      <!-- TikTok Status -->
      @if(session('tiktok_connected'))
        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-purple-100 border border-purple-300 rounded-full text-xs font-semibold text-purple-800">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          🎵 TikTok: Connected ({{ session('tiktok_user')['nickname'] ?? 'User' }})
        </div>
      @else
        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-100 border border-gray-300 rounded-full text-xs font-semibold text-gray-700">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          🎵 TikTok: Sample Data
          <a href="{{ route('home') }}" class="underline hover:text-gray-900">Connect for real-time</a>
        </div>
      @endif
      
      <!-- Instagram Status -->
      <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-100 border border-gray-300 rounded-full text-xs font-semibold text-gray-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        📸 Instagram: Sample Data
      </div>
    </div>
  </div>

  <!-- Summary Stats -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
    <div class="bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-2xl border border-green-200">
      <div class="flex items-center justify-between mb-2">
        <span class="text-3xl">😊</span>
        <span class="text-2xl font-bold text-green-600">{{ $sentimentSummary['positive'] ?? 0 }}</span>
      </div>
      <p class="text-sm font-medium text-green-700">Positif</p>
    </div>

    <div class="bg-gradient-to-br from-red-50 to-red-100 p-6 rounded-2xl border border-red-200">
      <div class="flex items-center justify-between mb-2">
        <span class="text-3xl">😞</span>
        <span class="text-2xl font-bold text-red-600">{{ $sentimentSummary['negative'] ?? 0 }}</span>
      </div>
      <p class="text-sm font-medium text-red-700">Negatif</p>
    </div>

    <div class="bg-gradient-to-br from-amber-50 to-amber-100 p-6 rounded-2xl border border-amber-200">
      <div class="flex items-center justify-between mb-2">
        <span class="text-3xl">😐</span>
        <span class="text-2xl font-bold text-amber-600">{{ $sentimentSummary['neutral'] ?? 0 }}</span>
      </div>
      <p class="text-sm font-medium text-amber-700">Netral</p>
    </div>

    <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-2xl border border-blue-200">
      <div class="flex items-center justify-between mb-2">
        <span class="text-3xl">📊</span>
        <span class="text-2xl font-bold text-blue-600">{{ $totalPosts }}</span>
      </div>
      <p class="text-sm font-medium text-blue-700">Total Posts</p>
    </div>
  </div>

  <!-- Charts Section -->
  <div class="grid md:grid-cols-2 gap-6 mb-10">
    <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100">
      <h3 class="font-semibold text-lg mb-4 text-gray-800 flex items-center">
        <span class="w-2 h-2 bg-blue-600 rounded-full mr-2"></span>
        Distribusi Sentimen
      </h3>
      <div class="h-64">
        <canvas id="sentimentChart"></canvas>
      </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100">
      <h3 class="font-semibold text-lg mb-4 text-gray-800 flex items-center">
        <span class="w-2 h-2 bg-purple-600 rounded-full mr-2"></span>
        Popularitas per Platform
      </h3>
      <div class="h-64">
        <canvas id="platformChart"></canvas>
      </div>
    </div>
  </div>

  <!-- AI Comprehensive Insights -->
  @if(isset($aiInsights))
  <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-200 mb-10">
    <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
      <span class="text-3xl mr-3">🤖</span>
      AI Comprehensive Insights
    </h3>

    <!-- Overview -->
    @if(isset($aiInsights['overview']))
    <div class="mb-8 p-6 bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl border border-blue-200">
      <h4 class="font-bold text-lg text-gray-800 mb-3">📋 Overview - Penjelasan Mendalam</h4>
      <p class="text-gray-700 leading-relaxed text-justify mb-4">{{ $aiInsights['overview']['summary'] ?? '' }}</p>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
        <div class="text-center bg-white p-3 rounded-lg shadow-sm">
          <div class="text-2xl font-bold text-blue-600">{{ $aiInsights['overview']['total_analyzed'] ?? 0 }}</div>
          <div class="text-xs text-gray-600">Total Analyzed</div>
        </div>
        <div class="text-center bg-white p-3 rounded-lg shadow-sm">
          <div class="text-2xl font-bold text-green-600">{{ $aiInsights['overview']['data_quality'] ?? '0%' }}</div>
          <div class="text-xs text-gray-600">Data Quality</div>
        </div>
        <div class="text-center bg-white p-3 rounded-lg shadow-sm">
          <div class="text-2xl font-bold text-purple-600">{{ number_format($aiInsights['overview']['sentiment_score'] ?? 0, 2) }}</div>
          <div class="text-xs text-gray-600">Sentiment Score</div>
        </div>
        <div class="text-center bg-white p-3 rounded-lg shadow-sm">
          <div class="text-2xl font-bold text-indigo-600">{{ $aiInsights['overview']['dominant_sentiment'] ?? 'N/A' }}</div>
          <div class="text-xs text-gray-600">Dominant</div>
        </div>
      </div>
    </div>
    @endif

    <!-- Personalized Notes -->
    @if(isset($aiInsights['personalized_notes']) && $aiInsights['personalized_notes'])
    <div class="mb-8 p-6 bg-gradient-to-r from-green-50 to-blue-50 rounded-xl border border-green-200">
      <h4 class="font-bold text-lg text-gray-800 mb-4">🎵 Personalized Notes - {{ $aiInsights['personalized_notes']['category'] ?? 'General' }}</h4>
      
      @if(isset($aiInsights['personalized_notes']['notes']))
      <div class="space-y-4">
        @foreach($aiInsights['personalized_notes']['notes'] as $noteKey => $note)
        <div class="bg-white p-4 rounded-lg border border-gray-200">
          <div class="flex items-start">
            <span class="text-2xl mr-3">{{ $note['icon'] ?? '💡' }}</span>
            <div class="flex-1">
              <h5 class="font-semibold text-gray-800 mb-1">{{ $note['title'] ?? '' }}</h5>
              @if(isset($note['value']))
              <p class="text-sm text-blue-600 font-medium mb-1">{{ $note['value'] }}</p>
              @endif
              <p class="text-sm text-gray-600">{{ $note['explanation'] ?? '' }}</p>
            </div>
          </div>
        </div>
        @endforeach
      </div>
      @endif
    </div>
    @endif

    <!-- Recommendations -->
    @if(isset($aiInsights['recommendations']) && count($aiInsights['recommendations']) > 0)
    <div class="p-6 bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl border border-yellow-200">
      <h4 class="font-bold text-lg text-gray-800 mb-4">💡 Actionable Recommendations</h4>
      
      <div class="space-y-6">
        @foreach($aiInsights['recommendations'] as $index => $recommendation)
        <div class="bg-white p-5 rounded-lg border border-gray-200">
          <h5 class="font-semibold text-gray-800 mb-3">{{ $index + 1 }}. {{ $recommendation['title'] ?? '' }}</h5>
          <p class="text-sm text-gray-700 mb-4">{{ $recommendation['description'] ?? '' }}</p>
          
          <!-- Evidence -->
          @if(isset($recommendation['evidence']))
          <div class="bg-blue-50 p-4 rounded-lg mb-4 border border-blue-200">
            <h6 class="font-semibold text-sm text-blue-800 mb-2">📊 BUKTI KONKRIT:</h6>
            
            @if(isset($recommendation['evidence']['top_posts']) && count($recommendation['evidence']['top_posts']) > 0)
            <div class="mb-3">
              <p class="text-xs font-semibold text-gray-700 mb-2">📊 Top Performing Posts:</p>
              @foreach($recommendation['evidence']['top_posts'] as $post)
              <div class="bg-gradient-to-br from-white to-gray-50 p-4 rounded-lg border border-gray-200 mb-2 hover:shadow-md transition">
                <div class="flex items-start justify-between gap-3">
                  <div class="flex-1">
                    <!-- Platform Badge & Author -->
                    <div class="flex items-center gap-2 mb-2">
                      @php
                      $platformIcons = [
                        'tiktok' => '🎵',
                        'instagram' => '📸',
                        'youtube' => '📺',
                        'twitter' => '🐦'
                      ];
                      $platformColors = [
                        'tiktok' => 'bg-purple-100 text-purple-700 border-purple-300',
                        'instagram' => 'bg-pink-100 text-pink-700 border-pink-300',
                        'youtube' => 'bg-red-100 text-red-700 border-red-300',
                        'twitter' => 'bg-blue-100 text-blue-700 border-blue-300'
                      ];
                      $platform = strtolower($post['platform'] ?? 'unknown');
                      $platformIcon = $platformIcons[$platform] ?? '📱';
                      $platformColor = $platformColors[$platform] ?? 'bg-gray-100 text-gray-700 border-gray-300';
                      @endphp
                      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold {{ $platformColor }} border shadow-sm">
                        {{ $platformIcon }} {{ ucfirst($post['platform'] ?? 'N/A') }}
                      </span>
                      <div class="flex items-center gap-1">
                        <span class="text-xs font-semibold text-gray-700">@{{ $post['author'] ?? 'N/A' }}</span>
                      </div>
                    </div>
                    
                    <!-- Content Preview -->
                    <div class="text-xs text-gray-800 mb-3 leading-relaxed bg-white p-2 rounded border border-gray-100">
                      "{{ Str::limit($post['content'] ?? '', 120) }}"
                    </div>
                    
                    <!-- Engagement Metrics -->
                    <div class="flex items-center gap-4 text-xs">
                      <div class="flex items-center gap-1 text-red-600 font-semibold">
                        <span>❤️</span>
                        <span>{{ number_format($post['engagement']['likes'] ?? 0) }}</span>
                      </div>
                      <div class="flex items-center gap-1 text-blue-600 font-semibold">
                        <span>💬</span>
                        <span>{{ number_format($post['engagement']['comments'] ?? 0) }}</span>
                      </div>
                      @if(isset($post['engagement']['views']) && $post['engagement']['views'] > 0)
                      <div class="flex items-center gap-1 text-gray-600 font-semibold">
                        <span>👁️</span>
                        <span>{{ number_format($post['engagement']['views']) }}</span>
                      </div>
                      @endif
                    </div>
                  </div>
                  
                  <!-- Action Button -->
                  @if(isset($post['link']) && $post['link'] !== '#')
                  <a href="{{ $post['link'] }}" target="_blank" 
                     class="flex-shrink-0 bg-gradient-to-r from-blue-500 to-purple-600 text-white px-4 py-2.5 rounded-lg text-xs font-bold hover:from-blue-600 hover:to-purple-700 transition-all flex flex-col items-center gap-1 shadow-lg hover:shadow-xl min-w-[90px]"
                     title="Klik untuk cari konten serupa di {{ ucfirst($post['platform']) }}">
                    @php
                    $platformIcon = $platformIcons[$platform] ?? '📱';
                    @endphp
                    <span class="text-lg">{{ $platformIcon }}</span>
                    <span class="text-[10px] leading-tight text-center">Buka<br>{{ ucfirst($post['platform']) }}</span>
                  </a>
                  @endif
                </div>
              </div>
              @endforeach
            </div>
            @endif
            
            @if(isset($recommendation['evidence']['supporting_comments']) && count($recommendation['evidence']['supporting_comments']) > 0)
            <div class="mb-3">
              <p class="text-xs font-semibold text-gray-700 mb-2">💬 Supporting Comments:</p>
              @foreach($recommendation['evidence']['supporting_comments'] as $comment)
              <div class="bg-gradient-to-br from-green-50 to-teal-50 p-3 rounded-lg border border-green-200 mb-2 hover:shadow-md transition">
                <div class="flex items-start gap-2">
                  @php
                  $platformIcons = [
                    'tiktok' => '🎵',
                    'instagram' => '📸',
                    'youtube' => '📺',
                    'twitter' => '🐦'
                  ];
                  $platformColors = [
                    'tiktok' => 'bg-purple-100 text-purple-700 border-purple-300',
                    'instagram' => 'bg-pink-100 text-pink-700 border-pink-300',
                    'youtube' => 'bg-red-100 text-red-700 border-red-300',
                    'twitter' => 'bg-blue-100 text-blue-700 border-blue-300'
                  ];
                  $platform = strtolower($comment['platform'] ?? 'unknown');
                  $platformIcon = $platformIcons[$platform] ?? '📱';
                  $platformColor = $platformColors[$platform] ?? 'bg-gray-100 text-gray-700 border-gray-300';
                  @endphp
                  <div class="flex-1">
                    <!-- Platform Badge & Author -->
                    <div class="flex items-center gap-2 mb-2">
                      <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold {{ $platformColor }} border shadow-sm">
                        {{ $platformIcon }} {{ ucfirst($comment['platform'] ?? 'N/A') }}
                      </span>
                      <span class="text-xs font-semibold text-gray-700">@{{ $comment['author'] ?? 'N/A' }}</span>
                    </div>
                    
                    <!-- Comment Content -->
                    <p class="text-xs text-gray-800 leading-relaxed bg-white p-2 rounded border border-gray-100 mb-2">"{{ $comment['content'] ?? '' }}"</p>
                    
                    <!-- Engagement Metrics -->
                    @if(isset($comment['engagement']))
                    <div class="flex items-center gap-3 text-xs">
                      <div class="flex items-center gap-1 text-red-600 font-semibold">
                        <span>❤️</span>
                        <span>{{ number_format($comment['engagement']['likes'] ?? 0) }}</span>
                      </div>
                      @if(isset($comment['engagement']['comments']) && $comment['engagement']['comments'] > 0)
                      <div class="flex items-center gap-1 text-blue-600 font-semibold">
                        <span>💬</span>
                        <span>{{ number_format($comment['engagement']['comments']) }}</span>
                      </div>
                      @endif
                    </div>
                    @endif
                  </div>
                  
                  <!-- Action Button -->
                  @if(isset($comment['link']) && $comment['link'] !== '#')
                  <a href="{{ $comment['link'] }}" target="_blank" 
                     class="flex-shrink-0 bg-gradient-to-r from-green-500 to-teal-600 text-white px-3 py-2 rounded-lg text-xs font-bold hover:from-green-600 hover:to-teal-700 transition-all flex flex-col items-center gap-0.5 shadow-lg hover:shadow-xl min-w-[70px]"
                     title="Klik untuk cari konten serupa di {{ ucfirst($comment['platform']) }}">
                    <span class="text-base">{{ $platformIcon }}</span>
                    <span class="text-[9px] leading-tight">Buka</span>
                  </a>
                  @endif
                </div>
              </div>
              @endforeach
            </div>
            @endif
            
            {{-- NEW: Top YouTube Videos by Views --}}
            @if(isset($recommendation['evidence']['top_youtube_videos']) && count($recommendation['evidence']['top_youtube_videos']) > 0)
            <div class="mb-4">
              <p class="text-xs font-semibold text-gray-700 mb-2 flex items-center gap-2">
                <span class="text-base">📺</span>
                Top YouTube Videos (by Views):
              </p>
              @foreach($recommendation['evidence']['top_youtube_videos'] as $video)
              <div class="bg-gradient-to-br from-red-50 to-orange-50 p-4 rounded-lg border border-red-200 mb-2 hover:shadow-md transition">
                <div class="flex items-start justify-between gap-3">
                  <div class="flex-1">
                    <!-- YouTube Badge -->
                    <div class="flex items-center gap-2 mb-2">
                      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 border-red-300 border shadow-sm">
                        📺 YouTube
                      </span>
                      <span class="text-xs font-semibold text-gray-700">{{ $video['channel'] ?? 'Unknown Channel' }}</span>
                    </div>
                    
                    <!-- Video Title -->
                    <div class="text-sm font-semibold text-gray-800 mb-2 leading-relaxed bg-white p-2 rounded border border-gray-100">
                      "{{ Str::limit($video['title'] ?? '', 100) }}"
                    </div>
                    
                    <!-- Stats -->
                    <div class="flex items-center gap-4 text-xs">
                      <div class="flex items-center gap-1 text-red-600 font-bold">
                        <span>👁️</span>
                        <span>{{ number_format($video['views'] ?? 0) }} views</span>
                      </div>
                      <div class="flex items-center gap-1 text-pink-600 font-semibold">
                        <span>❤️</span>
                        <span>{{ number_format($video['likes'] ?? 0) }}</span>
                      </div>
                      <div class="flex items-center gap-1 text-blue-600 font-semibold">
                        <span>💬</span>
                        <span>{{ number_format($video['comments'] ?? 0) }}</span>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Action Button -->
                  @if(isset($video['link']) && $video['link'] !== '#')
                  <a href="{{ $video['link'] }}" target="_blank" 
                     class="flex-shrink-0 bg-gradient-to-r from-red-500 to-red-600 text-white px-4 py-2.5 rounded-lg text-xs font-bold hover:from-red-600 hover:to-red-700 transition-all flex flex-col items-center gap-1 shadow-lg hover:shadow-xl min-w-[90px]"
                     title="Cari video serupa di YouTube">
                    <span class="text-lg">📺</span>
                    <span class="text-[10px] leading-tight text-center">Cari di<br>YouTube</span>
                  </a>
                  @endif
                </div>
              </div>
              @endforeach
            </div>
            @endif
            
            {{-- NEW: Top Comments by Likes --}}
            @if(isset($recommendation['evidence']['top_comments']) && count($recommendation['evidence']['top_comments']) > 0)
            <div class="mb-4">
              <p class="text-xs font-semibold text-gray-700 mb-2 flex items-center gap-2">
                <span class="text-base">💬</span>
                Top Comments (by Likes):
              </p>
              @foreach($recommendation['evidence']['top_comments'] as $comment)
              <div class="bg-gradient-to-br from-purple-50 to-pink-50 p-4 rounded-lg border border-purple-200 mb-2 hover:shadow-md transition">
                <div class="flex items-start justify-between gap-3">
                  <div class="flex-1">
                    <!-- Platform Badge & Author -->
                    <div class="flex items-center gap-2 mb-2">
                      @php
                      $platformIcons = [
                        'tiktok' => '🎵',
                        'instagram' => '📸',
                        'youtube' => '📺',
                        'twitter' => '🐦'
                      ];
                      $platformColors = [
                        'tiktok' => 'bg-purple-100 text-purple-700 border-purple-300',
                        'instagram' => 'bg-pink-100 text-pink-700 border-pink-300',
                        'youtube' => 'bg-red-100 text-red-700 border-red-300',
                        'twitter' => 'bg-blue-100 text-blue-700 border-blue-300'
                      ];
                      $platform = strtolower($comment['platform'] ?? 'unknown');
                      $platformIcon = $platformIcons[$platform] ?? '📱';
                      $platformColor = $platformColors[$platform] ?? 'bg-gray-100 text-gray-700 border-gray-300';
                      @endphp
                      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold {{ $platformColor }} border shadow-sm">
                        {{ $platformIcon }} {{ ucfirst($comment['platform'] ?? 'N/A') }}
                      </span>
                      <span class="text-xs font-semibold text-gray-700">@{{ $comment['author'] ?? 'Anonymous' }}</span>
                    </div>
                    
                    <!-- Comment Content -->
                    <div class="text-sm text-gray-800 mb-2 leading-relaxed bg-white p-3 rounded border border-gray-100">
                      "{{ $comment['content'] ?? '' }}"
                    </div>
                    
                    <!-- Likes Count (Prominent) -->
                    <div class="flex items-center gap-4 text-xs">
                      <div class="flex items-center gap-1 text-pink-600 font-bold bg-pink-100 px-3 py-1 rounded-full">
                        <span>❤️</span>
                        <span>{{ number_format($comment['likes'] ?? 0) }} likes</span>
                      </div>
                      @if(isset($comment['sentiment']))
                      @php
                      $sentimentColors = [
                        'positive' => 'bg-green-100 text-green-700',
                        'negative' => 'bg-red-100 text-red-700',
                        'neutral' => 'bg-gray-100 text-gray-700'
                      ];
                      $sentimentColor = $sentimentColors[$comment['sentiment']] ?? 'bg-gray-100 text-gray-700';
                      @endphp
                      <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $sentimentColor }}">
                        {{ ucfirst($comment['sentiment']) }}
                      </span>
                      @endif
                    </div>
                  </div>
                  
                  <!-- Action Button -->
                  @if(isset($comment['link']) && $comment['link'] !== '#')
                  <a href="{{ $comment['link'] }}" target="_blank" 
                     class="flex-shrink-0 bg-gradient-to-r from-purple-500 to-pink-600 text-white px-4 py-2.5 rounded-lg text-xs font-bold hover:from-purple-600 hover:to-pink-700 transition-all flex flex-col items-center gap-1 shadow-lg hover:shadow-xl min-w-[90px]"
                     title="Lihat konten ini di {{ ucfirst($comment['platform']) }}">
                    <span class="text-lg">{{ $platformIcon }}</span>
                    <span class="text-[10px] leading-tight text-center">Lihat di<br>{{ ucfirst($comment['platform']) }}</span>
                  </a>
                  @endif
                </div>
              </div>
              @endforeach
            </div>
            @endif
            
            @if(isset($recommendation['evidence']['viral_examples']) && count($recommendation['evidence']['viral_examples']) > 0)
            <div class="mb-3">
              <p class="text-xs font-semibold text-gray-700 mb-1">🔥 Viral Examples:</p>
              @foreach($recommendation['evidence']['viral_examples'] as $viral)
              <div class="text-xs text-gray-600 ml-2 mb-1">
                • Virality Score: {{ number_format($viral['virality_score'] ?? 0) }} 🔥<br>
                &nbsp;&nbsp;Why: {{ $viral['why_viral'] ?? '' }}
              </div>
              @endforeach
            </div>
            @endif
            
            @if(isset($recommendation['evidence']['critical_posts']) && count($recommendation['evidence']['critical_posts']) > 0)
            <div class="mb-3">
              <p class="text-xs font-semibold text-red-700 mb-2">⚠️ Critical Feedback Posts:</p>
              @foreach($recommendation['evidence']['critical_posts'] as $post)
              <div class="bg-gradient-to-br from-red-50 to-orange-50 p-3 rounded-lg border border-red-300 mb-2 hover:shadow-md transition">
                <div class="flex items-start gap-2">
                  @php
                  $platformIcons = [
                    'tiktok' => '🎵',
                    'instagram' => '📸',
                    'youtube' => '📺',
                    'twitter' => '🐦'
                  ];
                  $platformColors = [
                    'tiktok' => 'bg-purple-100 text-purple-700 border-purple-300',
                    'instagram' => 'bg-pink-100 text-pink-700 border-pink-300',
                    'youtube' => 'bg-red-100 text-red-700 border-red-300',
                    'twitter' => 'bg-blue-100 text-blue-700 border-blue-300'
                  ];
                  $platform = strtolower($post['platform'] ?? 'unknown');
                  $platformIcon = $platformIcons[$platform] ?? '📱';
                  $platformColor = $platformColors[$platform] ?? 'bg-gray-100 text-gray-700 border-gray-300';
                  @endphp
                  <div class="flex-1">
                    <!-- Platform Badge & Author -->
                    <div class="flex items-center gap-2 mb-2">
                      <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold {{ $platformColor }} border shadow-sm">
                        {{ $platformIcon }} {{ ucfirst($post['platform'] ?? 'N/A') }}
                      </span>
                      <span class="text-xs font-semibold text-gray-700">@{{ $post['author'] ?? 'N/A' }}</span>
                    </div>
                    
                    <!-- Content -->
                    <p class="text-xs text-gray-800 leading-relaxed bg-white p-2 rounded border border-gray-100 mb-2">"{{ Str::limit($post['content'] ?? '', 100) }}"</p>
                    
                    <!-- Engagement -->
                    @if(isset($post['engagement']))
                    <div class="flex items-center gap-3 text-xs">
                      <div class="flex items-center gap-1 text-red-600 font-semibold">
                        <span>❤️</span>
                        <span>{{ number_format($post['engagement']['likes'] ?? 0) }}</span>
                      </div>
                      <div class="flex items-center gap-1 text-blue-600 font-semibold">
                        <span>💬</span>
                        <span>{{ number_format($post['engagement']['comments'] ?? 0) }}</span>
                      </div>
                    </div>
                    @endif
                  </div>
                  
                  <!-- Action Button -->
                  @if(isset($post['link']) && $post['link'] !== '#')
                  <a href="{{ $post['link'] }}" target="_blank" 
                     class="flex-shrink-0 bg-gradient-to-r from-red-500 to-orange-600 text-white px-3 py-2 rounded-lg text-xs font-bold hover:from-red-600 hover:to-orange-700 transition-all flex flex-col items-center gap-0.5 shadow-lg hover:shadow-xl min-w-[70px]"
                     title="Klik untuk cari konten di {{ ucfirst($post['platform']) }}">
                    <span class="text-base">{{ $platformIcon }}</span>
                    <span class="text-[9px] leading-tight">Buka</span>
                  </a>
                  @endif
                </div>
              </div>
              @endforeach
            </div>
            @endif
            
            @if(isset($recommendation['evidence']['critical_comments']) && count($recommendation['evidence']['critical_comments']) > 0)
            <div class="mb-3">
              <p class="text-xs font-semibold text-red-700 mb-2">💬 Critical Comments:</p>
              @foreach($recommendation['evidence']['critical_comments'] as $comment)
              <div class="bg-red-50 p-3 rounded-lg border border-red-200 mb-2">
                <div class="flex items-start gap-2">
                  @php
                  $platformIcons = [
                    'tiktok' => '🎵',
                    'instagram' => '📸',
                    'youtube' => '📺',
                    'twitter' => '🐦'
                  ];
                  $platformColors = [
                    'tiktok' => 'bg-purple-100 text-purple-700 border-purple-300',
                    'instagram' => 'bg-pink-100 text-pink-700 border-pink-300',
                    'youtube' => 'bg-red-100 text-red-700 border-red-300',
                    'twitter' => 'bg-blue-100 text-blue-700 border-blue-300'
                  ];
                  $platform = strtolower($comment['platform'] ?? 'unknown');
                  $platformIcon = $platformIcons[$platform] ?? '📱';
                  $platformColor = $platformColors[$platform] ?? 'bg-gray-100 text-gray-700 border-gray-300';
                  @endphp
                  <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                      <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold {{ $platformColor }} border">
                        {{ $platformIcon }} {{ ucfirst($comment['platform'] ?? 'N/A') }}
                      </span>
                      <span class="text-xs text-gray-500">@{{ $comment['author'] ?? 'N/A' }}</span>
                    </div>
                    <p class="text-xs text-gray-700 leading-relaxed">"{{ $comment['content'] ?? '' }}"</p>
                  </div>
                </div>
              </div>
              @endforeach
            </div>
            @endif
            
            @if(isset($recommendation['evidence']['best_performing_platforms']) && count($recommendation['evidence']['best_performing_platforms']) > 0)
            <div class="mb-3">
              <p class="text-xs font-semibold text-gray-700 mb-2">🏆 Best Performing Platforms:</p>
              @foreach($recommendation['evidence']['best_performing_platforms'] as $platform)
              <div class="bg-white p-2 rounded-lg border border-gray-200 mb-1">
                @php
                $platformIcons = [
                  'tiktok' => '🎵',
                  'instagram' => '📸',
                  'youtube' => '📺',
                  'twitter' => '🐦'
                ];
                $platformColors = [
                  'tiktok' => 'bg-purple-100 text-purple-700 border-purple-300',
                  'instagram' => 'bg-pink-100 text-pink-700 border-pink-300',
                  'youtube' => 'bg-red-100 text-red-700 border-red-300',
                  'twitter' => 'bg-blue-100 text-blue-700 border-blue-300'
                ];
                $platformName = strtolower($platform['platform'] ?? 'unknown');
                $platformIcon = $platformIcons[$platformName] ?? '📱';
                $platformColor = $platformColors[$platformName] ?? 'bg-gray-100 text-gray-700 border-gray-300';
                @endphp
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold {{ $platformColor }} border">
                      {{ $platformIcon }} {{ ucfirst($platform['platform'] ?? 'N/A') }}
                    </span>
                    <span class="text-xs text-gray-600">Avg Engagement: {{ number_format($platform['avg_engagement'] ?? 0) }}</span>
                  </div>
                  <span class="text-xs text-gray-500">{{ $platform['post_count'] ?? 0 }} posts</span>
                </div>
              </div>
              @endforeach
            </div>
            @endif
          </div>
          @endif
          
          <!-- Action Items -->
          @if(isset($recommendation['action_items']) && count($recommendation['action_items']) > 0)
          <div class="bg-green-50 p-3 rounded-lg border border-green-200">
            <p class="text-xs font-semibold text-green-800 mb-2">✅ ACTION ITEMS:</p>
            @foreach($recommendation['action_items'] as $action)
            <div class="text-xs text-gray-700 ml-2 mb-1">{{ $action }}</div>
            @endforeach
          </div>
          @endif
        </div>
        @endforeach
      </div>
    </div>
    @endif
  </div>
  @endif

  <!-- Platform Cards -->
  <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
    <span class="text-3xl mr-3">📱</span>
    Hasil per Platform
  </h3>
  
  <div class="grid md:grid-cols-3 gap-6 mb-10">
    @foreach ($platforms as $p)
      <x-platform-card 
        :platform="$p['name']" 
        :icon="$p['icon']" 
        :posts="$p['posts']" 
        :color="$p['color']"
      />
    @endforeach
  </div>

  <!-- Export Section -->
  <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100 text-center">
    <h3 class="font-semibold text-lg mb-4 text-gray-800">📥 Export Hasil Analisis</h3>
    <p class="text-gray-600 mb-4">Download hasil analisis dalam format CSV untuk analisis lebih lanjut</p>
    <a href="{{ route('export.trend', ['topic' => $topic]) }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-3 rounded-full hover:from-blue-700 hover:to-purple-700 transition font-semibold">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
      </svg>
      Download CSV
    </a>
  </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Data dari controller
const sentimentData = @json($sentimentSummary);
const platformData = @json($platformSummary);

// Sentiment Pie Chart
const ctx1 = document.getElementById('sentimentChart');
new Chart(ctx1, {
  type: 'doughnut',
  data: {
    labels: ['Positif', 'Negatif', 'Netral'],
    datasets: [{
      data: [sentimentData.positive || 0, sentimentData.negative || 0, sentimentData.neutral || 0],
      backgroundColor: [
        'rgba(34, 197, 94, 0.8)',
        'rgba(239, 68, 68, 0.8)',
        'rgba(251, 191, 36, 0.8)'
      ],
      borderColor: [
        'rgba(34, 197, 94, 1)',
        'rgba(239, 68, 68, 1)',
        'rgba(251, 191, 36, 1)'
      ],
      borderWidth: 2
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'bottom',
        labels: {
          padding: 15,
          font: { size: 12 }
        }
      }
    }
  }
});

// Platform Bar Chart
const ctx2 = document.getElementById('platformChart');
new Chart(ctx2, {
  type: 'bar',
  data: {
    labels: Object.keys(platformData),
    datasets: [{
      label: 'Jumlah Posts',
      data: Object.values(platformData),
      backgroundColor: [
        'rgba(239, 68, 68, 0.8)',
        'rgba(59, 130, 246, 0.8)',
        'rgba(168, 85, 247, 0.8)',
        'rgba(236, 72, 153, 0.8)'
      ],
      borderColor: [
        'rgba(239, 68, 68, 1)',
        'rgba(59, 130, 246, 1)',
        'rgba(168, 85, 247, 1)',
        'rgba(236, 72, 153, 1)'
      ],
      borderWidth: 2,
      borderRadius: 8
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: { precision: 0 }
      }
    }
  }
});
</script>
@endpush
@endsection
