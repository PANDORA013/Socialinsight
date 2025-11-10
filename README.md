# 🔍 SocialInsight

A Laravel-based social media sentiment analysis platform that analyzes comments and posts from YouTube, Instagram, and TikTok using AI-powered sentiment analysis.

## ✨ Features

- 📊 **Real-time Dashboard** - View sentiment statistics and recent analyzed posts
- 🎥 **YouTube Integration** - Analyze comments from any YouTube video
- � **Twitter/X Integration** - Analyze tweet replies and conversations
- �📸 **Instagram Support** (Coming Soon) - Analyze Instagram post comments
- 🎵 **TikTok Support** (API Ready) - TikTok integration prepared
- 🤖 **AI Sentiment Analysis** - Powered by OpenAI GPT for accurate sentiment detection
- 📈 **Sentiment Tracking** - Track positive, negative, and neutral sentiments
- 🎨 **Modern UI** - Beautiful, responsive interface built with Bootstrap

## 🚀 Installation

### Prerequisites

- PHP 8.1 or higher
- Composer
- Node.js & NPM
- SQLite (or MySQL/PostgreSQL)

### Setup Instructions

1. **Clone the repository**
```bash
cd c:\xampp\htdocs
git clone https://github.com/yourusername/socialinsight.git
cd socialinsight
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Install Node dependencies**
```bash
npm install
```

4. **Configure environment**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Setup API Keys**

Edit `.env` file and add your API keys:
```env
# YouTube API Key (Get from: https://console.cloud.google.com/apis/credentials)
YOUTUBE_API_KEY=your_youtube_api_key

# Twitter/X Bearer Token (Get from: https://developer.twitter.com/)
TWITTER_BEARER_TOKEN=your_twitter_bearer_token

# OpenAI API Key (Get from: https://platform.openai.com/api-keys)
OPENAI_API_KEY=your_openai_api_key
```

**Note**: The app works without OpenAI key using basic sentiment analysis!

6. **Run migrations**
```bash
php artisan migrate
```

7. **Seed database (optional)**
```bash
php artisan db:seed --class=PostSeeder
```

8. **Build assets**
```bash
npm run build
```

9. **Start the development server**
```bash
php artisan serve
```

Visit: `http://localhost:8000`

## 📖 Usage

### Analyzing YouTube Comments

1. Navigate to the Dashboard
2. Click "Analyze" > "YouTube"
3. Paste a YouTube video URL
4. Click "Analyze Comments"
5. View results in the Dashboard

### Analyzing Twitter/X Replies

1. Navigate to the Dashboard
2. Click "Analyze" > "Twitter/X"
3. Paste a Tweet URL
4. Click "Analyze Replies"
5. View results in the Dashboard

### API Usage

The application also provides a RESTful API:

**Analyze YouTube:**
```bash
POST /api/analyze/youtube
Content-Type: application/json

{
  "video_url": "https://www.youtube.com/watch?v=VIDEO_ID"
}
```

**Analyze Twitter/X:**
```bash
POST /api/analyze/twitter
Content-Type: application/json

{
  "tweet_url": "https://twitter.com/username/status/TWEET_ID"
}
```

## 🏗️ Project Structure

```
socialinsight/
├── app/
│   ├── Console/           # Artisan commands
│   ├── Exceptions/        # Exception handlers
│   ├── Http/
│   │   ├── Controllers/   # Web & API controllers
│   │   └── Middleware/    # HTTP middleware
│   ├── Models/            # Eloquent models
│   ├── Providers/         # Service providers
│   └── Services/          # Business logic services
├── config/                # Configuration files
├── database/
│   ├── migrations/        # Database migrations
│   └── seeders/           # Database seeders
├── resources/
│   └── views/             # Blade templates
├── routes/
│   ├── web.php            # Web routes
│   └── api.php            # API routes
└── storage/               # File storage & logs
```

## 🔑 API Keys Setup

### YouTube Data API

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing
3. Enable "YouTube Data API v3"
4. Create credentials (API Key)
5. Add the key to `.env` as `YOUTUBE_API_KEY`

### OpenAI API

1. Visit [OpenAI Platform](https://platform.openai.com/)
2. Sign up or log in
3. Navigate to API Keys section
4. Create a new API key
5. Add the key to `.env` as `OPENAI_API_KEY`

## 🛠️ Technologies Used

- **Laravel 11** - PHP framework
- **SQLite** - Database
- **Bootstrap 5** - Frontend framework
- **YouTube Data API v3** - Fetch YouTube comments
- **OpenAI GPT** - Sentiment analysis
- **Vite** - Asset bundling

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📧 Contact

For questions or support, please open an issue on GitHub.

---

Made with ❤️ using Laravel

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
