# SocialInsight Product Rebuild Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Rebuild SocialInsight into a no-login creator trend analysis tool with insight-first results, bounded evidence, safe public routes, and tests that do not depend on live external APIs.

**Architecture:** Keep Laravel 12 with Blade, Tailwind, Vite, and the existing social service classes. Move orchestration out of `TrendController` into a focused analysis service, normalize platform data into one contract, store only short-lived session/cache result data for export, and rebuild the user-facing pages around the new view model.

**Tech Stack:** PHP 8.2, Laravel 12, Blade, Tailwind CSS, Vite, Chart.js, PHPUnit 11, Laravel HTTP client, Laravel rate limiter, Laravel session/cache.

## Global Constraints

- Target user is a non-technical UMKM owner or content creator.
- Login is not required.
- The main no-login analysis flow must not permanently store raw social posts.
- Platform mode is multi-platform demo-first: real APIs are used when credentials are available, and missing/failed APIs return clear demo or failed statuses.
- Visual style is an energetic creator trend tool with platform-aware colors and insight cards above technical charts.
- Public analysis and export endpoints must be validated and rate-limited.
- External HTTP calls must keep TLS verification enabled.
- Export must be bounded and CSV-safe.
- Tests must not require live YouTube, Twitter/X, TikTok, Instagram, OpenAI, or IndoBERT services.

---

## File Structure

- Create `app/Http/Requests/AnalyzeTrendRequest.php`: validates `topic` and selected `platforms`.
- Create `app/Support/SocialInsight/SocialPlatform.php`: central platform constants and normalization helpers.
- Create `app/Services/TrendAnalysisService.php`: coordinates platform fetch, filtering, sentiment, clustering, and insight view-model assembly.
- Create `app/Services/TrendResultStore.php`: stores and retrieves a bounded, short-lived analysis result for export.
- Modify `app/Http/Controllers/TrendController.php`: keep it thin; delegate analysis/export to services.
- Modify `app/Providers/AppServiceProvider.php`: define public route rate limiters.
- Modify `routes/web.php`: attach named throttles and remove stale placeholder route if unused by views.
- Modify `app/Services/YouTubeService.php` and `app/Services/TwitterService.php`: remove `withoutVerifying()`, add timeout/retry, normalize failed responses.
- Modify `resources/views/layouts/app-tailwind.blade.php`: shared shell for creator-tool styling.
- Modify `resources/views/home.blade.php`: rebuild as the primary product surface.
- Modify `resources/views/result.blade.php`: rebuild as insight-first result page.
- Modify `resources/views/dashboard.blade.php` or `resources/views/dashboard-tailwind.blade.php`: rebuild as demo/status overview, then ensure `DashboardController` returns that view consistently.
- Create or modify `tests/Feature/ProductRebuildFlowTest.php`: end-to-end HTTP flow tests using service mocks.
- Create or modify `tests/Unit/TrendAnalysisServiceTest.php`: service contract and fallback tests.
- Modify `tests/Feature/TrendSearchTest.php`: either update old expectations or replace with the new flow tests.

## Task 1: Request Validation And Public Route Limits

**Files:**
- Create: `app/Http/Requests/AnalyzeTrendRequest.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/ProductRebuildFlowTest.php`

**Interfaces:**
- Produces: `AnalyzeTrendRequest::validated()` returns `['topic' => string, 'platforms' => array<int,string>]`.
- Produces: route middleware names `throttle:trend-analysis` and `throttle:trend-export`.
- Later tasks consume validated `topic` and `platforms`.

- [ ] **Step 1: Write failing route validation tests**

Add `tests/Feature/ProductRebuildFlowTest.php`:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductRebuildFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads_as_creator_tool(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('SocialInsight')
            ->assertSee('Analisis');
    }

    public function test_analyze_requires_topic(): void
    {
        $this->from('/')
            ->post(route('analyze.trend'), [
                'topic' => '',
                'platforms' => ['youtube'],
            ])
            ->assertRedirect('/')
            ->assertSessionHasErrors('topic');
    }

    public function test_analyze_rejects_unknown_platform(): void
    {
        $this->from('/')
            ->post(route('analyze.trend'), [
                'topic' => 'kopi susu',
                'platforms' => ['youtube', 'unknown'],
            ])
            ->assertRedirect('/')
            ->assertSessionHasErrors('platforms.1');
    }

    public function test_analysis_route_uses_named_throttle(): void
    {
        $route = app('router')->getRoutes()->getByName('analyze.trend');

        $this->assertContains('throttle:trend-analysis', $route->gatherMiddleware());
    }

    public function test_export_route_uses_named_throttle(): void
    {
        $route = app('router')->getRoutes()->getByName('export.trend');

        $this->assertContains('throttle:trend-export', $route->gatherMiddleware());
    }
}
```

- [ ] **Step 2: Run the focused failing tests**

Run:

```powershell
php artisan test --filter=ProductRebuildFlowTest
```

Expected: validation and throttle assertions fail because the request class and route middleware are not implemented yet.

- [ ] **Step 3: Add the form request**

Create `app/Http/Requests/AnalyzeTrendRequest.php`:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnalyzeTrendRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'topic' => ['required', 'string', 'min:2', 'max:120'],
            'platforms' => ['nullable', 'array', 'max:4'],
            'platforms.*' => ['string', Rule::in(['youtube', 'twitter', 'tiktok', 'instagram'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $platforms = $this->input('platforms', ['youtube', 'twitter', 'tiktok', 'instagram']);

        if (! is_array($platforms)) {
            $platforms = [$platforms];
        }

        $this->merge([
            'topic' => trim((string) $this->input('topic')),
            'platforms' => array_values(array_unique(array_filter($platforms))),
        ]);
    }
}
```

- [ ] **Step 4: Register rate limiters**

Modify `app/Providers/AppServiceProvider.php`:

```php
<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        RateLimiter::for('trend-analysis', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('trend-export', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });
    }
}
```

- [ ] **Step 5: Attach route throttles**

Modify the relevant routes in `routes/web.php`:

```php
Route::post('/analyze/trend', [TrendController::class, 'analyzeTrend'])
    ->middleware('throttle:trend-analysis')
    ->name('analyze.trend');

Route::get('/export/trend', [TrendController::class, 'exportTrend'])
    ->middleware('throttle:trend-export')
    ->name('export.trend');
```

- [ ] **Step 6: Run tests**

Run:

```powershell
php artisan test --filter=ProductRebuildFlowTest
```

Expected: tests in this class pass except tests that later depend on analysis rendering if added in later tasks.

## Task 2: Normalized Analysis Service And Short-Lived Result Store

**Files:**
- Create: `app/Support/SocialInsight/SocialPlatform.php`
- Create: `app/Services/TrendResultStore.php`
- Create: `app/Services/TrendAnalysisService.php`
- Modify: `app/Http/Controllers/TrendController.php`
- Test: `tests/Unit/TrendAnalysisServiceTest.php`
- Test: `tests/Feature/ProductRebuildFlowTest.php`

**Interfaces:**
- Consumes: `AnalyzeTrendRequest::validated()`.
- Produces: `TrendAnalysisService::analyze(string $topic, array $platforms): array`.
- Produces: `TrendResultStore::put(array $result): string`, `TrendResultStore::get(string $id): ?array`.
- Later UI tasks consume result keys: `id`, `topic`, `generated_at`, `summary`, `actions`, `platforms`, `items`, `sentiment`, `charts`, `source_statuses`.

- [ ] **Step 1: Write failing service tests**

Create `tests/Unit/TrendAnalysisServiceTest.php`:

```php
<?php

namespace Tests\Unit;

use App\Services\AIInsightsService;
use App\Services\DataFilteringService;
use App\Services\InstagramService;
use App\Services\KMeansClusteringService;
use App\Services\NaiveBayesService;
use App\Services\TikTokService;
use App\Services\TrendAnalysisService;
use App\Services\TwitterService;
use App\Services\YouTubeService;
use Tests\TestCase;

class TrendAnalysisServiceTest extends TestCase
{
    public function test_analyze_returns_insight_first_result_contract(): void
    {
        $service = new TrendAnalysisService(
            $this->fakePlatformService('youtube'),
            $this->fakePlatformService('twitter'),
            $this->fakePlatformService('tiktok'),
            $this->fakePlatformService('instagram'),
            new DataFilteringService(),
            new NaiveBayesService(),
            new KMeansClusteringService(),
            $this->fakeInsightsService()
        );

        $result = $service->analyze('kopi susu', ['youtube', 'tiktok']);

        $this->assertSame('kopi susu', $result['topic']);
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('actions', $result);
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('source_statuses', $result);
        $this->assertContains($result['source_statuses']['youtube']['status'], ['real', 'demo', 'failed']);
    }

    private function fakePlatformService(string $platform): object
    {
        return new class($platform) {
            public function __construct(private string $platform)
            {
            }

            public function search(string $topic, int $limit = 10): array
            {
                return [[
                    'platform' => $this->platform,
                    'external_id' => $this->platform.'-1',
                    'author' => 'creator_'.$this->platform,
                    'content' => 'Konten tentang '.$topic,
                    'likes' => 100,
                    'comments' => 12,
                    'views' => 1000,
                    'link' => 'https://example.test/'.$this->platform,
                ]];
            }
        };
    }

    private function fakeInsightsService(): AIInsightsService
    {
        return new class extends AIInsightsService {
            public function generateInsights($topic, $posts, $sentimentAnalysis, $clusteringResult, $filteringStats): array
            {
                return [
                    'overview' => [
                        'summary' => 'Topik '.$topic.' sedang punya sinyal positif untuk dicoba.',
                        'dominant_sentiment' => 'positive',
                    ],
                    'recommendations' => [[
                        'title' => 'Buat konten edukasi singkat',
                        'description' => 'Gunakan angle praktis yang mudah dicoba.',
                        'action_items' => ['Buat video 30 detik', 'Gunakan hook di 3 detik pertama'],
                    ]],
                ];
            }
        };
    }
}
```

- [ ] **Step 2: Run service test to verify it fails**

Run:

```powershell
php artisan test --filter=TrendAnalysisServiceTest
```

Expected: fails because `TrendAnalysisService` does not exist.

- [ ] **Step 3: Add platform normalization helper**

Create `app/Support/SocialInsight/SocialPlatform.php`:

```php
<?php

namespace App\Support\SocialInsight;

use Illuminate\Support\Arr;

class SocialPlatform
{
    public const ALL = ['youtube', 'twitter', 'tiktok', 'instagram'];

    public static function normalizeItem(array $item, string $platform, string $status = 'real'): array
    {
        return [
            'platform' => strtolower((string) ($item['platform'] ?? $platform)),
            'external_id' => isset($item['external_id']) ? (string) $item['external_id'] : null,
            'author' => $item['author'] ?? null,
            'content' => (string) ($item['content'] ?? $item['title'] ?? ''),
            'url' => $item['url'] ?? $item['link'] ?? null,
            'published_at' => $item['published_at'] ?? null,
            'engagement' => [
                'likes' => (int) Arr::get($item, 'engagement.likes', $item['likes'] ?? 0),
                'comments' => (int) Arr::get($item, 'engagement.comments', $item['comments'] ?? 0),
                'views' => (int) Arr::get($item, 'engagement.views', $item['views'] ?? 0),
                'shares' => (int) Arr::get($item, 'engagement.shares', $item['shares'] ?? 0),
            ],
            'sentiment' => $item['sentiment'] ?? null,
            'sentiment_score' => isset($item['sentiment_score']) ? (float) $item['sentiment_score'] : null,
            'source_status' => $status,
        ];
    }

    public static function demoItems(string $platform, string $topic): array
    {
        return [
            self::normalizeItem([
                'platform' => $platform,
                'external_id' => 'demo-'.$platform.'-1',
                'author' => 'demo_creator',
                'content' => 'Contoh percakapan creator tentang '.$topic,
                'likes' => 240,
                'comments' => 31,
                'views' => 5200,
                'link' => 'https://example.test/demo/'.$platform,
            ], $platform, 'demo'),
        ];
    }
}
```

- [ ] **Step 4: Add short-lived result store**

Create `app/Services/TrendResultStore.php`:

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TrendResultStore
{
    public function put(array $result): string
    {
        $id = (string) Str::uuid();
        $result['id'] = $id;

        Cache::put($this->key($id), $result, now()->addMinutes(30));

        return $id;
    }

    public function get(string $id): ?array
    {
        $result = Cache::get($this->key($id));

        return is_array($result) ? $result : null;
    }

    private function key(string $id): string
    {
        return 'socialinsight:trend-result:'.$id;
    }
}
```

- [ ] **Step 5: Add the analysis coordinator**

Create `app/Services/TrendAnalysisService.php` with constructor dependencies matching the current controller. The `analyze()` method returns:

```php
return [
    'id' => null,
    'topic' => $topic,
    'generated_at' => now()->toIso8601String(),
    'summary' => [
        'headline' => $headline,
        'trend' => $overview,
        'dominant_sentiment' => $dominantSentiment,
        'audience_mood' => $audienceMood,
        'best_angle' => $bestAngle,
        'risk_note' => $riskNote,
    ],
    'actions' => $actions,
    'platforms' => $platformBuckets,
    'items' => $items,
    'sentiment' => $sentimentSummary,
    'charts' => [
        'sentiment' => $sentimentSummary,
        'platforms' => $platformSummary,
    ],
    'source_statuses' => $sourceStatuses,
    'filtering' => $filteringStats,
    'clustering' => $clusteringMetrics,
];
```

Implementation rules:

- call only selected platforms;
- wrap each platform call in `try/catch`;
- on exception, return `status = failed`, user-safe `message`, and demo items only when the platform service has no credential or returns empty;
- call `SocialPlatform::normalizeItem()` for every item;
- run current filtering, Naive Bayes, K-Means, and AI insights services against normalized items;
- derive fallback summary/actions from sentiment and engagement if AI insights are empty.

- [ ] **Step 6: Thin the controller**

Modify `TrendController`:

```php
use App\Http\Requests\AnalyzeTrendRequest;
use App\Services\TrendAnalysisService;
use App\Services\TrendResultStore;

public function analyzeTrend(
    AnalyzeTrendRequest $request,
    TrendAnalysisService $analysis,
    TrendResultStore $store
) {
    $data = $request->validated();
    $result = $analysis->analyze($data['topic'], $data['platforms']);
    $result['id'] = $store->put($result);

    return view('result', ['result' => $result]);
}
```

Keep `home()` intact for Task 4.

- [ ] **Step 7: Run service and flow tests**

Run:

```powershell
php artisan test --filter=TrendAnalysisServiceTest
php artisan test --filter=ProductRebuildFlowTest
```

Expected: service test passes; flow tests pass except view assertions that will be updated with new UI in later tasks.

## Task 3: Safe Export For Current Bounded Results

**Files:**
- Modify: `app/Http/Controllers/TrendController.php`
- Modify: `resources/views/result.blade.php`
- Test: `tests/Feature/ProductRebuildFlowTest.php`

**Interfaces:**
- Consumes: `TrendResultStore::get(string $id): ?array`.
- Produces: `GET /export/trend?id=<uuid>` CSV response with only the cached current result.

- [ ] **Step 1: Add failing export tests**

Append to `ProductRebuildFlowTest`:

```php
public function test_export_requires_existing_result_id(): void
{
    $this->get(route('export.trend'))
        ->assertRedirect('/')
        ->assertSessionHas('error');
}

public function test_export_returns_bounded_safe_csv(): void
{
    $store = app(\App\Services\TrendResultStore::class);

    $id = $store->put([
        'topic' => 'kopi susu',
        'items' => [[
            'platform' => 'youtube',
            'author' => '=bad',
            'content' => '+formula',
            'sentiment' => 'positive',
            'sentiment_score' => 0.91,
            'engagement' => ['likes' => 5, 'comments' => 2, 'views' => 100, 'shares' => 0],
            'url' => 'https://example.test/video',
        ]],
    ]);

    $this->get(route('export.trend', ['id' => $id]))
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8')
        ->assertSee("'=bad", false)
        ->assertSee("'+formula", false);
}
```

- [ ] **Step 2: Run export tests to verify failure**

Run:

```powershell
php artisan test --filter=export
```

Expected: fails because export still reads global `Post` records.

- [ ] **Step 3: Implement bounded export**

Modify `TrendController::exportTrend()`:

```php
public function exportTrend(Request $request, TrendResultStore $store)
{
    $id = (string) $request->query('id', '');
    $result = $id !== '' ? $store->get($id) : null;

    if (! $result) {
        return redirect()->route('home')->with('error', 'Hasil analisis sudah kedaluwarsa. Jalankan analisis lagi untuk export.');
    }

    $topic = \Illuminate\Support\Str::slug((string) ($result['topic'] ?? 'trend')) ?: 'trend';
    $filename = 'socialinsight-'.$topic.'-'.now()->format('Y-m-d').'.csv';

    return response()->streamDownload(function () use ($result) {
        $file = fopen('php://output', 'w');
        fputcsv($file, ['Platform', 'Author', 'Content', 'Sentiment', 'Score', 'Likes', 'Comments', 'Views', 'Shares', 'URL']);

        foreach (array_slice($result['items'] ?? [], 0, 100) as $item) {
            fputcsv($file, [
                $item['platform'] ?? '',
                $this->safeCsvCell($item['author'] ?? ''),
                $this->safeCsvCell($item['content'] ?? ''),
                $item['sentiment'] ?? '',
                $item['sentiment_score'] ?? '',
                $item['engagement']['likes'] ?? 0,
                $item['engagement']['comments'] ?? 0,
                $item['engagement']['views'] ?? 0,
                $item['engagement']['shares'] ?? 0,
                $item['url'] ?? '',
            ]);
        }

        fclose($file);
    }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
}

private function safeCsvCell(mixed $value): string
{
    $text = (string) $value;

    return preg_match('/^[=+\-@]/', $text) ? "'".$text : $text;
}
```

- [ ] **Step 4: Update result export link**

In `resources/views/result.blade.php`, use:

```blade
<a href="{{ route('export.trend', ['id' => $result['id']]) }}">
    Download CSV
</a>
```

- [ ] **Step 5: Run export tests**

Run:

```powershell
php artisan test --filter=export
```

Expected: export tests pass.

## Task 4: Creator-Focused Home Page

**Files:**
- Modify: `resources/views/layouts/app-tailwind.blade.php`
- Modify: `resources/views/home.blade.php`
- Test: `tests/Feature/ProductRebuildFlowTest.php`

**Interfaces:**
- Consumes: `route('analyze.trend')`.
- Produces: form fields `topic` and `platforms[]`.

- [ ] **Step 1: Add home UI assertions**

Extend `test_home_page_loads_as_creator_tool()`:

```php
$this->get('/')
    ->assertOk()
    ->assertSee('Asisten tren untuk creator dan UMKM')
    ->assertSee('YouTube')
    ->assertSee('TikTok')
    ->assertSee('Instagram')
    ->assertSee('Twitter');
```

- [ ] **Step 2: Run home test to verify failure**

Run:

```powershell
php artisan test --filter=home_page_loads
```

Expected: fails until the home view is rebuilt.

- [ ] **Step 3: Rebuild shared layout shell**

In `resources/views/layouts/app-tailwind.blade.php`, keep Vite assets and Blade stacks, then ensure the body has:

```blade
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
    <main>
        @yield('content')
    </main>
    @stack('scripts')
</body>
```

Preserve any required `@vite(['resources/css/app.css', 'resources/js/app.js'])`.

- [ ] **Step 4: Rebuild home**

Replace `resources/views/home.blade.php` with a creator-tool first screen:

```blade
@extends('layouts.app-tailwind')

@section('title', 'SocialInsight')

@section('content')
<section class="min-h-screen bg-[radial-gradient(circle_at_top_left,#ec4899_0,#111827_34%,#020617_100%)]">
    <div class="mx-auto flex min-h-screen max-w-6xl flex-col px-6 py-8">
        <nav class="flex items-center justify-between">
            <a href="{{ route('home') }}" class="text-xl font-black tracking-tight">SocialInsight</a>
            <a href="{{ route('dashboard') }}" class="rounded-full border border-white/15 px-4 py-2 text-sm text-white/80 hover:bg-white/10">Dashboard</a>
        </nav>

        <div class="grid flex-1 items-center gap-10 py-12 lg:grid-cols-[1.1fr_0.9fr]">
            <div>
                <p class="mb-4 inline-flex rounded-full bg-white/10 px-4 py-2 text-sm text-pink-100 ring-1 ring-white/15">Asisten tren untuk creator dan UMKM</p>
                <h1 class="max-w-3xl text-5xl font-black leading-tight text-white md:text-7xl">Cari angle konten dari sinyal sosial terbaru.</h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-200">Masukkan topik, pilih platform, lalu dapatkan ringkasan tren, mood audiens, ide aksi, dan bukti post yang bisa dicek.</p>
            </div>

            <form action="{{ route('analyze.trend') }}" method="POST" class="rounded-3xl bg-white p-6 text-slate-950 shadow-2xl">
                @csrf
                <label for="topic" class="text-sm font-bold text-slate-700">Topik yang mau dianalisis</label>
                <input id="topic" name="topic" value="{{ old('topic') }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-4 text-lg outline-none focus:border-pink-500 focus:ring-4 focus:ring-pink-100" placeholder="Contoh: kopi susu, skincare lokal, menu bukber" required>
                @error('topic')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror

                <div class="mt-6">
                    <p class="text-sm font-bold text-slate-700">Platform</p>
                    <div class="mt-3 grid grid-cols-2 gap-3">
                        @foreach(['youtube' => 'YouTube', 'twitter' => 'Twitter/X', 'tiktok' => 'TikTok', 'instagram' => 'Instagram'] as $value => $label)
                            <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 p-3 hover:border-pink-300">
                                <input type="checkbox" name="platforms[]" value="{{ $value }}" class="h-4 w-4 rounded border-slate-300 text-pink-600" checked>
                                <span class="text-sm font-semibold">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-2">
                    @foreach(['Kopi susu viral', 'Skincare lokal', 'Menu Ramadan', 'Fashion thrift'] as $topic)
                        <button type="button" class="rounded-full bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-pink-100" onclick="document.getElementById('topic').value='{{ $topic }}'">{{ $topic }}</button>
                    @endforeach
                </div>

                <button class="mt-6 w-full rounded-2xl bg-pink-600 px-5 py-4 text-base font-black text-white hover:bg-pink-700">Analisis Tren</button>
                <p class="mt-4 text-center text-xs text-slate-500">Tanpa login. Data real dipakai saat API key tersedia; sisanya ditandai sebagai demo.</p>
            </form>
        </div>
    </div>
</section>
@endsection
```

- [ ] **Step 5: Run home test**

Run:

```powershell
php artisan test --filter=home_page_loads
```

Expected: passes.

## Task 5: Insight-First Result Page

**Files:**
- Modify: `resources/views/result.blade.php`
- Test: `tests/Feature/ProductRebuildFlowTest.php`

**Interfaces:**
- Consumes: `$result` view model from `TrendAnalysisService`.
- Produces: visible sections `Jawaban cepat`, `Rekomendasi aksi`, `Bukti dari platform`, and bounded export link.

- [ ] **Step 1: Add result rendering test**

Add to `ProductRebuildFlowTest`:

```php
public function test_result_view_renders_insight_first_contract(): void
{
    $result = [
        'id' => 'test-result',
        'topic' => 'kopi susu',
        'generated_at' => now()->toIso8601String(),
        'summary' => [
            'headline' => 'Kopi susu sedang kuat untuk konten praktis.',
            'trend' => 'Percakapan fokus pada harga, rasa, dan ide menu.',
            'dominant_sentiment' => 'positive',
            'audience_mood' => 'Penasaran dan siap mencoba.',
            'best_angle' => 'Menu hemat untuk creator kuliner.',
            'risk_note' => 'Hindari klaim rasa yang terlalu berlebihan.',
        ],
        'actions' => [[
            'title' => 'Buat video resep 30 detik',
            'description' => 'Tunjukkan bahan, harga, dan hasil akhir.',
            'steps' => ['Hook visual', 'Harga bahan', 'CTA komentar'],
        ]],
        'charts' => [
            'sentiment' => ['positive' => 1, 'negative' => 0, 'neutral' => 0],
            'platforms' => ['youtube' => 1],
        ],
        'source_statuses' => [
            'youtube' => ['status' => 'demo', 'message' => 'Demo data'],
        ],
        'items' => [[
            'platform' => 'youtube',
            'author' => 'creator',
            'content' => 'Konten kopi susu',
            'url' => 'https://example.test',
            'sentiment' => 'positive',
            'engagement' => ['likes' => 10, 'comments' => 2, 'views' => 100, 'shares' => 0],
        ]],
    ];

    $this->view('result', ['result' => $result])
        ->assertSee('Jawaban cepat')
        ->assertSee('Rekomendasi aksi')
        ->assertSee('Bukti dari platform')
        ->assertSee('Kopi susu sedang kuat');
}
```

- [ ] **Step 2: Run result view test to verify failure**

Run:

```powershell
php artisan test --filter=result_view
```

Expected: fails until the old result view is replaced.

- [ ] **Step 3: Rebuild result page**

Replace the old variable-heavy `result.blade.php` with a view that reads from `$result`. Required top-level sections:

```blade
@php
    $summary = $result['summary'];
    $actions = $result['actions'] ?? [];
    $items = $result['items'] ?? [];
    $statuses = $result['source_statuses'] ?? [];
@endphp
```

Render:

- header with topic and generated time;
- `Jawaban cepat` card using `$summary`;
- `Rekomendasi aksi` cards using `$actions`;
- sentiment/platform chart data from `$result['charts']`;
- source status badges from `$statuses`;
- evidence list from `$items`;
- export link with `route('export.trend', ['id' => $result['id']])`.

- [ ] **Step 4: Keep charts robust**

In the result script, guard Chart.js initialization:

```js
if (window.Chart) {
  const sentiment = @json($result['charts']['sentiment'] ?? []);
  const platforms = @json($result['charts']['platforms'] ?? []);
}
```

If `Chart` is unavailable, the page must still render the insight and evidence.

- [ ] **Step 5: Run result view test**

Run:

```powershell
php artisan test --filter=result_view
```

Expected: passes.

## Task 6: Platform HTTP Safety And Fallback Behavior

**Files:**
- Modify: `app/Services/YouTubeService.php`
- Modify: `app/Services/TwitterService.php`
- Modify: `app/Services/TikTokService.php`
- Modify: `app/Services/InstagramService.php`
- Test: `tests/Unit/TrendAnalysisServiceTest.php`

**Interfaces:**
- Platform `search(string $topic, int $limit = 10): array` remains compatible.
- Each returned item includes enough fields for `SocialPlatform::normalizeItem()`.

- [ ] **Step 1: Add regression scan test for disabled TLS**

Add to `TrendAnalysisServiceTest`:

```php
public function test_platform_services_do_not_disable_tls_verification(): void
{
    $files = [
        app_path('Services/YouTubeService.php'),
        app_path('Services/TwitterService.php'),
        app_path('Services/TikTokService.php'),
        app_path('Services/InstagramService.php'),
    ];

    foreach ($files as $file) {
        $this->assertStringNotContainsString('withoutVerifying()', file_get_contents($file), $file);
    }
}
```

- [ ] **Step 2: Run test to verify failure**

Run:

```powershell
php artisan test --filter=disabled_tls
```

Expected: fails while `withoutVerifying()` remains in service files.

- [ ] **Step 3: Replace unsafe HTTP chains**

In each platform service, replace:

```php
Http::withoutVerifying()
```

with:

```php
Http::timeout(10)->retry(2, 250)
```

If a service already has `Http::withHeaders()`, chain timeout/retry after headers:

```php
Http::withHeaders($headers)->timeout(10)->retry(2, 250)
```

- [ ] **Step 4: Make empty credentials explicit**

When an API key or token is missing, return demo-shaped items or an empty array that `TrendAnalysisService` can mark as demo. Do not call remote APIs with dummy credentials.

- [ ] **Step 5: Run TLS regression test**

Run:

```powershell
php artisan test --filter=disabled_tls
```

Expected: passes.

## Task 7: Dashboard As Demo/Status Overview

**Files:**
- Modify: `app/Http/Controllers/DashboardController.php`
- Modify: `resources/views/dashboard.blade.php`
- Test: `tests/Feature/ProductRebuildFlowTest.php`

**Interfaces:**
- Produces: dashboard view variables `platformStatuses`, `pipelineSteps`, and `demoMetrics`.

- [ ] **Step 1: Add dashboard test**

Add to `ProductRebuildFlowTest`:

```php
public function test_dashboard_is_status_overview_not_raw_post_history(): void
{
    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Status platform')
        ->assertSee('Cara kerja analisis')
        ->assertDontSee('Recent Posts');
}
```

- [ ] **Step 2: Run dashboard test to verify failure**

Run:

```powershell
php artisan test --filter=dashboard_is_status
```

Expected: fails while dashboard still renders old raw-post table.

- [ ] **Step 3: Update controller**

Modify `DashboardController@index` to return status/demo data without `Post::all()`:

```php
return view('dashboard', [
    'platformStatuses' => [
        'youtube' => ['label' => 'YouTube', 'mode' => config('services.youtube.key') ? 'real' : 'demo'],
        'twitter' => ['label' => 'Twitter/X', 'mode' => config('services.twitter.bearer_token') ? 'real' : 'demo'],
        'tiktok' => ['label' => 'TikTok', 'mode' => config('services.tiktok.client_key') ? 'real' : 'demo'],
        'instagram' => ['label' => 'Instagram', 'mode' => config('services.instagram.access_token') ? 'real' : 'demo'],
    ],
    'pipelineSteps' => ['Ambil sinyal', 'Filter relevansi', 'Baca sentimen', 'Susun rekomendasi'],
    'demoMetrics' => ['platforms' => 4, 'maxEvidence' => 100, 'cacheMinutes' => 30],
]);
```

- [ ] **Step 4: Rebuild dashboard view**

Render:

- title `Status platform`;
- cards for each platform mode;
- `Cara kerja analisis` pipeline;
- small note that no-login analysis does not save raw posts permanently.

- [ ] **Step 5: Run dashboard test**

Run:

```powershell
php artisan test --filter=dashboard_is_status
```

Expected: passes.

## Task 8: Legacy Tests, Formatting, And Full Verification

**Files:**
- Modify: `tests/Feature/TrendSearchTest.php`
- Modify: files touched by previous tasks if formatting finds issues.

**Interfaces:**
- Produces: full test suite result with no failing tests.

- [ ] **Step 1: Update old TrendSearchTest expectations**

Replace old redirect-on-error assumptions with new valid-flow assertions. The new test should mock `TrendAnalysisService`:

```php
public function test_can_perform_search(): void
{
    $this->mock(\App\Services\TrendAnalysisService::class, function ($mock) {
        $mock->shouldReceive('analyze')->once()->andReturn([
            'id' => null,
            'topic' => 'kopi susu',
            'generated_at' => now()->toIso8601String(),
            'summary' => [
                'headline' => 'Kopi susu sedang ramai.',
                'trend' => 'Topik punya sinyal positif.',
                'dominant_sentiment' => 'positive',
                'audience_mood' => 'Penasaran.',
                'best_angle' => 'Resep singkat.',
                'risk_note' => 'Jangan klaim berlebihan.',
            ],
            'actions' => [],
            'platforms' => [],
            'items' => [],
            'sentiment' => ['positive' => 0, 'negative' => 0, 'neutral' => 0],
            'charts' => ['sentiment' => [], 'platforms' => []],
            'source_statuses' => [],
        ]);
    });

    $response = $this->post(route('analyze.trend'), [
        'topic' => 'kopi susu',
        'platforms' => ['youtube'],
    ]);

    $response->assertOk()->assertSee('kopi susu');
}
```

- [ ] **Step 2: Run targeted tests**

Run:

```powershell
php artisan test --filter=ProductRebuildFlowTest
php artisan test --filter=TrendAnalysisServiceTest
php artisan test --filter=TrendSearchTest
```

Expected: all targeted tests pass.

- [ ] **Step 3: Run formatter**

Run:

```powershell
vendor\\bin\\pint
```

Expected: Pint completes and reports formatted files or no changes.

- [ ] **Step 4: Build frontend assets**

Run:

```powershell
npm run build
```

Expected: Vite build succeeds.

- [ ] **Step 5: Run full Laravel tests**

Run:

```powershell
php artisan test
```

Expected: no failures. Integration tests that intentionally skip without live services may remain skipped.

- [ ] **Step 6: Verify no disabled TLS remains**

Run:

```powershell
rg -n "withoutVerifying\\(" app
```

Expected: no matches.

- [ ] **Step 7: Verify no raw-post persistence in main flow**

Run:

```powershell
rg -n "Post::updateOrCreate|Post::create|raw" app\\Http app\\Services resources\\views
```

Expected: no `Post::updateOrCreate` or raw payload persistence in the main analysis flow. Existing migrations may still define legacy columns.

## Self-Review

- Spec coverage: covered home, analyze flow, result page, dashboard, backend architecture, no-login behavior, no raw permanent storage, API safety, export, and tests.
- Placeholder scan: the plan contains no placeholder work items; every task has concrete files, expected interfaces, and commands.
- Type consistency: `AnalyzeTrendRequest`, `TrendAnalysisService`, `TrendResultStore`, and `$result` view-model keys are consistent across tasks.
- Scope: the plan avoids auth, billing, admin, deployment, and raw-history persistence as required by the approved spec.
