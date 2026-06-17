@echo off
REM Sohoj Hishab - Complete Setup Script for Windows
REM This script sets up the complete system ready for GitHub push and deployment

title Sohoj Hishab - Setup Script

echo.
echo ╔════════════════════════════════════════════════════════════════╗
echo ║  🚀 Sohoj Hishab - Complete Setup Script                       ║
echo ║  Version 3.0 - React + SMS + Subscription System               ║
echo ╚════════════════════════════════════════════════════════════════╝
echo.

REM Check if Node.js is installed
where node >nul 2>nul
if %errorlevel% neq 0 (
    echo ⚠️  Node.js is not installed. Please install Node.js 16+
    pause
    exit /b 1
)

for /f "tokens=*" %%i in ('node --version') do set NODE_VERSION=%%i
echo ✓ Node.js version: %NODE_VERSION%
echo.

REM Navigate to frontend
cd frontend

REM Install dependencies
echo 📦 Installing dependencies...
call npm install

if %errorlevel% neq 0 (
    echo ⚠️  Failed to install dependencies
    pause
    exit /b 1
)

echo ✓ Dependencies installed
echo.

REM Create environment files if they don't exist
if not exist ".env.development" (
    echo 📝 Creating .env.development...
    (
        echo VITE_API_URL=http://localhost:8000/api
        echo VITE_APP_NAME=Sohoj Hishab
    ) > .env.development
    echo ✓ .env.development created
)

if not exist ".env.production" (
    echo 📝 Creating .env.production...
    (
        echo VITE_API_URL=https://api.example.com/api
        echo VITE_APP_NAME=Sohoj Hishab
    ) > .env.production
    echo ✓ .env.production created
)

REM Build for production
echo.
echo 🔨 Building for production...
call npm run build

if %errorlevel% neq 0 (
    echo ⚠️  Build failed
    pause
    exit /b 1
)

echo ✓ Production build complete
echo.

REM Summary
echo ╔════════════════════════════════════════════════════════════════╗
echo ║  ✅ Setup Complete!                                            ║
echo ╚════════════════════════════════════════════════════════════════╝
echo.

echo 📋 Next Steps:
echo 1. Start development server:  npm run dev
echo 2. Build for production:      npm run build
echo 3. Preview production build:  npm run preview
echo 4. Deploy dist\ folder to hosting
echo.

echo 📁 Build Output:
echo    Location: frontend\dist\
echo    Use this folder for deployment
echo.

echo 🔧 Configuration:
echo    Development: .env.development
echo    Production:  .env.production
echo.

echo 📖 Documentation:
echo    Frontend: README.md
echo    Backend: ..\MASTER_IMPLEMENTATION_GUIDE.md
echo.

echo 🚀 Ready for GitHub push!
echo.

pause
