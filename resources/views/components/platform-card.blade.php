@props(['platform', 'icon', 'posts', 'color' => 'blue'])

@php
$colorClasses = [
  'youtube' => 'from-red-50 to-red-200',
  'twitter' => 'from-blue-50 to-blue-100 border-blue-200',
  'tiktok' => 'from-purple-50 to-purple-100 border-purple-200',
  'instagram' => 'from-pink-50 to-pink-100 border-pink-200'
];

$iconEmojis = [
  'youtube' => '📺',
  'twitter' => '🐦',
  'tiktok' => '🎵',
  'instagram' => '📸'
];

$bgColor = $colorClasses[$platform] ?? $colorClasses['youtube'];
$emoji = $iconEmojis[$platform] ?? '📱';
$postCount = is_array($posts) ? count($posts) : 0;
$uniqueId = 'platform-' . $platform . '-' . uniqid();
@endphp

<div class="bg-gradient-to-br {{ $bgColor }} rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 flex flex-col border transform hover:-translate-y-1">
  <!-- Header -->
  <div class="flex items-center justify-between mb-4">
    <div class="flex items-center">
      <span class="text-3xl mr-3">{{ $emoji }}</span>
      <h4 class="font-semibold text-xl text-gray-800">{{ ucfirst($platform) }}</h4>
    </div>
    <span class="bg-white px-3 py-1 rounded-full text-sm font-semibold text-gray-700 shadow-sm">
      {{ $postCount }}
    </span>
  </div>

  <!-- Posts Preview -->
  @if($postCount > 0)
    <div class="space-y-3 mb-4 flex-grow" id="{{ $uniqueId }}-container">
      @foreach(array_slice($posts, 0, 2) as $post)
        @php
        $sentimentColors = [
          'positive' => 'bg-green-100 text-green-700 border-green-300',
          'negative' => 'bg-red-100 text-red-700 border-red-300',
          'neutral' => 'bg-amber-100 text-amber-700 border-amber-300'
        ];
        $sentimentColor = $sentimentColors[$post['sentiment']] ?? $sentimentColors['neutral'];
        
        // Generate post link for search (since using mock data)
        $postLink = '';
        $linkText = '';
        if (isset($post['content'])) {
          // Extract keywords from content for search
          $words = array_filter(explode(' ', strtolower($post['content'])), function($word) {
            return strlen($word) > 3;
          });
          $searchQuery = urlencode(implode(' ', array_slice($words, 0, 3)));
          
          switch(strtolower($post['platform'] ?? '')) {
            case 'youtube':
              $postLink = 'https://youtube.com/results?search_query=' . $searchQuery;
              $linkText = '🔍 Cari di YouTube';
              break;
            case 'twitter':
              $postLink = 'https://twitter.com/search?q=' . $searchQuery;
              $linkText = '🔍 Cari di Twitter';
              break;
            case 'tiktok':
              $postLink = 'https://www.tiktok.com/search?q=' . $searchQuery;
              $linkText = '🔍 Cari di TikTok';
              break;
            case 'instagram':
              $postLink = 'https://www.instagram.com/explore/tags/' . str_replace(' ', '', $searchQuery);
              $linkText = '🔍 Explore Instagram';
              break;
          }
        }
        @endphp
        
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition">
          <p class="text-sm text-gray-800 mb-2 line-clamp-2">
            "{{ Str::limit($post['content'], 100) }}"
          </p>
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs {{ $sentimentColor }} px-2 py-1 rounded-full font-medium border">
              {{ ucfirst($post['sentiment']) }}
            </span>
            @if(isset($post['score']))
            <span class="text-xs text-gray-500 font-medium">
              Score: {{ number_format($post['score'], 2) }}
            </span>
            @endif
          </div>
          
          @if($postLink)
          <a href="{{ $postLink }}" target="_blank" 
             class="block text-center bg-gradient-to-r from-blue-500 to-purple-500 text-white px-3 py-1.5 rounded-lg text-xs font-semibold hover:from-blue-600 hover:to-purple-600 transition"
             title="Klik untuk cari konten serupa">
            {{ $linkText }}
          </a>
          @endif
        </div>
      @endforeach
      
      <!-- Hidden posts that will be revealed -->
      <div id="{{ $uniqueId }}-hidden" class="hidden space-y-3">
        @foreach(array_slice($posts, 2) as $post)
          @php
          $sentimentColors = [
            'positive' => 'bg-green-100 text-green-700 border-green-300',
            'negative' => 'bg-red-100 text-red-700 border-red-300',
            'neutral' => 'bg-amber-100 text-amber-700 border-amber-300'
          ];
          $sentimentColor = $sentimentColors[$post['sentiment']] ?? $sentimentColors['neutral'];
          
          // Generate post link for search (since using mock data)
          $postLink = '';
          $linkText = '';
          if (isset($post['content'])) {
            // Extract keywords from content for search
            $words = array_filter(explode(' ', strtolower($post['content'])), function($word) {
              return strlen($word) > 3;
            });
            $searchQuery = urlencode(implode(' ', array_slice($words, 0, 3)));
            
            switch(strtolower($post['platform'] ?? '')) {
              case 'youtube':
                $postLink = 'https://youtube.com/results?search_query=' . $searchQuery;
                $linkText = '🔍 Cari di YouTube';
                break;
              case 'twitter':
                $postLink = 'https://twitter.com/search?q=' . $searchQuery;
                $linkText = '🔍 Cari di Twitter';
                break;
              case 'tiktok':
                $postLink = 'https://www.tiktok.com/search?q=' . $searchQuery;
                $linkText = '🔍 Cari di TikTok';
                break;
              case 'instagram':
                $postLink = 'https://www.instagram.com/explore/tags/' . str_replace(' ', '', $searchQuery);
                $linkText = '🔍 Explore Instagram';
                break;
            }
          }
          @endphp
          
          <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition">
            <p class="text-sm text-gray-800 mb-2 line-clamp-2">
              "{{ Str::limit($post['content'], 100) }}"
            </p>
            <div class="flex items-center justify-between mb-2">
              <span class="text-xs {{ $sentimentColor }} px-2 py-1 rounded-full font-medium border">
                {{ ucfirst($post['sentiment']) }}
              </span>
              @if(isset($post['score']))
              <span class="text-xs text-gray-500 font-medium">
                Score: {{ number_format($post['score'], 2) }}
              </span>
              @endif
            </div>
            
            @if($postLink)
            <a href="{{ $postLink }}" target="_blank" 
               class="block text-center bg-gradient-to-r from-blue-500 to-purple-500 text-white px-3 py-1.5 rounded-lg text-xs font-semibold hover:from-blue-600 hover:to-purple-600 transition"
               title="Klik untuk cari konten serupa">
              {{ $linkText }}
            </a>
            @endif
          </div>
        @endforeach
      </div>
    </div>

    <!-- View More/Less Button -->
    @if($postCount > 2)
    <div class="text-center pt-3 border-t border-gray-200">
      <button 
        onclick="toggleViewMore('{{ $uniqueId }}')"
        id="{{ $uniqueId }}-btn"
        data-expanded="false"
        class="text-blue-600 hover:text-blue-700 text-sm font-semibold flex items-center justify-center mx-auto gap-1 hover:gap-2 transition-all"
      >
        <span id="{{ $uniqueId }}-text">Lihat {{ $postCount - 2 }} lainnya</span>
        <svg id="{{ $uniqueId }}-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>
    </div>
    @endif
  @else
    <!-- Empty State -->
    <div class="flex-grow flex items-center justify-center py-8">
      <div class="text-center">
        <svg class="w-16 h-16 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-gray-500">Tidak ada data ditemukan</p>
      </div>
    </div>
  @endif

  <!-- Stats Footer -->
  @if($postCount > 0)
  <div class="mt-4 pt-4 border-t border-gray-200">
    <div class="grid grid-cols-3 gap-2 text-center">
      @php
      $posCount = collect($posts)->where('sentiment', 'positive')->count();
      $negCount = collect($posts)->where('sentiment', 'negative')->count();
      $neuCount = collect($posts)->where('sentiment', 'neutral')->count();
      @endphp
      
      <div>
        <div class="text-lg font-bold text-green-600">{{ $posCount }}</div>
        <div class="text-xs text-gray-600">Positif</div>
      </div>
      <div>
        <div class="text-lg font-bold text-red-600">{{ $negCount }}</div>
        <div class="text-xs text-gray-600">Negatif</div>
      </div>
      <div>
        <div class="text-lg font-bold text-amber-600">{{ $neuCount }}</div>
        <div class="text-xs text-gray-600">Netral</div>
      </div>
    </div>
  </div>
  @endif
</div>

@push('scripts')
<script>
function toggleViewMore(uniqueId) {
  const hiddenDiv = document.getElementById(uniqueId + '-hidden');
  const btn = document.getElementById(uniqueId + '-btn');
  const text = document.getElementById(uniqueId + '-text');
  const icon = document.getElementById(uniqueId + '-icon');
  const isExpanded = btn.getAttribute('data-expanded') === 'true';
  
  if (isExpanded) {
    // Collapse
    hiddenDiv.classList.add('hidden');
    btn.setAttribute('data-expanded', 'false');
    const originalCount = hiddenDiv.children.length;
    text.textContent = 'Lihat ' + originalCount + ' lainnya';
    icon.style.transform = 'rotate(0deg)';
  } else {
    // Expand
    hiddenDiv.classList.remove('hidden');
    btn.setAttribute('data-expanded', 'true');
    text.textContent = 'Sembunyikan';
    icon.style.transform = 'rotate(180deg)';
  }
}
</script>
@endpush
