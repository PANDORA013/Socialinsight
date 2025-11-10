@echo off
setlocal enabledelayedexpansion

echo ========================================================
echo   UPDATE .ENV dengan ngrok URL
echo ========================================================
echo.
echo Script ini akan membantu update file .env dengan ngrok URL
echo.
echo ========================================================

:input
echo.
echo Masukkan ngrok URL Anda (tanpa path):
echo Contoh: https://abcd-1234-5678.ngrok-free.app
echo.
set /p NGROK_URL="ngrok URL: "

if "%NGROK_URL%"=="" (
    echo [ERROR] URL tidak boleh kosong!
    goto input
)

echo.
echo ========================================================
echo   KONFIRMASI
echo ========================================================
echo.
echo ngrok URL Anda: %NGROK_URL%
echo.
echo Akan diupdate ke:
echo   APP_URL=%NGROK_URL%
echo   TIKTOK_REDIRECT_URI=%NGROK_URL%/auth/tiktok/callback
echo.
set /p CONFIRM="Lanjutkan? (Y/N): "

if /i not "%CONFIRM%"=="Y" (
    echo Setup dibatalkan.
    pause
    exit /b
)

echo.
echo ========================================================
echo   UPDATING .ENV FILE
echo ========================================================
echo.

REM Backup .env
copy .env .env.backup.%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%%time:~6,2% > nul
echo [OK] Backup dibuat: .env.backup.xxxxx

REM Create temporary file
set "TEMP_FILE=.env.tmp"
if exist "%TEMP_FILE%" del "%TEMP_FILE%"

REM Read and update .env
setlocal disabledelayedexpansion
for /f "usebackq delims=" %%a in (".env") do (
    set "line=%%a"
    setlocal enabledelayedexpansion
    
    REM Check if line starts with APP_URL=
    echo !line! | findstr /b "APP_URL=" >nul
    if !errorlevel! equ 0 (
        echo APP_URL=%NGROK_URL% >> "%TEMP_FILE%"
    ) else (
        REM Check if line starts with TIKTOK_REDIRECT_URI=
        echo !line! | findstr /b "TIKTOK_REDIRECT_URI=" >nul
        if !errorlevel! equ 0 (
            echo TIKTOK_REDIRECT_URI=%NGROK_URL%/auth/tiktok/callback >> "%TEMP_FILE%"
        ) else (
            echo !line! >> "%TEMP_FILE%"
        )
    )
    endlocal
)

REM Replace original .env with updated version
move /y "%TEMP_FILE%" .env > nul
echo [OK] File .env berhasil diupdate!

echo.
echo ========================================================
echo   CLEARING LARAVEL CACHE
echo ========================================================
echo.

php artisan config:clear
php artisan cache:clear
php artisan route:clear

echo.
echo ========================================================
echo   SETUP COMPLETE!
echo ========================================================
echo.
echo NEXT STEPS:
echo.
echo 1. Pastikan ngrok masih running di Terminal 2
echo.
echo 2. Update TikTok Developer Portal:
echo    - Login: https://developers.tiktok.com/
echo    - Redirect URI: %NGROK_URL%/auth/tiktok/callback
echo.
echo 3. Test di browser:
echo    - URL: %NGROK_URL%
echo    - JANGAN gunakan localhost lagi!
echo.
echo 4. Coba TikTok OAuth flow
echo.
echo ========================================================
echo.
pause
