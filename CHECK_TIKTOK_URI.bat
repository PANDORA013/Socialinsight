@echo off
echo ================================================
echo   TIKTOK OAUTH - REDIRECT URI CHECKER
echo ================================================
echo.

REM Read .env file and display TIKTOK settings
echo [1] Checking .env configuration...
echo.
findstr "TIKTOK_" .env
echo.
echo ================================================

echo.
echo [2] Current Redirect URI from .env:
echo.
findstr "TIKTOK_REDIRECT_URI" .env
echo.
echo ================================================

echo.
echo [3] Instructions:
echo.
echo    a. Copy your Redirect URI from TikTok Developer Portal
echo    b. Compare with the URI shown above
echo    c. They must match EXACTLY (no spaces, no extra slash)
echo.
echo    Current URI in .env:
findstr "TIKTOK_REDIRECT_URI" .env
echo.
echo    Portal URI should be:
echo    (Copy from https://developers.tiktok.com/)
echo.
echo ================================================

echo.
echo [4] If you need to update:
echo.
echo    Edit file: .env
echo    Find line: TIKTOK_REDIRECT_URI=...
echo    Replace with exact URI from portal
echo.
echo    Then run:
echo    php artisan config:clear
echo    php artisan cache:clear
echo.
echo ================================================

echo.
echo [5] Common Redirect URI formats:
echo.
echo    For localhost (testing):
echo    http://localhost:8000/auth/tiktok/callback
echo.
echo    For ngrok (if localhost doesn't work):
echo    https://YOUR-NGROK-ID.ngrok.io/auth/tiktok/callback
echo.
echo    For production:
echo    https://yourdomain.com/auth/tiktok/callback
echo.
echo ================================================

echo.
echo Press any key to clear Laravel cache...
pause >nul

echo.
echo Clearing Laravel configuration cache...
php artisan config:clear
php artisan cache:clear
echo.
echo Cache cleared!
echo.

echo.
echo ================================================
echo   NEXT STEPS:
echo ================================================
echo.
echo 1. Make sure Redirect URI in .env matches portal
echo 2. Restart Laravel server (Ctrl+C, then 'php artisan serve')
echo 3. Test: http://localhost:8000
echo 4. Click "Connect TikTok" button
echo 5. Should redirect to TikTok login (NOT "Something went wrong")
echo.
echo ================================================
echo.
pause
