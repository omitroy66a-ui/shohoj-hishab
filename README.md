# 🚀 Sohoj Hishab - ERP System

Modern, scalable business management platform with real-time capabilities.

## 📁 Project Structure

```
sohoj_hishab/
├── backend/              (PHP API Core)
│   ├── auth/            (JWT Authentication)
│   ├── routes/          (API Endpoints)
│   ├── middleware/      (Auth & CORS)
│   ├── config/          (Database config)
│   ├── modules/         (Business logic)
│   └── index.php        (Main entry point)
│
├── admin-web/           (React Next.js Admin)
│   ├── app/            (Pages & layouts)
│   ├── components/     (UI components)
│   └── public/         (Assets)
│
├── mobile-app/          (Flutter App)
│   └── lib/
│       ├── screens/    (UI screens)
│       ├── models/     (Data models)
│       └── services/   (API services)
│
├── websocket-server/    (Node.js Real-time)
│
├── infra/               (Infrastructure)
│   ├── docker/         (Docker configs)
│   ├── nginx/          (Nginx reverse proxy)
│   └── scripts/        (Deployment scripts)
│
├── database/            (Schema & migrations)
└── docker-compose.yml   (Container orchestration)
```

## 🔧 Setup & Installation

### Prerequisites
- Docker & Docker Compose
- Node.js 18+
- Flutter SDK (for mobile)

### Quick Start

```bash
# Clone repository
git clone https://your-repo.git
cd sohoj_hishab

# Start all services
docker-compose up -d

# Access services
- API: http://localhost:8000/backend
- WebSocket: ws://localhost:6001
- Admin: http://localhost:3000 (Next.js)
```

## 🔐 Authentication

JWT-based authentication with 24-hour token expiry.

```
POST /api/auth/login
{
  "email": "user@example.com",
  "password": "password"
}

Response:
{
  "success": true,
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

## 📊 API Endpoints

### Dashboard
- `GET /api/dashboard` - Get dashboard metrics

### Expenses
- `GET /api/expenses` - List expenses
- `POST /api/expenses` - Create expense
- `PUT /api/expenses/:id` - Update expense
- `DELETE /api/expenses/:id` - Delete expense

### Customers
- `GET /api/customers` - List customers
- `POST /api/customers` - Create customer
- `PUT /api/customers/:id` - Update customer
- `DELETE /api/customers/:id` - Delete customer

### Employees
- `GET /api/employees` - List employees
- `POST /api/employees` - Create employee
- `PUT /api/employees/:id` - Update employee
- `DELETE /api/employees/:id` - Delete employee

## 🚀 Deployment

### VPS Deployment

```bash
# Run deployment script
bash infra/scripts/deploy.sh
```

### Docker Compose (Local)

```bash
docker-compose up -d --build
```

## 📱 Features

✅ Multi-tenant ERP system
✅ Real-time updates via WebSocket
✅ JWT authentication
✅ Responsive admin dashboard
✅ Mobile app support
✅ Audit logging
✅ SQL injection prevention
✅ CORS enabled
✅ Docker containerized
✅ Production-ready deployment

## 🔒 Security

- Prepared statements (SQL Injection prevention)
- JWT authentication
- CORS middleware
- Input validation
- Audit logging
- Session management

## 📄 License

MIT License
