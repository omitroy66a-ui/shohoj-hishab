#!/bin/bash

# ============================================================
# REACT + VITE + TAILWIND + TYPESCRIPT SETUP
# ============================================================

# Navigate to project root
cd /path/to/sohoj-hishab

# Step 1: Create React Vite project
npm create vite@latest frontend -- --template react-ts

cd frontend

# Step 2: Install dependencies
npm install

# Step 3: Install TailwindCSS
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p

# Step 4: Install additional UI libraries
npm install @headlessui/react @heroicons/react
npm install axios react-router-dom zustand react-query
npm install clsx tailwind-merge

# Step 5: Configure Tailwind
# Update tailwind.config.js:
# content: ["./index.html", "./src/**/*.{js,ts,jsx,tsx}"]

# Step 6: Create directory structure
mkdir -p src/{components,pages,services,hooks,types,store,utils}

# Step 7: Start development
npm run dev

# Build for production
npm run build

# Output will be in: frontend/dist/
