@extends('layouts.app-tailwind')

@section('title', 'SocialInsight')

@section('content')
<section class="min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top_left,#ec4899_0,#111827_34%,#020617_100%)]">
    <div class="mx-auto flex min-h-screen max-w-6xl flex-col px-6 py-8">
        <nav class="flex items-center justify-between">
            <a href="{{ route('home') }}" class="text-xl font-black tracking-tight text-white">SocialInsight</a>
            <a href="{{ route('dashboard') }}" class="rounded-full border border-white/15 px-4 py-2 text-sm font-semibold text-white/80 hover:bg-white/10">
                Dashboard
            </a>
        </nav>

        <div class="grid flex-1 items-center gap-10 py-12 lg:grid-cols-[1.1fr_0.9fr]">
            <div>
                <p class="mb-4 inline-flex rounded-full bg-white/10 px-4 py-2 text-sm font-semibold text-pink-100 ring-1 ring-white/15">
                    Asisten tren untuk creator dan UMKM
                </p>
                <h1 class="max-w-3xl text-5xl font-black leading-tight text-white md:text-7xl">
                    Cari angle konten dari sinyal sosial terbaru.
                </h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-200">
                    Masukkan topik, pilih platform, lalu dapatkan ringkasan tren, mood audiens, ide aksi, dan bukti post yang bisa dicek.
                </p>

                <div class="mt-8 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                        <p class="text-2xl font-black">4</p>
                        <p class="mt-1 text-sm text-slate-300">platform sosial</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                        <p class="text-2xl font-black">No login</p>
                        <p class="mt-1 text-sm text-slate-300">langsung analisis</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                        <p class="text-2xl font-black">Aksi</p>
                        <p class="mt-1 text-sm text-slate-300">bukan cuma data</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('analyze.trend') }}" method="POST" class="rounded-3xl bg-white p-6 text-slate-950 shadow-2xl">
                @csrf

                <label for="topic" class="text-sm font-bold text-slate-700">Topik yang mau dianalisis</label>
                <input
                    id="topic"
                    name="topic"
                    value="{{ old('topic') }}"
                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-4 text-lg outline-none focus:border-pink-500 focus:ring-4 focus:ring-pink-100"
                    placeholder="Contoh: kopi susu, skincare lokal, menu bukber"
                    required
                >
                @error('topic')
                    <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                @enderror

                <div class="mt-6">
                    <p class="text-sm font-bold text-slate-700">Platform</p>
                    <div class="mt-3 grid grid-cols-2 gap-3">
                        @foreach(['youtube' => ['label' => 'YouTube', 'status' => 'Aktif'], 'twitter' => ['label' => 'Twitter/X', 'status' => 'Coming Soon'], 'tiktok' => ['label' => 'TikTok', 'status' => 'Maintenance'], 'instagram' => ['label' => 'Instagram', 'status' => 'Coming Soon']] as $value => $platform)
                            @php($disabled = $value !== 'youtube')
                            <label class="flex items-center gap-3 rounded-2xl border p-3 {{ $disabled ? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400' : 'cursor-pointer border-slate-200 hover:border-pink-300' }}">
                                <input type="checkbox" name="platforms[]" value="{{ $value }}" class="h-4 w-4 rounded border-slate-300 text-pink-600" {{ $disabled ? 'disabled' : 'checked' }}>
                                <span class="text-sm font-semibold">{{ $platform['label'] }}</span>
                                <span class="ml-auto rounded-full bg-slate-200 px-2 py-1 text-[10px] font-black uppercase text-slate-600">{{ $platform['status'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-2">
                    @foreach(['Kopi susu viral', 'Skincare lokal', 'Menu Ramadan', 'Fashion thrift'] as $quickTopic)
                        <button
                            type="button"
                            class="rounded-full bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-pink-100"
                            onclick="document.getElementById('topic').value='{{ $quickTopic }}'"
                        >
                            {{ $quickTopic }}
                        </button>
                    @endforeach
                </div>

                <button class="mt-6 w-full rounded-2xl bg-pink-600 px-5 py-4 text-base font-black text-white hover:bg-pink-700">
                    Analisis Tren
                </button>
                <p class="mt-4 text-center text-xs text-slate-500">
                    Tanpa login. Saat ini hanya YouTube aktif; platform lain Coming Soon / Maintenance.
                </p>
            </form>
        </div>
    </div>
</section>
@endsection
