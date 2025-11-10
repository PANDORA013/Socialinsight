# 🚀 Quick Start Guide

Mulai menggunakan SocialInsight dalam 5 menit!

---

## Prerequisites

```bash
✅ PHP 8.2+
✅ MySQL 8.0+
✅ Composer
✅ Node.js & npm
✅ Python 3.13+ (optional, for IndoBERT)
```

---

## Step 1: Clone & Install

```bash
# Clone repository
git clone [repo-url]
cd socialinsight

# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

---

## Step 2: Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Configure database in .env
DB_DATABASE=socialinsight
DB_USERNAME=root
DB_PASSWORD=
```

---

## Step 3: API Keys

Edit `.env` dan tambahkan API keys:

```env
# YouTube API
YOUTUBE_API_KEY=your_youtube_key

# Twitter API
TWITTER_BEARER_TOKEN=your_twitter_token

# TikTok API
TIKTOK_CLIENT_KEY=your_tiktok_key
TIKTOK_CLIENT_SECRET=your_tiktok_secret

# Instagram API
INSTAGRAM_ACCESS_TOKEN=your_instagram_token
```

---

## Step 4: Database Migration

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE socialinsight"

# Run migrations
php artisan migrate
```

---

## Step 5: Start Server

```bash
# Terminal 1: Laravel
php artisan serve

# Terminal 2: Vite
npm run dev
```

---

## Step 6: Test

```bash
# Open browser
http://localhost:8000

# Search for trending topic
Search: "K-Pop Idols"

# Click: "Analisis Sekarang"
```

---

## Optional: IndoBERT Setup

Untuk akurasi 99%, setup IndoBERT:

```bash
# Create virtual environment
python -m venv .venv

# Activate
.venv\Scripts\activate  # Windows
source .venv/bin/activate  # Linux/Mac

# Install dependencies
pip install transformers torch sentencepiece

# Test
python storage/app/python/analyze.py "Lagu ini bagus"
```

Edit `.env`:

```env
PYTHON_PATH=C:/xampp/htdocs/socialinsight/.venv/Scripts/python.exe
```

---

## ✅ You're Ready!

System siap digunakan dengan:

- ✅ 4 Platform integration
- ✅ Sentiment analysis (80% Naive Bayes)
- ✅ Topic clustering
- ✅ AI insights
- ✅ (Optional) IndoBERT 99% accuracy

---

**Next Steps:**

- [Full Installation Guide](INSTALLATION.md)
- [IndoBERT Setup](INDOBERT_SETUP.md)
- [API Configuration](../api/)
