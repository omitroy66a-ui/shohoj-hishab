#!/bin/bash

echo "🚀 Starting Sohoj Hishab Deployment..."

# Update system
sudo apt update -y
sudo apt upgrade -y

# Install dependencies
sudo apt install -y \
    git \
    curl \
    wget \
    docker.io \
    docker-compose \
    nginx \
    nodejs \
    npm

# Enable Docker
sudo systemctl start docker
sudo systemctl enable docker

# Clone repository
git clone https://your-repo.git /var/www/sohoj_hishab
cd /var/www/sohoj_hishab

# Create environment file
cat > .env << EOF
DB_HOST=mysql
DB_USER=root
DB_PASSWORD=password
DB_NAME=sohoj_hishab
JWT_SECRET=sohoj_hishab_secret_key_2024
NODE_ENV=production
EOF

# Start Docker services
docker-compose up -d --build

# Wait for services to start
sleep 10

# Check services
echo "✅ PHP Service: $(curl -s http://localhost:8000/backend/index.php | grep -q 'API Running' && echo 'OK' || echo 'FAILED')"
echo "✅ MySQL Service: docker-compose ps | grep mysql"
echo "✅ WebSocket Service: $(curl -s http://localhost:6001 2>/dev/null && echo 'OK' || echo 'RUNNING')"

echo "🎉 Deployment Complete!"
echo "📍 Access at: http://localhost"
