@echo off
echo ==========================================
echo  SocialInsight - Development Server
echo ==========================================
echo.
echo Starting development servers...
echo.
echo Vite Dev Server will run on http://localhost:5173
echo Laravel Server will run on http://localhost:8000
echo.
echo Press Ctrl+C to stop
echo.

start "Vite Dev" cmd /k "npm run dev"
timeout /t 3 /nobreak > nul
start "Laravel Server" cmd /k "php artisan serve"

echo.
echo Both servers started!
echo Open http://localhost:8000 in your browser
echo.
