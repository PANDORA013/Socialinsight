# 🏗️ System Architecture

Dokumentasi arsitektur sistem SocialInsight.

---

## 📐 Layer Architecture

```text
┌─────────────────────────────────────────────┐
│         PRESENTATION LAYER                  │
│  (HTML, CSS, JavaScript, Blade Templates)   │
└─────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────┐
│         APPLICATION LAYER                   │
│  (Laravel Controllers, Routes, Middleware)  │
└─────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────┐
│         BUSINESS LOGIC LAYER                │
│  (Services: AI, Filter, Clustering)         │
└─────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────┐
│         DATA ACCESS LAYER                   │
│  (API Services: YouTube, Twitter, etc)      │
└─────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────┐
│         EXTERNAL SERVICES                   │
│  (YouTube API, Twitter API, Python ML)      │
└─────────────────────────────────────────────┘
```

---

## 🔄 Data Flow

```text
START
  │
  ├─→ [1] User Input Query
  ├─→ [2] Fetch Data (4 Platforms)
  ├─→ [3] Apply Filters (5 Stages)
  ├─→ [4] Sentiment Analysis (Hybrid)
  ├─→ [5] Topic Clustering (K-Means)
  ├─→ [6] Generate AI Insights
  └─→ [7] Display Results
```

---

## 📦 Component Diagram

```text
User Browser → Laravel Router → TrendController
                                      ↓
                              AIInsightsService
                                      ↓
                    ┌─────────────────┼─────────────────┐
                    ↓                 ↓                 ↓
            NaiveBayesService  IndoBERTService  KMeansService
                                      ↓
                              Python Script
                              (analyze.py)
```

---

## 🔧 Service Layer

### Core Services

- **AIInsightsService** - Orchestrator utama
- **NaiveBayesService** - Sentiment analysis cepat
- **IndoBERTService** - Deep learning sentiment
- **KMeansClusteringService** - Topic grouping
- **DataFilteringService** - Data quality control

### API Services

- **YouTubeService** - YouTube Data API v3
- **TwitterService** - Twitter API v2
- **TikTokService** - TikTok API + OAuth
- **InstagramService** - Instagram Graph API

---

## 💾 Database Schema

```sql
-- Sessions table
sessions (
    id, user_id, payload, last_activity
)

-- Cache table
cache (
    key, value, expiration
)

-- Analysis results (optional)
analysis_results (
    id, query, platform, data, created_at
)
```

---

## 🚀 Deployment Architecture

### Development

```text
localhost:8000 (PHP Dev Server)
     ↓
Laravel Application
     ↓
MySQL (localhost)
```

### Production

```text
Web Server (Apache/Nginx)
     ↓
PHP-FPM
     ↓
Laravel Application
     ↓
MySQL (Remote/Cloud)
     ↓
Redis (Cache)
```

---

**See also:**

- [Algorithms](ALGORITHMS.md)
- [Filter System](FILTERS.md)
- [Technology Stack](TECH_STACK.md)
