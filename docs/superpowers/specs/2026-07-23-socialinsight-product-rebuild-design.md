# SocialInsight Product Rebuild Design

## Goal

Rebuild SocialInsight into a creator-focused trend analysis tool for public, no-login usage. The product should help UMKM owners and content creators enter a topic, understand what is trending across social platforms, and leave with practical content actions.

## Product Positioning

SocialInsight is not an internal analyst dashboard in this rebuild. It is a lightweight trend assistant for non-technical users who need quick answers:

- What is the current mood around this topic?
- Which platforms are showing useful signals?
- What content angles should I try?
- What risks or negative reactions should I avoid?
- Which source posts support the recommendation?

Login is not required. Because the app remains public, analysis and export routes must be rate-limited and validated.

## Scope

This rebuild covers:

- Home/search experience.
- Trend analysis flow.
- Result page with insight-first output and evidence below it.
- Dashboard as demo/status overview, not raw-history analytics.
- Backend service structure for analysis orchestration.
- API safety: validation, rate limits, HTTP client timeouts, TLS verification, safe error handling.
- Export behavior that does not dump the full database.
- Tests for the main user flow and service fallback behavior.

This rebuild does not include:

- Mandatory login.
- Paid SaaS billing.
- Long-term storage of raw social posts.
- Full admin panel.
- Production deployment.

## Visual Direction

Use a "creator trend tool" style:

- Energetic but still readable.
- Platform-aware colors for YouTube, Twitter/X, TikTok, and Instagram.
- Strong insight cards above technical charts.
- Rounded cards are acceptable, but keep hierarchy clear and avoid nested card clutter.
- Copy should be plain Indonesian, focused on what the user can do next.
- Avoid a generic marketing landing page. The first screen should be the usable analysis tool.

## User Flow

1. User opens the home page.
2. User enters a topic, selects platforms, and submits analysis.
3. App validates the request and applies route-level rate limiting.
4. Backend gathers social data from available real APIs.
5. If a platform key is missing or an external API fails, the platform returns an explicit demo/fallback status instead of a hard crash.
6. Backend normalizes all platform results into one result contract.
7. Sentiment, filtering, clustering, and AI insight services process the normalized results.
8. Result page shows insight summary first, then charts, then source evidence.
9. User may export the current bounded result set only.

## Home Page

The home page should be a working product surface:

- Headline: SocialInsight as a creator trend assistant.
- Search input for `topic`.
- Platform selector with YouTube, Twitter/X, TikTok, and Instagram.
- Quick topic chips for creator-friendly examples.
- Small platform status area showing real API or demo mode.
- Clear submit button.
- Short trust message: no login required, results depend on available API access.

The form submits to the existing `analyze.trend` route unless the implementation plan finds a cleaner route name with low migration cost.

## Result Page

The result page should be ordered for non-technical users:

1. Topic header and data freshness.
2. "Jawaban cepat" panel with:
   - trend summary,
   - dominant sentiment,
   - audience mood,
   - best content angle,
   - risk note.
3. Action cards with 3-5 recommendations.
4. Platform and sentiment charts.
5. Evidence list grouped by platform.
6. Export action for the bounded current result.

Raw source content can be shown as evidence, but it must not be stored permanently as a full raw dump.

## Dashboard

The dashboard should be rebuilt as a demo/status overview:

- Platform availability.
- Count of current or sample signals.
- Data quality indicators.
- How the analysis pipeline works at a high level.
- Recent analysis summary only if it can be represented without storing raw content permanently.

It should not rely on `Post::all()` or a global raw-post history as the primary product experience.

## Backend Architecture

Keep Laravel 12 and Blade/Tailwind/Vite.

Introduce a clearer analysis boundary:

- A form request validates trend analysis input.
- A route-level rate limiter protects public analysis and export endpoints.
- A coordinator service gathers platform data.
- Each platform service returns normalized arrays with a shared shape.
- A result assembler prepares view models for Blade.
- Export uses the current bounded result payload or an intentionally limited aggregate, not the full posts table.

The current `TrendController` should become thin. It should validate, call the coordinator, and return views/responses.

## Data Contract

Each normalized social item should include:

- `platform`: `youtube`, `twitter`, `tiktok`, or `instagram`.
- `external_id`: nullable string.
- `author`: nullable string.
- `content`: string.
- `url`: nullable string.
- `published_at`: nullable datetime string.
- `engagement`: array with `likes`, `comments`, `views`, and `shares` numeric values.
- `sentiment`: nullable string after processing.
- `sentiment_score`: nullable float after processing.
- `source_status`: `real`, `demo`, or `failed`.

Each platform result should also include metadata:

- `platform`.
- `status`: `real`, `demo`, or `failed`.
- `message`: user-safe status text.
- `items`: normalized social items.

## Persistence

Do not store raw posts permanently in the main analysis flow.

Allowed persistence:

- cache short-lived normalized results when useful for export or refresh;
- store aggregate counts only if needed for dashboard;
- keep the existing `posts` table for compatibility until safely removed or repurposed.

Disallowed persistence:

- dumping full raw API payloads into the database;
- exporting every global post from the database;
- showing one user's raw search results as another user's dashboard data.

## Security And Reliability

Use Laravel 12 patterns from the official docs:

- web routes keep CSRF protection for forms;
- route middleware applies named rate limiters to public analysis/export routes;
- request validation rejects invalid topics and platform lists;
- Laravel HTTP client uses TLS verification, timeout, retry, and safe failure handling;
- exceptions are logged server-side, while users see clear non-sensitive messages;
- CSV export uses safe CSV writing and neutralizes spreadsheet formula injection.

The rebuild must remove `Http::withoutVerifying()` from platform services unless there is a clearly documented local-only development exception that cannot affect production.

## Testing

Add or update tests for:

- home page loads;
- trend form validation rejects empty or invalid topic;
- analyze route accepts valid topic and selected platforms;
- platform failures return demo/failed status without a 500;
- no raw posts are persisted during the normal no-login analysis flow;
- export returns bounded CSV data;
- public analysis route has a rate limiter attached;
- main Blade views render with representative result data.

Use Laravel HTTP testing and fakes/mocks for external APIs. Tests must not require live YouTube, Twitter/X, TikTok, Instagram, OpenAI, or IndoBERT services.

## Documentation References

Use the official Laravel 12 documentation during implementation:

- Routing and rate limiting: https://laravel.com/docs/12.x/routing
- Validation: https://laravel.com/docs/12.x/validation
- HTTP client: https://laravel.com/docs/12.x/http-client
- Eloquent and mass assignment: https://laravel.com/docs/12.x/eloquent
- HTTP tests: https://laravel.com/docs/12.x/http-tests

## Acceptance Criteria

- A non-technical creator or UMKM user can enter a topic and understand the result without reading tables first.
- The result page clearly separates insight, action, charts, and evidence.
- Missing API credentials or failed external APIs do not crash the user flow.
- The app does not permanently store raw social posts in the main no-login flow.
- Public analysis/export endpoints are validated and rate-limited.
- TLS verification is enabled for external HTTP calls.
- Export is bounded and CSV-safe.
- Automated tests cover the main flow without live external APIs.
