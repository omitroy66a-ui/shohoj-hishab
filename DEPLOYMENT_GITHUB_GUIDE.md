# 📦 COMPLETE DEPLOYMENT & GIT GUIDE

## 🎯 Getting Started - 5 Minutes

### Step 1: Install Dependencies
```bash
# Navigate to frontend
cd frontend

# Install all npm packages
npm install
```

### Step 2: Configure Environment
```bash
# For development (automatic, already created)
# File: frontend/.env.development
VITE_API_URL=http://localhost:8000/api

# For production (update this)
# File: frontend/.env.production
VITE_API_URL=https://your-domain.com/api
```

### Step 3: Start Development Server
```bash
npm run dev
```

Frontend opens at: **http://localhost:5173**

### Step 4: Start Backend
```bash
# In another terminal, from project root
php -S localhost:8000
```

Backend runs at: **http://localhost:8000**

---

## 🚀 DEPLOYMENT GUIDE

### Option 1: Deploy to Vercel (Recommended)

**Pros**: Zero-config, automatic HTTPS, global CDN, free tier available

```bash
# Install Vercel CLI
npm install -g vercel

# Deploy
cd frontend
vercel

# Follow prompts to connect GitHub repo
# Vercel automatically builds and deploys
```

**Configure Environment in Vercel Dashboard:**
1. Go to Settings → Environment Variables
2. Add: `VITE_API_URL` = `https://your-api-domain.com/api`
3. Deploy will use updated environment

### Option 2: Deploy to Netlify

**Pros**: Simple, good free tier, great documentation

```bash
# Install Netlify CLI
npm install -g netlify-cli

# Build locally first
npm run build

# Deploy
netlify deploy --prod --dir=dist
```

**Or deploy via Git:**
1. Push code to GitHub
2. Go to Netlify → New site from Git
3. Connect GitHub repo
4. Build command: `npm run build`
5. Publish directory: `dist`
6. Add environment variable: `VITE_API_URL`

### Option 3: Deploy to GitHub Pages

**Pros**: Free, integrated with GitHub, simple

```bash
# Build for production
npm run build

# Deploy dist folder to GitHub Pages
# Option A: Manual upload to gh-pages branch
# Option B: Use github-pages action (recommended)
```

Create `.github/workflows/deploy.yml`:
```yaml
name: Deploy to GitHub Pages

on:
  push:
    branches: [main]

jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: actions/setup-node@v3
        with:
          node-version: '18'
      - run: cd frontend && npm install
      - run: cd frontend && npm run build
      - uses: peaceiris/actions-gh-pages@v3
        with:
          github_token: ${{ secrets.GITHUB_TOKEN }}
          publish_dir: ./frontend/dist
```

### Option 4: Deploy to Traditional Web Hosting

**Pros**: Full control, no vendor lock-in

```bash
# Build for production
npm run build

# Upload dist folder to hosting
# Using FTP/SFTP/SSH:
scp -r dist/* user@host:/var/www/html/

# Or use hosting control panel (cPanel, Plesk, etc.)
```

### Option 5: Deploy with Docker (Advanced)

Create `Dockerfile`:
```dockerfile
FROM node:18-alpine AS builder
WORKDIR /app/frontend
COPY frontend/package*.json ./
RUN npm install
COPY frontend/ ./
RUN npm run build

FROM node:18-alpine
WORKDIR /app
COPY --from=builder /app/frontend/dist ./dist
RUN npm install -g serve
EXPOSE 3000
CMD ["serve", "-s", "dist", "-l", "3000"]
```

Build and run:
```bash
docker build -t sohoj-hishab:latest .
docker run -p 3000:3000 -e VITE_API_URL=http://api-server:8000/api sohoj-hishab:latest
```

---

## 🐙 GITHUB SETUP & PUSH

### Step 1: Initialize Git Repository
```bash
# From project root
git init

# Add all files
git add .

# Create initial commit
git commit -m "Initial commit: Complete SaaS subscription system with React frontend"
```

### Step 2: Create GitHub Repository

1. Go to **github.com** → New Repository
2. Name: `sohoj-hishab`
3. Description: `Complete SaaS Subscription Management System`
4. Public or Private (your choice)
5. **Don't** initialize with README (we have one)
6. Create Repository

### Step 3: Connect Local to GitHub
```bash
# Add remote (replace YOUR_USERNAME and REPO_NAME)
git remote add origin https://github.com/YOUR_USERNAME/sohoj-hishab.git

# Rename branch to main if needed
git branch -M main

# Push to GitHub
git push -u origin main
```

### Step 4: Verify on GitHub
- Visit: `https://github.com/YOUR_USERNAME/sohoj-hishab`
- All files should be visible
- README.md should render beautifully

---

## 📝 GIT WORKFLOW

### Daily Workflow
```bash
# Before starting work
git pull origin main

# Make changes
# Edit files...

# Check status
git status

# Stage changes
git add .

# Commit
git commit -m "Add feature X"

# Push to GitHub
git push origin main
```

### Create Feature Branch (Recommended)
```bash
# Create branch
git checkout -b feature/payment-improvements

# Make changes...

# Push branch
git push origin feature/payment-improvements

# Create Pull Request on GitHub
# Review → Merge to main
```

### Update Local from GitHub
```bash
# Fetch latest
git fetch origin

# Pull latest main
git pull origin main
```

---

## 🔐 Setup Environment Variables

### Development Setup
File: `frontend/.env.development`
```
VITE_API_URL=http://localhost:8000/api
VITE_APP_NAME=Sohoj Hishab
```

### Production Setup
File: `frontend/.env.production`
```
VITE_API_URL=https://your-api-domain.com/api
VITE_APP_NAME=Sohoj Hishab
```

### Secrets (Never commit these!)
Add to `.gitignore`:
```
.env
.env.local
.env.*.local
node_modules
dist
.DS_Store
```

---

## 🧪 TESTING BEFORE DEPLOYMENT

### Checklist
```
Frontend:
☐ All pages load without errors
☐ Navigation works
☐ Forms validate correctly
☐ API calls succeed
☐ Authentication works
☐ SMS notifications send
☐ Payment form displays
☐ Admin dashboard loads
☐ Responsive on mobile

Backend:
☐ PHP server running
☐ Database connected
☐ API endpoints respond
☐ CORS headers configured
☐ Authentication working
☐ SMS service active
☐ Cron jobs scheduled

Integration:
☐ Frontend connects to backend
☐ User can register
☐ User can login
☐ Can upgrade plan
☐ Payment flow works
☐ Admin can approve
☐ SMS sends correctly
```

### Run Tests
```bash
# Build test
npm run build

# Type check
npm run type-check

# Lint
npm run lint
```

---

## 🚀 PRODUCTION DEPLOYMENT STEPS

### 1. Prepare Code
```bash
# Make sure code is committed
git status

# Build for production
npm run build

# Test build locally
npm run preview
```

### 2. Update Configuration
- [ ] Update `.env.production` with real API URL
- [ ] Verify CORS settings on backend
- [ ] Enable HTTPS on API server
- [ ] Test all APIs from production frontend

### 3. Deploy
```bash
# Using your chosen deployment method
# Example for Vercel:
vercel --prod

# Or Netlify:
netlify deploy --prod

# Or manual FTP:
scp -r dist/* user@host:/var/www/html/
```

### 4. Verify
- [ ] Frontend loads at correct URL
- [ ] All pages accessible
- [ ] API calls work
- [ ] Authentication works
- [ ] Payment flow tested
- [ ] SMS notifications work
- [ ] Database accessible
- [ ] No console errors

### 5. Monitor
```bash
# Check Vercel/Netlify dashboard
# Monitor error logs
# Set up uptime monitoring
# Configure alerts
```

---

## 📋 FILE STRUCTURE FOR GITHUB

```
sohoj-hishab/
├── frontend/                  # React app
│   ├── src/
│   ├── public/
│   ├── dist/                  # Build output (add to .gitignore)
│   ├── package.json
│   ├── vite.config.ts
│   ├── .env.development
│   ├── .env.production
│   ├── README.md
│   └── setup.sh / setup.bat
│
├── database/                  # SQL schemas
│   ├── subscription_schema.sql
│   ├── subscription_updates.sql
│   └── sms_config.sql
│
├── services/                  # PHP services
│   ├── SubscriptionService.php
│   ├── SMSService.php
│   └── ...
│
├── api/                       # API endpoints
│   ├── subscription.php
│   ├── sms.php
│   └── ...
│
├── super_admin/              # Admin panels
├── modules/                  # User modules
├── config/                   # Configuration
├── .gitignore               # Git ignore file
├── README.md                # Main readme
└── MASTER_IMPLEMENTATION_GUIDE.md
```

---

## 🔧 TROUBLESHOOTING

### Build Fails
```bash
# Clear cache
rm -rf node_modules dist
npm install
npm run build
```

### Port Already in Use
```bash
# Find process on port 5173
lsof -i :5173

# Kill process
kill -9 <PID>

# Or use different port
npm run dev -- --port 5174
```

### API Connection Issues
```bash
# Check API URL in .env
# Check backend is running
# Check CORS headers
# Use browser DevTools Network tab
```

### Deployment Issues
- Check environment variables
- Verify API URL is correct
- Check HTTPS/HTTP protocols match
- Look at deployment service logs
- Test locally first

---

## 📚 HELPFUL COMMANDS

```bash
# Frontend commands
npm run dev               # Start dev server
npm run build            # Build for production
npm run preview          # Preview production build
npm run type-check       # Check TypeScript
npm run lint            # Run linter

# Git commands
git status              # Check changes
git add .               # Stage all changes
git commit -m "msg"     # Commit changes
git push origin main    # Push to GitHub
git pull origin main    # Pull from GitHub
git branch -a           # List all branches
git log --oneline -10   # Show recent commits

# Backend commands
php -S localhost:8000   # Start PHP server
mysql -u root -p        # Connect to MySQL

# Docker commands (if using)
docker build -t name .  # Build image
docker run -p 3000:3000 name  # Run container
```

---

## ✅ FINAL CHECKLIST

Before marking as "Production Ready":

- [ ] Code committed to GitHub
- [ ] README.md complete and clear
- [ ] .gitignore configured properly
- [ ] Environment files configured
- [ ] Build succeeds without warnings
- [ ] All pages tested locally
- [ ] API endpoints tested
- [ ] Authentication tested
- [ ] Payment flow tested
- [ ] SMS notifications tested
- [ ] Database migrations applied
- [ ] CORS configured on backend
- [ ] HTTPS enabled
- [ ] Error handling implemented
- [ ] Console has no errors
- [ ] Performance optimized
- [ ] Mobile responsiveness checked
- [ ] Admin functions tested
- [ ] Security reviewed
- [ ] Backup strategy implemented
- [ ] Monitoring configured
- [ ] Documentation complete

---

## 🎉 DEPLOYMENT COMPLETE!

Your SaaS system is now:
- ✅ Code committed to GitHub
- ✅ Deployed to production
- ✅ Accessible to users
- ✅ Monitored and maintained

**Next Steps:**
1. Share GitHub link with team
2. Monitor production logs
3. Collect user feedback
4. Plan feature updates
5. Scale infrastructure as needed

---

**Version**: 3.0  
**Last Updated**: 2024  
**Status**: Production Ready 🚀
