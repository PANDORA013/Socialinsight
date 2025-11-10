<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TikTok Dashboard - My Videos</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-purple-50 min-h-screen">
    <!-- Header -->
    <div class="bg-white shadow-md border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">TikTok Dashboard</h1>
                        <p class="text-sm text-gray-600">Welcome, <strong>{{ $display_name }}</strong></p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="/" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-200 transition">
                        🏠 Home
                    </a>
                    <a href="{{ route('tiktok.disconnect') }}" class="px-4 py-2 bg-red-500 text-white rounded-lg text-sm font-semibold hover:bg-red-600 transition">
                        Disconnect
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border-2 border-green-200 rounded-xl p-4">
                <div class="flex items-center gap-2 text-green-800">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-semibold">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if($error)
            <div class="mb-6 bg-red-50 border-2 border-red-200 rounded-xl p-4">
                <div class="flex items-center gap-2 text-red-800">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-semibold">{{ $error }}</span>
                </div>
            </div>
        @endif

        <!-- Page Title -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-2">My TikTok Videos</h2>
            <p class="text-gray-600">Data fetched using TikTok Video List API with your authorized access token</p>
        </div>

        <!-- Videos Grid -->
        @if(!empty($videos))
            <div class="mb-4 text-sm text-gray-600">
                <span class="font-semibold">{{ count($videos) }}</span> videos found
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($videos as $video)
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                        <!-- Video Cover/Embed -->
                        <div class="relative bg-gray-900 aspect-[9/16] flex items-center justify-center">
                            @if(isset($video['cover_image_url']))
                                <img src="{{ $video['cover_image_url'] }}" 
                                     alt="Video Cover" 
                                     class="w-full h-full object-cover">
                            @elseif(isset($video['embed_html']))
                                <div class="w-full h-full">
                                    {!! $video['embed_html'] !!}
                                </div>
                            @else
                                <div class="text-white text-center p-4">
                                    <svg class="w-16 h-16 mx-auto mb-2 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                    </svg>
                                    <p class="text-sm">Video Preview</p>
                                </div>
                            @endif

                            <!-- Video Duration Badge -->
                            @if(isset($video['duration']))
                                <div class="absolute bottom-2 right-2 bg-black bg-opacity-75 text-white px-2 py-1 rounded text-xs font-semibold">
                                    {{ gmdate('i:s', $video['duration']) }}
                                </div>
                            @endif
                        </div>

                        <!-- Video Info -->
                        <div class="p-4">
                            <!-- Title/Description -->
                            @if(isset($video['title']) && $video['title'])
                                <h3 class="font-bold text-gray-900 mb-2 line-clamp-2">{{ $video['title'] }}</h3>
                            @elseif(isset($video['video_description']))
                                <p class="text-sm text-gray-700 mb-2 line-clamp-2">{{ $video['video_description'] }}</p>
                            @endif

                            <!-- Stats -->
                            <div class="grid grid-cols-2 gap-2 mb-3">
                                <!-- Views -->
                                <div class="flex items-center gap-1 text-gray-600">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-xs font-semibold">{{ number_format($video['view_count'] ?? 0) }}</span>
                                </div>

                                <!-- Likes -->
                                <div class="flex items-center gap-1 text-red-500">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-xs font-semibold">{{ number_format($video['like_count'] ?? 0) }}</span>
                                </div>

                                <!-- Comments -->
                                <div class="flex items-center gap-1 text-blue-500">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-xs font-semibold">{{ number_format($video['comment_count'] ?? 0) }}</span>
                                </div>

                                <!-- Shares -->
                                <div class="flex items-center gap-1 text-green-500">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M15 8a3 3 0 10-2.977-2.63l-4.94 2.47a3 3 0 100 4.319l4.94 2.47a3 3 0 10.895-1.789l-4.94-2.47a3.027 3.027 0 000-.74l4.94-2.47C13.456 7.68 14.19 8 15 8z"/>
                                    </svg>
                                    <span class="text-xs font-semibold">{{ number_format($video['share_count'] ?? 0) }}</span>
                                </div>
                            </div>

                            <!-- Video ID -->
                            <div class="text-xs text-gray-500 mb-3 font-mono">
                                ID: {{ $video['id'] ?? 'N/A' }}
                            </div>

                            <!-- Actions -->
                            @if(isset($video['share_url']))
                                <a href="{{ $video['share_url'] }}" 
                                   target="_blank"
                                   class="block w-full bg-black text-white text-center py-2 rounded-lg text-sm font-semibold hover:bg-gray-800 transition">
                                    View on TikTok →
                                </a>
                            @elseif(isset($video['embed_link']))
                                <a href="{{ $video['embed_link'] }}" 
                                   target="_blank"
                                   class="block w-full bg-black text-white text-center py-2 rounded-lg text-sm font-semibold hover:bg-gray-800 transition">
                                    View Video →
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

        @else
            <!-- Empty State -->
            <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                <svg class="w-24 h-24 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <h3 class="text-xl font-bold text-gray-900 mb-2">No Videos Found</h3>
                <p class="text-gray-600 mb-6">No videos were found in your TikTok account, or you may need to grant additional permissions.</p>
                <div class="flex gap-3 justify-center">
                    <a href="/" class="px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                        Back to Home
                    </a>
                    <a href="{{ route('tiktok.disconnect') }}" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition">
                        Try Different Account
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Footer -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-center text-sm text-gray-500">
        <p>Data fetched using <strong>TikTok Video List API</strong> (<code>https://open.tiktokapis.com/v2/video/list/</code>)</p>
        <p class="mt-2">OAuth 2.0 Implementation for SocialInsight</p>
    </div>
</body>
</html>
