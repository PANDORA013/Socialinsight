@echo off
echo ==========================================
echo  SocialInsight - Quick Start Setup
echo ==========================================
echo.

echo [1/5] Installing Composer dependencies...
call composer install

echo.
echo [2/5] Installing NPM dependencies...
call npm install

echo.
echo [3/5] Building frontend assets...
call npm run build

echo.
echo [4/5] Running database migrations...
call php artisan migrate:fresh

echo.
echo [5/5] Clearing caches...
call php artisan config:clear
call php artisan route:clear
call php artisan view:clear

echo.
echo ==========================================
echo  Setup Complete!
echo ==========================================
echo.
echo Next steps:
echo 1. Start dev server: npm run dev
echo 2. Start Laravel: php artisan serve
echo 3. Open browser: http://localhost:8000
echo.
pause
