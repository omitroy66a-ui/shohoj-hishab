#!/bin/bash

# Sohoj Hishab - Complete Setup Script
# This script sets up the complete system ready for GitHub push and deployment

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║  🚀 Sohoj Hishab - Complete Setup Script                       ║"
echo "║  Version 3.0 - React + SMS + Subscription System               ║"
echo "╚════════════════════════════════════════════════════════════════╝"

# Color codes
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if Node.js is installed
if ! command -v node &> /dev/null; then
    echo -e "${YELLOW}⚠️  Node.js is not installed. Please install Node.js 16+${NC}"
    exit 1
fi

echo -e "${BLUE}✓ Node.js version: $(node --version)${NC}"

# Navigate to frontend
cd frontend

# Install dependencies
echo -e "${BLUE}\n📦 Installing dependencies...${NC}"
npm install

if [ $? -ne 0 ]; then
    echo -e "${YELLOW}⚠️  Failed to install dependencies${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Dependencies installed${NC}"

# Create environment files if they don't exist
if [ ! -f .env.development ]; then
    echo -e "${BLUE}📝 Creating .env.development...${NC}"
    cat > .env.development << EOF
VITE_API_URL=http://localhost:8000/api
VITE_APP_NAME=Sohoj Hishab
EOF
    echo -e "${GREEN}✓ .env.development created${NC}"
fi

if [ ! -f .env.production ]; then
    echo -e "${BLUE}📝 Creating .env.production...${NC}"
    cat > .env.production << EOF
VITE_API_URL=https://api.example.com/api
VITE_APP_NAME=Sohoj Hishab
EOF
    echo -e "${GREEN}✓ .env.production created${NC}"
fi

# Build for production
echo -e "${BLUE}\n🔨 Building for production...${NC}"
npm run build

if [ $? -ne 0 ]; then
    echo -e "${YELLOW}⚠️  Build failed${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Production build complete${NC}"

# Summary
echo -e "${GREEN}\n╔════════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  ✅ Setup Complete!                                             ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════════════╝${NC}"

echo -e "\n${BLUE}📋 Next Steps:${NC}"
echo "1. Start development server:  npm run dev"
echo "2. Build for production:      npm run build"
echo "3. Preview production build:  npm run preview"
echo "4. Deploy dist/ folder to hosting"
echo ""
echo -e "${BLUE}📁 Build Output:${NC}"
echo "   Location: frontend/dist/"
echo "   Use this folder for deployment"
echo ""
echo -e "${BLUE}🔧 Configuration:${NC}"
echo "   Development: .env.development"
echo "   Production:  .env.production"
echo ""
echo -e "${BLUE}📖 Documentation:${NC}"
echo "   Frontend: README.md"
echo "   Backend: ../MASTER_IMPLEMENTATION_GUIDE.md"
echo ""
echo -e "${GREEN}🚀 Ready for GitHub push!${NC}"
