@extends('layouts.app-tailwind')

@section('title', 'Hasil Analisis')

@section('content')
@php
    $summary = $result['summary'] ?? [];
    $actions = $result['actions'] ?? [];
    $items = $result['items'] ?? [];
    $statuses = $result['source_statuses'] ?? [];
    $charts = $result['charts'] ?? ['sentiment' => [], 'platforms' => []];
    $sentimentLabels = [
        'positive' => 'Positif',
        'negative' => 'Negatif',
        'neutral' => 'Netral',
    ];
    $platformLabels = [
        'youtube' => 'YouTube',
        'twitter' => 'Twitter/X',
        'tiktok' => 'TikTok',
        'instagram' => 'Instagram',
    ];
@endphp

<section class="min-h-screen bg-slate-950">
    <div class="mx-auto max-w-6xl px-6 py-8">
        <nav class="mb-8 flex items-center justify-between">
            <a href="{{ route('home') }}" class="text-xl font-black tracking-tight text-white">SocialInsight</a>
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="rounded-full border border-white/15 px-4 py-2 text-sm font-semibold text-white/80 hover:bg-white/10">Dashboard</a>
                <a href="{{ route('home') }}" class="rounded-full bg-white px-4 py-2 text-sm font-black text-slate-950 hover:bg-pink-100">Analisis Baru</a>
            </div>
        </nav>

        <header class="rounded-3xl border border-white/10 bg-white/10 p-6">
            <p class="text-sm font-semibold text-pink-200">Hasil analisis tren</p>
            <div class="mt-3 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h1 class="text-4xl font-black text-white md:text-6xl">{{ $result['topic'] ?? 'Trend' }}</h1>
                    <p class="mt-3 text-sm text-slate-300">Dibuat {{ isset($result['generated_at']) ? \Carbon\Carbon::parse($result['generated_at'])->diffForHumans() : 'baru saja' }}</p>
                </div>
                @if(! empty($result['id']))
                    <a href="{{ route('export.trend', ['id' => $result['id']]) }}" class="rounded-2xl bg-pink-600 px-5 py-3 text-center text-sm font-black text-white hover:bg-pink-700">
                        Download CSV
                    </a>
                @endif
            </div>
        </header>

        <section class="mt-6 grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
            <div class="rounded-3xl bg-white p-6 text-slate-950 shadow-xl">
                <p class="text-sm font-black uppercase tracking-wide text-pink-600">Jawaban cepat</p>
                <h2 class="mt-3 text-3xl font-black leading-tight">{{ $summary['headline'] ?? 'Topik ini siap dianalisis.' }}</h2>
                <p class="mt-4 text-base leading-7 text-slate-700">{{ $summary['trend'] ?? 'Data sedang diproses menjadi insight praktis.' }}</p>

                <div class="mt-6 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl bg-slate-100 p-4">
                        <p class="text-xs font-bold uppercase text-slate-500">Mood audiens</p>
                        <p class="mt-2 font-black">{{ $summary['audience_mood'] ?? 'Beragam' }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-100 p-4">
                        <p class="text-xs font-bold uppercase text-slate-500">Angle terbaik</p>
                        <p class="mt-2 font-black">{{ $summary['best_angle'] ?? 'Konten praktis dan mudah dicoba.' }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-100 p-4">
                        <p class="text-xs font-bold uppercase text-slate-500">Risiko</p>
                        <p class="mt-2 font-black">{{ $summary['risk_note'] ?? 'Jaga klaim tetap realistis.' }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/10 p-6">
                <p class="text-sm font-black uppercase tracking-wide text-pink-200">Status sumber</p>
                <div class="mt-4 space-y-3">
                    @forelse($statuses as $platform => $status)
                        <div class="rounded-2xl bg-white p-4 text-slate-950">
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-black">{{ $platformLabels[$platform] ?? ucfirst($platform) }}</p>
                                <span class="rounded-full px-3 py-1 text-xs font-black {{ ($status['status'] ?? '') === 'real' ? 'bg-emerald-100 text-emerald-700' : ((($status['status'] ?? '') === 'failed') ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                    {{ strtoupper($status['status'] ?? 'demo') }}
                                </span>
                            </div>
                            <p class="mt-2 text-sm text-slate-600">{{ $status['message'] ?? 'Status tidak tersedia.' }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-300">Belum ada sumber aktif.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="mt-6 rounded-3xl bg-white p-6 text-slate-950 shadow-xl">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-black uppercase tracking-wide text-pink-600">Rekomendasi aksi</p>
                    <h2 class="mt-2 text-2xl font-black">Langkah konten yang bisa dicoba</h2>
                </div>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-3">
                @forelse($actions as $action)
                    <article class="rounded-2xl border border-slate-200 p-5">
                        <h3 class="text-lg font-black">{{ $action['title'] ?? 'Coba angle baru' }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $action['description'] ?? 'Gunakan insight ini sebagai eksperimen konten berikutnya.' }}</p>
                        @if(! empty($action['steps']))
                            <ul class="mt-4 space-y-2 text-sm font-semibold text-slate-700">
                                @foreach($action['steps'] as $step)
                                    <li>{{ $step }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </article>
                @empty
                    <p class="text-sm text-slate-600">Belum ada rekomendasi aksi.</p>
                @endforelse
            </div>
        </section>

        <section class="mt-6 grid gap-6 lg:grid-cols-2">
            <div class="rounded-3xl bg-white p-6 text-slate-950 shadow-xl">
                <h2 class="text-xl font-black">Distribusi sentimen</h2>
                <div class="mt-4 h-64">
                    <canvas id="sentimentChart"></canvas>
                </div>
                <div class="mt-4 grid grid-cols-3 gap-3 text-sm">
                    @foreach($charts['sentiment'] ?? [] as $key => $count)
                        <div class="rounded-2xl bg-slate-100 p-3">
                            <p class="font-black">{{ $count }}</p>
                            <p class="text-slate-600">{{ $sentimentLabels[$key] ?? ucfirst($key) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-3xl bg-white p-6 text-slate-950 shadow-xl">
                <h2 class="text-xl font-black">Sinyal per platform</h2>
                <div class="mt-4 h-64">
                    <canvas id="platformChart"></canvas>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                    @foreach($charts['platforms'] ?? [] as $platform => $count)
                        <div class="rounded-2xl bg-slate-100 p-3">
                            <p class="font-black">{{ $count }}</p>
                            <p class="text-slate-600">{{ ucfirst($platform) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="mt-6 rounded-3xl bg-white p-6 text-slate-950 shadow-xl">
            <p class="text-sm font-black uppercase tracking-wide text-pink-600">Bukti dari platform</p>
            <h2 class="mt-2 text-2xl font-black">Sumber yang mendukung insight</h2>

            <div class="mt-5 space-y-4">
                @forelse($items as $item)
                    <article class="rounded-2xl border border-slate-200 p-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-black uppercase text-white">{{ $item['platform'] ?? 'unknown' }}</span>
                                    <span class="text-sm font-semibold text-slate-500">{{ $item['author'] ?? 'Unknown creator' }}</span>
                                    <span class="text-sm font-semibold text-pink-600">{{ ucfirst($item['sentiment'] ?? 'neutral') }}</span>
                                </div>
                                <p class="mt-3 text-base leading-7 text-slate-800">{{ $item['content'] ?? '' }}</p>
                            </div>
                            @if(! empty($item['url']))
                                <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 hover:bg-slate-100">
                                    Buka sumber
                                </a>
                            @endif
                        </div>
                        <div class="mt-4 flex flex-wrap gap-3 text-sm font-semibold text-slate-600">
                            <span>{{ number_format($item['engagement']['likes'] ?? 0) }} likes</span>
                            <span>{{ number_format($item['engagement']['comments'] ?? 0) }} komentar</span>
                            <span>{{ number_format($item['engagement']['views'] ?? 0) }} views</span>
                            <span>{{ number_format($item['engagement']['shares'] ?? 0) }} shares</span>
                        </div>
                    </article>
                @empty
                    <p class="rounded-2xl bg-slate-100 p-5 text-sm text-slate-600">Belum ada bukti yang bisa ditampilkan.</p>
                @endforelse
            </div>
        </section>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.Chart) {
        return;
    }

    const sentiment = @json($charts['sentiment'] ?? []);
    const platforms = @json($charts['platforms'] ?? []);
    const sentimentEl = document.getElementById('sentimentChart');
    const platformEl = document.getElementById('platformChart');

    if (sentimentEl) {
        new Chart(sentimentEl, {
            type: 'doughnut',
            data: {
                labels: Object.keys(sentiment),
                datasets: [{ data: Object.values(sentiment), backgroundColor: ['#22c55e', '#ef4444', '#f59e0b'] }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    if (platformEl) {
        new Chart(platformEl, {
            type: 'bar',
            data: {
                labels: Object.keys(platforms),
                datasets: [{ data: Object.values(platforms), backgroundColor: ['#ef4444', '#38bdf8', '#a855f7', '#ec4899'] }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });
    }
});
</script>
@endpush
