@echo off
echo ========================================
echo  IndoBERT Setup - SocialInsight
echo ========================================
echo.

echo [1/4] Checking Python installation...
python --version
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Python not found!
    echo Please install Python from: https://www.python.org/downloads/
    pause
    exit /b 1
)
echo Python found!
echo.

echo [2/4] Checking pip...
python -m pip --version
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: pip not found!
    pause
    exit /b 1
)
echo pip found!
echo.

echo [3/4] Installing Python dependencies...
echo This will download ~2GB of data (PyTorch + Transformers)
echo Please be patient, this may take 10-30 minutes...
echo.

python -m pip install --upgrade pip
python -m pip install transformers torch

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ERROR: Failed to install dependencies!
    echo Try manually: python -m pip install transformers torch
    pause
    exit /b 1
)

echo.
echo [4/4] Testing IndoBERT script...
echo.

python storage\app\python\analyze.py "Lagu ini sangat bagus!"

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo WARNING: Test failed. Please check the output above.
    echo.
) else (
    echo.
    echo ========================================
    echo  SUCCESS! IndoBERT is ready to use!
    echo ========================================
    echo.
    echo Next steps:
    echo 1. Start Laravel: php artisan serve
    echo 2. Open: http://localhost:8000/indobert-test.html
    echo 3. Click "Check IndoBERT Status"
    echo.
)

pause
