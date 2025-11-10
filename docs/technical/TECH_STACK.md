# 💻 Technology Stack

Dokumentasi lengkap teknologi yang digunakan.

---

## Backend

### PHP 8.2+

- **Framework:** Laravel 11
- **Purpose:** Main application logic
- **Features:**
  - MVC architecture
  - Eloquent ORM
  - Blade templating
  - Artisan CLI

### Python 3.13.9

- **Environment:** Virtual environment (.venv)
- **Purpose:** Machine Learning
- **Libraries:**
  - transformers (Hugging Face)
  - torch (PyTorch)
  - sentencepiece (Tokenization)

---

## Frontend

### HTML5

- **Purpose:** Structure & markup
- **Files:** Blade templates (`.blade.php`)

### CSS3

- **Framework:** Tailwind CSS v3
- **Approach:** Utility-first CSS
- **Features:**
  - Responsive design
  - Dark mode support
  - Custom components

### JavaScript

- **Type:** Vanilla JS (no framework)
- **Purpose:**
  - AJAX requests
  - Dynamic content
  - Chart rendering
  - Animations

### Chart.js

- **Purpose:** Data visualization
- **Usage:**
  - Sentiment distribution
  - Trend graphs
  - Engagement charts

---

## Database

### MySQL 8.0

- **Purpose:** Data storage
- **Tables:**
  - sessions
  - cache
  - analysis_results (optional)

---

## Machine Learning

### Models

1. **Naive Bayes**
   - Library: PHP-ML
   - Purpose: Fast sentiment analysis

2. **IndoBERT/RoBERTa**
   - Model: w11wo/indonesian-roberta-base-sentiment-classifier
   - Size: 499MB
   - Accuracy: 99%

3. **K-Means Clustering**
   - Library: PHP-ML
   - Purpose: Topic grouping

---

## External APIs

### YouTube Data API v3

- **Endpoint:** googleapis.com/youtube/v3
- **Methods:**
  - search
  - videos
  - commentThreads
- **Rate Limit:** 10,000 units/day

### Twitter API v2

- **Endpoint:** api.twitter.com/2
- **Methods:**
  - tweets/search/recent
- **Rate Limit:** 500 requests/15min
- **Auth:** Bearer Token

### TikTok API

- **Endpoint:** open.tiktokapis.com
- **Methods:**
  - user.info
  - video.list
- **Auth:** OAuth 2.0

### Instagram Graph API

- **Endpoint:** graph.instagram.com
- **Methods:**
  - media
  - insights
- **Auth:** OAuth 2.0

---

## Development Tools

### Build Tools

- **Vite 5:** Asset bundling
- **npm:** Package management
- **Composer:** PHP dependencies

### Version Control

- **Git:** Source control
- **GitHub:** Repository hosting

### Testing

- **PHPUnit:** Unit testing
- **Test Scripts:**
  - test-indobert.php
  - test-ai-insights.php
  - test-complete-system.php

---

## Deployment

### Development

```bash
# PHP Dev Server
php artisan serve

# Vite Dev Server
npm run dev
```

### Production

```text
- Web Server: Apache/Nginx
- PHP: PHP-FPM
- Database: MySQL (Cloud)
- Cache: Redis (optional)
```

---

## Performance

### Metrics

```text
Page Load: 1-2 seconds (cached)
Analysis Time: 25-70 seconds
Memory Usage: 128-256MB
Database Queries: 5-10 per request
```

### Caching

- Config cache (production)
- Route cache (production)
- View cache (auto-compiled)
- Data cache (1 hour TTL)

---

## Security

### Features

- CSRF Protection
- XSS Protection (Blade escaping)
- SQL Injection Prevention (PDO)
- Rate Limiting (60/min)
- Environment Variables (.env)

---

**See also:**

- [System Architecture](SYSTEM_ARCHITECTURE.md)
- [Algorithms](ALGORITHMS.md)
- [Installation Guide](../setup/INSTALLATION.md)
