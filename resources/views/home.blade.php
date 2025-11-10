@extends('layouts.app-tailwind')

@section('title', 'Home')

@section('content')
<div class="min-h-[calc(100vh-200px)] bg-gradient-to-br from-blue-50 via-white to-purple-50 flex flex-col items-center justify-center px-6 -mt-8">
  <!-- Hero Section -->
  <div class="text-center mb-12 animate-fade-in">
    <div class="mb-6">
      <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-r from-blue-600 to-purple-600 shadow-lg mb-4">
        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
        </svg>
      </div>
    </div>
    
    <h1 class="text-5xl md:text-6xl font-bold text-gray-900 mb-4">
      🔥 <span class="gradient-text">SocialInsight</span>
    </h1>
    
    <p class="text-lg md:text-xl text-gray-600 mb-2 max-w-3xl mx-auto">
      Analisis tren terbaru dari berbagai platform dalam sekali klik
    </p>
    <p class="text-sm text-gray-500 max-w-2xl mx-auto">
      Cukup masukkan topik yang ingin dianalisis, dan kami akan mencari tren di <strong>YouTube, Twitter, TikTok, dan Instagram</strong>
    </p>
  </div>

  <!-- TikTok Connect Banner (Dynamic based on session) -->
  <div class="w-full max-w-3xl mb-6">
    @if(session('tiktok_connected'))
      <!-- Connected State -->
      <div class="bg-gradient-to-r from-purple-50 to-pink-50 border-2 border-purple-300 rounded-2xl p-4 flex items-center justify-between shadow-md">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-black rounded-full flex items-center justify-center">
            <span class="text-2xl">🎵</span>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-800">TikTok Account</p>
            <p class="text-xs text-purple-700">
              <svg class="inline-block w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
              </svg>
              Connected as {{ session('tiktok_user')['display_name'] ?? 'User' }} • Enhanced trend analysis
            </p>
          </div>
        </div>
        <a href="{{ route('tiktok.disconnect') }}" class="px-4 py-2 bg-black text-white rounded-lg text-xs font-semibold hover:bg-gray-800 transition flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Disconnect
        </a>
      </div>
    @else
      <!-- Not Connected State -->
      <div class="bg-gradient-to-r from-gray-50 to-gray-100 border-2 border-gray-300 rounded-2xl p-4 flex items-center justify-between shadow-md hover:shadow-lg transition">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
            <span class="text-2xl opacity-60">🎵</span>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-800">TikTok Account</p>
            <p class="text-xs text-gray-600">Not connected • Using sample data</p>
          </div>
        </div>
        <a href="{{ route('tiktok.login') }}" class="px-4 py-2 bg-black text-white rounded-lg text-xs font-semibold hover:bg-gray-800 transition flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
          </svg>
          Connect TikTok
        </a>
      </div>
    @endif
  </div>

  <!-- Search Form -->
  <div class="w-full max-w-3xl mb-12">
    <form action="{{ route('analyze.trend') }}" method="POST" class="relative">
      @csrf
      
      <div class="relative flex items-center bg-white shadow-2xl rounded-full p-2 hover:shadow-3xl transition-all duration-300 border-2 border-transparent hover:border-blue-200">
        <!-- Search Icon -->
        <div class="pl-6 pr-2">
          <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>
        
        <!-- Input Field -->
        <input 
          type="text" 
          name="topic" 
          id="topicInput"
          placeholder="Masukkan topik trending... (contoh: fashion 2025, musik korea, AI technology)" 
          class="flex-grow px-4 py-4 text-lg rounded-l-full outline-none text-gray-700 placeholder-gray-400"
          required
          autocomplete="off"
        >
        
        <!-- Submit Button -->
        <button 
          type="submit"
          class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-4 rounded-full hover:from-blue-700 hover:to-purple-700 transition-all duration-300 font-semibold text-lg shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center gap-2"
        >
          <span>Analisis Sekarang</span>
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
          </svg>
        </button>
      </div>

      <!-- Quick Topics -->
      <div class="mt-6 text-center">
        <p class="text-sm text-gray-500 mb-3">Coba topik populer:</p>
        <div class="flex flex-wrap justify-center gap-2">
          <button type="button" onclick="setTopic('K-Pop Idols')" class="px-4 py-2 bg-white border border-gray-300 rounded-full text-sm text-gray-700 hover:bg-blue-50 hover:border-blue-300 transition">
            � K-Pop Idols
          </button>
          <button type="button" onclick="setTopic('Korean Drama')" class="px-4 py-2 bg-white border border-gray-300 rounded-full text-sm text-gray-700 hover:bg-blue-50 hover:border-blue-300 transition">
            📺 Korean Drama
          </button>
          <button type="button" onclick="setTopic('Hollywood Movies')" class="px-4 py-2 bg-white border border-gray-300 rounded-full text-sm text-gray-700 hover:bg-blue-50 hover:border-blue-300 transition">
            🎬 Hollywood Movies
          </button>
          <button type="button" onclick="setTopic('Celebrity Fashion')" class="px-4 py-2 bg-white border border-gray-300 rounded-full text-sm text-gray-700 hover:bg-blue-50 hover:border-blue-300 transition">
            👗 Celebrity Fashion
          </button>
          <button type="button" onclick="setTopic('Viral Trends')" class="px-4 py-2 bg-white border border-gray-300 rounded-full text-sm text-gray-700 hover:bg-blue-50 hover:border-blue-300 transition">
            � Viral Trends
          </button>
        </div>
      </div>
    </form>
  </div>

  <!-- Features Grid -->
  <div class="grid md:grid-cols-4 gap-6 max-w-5xl">
    <div class="text-center p-6 bg-white rounded-2xl shadow-lg hover:shadow-xl transition">
      <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
        <span class="text-2xl">📺</span>
      </div>
      <h3 class="font-semibold text-gray-800 mb-2">YouTube</h3>
      <p class="text-sm text-gray-600">Video & comments trending</p>
    </div>

    <div class="text-center p-6 bg-white rounded-2xl shadow-lg hover:shadow-xl transition">
      <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
        <span class="text-2xl">🐦</span>
      </div>
      <h3 class="font-semibold text-gray-800 mb-2">Twitter/X</h3>
      <p class="text-sm text-gray-600">Tweets & discussions</p>
    </div>

    <div class="text-center p-6 bg-white rounded-2xl shadow-lg hover:shadow-xl transition">
      <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
        <span class="text-2xl">🎵</span>
      </div>
      <h3 class="font-semibold text-gray-800 mb-2">TikTok</h3>
      <p class="text-sm text-gray-600">Viral videos & sounds</p>
    </div>

    <div class="text-center p-6 bg-white rounded-2xl shadow-lg hover:shadow-xl transition">
      <div class="w-12 h-12 bg-pink-100 rounded-full flex items-center justify-center mx-auto mb-3">
        <span class="text-2xl">📸</span>
      </div>
      <h3 class="font-semibold text-gray-800 mb-2">Instagram</h3>
      <p class="text-sm text-gray-600">Posts & stories</p>
    </div>
  </div>
</div>

@push('styles')
<style>
  .gradient-text {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }
  
  @keyframes fade-in {
    from {
      opacity: 0;
      transform: translateY(-20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  
  .animate-fade-in {
    animation: fade-in 0.8s ease-out;
  }
</style>
@endpush

@push('scripts')
<script>
function setTopic(topic) {
  document.getElementById('topicInput').value = topic;
  document.getElementById('topicInput').focus();
}
</script>
@endpush
@endsection
