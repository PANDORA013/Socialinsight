@extends('layouts.app-tailwind')

@section('title', 'Dashboard')

@section('content')
<section class="min-h-screen bg-slate-950">
    <div class="mx-auto max-w-6xl px-6 py-8">
        <nav class="mb-8 flex items-center justify-between">
            <a href="{{ route('home') }}" class="text-xl font-black tracking-tight text-white">SocialInsight</a>
            <a href="{{ route('home') }}" class="rounded-full bg-white px-4 py-2 text-sm font-black text-slate-950 hover:bg-pink-100">
                Analisis Tren
            </a>
        </nav>

        <header class="rounded-3xl border border-white/10 bg-white/10 p-6">
            <p class="text-sm font-semibold text-pink-200">Dashboard demo</p>
            <h1 class="mt-3 text-4xl font-black text-white md:text-6xl">Status platform</h1>
            <p class="mt-4 max-w-2xl text-slate-300">
                SocialInsight berjalan tanpa login. Halaman ini menunjukkan kesiapan platform dan cara kerja analisis, bukan riwayat raw post global.
            </p>
        </header>

        <section class="mt-6 grid gap-4 md:grid-cols-4">
            @foreach($platformStatuses as $key => $platform)
                <article class="rounded-3xl bg-white p-5 text-slate-950 shadow-xl">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-bold uppercase text-slate-500">{{ $key }}</p>
                            <h2 class="mt-2 text-xl font-black">{{ $platform['label'] }}</h2>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-black {{ $platform['mode'] === 'real' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ strtoupper($platform['mode']) }}
                        </span>
                    </div>
                    <p class="mt-4 text-sm leading-6 text-slate-600">
                        @if($platform['mode'] === 'real')
                            Credential tersedia dan siap dipakai.
                        @elseif($platform['mode'] === 'maintenance')
                            Coming Soon / Maintenance. Platform ini belum dipakai untuk analisis.
                        @else
                            Credential belum tersedia, flow akan memakai fallback demo yang ditandai jelas.
                        @endif
                    </p>
                </article>
            @endforeach
        </section>

        <section class="mt-6 grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
            <div class="rounded-3xl bg-white p-6 text-slate-950 shadow-xl">
                <p class="text-sm font-black uppercase tracking-wide text-pink-600">Batas publik</p>
                <div class="mt-5 grid gap-3">
                    <div class="rounded-2xl bg-slate-100 p-4">
                        <p class="text-2xl font-black">{{ $demoMetrics['platforms'] }}</p>
                        <p class="text-sm text-slate-600">platform tersedia</p>
                    </div>
                    <div class="rounded-2xl bg-slate-100 p-4">
                        <p class="text-2xl font-black">{{ $demoMetrics['maxEvidence'] }}</p>
                        <p class="text-sm text-slate-600">maksimal bukti per export</p>
                    </div>
                    <div class="rounded-2xl bg-slate-100 p-4">
                        <p class="text-2xl font-black">{{ $demoMetrics['cacheMinutes'] }} menit</p>
                        <p class="text-sm text-slate-600">hasil sementara untuk export</p>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl bg-white p-6 text-slate-950 shadow-xl">
                <p class="text-sm font-black uppercase tracking-wide text-pink-600">Cara kerja analisis</p>
                <div class="mt-5 space-y-4">
                    @foreach($pipelineSteps as $index => $step)
                        <div class="flex items-center gap-4 rounded-2xl border border-slate-200 p-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-950 text-sm font-black text-white">{{ $index + 1 }}</span>
                            <div>
                                <h2 class="font-black">{{ $step }}</h2>
                                <p class="text-sm text-slate-600">Tahap ini menjaga hasil tetap ringkas, relevan, dan bisa dipakai untuk keputusan konten.</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="mt-6 rounded-3xl border border-white/10 bg-white/10 p-6">
            <h2 class="text-2xl font-black text-white">Privasi no-login</h2>
            <p class="mt-3 max-w-3xl text-slate-300">
                Flow utama tidak menyimpan raw post permanen. Hasil disimpan sementara hanya untuk export saat itu, lalu kedaluwarsa otomatis.
            </p>
        </section>
    </div>
</section>
@endsection
