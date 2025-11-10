@echo off
echo ================================
echo Starting SocialInsight Server
echo ================================
echo.

cd /d %~dp0

echo Starting Laravel development server...
echo.
echo Server will be available at: http://localhost:8000
echo.
echo Press Ctrl+C to stop the server
echo.
echo ================================

php artisan serve
