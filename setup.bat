@echo off
echo ================================
echo SocialInsight Setup Script
echo ================================
echo.

cd /d %~dp0

echo [1/6] Creating MySQL Database...
echo Please make sure MySQL is running in XAMPP!
echo.
pause

mysql -u root -e "CREATE DATABASE IF NOT EXISTS socialinsight CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if %errorlevel% neq 0 (
    echo ERROR: Could not create database. Make sure MySQL is running!
    pause
    exit /b 1
)
echo ✓ Database created successfully
echo.

echo [2/6] Installing Composer Dependencies...
call composer install
if %errorlevel% neq 0 (
    echo ERROR: Composer install failed!
    pause
    exit /b 1
)
echo ✓ Composer dependencies installed
echo.

echo [3/6] Installing NPM Dependencies...
call npm install
if %errorlevel% neq 0 (
    echo ERROR: NPM install failed!
    pause
    exit /b 1
)
echo ✓ NPM dependencies installed
echo.

echo [4/6] Running Database Migrations...
call php artisan migrate --force
if %errorlevel% neq 0 (
    echo ERROR: Migration failed!
    pause
    exit /b 1
)
echo ✓ Database migrated successfully
echo.

echo [5/6] Seeding Sample Data...
call php artisan db:seed --class=PostSeeder
echo ✓ Sample data seeded
echo.

echo [6/6] Building Frontend Assets...
call npm run build
if %errorlevel% neq 0 (
    echo WARNING: Asset build failed, but you can continue
)
echo ✓ Assets built
echo.

echo ================================
echo Setup Complete! 🎉
echo ================================
echo.
echo Next steps:
echo 1. Add your API keys to .env file:
echo    - YOUTUBE_API_KEY
echo    - OPENAI_API_KEY
echo.
echo 2. Start the development server:
echo    php artisan serve
echo.
echo 3. Visit: http://localhost:8000
echo.
echo ================================
pause
