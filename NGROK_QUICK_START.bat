@echo off
echo ========================================================
echo   NGROK SETUP - TikTok OAuth dengan URL Publik
echo ========================================================
echo.
echo PERHATIAN PENTING:
echo - Anda HARUS download ngrok terlebih dahulu dari:
echo   https://ngrok.com/download
echo.
echo - Extract ngrok.exe ke folder (misal: C:\ngrok\)
echo.
echo ========================================================
echo   INSTRUKSI SETUP
echo ========================================================
echo.
echo TERMINAL 1 (Laravel Server):
echo   cd c:\xampp\htdocs\socialinsight
echo   php artisan serve
echo.
echo TERMINAL 2 (ngrok - BUKA TERMINAL BARU):
echo   cd C:\ngrok
echo   .\ngrok http 8000
echo.
echo ========================================================
echo   SETELAH NGROK RUNNING
echo ========================================================
echo.
echo 1. Copy URL dari ngrok (contoh: https://abcd-1234.ngrok-free.app)
echo.
echo 2. Update TikTok Developer Portal:
echo    - Redirect URI: https://YOUR-NGROK-URL.ngrok-free.app/auth/tiktok/callback
echo.
echo 3. Update file .env:
echo    - TIKTOK_REDIRECT_URI=https://YOUR-NGROK-URL.ngrok-free.app/auth/tiktok/callback
echo    - APP_URL=https://YOUR-NGROK-URL.ngrok-free.app
echo.
echo 4. Clear cache:
echo    php artisan config:clear
echo    php artisan cache:clear
echo.
echo 5. Test di browser dengan URL ngrok (JANGAN localhost!)
echo.
echo ========================================================
echo   DOKUMENTASI LENGKAP
echo ========================================================
echo.
echo Baca: NGROK_SETUP_GUIDE.md untuk panduan step-by-step
echo.
pause
