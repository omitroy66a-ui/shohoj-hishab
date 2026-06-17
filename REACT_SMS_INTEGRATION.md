# 🚀 COMPLETE REACT + SMS INTEGRATION SETUP GUIDE

## Project Structure

```
frontend/
├── src/
│   ├── components/
│   │   ├── SubscriptionDashboard.tsx
│   │   ├── UpgradeForm.tsx
│   │   ├── AdminPanel.tsx
│   │   ├── PaymentForm.tsx
│   │   └── SMSNotificationCenter.tsx
│   ├── pages/
│   │   ├── Dashboard.tsx
│   │   ├── Upgrade.tsx
│   │   ├── Admin.tsx
│   │   └── Login.tsx
│   ├── services/
│   │   ├── api.ts
│   │   ├── auth.ts
│   │   └── sms.ts
│   ├── hooks/
│   │   ├── useSubscription.ts
│   │   └── useSMS.ts
│   ├── types/
│   │   └── index.ts
│   ├── store/
│   │   └── useStore.ts
│   ├── utils/
│   │   ├── formatting.ts
│   │   └── validation.ts
│   ├── App.tsx
│   └── main.tsx
├── public/
├── package.json
├── vite.config.ts
├── tsconfig.json
└── tailwind.config.js
```

## Installation Steps

### Step 1: Create React Vite Project
```bash
npm create vite@latest frontend -- --template react-ts
cd frontend
npm install
```

### Step 2: Install Dependencies
```bash
npm install axios react-router-dom zustand react-query
npm install -D tailwindcss postcss autoprefixer
npm install @headlessui/react @heroicons/react
npm install clsx tailwind-merge
```

### Step 3: Configure Tailwind
```bash
npx tailwindcss init -p
```

Update `tailwind.config.js`:
```js
export default {
  content: ["./index.html", "./src/**/*.{js,ts,jsx,tsx}"],
  theme: {
    extend: {},
  },
  plugins: [],
}
```

### Step 4: Update vite.config.ts
```ts
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      }
    }
  }
})
```

### Step 5: Create Project Files

Copy all React components into `src/components/`
Copy all services into `src/services/`
Copy all hooks into `src/hooks/`

### Step 6: Run Development Server
```bash
npm run dev
```

## Build for Production
```bash
npm run build
# Output: dist/
```

---

## Backend API Setup

### Update PHP API (api/subscription.php)

Add SMS sending on events:

```php
// When payment is approved
if ($result['success']) {
    $smsService = new SMSService($conn, 'local');
    $phone = getBusinessPhone($subscription['business_id']);
    $message = SMSService::getPaymentApprovedSMS($subscription['plan_name']);
    $smsService->sendSMS($phone, $message, 'payment_approved');
}

// When subscription activated
$message = SMSService::getSubscriptionActivatedSMS($plan_name, $expiry_date);
$smsService->sendSMS($phone, $message, 'subscription_activated');
```

### SMS API Endpoint

Create `/api/sms.php`:

```php
<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/SMSService.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if (!isset($_SESSION['business_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$business_id = $_SESSION['business_id'];
$smsService = new SMSService($conn, 'local');

// Get SMS statistics
if ($action === 'stats') {
    $stats = $smsService->getStatistics();
    echo json_encode(['success' => true, 'data' => $stats]);
    exit;
}

// Send bulk SMS (admin only)
if ($action === 'send_bulk' && $_POST['method'] === 'POST') {
    // Check admin role
    if ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Admin only']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $phones = $input['phones'] ?? [];
    $message = $input['message'] ?? '';

    if (empty($phones) || empty($message)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing parameters']);
        exit;
    }

    $result = $smsService->sendBulkSMS($phones, $message, 'bulk');
    echo json_encode($result);
    exit;
}
?>
```

---

## Environment Variables

Create `.env` file:

```env
VITE_API_URL=http://localhost:8000
VITE_APP_NAME=Sohoj Hishab
```

---

## React Component Examples

### 1. Subscription Dashboard
- Display current subscription
- Show available plans
- Plan comparison
- Upgrade button

### 2. Payment Form
- Select payment method
- Enter payment details
- Nagad integration
- SMS confirmation

### 3. Admin Panel
- Pending payments review
- One-click approve/reject
- Apply discounts
- SMS bulk send

### 4. SMS Notification Center
- SMS history
- SMS statistics
- Template management
- Send test SMS

---

## Features to Implement

✅ User Registration & Login
✅ Subscription Dashboard
✅ Plan Upgrade Form
✅ Payment Processing
✅ SMS Notifications
✅ Admin Payment Review
✅ Admin Discount Management
✅ SMS Campaign Management
✅ Analytics Dashboard

---

## Development Workflow

1. **Frontend Development**
   ```bash
   cd frontend
   npm run dev
   ```
   - Runs on http://localhost:5173

2. **Backend Development**
   - PHP server: http://localhost:8000
   - API: http://localhost:8000/api/

3. **Testing**
   ```bash
   npm run test
   ```

4. **Build**
   ```bash
   npm run build
   npm run preview
   ```

---

## Deployment

### Build Frontend
```bash
npm run build
# Creates dist/ folder
```

### Deploy to Server
```bash
# Copy dist/ to web root
scp -r frontend/dist/* user@server:/var/www/html/
```

### Or use Vercel/Netlify
```bash
npm install -g vercel
vercel
```

---

## SMS Configuration

### Step 1: Setup SMS Gateway

**Option 1: Local Gateway**
- No external dependency
- Good for testing

**Option 2: Twilio**
```bash
npm install twilio
```
Configure in `sms_config` table

**Option 3: Nexmo (Vonage)**
- Similar to Twilio
- Alternative provider

---

## Complete Integration Checklist

- [ ] React project created
- [ ] Tailwind CSS configured
- [ ] Components created
- [ ] API integration done
- [ ] SMS service configured
- [ ] Database tables created
- [ ] Routes setup
- [ ] Authentication implemented
- [ ] SMS notifications working
- [ ] Admin panel functional
- [ ] Payment flow tested
- [ ] Discount system working
- [ ] Build successful
- [ ] Deployed to server

---

## Support & Documentation

Refer to:
- React Documentation: https://react.dev
- Tailwind CSS: https://tailwindcss.com
- Vite: https://vitejs.dev
- Zustand: https://github.com/pmndrs/zustand

---

## Next Steps

1. Create React project directory
2. Install dependencies
3. Setup Tailwind CSS
4. Create components
5. Integrate with backend
6. Configure SMS service
7. Test end-to-end
8. Deploy to production
