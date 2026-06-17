# 🚀 Sohoj Hishab - React Frontend

Complete SaaS subscription management system with React, TypeScript, and Tailwind CSS.

## 📋 Features

### 🎯 User Features
- ✅ User Registration & Authentication
- ✅ Subscription Management Dashboard
- ✅ Plan Upgrade & Downgrade
- ✅ Payment Processing (Nagad/bKash/Rocket)
- ✅ SMS Notifications
- ✅ Subscription Status Tracking
- ✅ Payment History
- ✅ Settings & Profile Management

### 👑 Admin Features
- ✅ Admin Dashboard with Analytics
- ✅ Payment Review & Approval
- ✅ Discount Management
- ✅ SMS Campaign Management
- ✅ Bulk SMS Sending
- ✅ Statistics & Reports
- ✅ User Subscription Management

## 🛠️ Tech Stack

- **Frontend**: React 18 + TypeScript
- **Build Tool**: Vite
- **Styling**: Tailwind CSS + Custom CSS
- **State Management**: Zustand
- **HTTP Client**: Axios
- **Charts**: Recharts
- **Icons**: Lucide React
- **UI Components**: Material-UI

## 📦 Installation

### Prerequisites
- Node.js 16+ 
- npm or yarn
- Git

### Step 1: Clone or Download
```bash
cd frontend
```

### Step 2: Install Dependencies
```bash
npm install
```

### Step 3: Configure Environment
Create `.env.development` for development:
```
VITE_API_URL=http://localhost:8000/api
VITE_APP_NAME=Sohoj Hishab
```

For production, update `.env.production`:
```
VITE_API_URL=https://your-api-domain.com/api
VITE_APP_NAME=Sohoj Hishab
```

### Step 4: Start Development Server
```bash
npm run dev
```

Frontend will be available at: `http://localhost:5173`

## 🚀 Running the Application

### Development Mode
```bash
npm run dev
```

### Production Build
```bash
npm run build
npm run preview
```

### Type Checking
```bash
npm run type-check
```

## 📁 Project Structure

```
frontend/
├── src/
│   ├── components/
│   │   ├── common/              # Reusable components
│   │   │   ├── Header.tsx
│   │   │   ├── Footer.tsx
│   │   │   ├── Layout.tsx
│   │   │   └── Loader.tsx
│   │   ├── subscription/        # Subscription components
│   │   ├── payment/             # Payment components
│   │   ├── admin/               # Admin components
│   │   └── sms/                 # SMS components
│   │
│   ├── pages/
│   │   ├── auth/                # Authentication pages
│   │   │   ├── LoginPage.tsx
│   │   │   └── RegisterPage.tsx
│   │   ├── user/                # User pages
│   │   │   ├── DashboardPage.tsx
│   │   │   ├── UpgradePage.tsx
│   │   │   ├── SMSPage.tsx
│   │   │   └── SettingsPage.tsx
│   │   └── admin/               # Admin pages
│   │       ├── AdminDashboard.tsx
│   │       ├── AdminPayments.tsx
│   │       └── AdminSMS.tsx
│   │
│   ├── services/
│   │   ├── api.ts               # Axios instance
│   │   ├── authService.ts       # Auth API
│   │   ├── subscriptionService.ts # Subscription API
│   │   └── smsService.ts        # SMS API
│   │
│   ├── store/
│   │   ├── authStore.ts         # Auth state
│   │   └── subscriptionStore.ts # Subscription state
│   │
│   ├── hooks/                   # Custom hooks
│   ├── types/                   # TypeScript types
│   ├── utils/                   # Utilities
│   ├── styles/
│   │   ├── index.css            # Global styles
│   │   └── App.css              # App styles
│   │
│   ├── App.tsx                  # Root component
│   └── main.tsx                 # Entry point
│
├── public/
│   ├── images/
│   └── icons/
│
├── index.html
├── package.json
├── vite.config.ts
├── tsconfig.json
└── .env.development
```

## 🔐 Authentication

### Login Flow
1. User enters email & password
2. Request sent to `/api/auth/login`
3. Token stored in localStorage
4. User redirected to dashboard

### Register Flow
1. User fills registration form
2. Request sent to `/api/auth/register`
3. Trial subscription auto-created
4. User redirected to dashboard

### Token Management
- Tokens automatically added to all API requests
- Expired tokens trigger re-login
- Logout clears token and user data

## 💳 Payment Flow

### User Flow
1. Select plan from dashboard
2. Choose payment method (Nagad/bKash/Rocket)
3. See payment instructions
4. Send payment via gateway
5. Enter payment reference & transaction ID
6. Payment recorded as "pending"
7. SMS sent confirming receipt

### Admin Flow
1. Open admin dashboard
2. View pending payments
3. Verify payment details
4. Click "Approve" or "Reject"
5. Payment auto-activates if approved
6. SMS sent to user
7. User subscription updated

## 📱 SMS Notifications

### Automated SMS Triggers
- User registration: Welcome message
- Payment received: Confirmation
- Payment approved: Subscription active
- Trial expiring: Upgrade reminder
- Trial expired: Reactivation needed

### Admin SMS Features
- Send bulk SMS campaigns
- Create custom templates
- View SMS history & logs
- Track delivery status
- SMS statistics

## 🎨 Styling

### Color Scheme
- **Primary**: #667eea (Purple)
- **Secondary**: #764ba2 (Dark Purple)
- **Success**: #10b981 (Green)
- **Danger**: #ef4444 (Red)
- **Warning**: #f59e0b (Orange)
- **Info**: #3b82f6 (Blue)

### Responsive Breakpoints
- Mobile: 480px
- Tablet: 768px
- Desktop: 1024px
- Large: 1200px+

## 📊 API Endpoints

### Authentication
- `POST /api/auth/login` - User login
- `POST /api/auth/register` - User registration
- `POST /api/auth/logout` - User logout
- `GET /api/auth/user` - Get current user

### Subscription (User)
- `GET /api/subscription/get-subscription` - Get current subscription
- `GET /api/subscription/plans` - Get all plans
- `POST /api/subscription/upgrade` - Upgrade subscription
- `GET /api/subscription/payment-history` - Payment history

### Subscription (Admin)
- `GET /api/subscription/admin/pending-payments` - Pending payments
- `POST /api/subscription/admin/approve-payment/{id}` - Approve payment
- `POST /api/subscription/admin/reject-payment/{id}` - Reject payment
- `POST /api/subscription/admin/apply-discount/{id}` - Apply discount

### SMS (User)
- `POST /api/sms/send-test` - Send test SMS
- `GET /api/sms/logs` - SMS logs
- `GET /api/sms/templates` - SMS templates

### SMS (Admin)
- `POST /api/sms/admin/campaign/create` - Create SMS campaign
- `GET /api/sms/admin/campaigns` - View campaigns
- `GET /api/sms/admin/stats` - SMS statistics

## 🧪 Testing

### Manual Testing Checklist
- [ ] User can register
- [ ] User receives welcome SMS
- [ ] User can login
- [ ] Dashboard shows subscription status
- [ ] User can select upgrade plan
- [ ] Payment form shows correctly
- [ ] Payment can be submitted
- [ ] Admin can review pending payments
- [ ] Admin can approve payment
- [ ] User receives approval SMS
- [ ] Subscription status updates
- [ ] Admin can send bulk SMS
- [ ] SMS appears in logs

## 🚀 Deployment

### Build for Production
```bash
npm run build
```

### Deploy to Hosting
1. Build the project: `npm run build`
2. Upload `dist/` folder to your web server
3. Configure API URL in `.env.production`
4. Enable CORS on backend API
5. Test all flows end-to-end

### Popular Hosting Options
- Vercel (Recommended for Vite)
- Netlify
- GitHub Pages
- AWS S3 + CloudFront
- Heroku
- DigitalOcean

### Environment Setup
```bash
# Copy production env
cp .env.production .env

# Build for production
npm run build

# Deploy dist folder
```

## 🔧 Troubleshooting

### Port Already in Use
```bash
# Find process using port 5173
lsof -i :5173

# Kill process or use different port
npm run dev -- --port 5174
```

### API Connection Issues
- Check `.env` file has correct API URL
- Verify backend is running on correct port
- Enable CORS on backend
- Check network in browser DevTools

### CORS Errors
Add headers to backend PHP:
```php
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
```

### Build Errors
```bash
# Clear node modules and reinstall
rm -rf node_modules package-lock.json
npm install

# Clear Vite cache
rm -rf .vite

# Rebuild
npm run build
```

## 📚 Documentation

- [Backend Documentation](../MASTER_IMPLEMENTATION_GUIDE.md)
- [API Reference](../api/subscription.php)
- [SMS Integration](../REACT_SMS_INTEGRATION.md)
- [Payment Setup](../NAGAD_DISCOUNT_SETUP.md)

## 🤝 Contributing

1. Create a feature branch: `git checkout -b feature/your-feature`
2. Commit changes: `git commit -am 'Add new feature'`
3. Push to branch: `git push origin feature/your-feature`
4. Submit pull request

## 📝 License

This project is proprietary and confidential.

## 🆘 Support

For issues or questions:
1. Check documentation in root folder
2. Review example files in `REACT_COMPONENTS_CODE.txt`
3. Check API endpoints in backend services
4. Contact: support@sohojhishab.com

## ✅ Checklist

Before deployment:
- [ ] Environment variables configured
- [ ] Backend API running
- [ ] Database tables created
- [ ] SMS service configured
- [ ] Payment gateway credentials added
- [ ] Build passes without errors
- [ ] All pages load correctly
- [ ] Authentication flow works
- [ ] Payment flow tested end-to-end
- [ ] Admin functions tested
- [ ] SMS notifications working
- [ ] CORS configured
- [ ] SSL certificate installed (production)
- [ ] Performance optimized
- [ ] Error logging enabled

## 🎉 Version

**Version**: 3.0.0  
**Last Updated**: 2024  
**Status**: Production Ready

---

**Ready to deploy? 🚀 Good luck!**
