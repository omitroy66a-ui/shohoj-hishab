# 🚀 QUICK REFERENCE CARD - SaaS Subscription System

## One-Page Cheat Sheet

### **3-Minute Breakdown**

Your system now includes:
- ✅ Complete subscription management (3 tiers)
- ✅ Nagad payment integration (01763206165)
- ✅ Admin payment approval system
- ✅ SMS notifications (Twilio/Nexmo/Local)
- ✅ React frontend with TailwindCSS
- ✅ Bulk SMS campaigns
- ✅ Auto-activation on approval
- ✅ Feature access control

---

## **Database Tables (15 total)**

```
Core Subscription:
- subscription_plans
- business_subscriptions
- subscription_payments
- feature_permissions
- subscription_history

Payments & Gateways:
- payment_gateways (Nagad configured)
- subscription_payments

Admin & Automation:
- subscription_discounts (Admin discounts)
- subscription_queue (Auto-send queue)

SMS:
- sms_config
- sms_logs
- sms_campaigns
- sms_templates
- subscription_sms_history
```

---

## **Key PHP Classes**

```php
// Core subscription logic
$sub = new SubscriptionService($conn);
$sub->createTrialSubscription($business_id);
$sub->upgradeSubscription($business_id, $plan_id);

// Payment processing
$payment = new PaymentGatewayService($conn);
$payment->processNagadPayment($amount, $phone);

// Admin discounts
$discount = new SubscriptionDiscountService($conn);
$discount->applyDiscount($subscription_id, 30, 'percentage');

// SMS sending
$sms = new SMSService($conn, 'local');
$sms->sendSMS('01700000000', 'Your subscription is active!');
$sms->sendBulkSMS(['01700000000', '01800000000'], 'Message');
```

---

## **API Endpoints**

```
GET  /api/subscription.php?action=getUserSubscription
POST /api/subscription.php?action=upgradeSubscription
POST /api/subscription.php?action=processPayment
GET  /api/subscription.php?action=adminGetPendingPayments
POST /api/subscription.php?action=adminApprovePayment
POST /api/subscription.php?action=adminRejectPayment
POST /api/subscription.php?action=adminApplyDiscount
POST /api/sms.php?action=sendBulkSMS
GET  /api/sms.php?action=getSmsCampaigns
```

---

## **Payment Gateway Details**

```
Nagad Setup:
- Merchant Phone: 01763206165
- Integration: PaymentGatewayService.php
- Testing: Use any phone number for testing

Other Gateways:
- bKash: Configurable via payment_gateways table
- Rocket: Configurable via payment_gateways table
```

---

## **SMS Configuration**

```php
// Default provider
$sms = new SMSService($conn, 'local');

// Twilio
$sms = new SMSService($conn, 'twilio', [
    'account_sid' => 'YOUR_ACCOUNT_SID',
    'auth_token' => 'YOUR_AUTH_TOKEN',
    'from_number' => '+1XXXXXXXXXX'
]);

// Nexmo
$sms = new SMSService($conn, 'nexmo', [
    'api_key' => 'YOUR_API_KEY',
    'api_secret' => 'YOUR_API_SECRET',
    'from_number' => 'YourBrand'
]);
```

---

## **Subscription Plans**

```
Trial (Plan ID: 1)
- Price: Free
- Duration: 3 days
- Features: All unlocked
- Auto-lock on day 4

Standard (Plan ID: 2)
- Price: ৳60/month (৳219/6 months, ৳699/year)
- Features: POS, Products, Customers, Suppliers, Basic Reports
- No: Multi-branch, E-commerce, API

Advanced (Plan ID: 3)
- Price: ৳199/month (৳999/6 months, ৳1999/year)
- Features: Everything unlocked
- No restrictions
```

---

## **Feature Access Control**

```php
// Check if user can access feature
if (canAccessFeature($plan_type, 'ecommerce')) {
    // Show ecommerce features
}

Features by Plan:
- trial: ALL
- standard: pos, products, customers, suppliers, reports
- advanced: ALL
```

---

## **Cron Jobs Setup**

```bash
# Daily - Check expired subscriptions
0 0 * * * php /path/cron_expire_subscriptions.php

# Hourly - Process subscription queue
0 * * * * php /path/cron_send_subscriptions.php
```

---

## **Complete Payment Flow**

```
1. User registers → Trial auto-created
2. User selects plan → Shows upgrade form
3. User sees "Send ৳X to Nagad: 01763206165"
4. User sends payment via Nagad
5. User gets Transaction ID from Nagad
6. User enters: Payment # + Transaction ID
7. Payment status: pending
   → SMS sent to user
8. Admin opens dashboard
9. Admin clicks "Approve"
10. AUTOMATIC:
    - Payment marked: completed
    - Subscription marked: active
    - SMS sent: "Your subscription is active!"
11. User can now use all features!
```

---

## **React Component Structure**

```
frontend/
├── src/
│   ├── components/
│   │   ├── SubscriptionDashboard.tsx
│   │   ├── PaymentForm.tsx
│   │   ├── AdminPanel.tsx
│   │   └── SMSCenter.tsx
│   │
│   ├── pages/
│   │   ├── Dashboard.tsx
│   │   ├── Upgrade.tsx
│   │   └── Admin.tsx
│   │
│   ├── services/
│   │   ├── api.ts (API calls)
│   │   ├── sms.ts (SMS logic)
│   │   └── payment.ts (Payment logic)
│   │
│   ├── hooks/
│   │   ├── useSubscription.ts
│   │   ├── useSMS.ts
│   │   └── usePayment.ts
│   │
│   ├── store/
│   │   └── store.ts (Zustand state)
│   │
│   └── types/
│       └── index.ts
```

---

## **SMS Triggers (Auto-Sending)**

```
✉️ On user registration:
   "Welcome to Sohoj Hishab! Your 3-day trial is active."

✉️ On payment received:
   "Payment received! Amount: ৳X. Awaiting admin review."

✉️ On payment approved:
   "Congratulations! Your subscription is now active."

✉️ On trial expiring (daily check):
   "Your trial expires in X days. Upgrade now!"

✉️ On trial expired:
   "Your trial has expired. Upgrade to continue using features."

✉️ For bulk campaigns:
   Custom message to multiple users
```

---

## **Admin Dashboard Features**

**Super Admin Can:**
- ✅ View all pending payments
- ✅ See payment details (Amount, Merchant, Date)
- ✅ One-click approve/reject
- ✅ Apply discounts (flat or percentage)
- ✅ View discount history
- ✅ Configure payment gateways
- ✅ Send bulk SMS campaigns
- ✅ View SMS statistics
- ✅ View subscription activity
- ✅ Track audit logs

---

## **Quick Database Queries**

```sql
-- Get user's active subscription
SELECT * FROM business_subscriptions 
WHERE business_id=5 AND status='active';

-- Get pending payments
SELECT * FROM subscription_payments 
WHERE status='pending' ORDER BY created_at DESC;

-- Get active discounts
SELECT * FROM subscription_discounts 
WHERE status='active' AND expiry_date > NOW();

-- Get SMS history
SELECT * FROM subscription_sms_history 
WHERE subscription_id=10 ORDER BY created_at DESC;

-- Calculate revenue
SELECT SUM(amount) FROM subscription_payments 
WHERE status='completed' AND MONTH(created_at)=MONTH(NOW());
```

---

## **Testing Checklist**

```
☐ User registration creates 3-day trial
☐ User can see subscription plans
☐ User can select plan and see "Send to Nagad: 01763206165"
☐ User can submit payment details
☐ Payment appears in admin dashboard
☐ Admin can approve payment
☐ SMS sent on approval
☐ Subscription auto-activates
☐ User can see active subscription
☐ Feature access control works
☐ Admin can apply discount
☐ Bulk SMS works
☐ Cron jobs run successfully
☐ React frontend loads
☐ React API calls work
```

---

## **Deployment Checklist**

```
☐ Import all 3 SQL files
☐ Configure payment gateway
☐ Setup SMS provider (or use local)
☐ Create React build (npm run build)
☐ Deploy React build to server
☐ Configure cron jobs
☐ Test payment flow end-to-end
☐ Test SMS sending
☐ Setup admin user
☐ Setup monitoring/alerts
☐ Backup database
☐ Go live!
```

---

## **Troubleshooting**

**Problem: SMS not sending**
→ Check SMSService.php provider setting
→ Verify phone number format

**Problem: Payment not auto-activating**
→ Check cron_send_subscriptions.php is running
→ Check subscription_queue table

**Problem: React not connecting to backend**
→ Check API endpoint in api.ts
→ Check CORS headers if cross-origin
→ Check database connection

**Problem: Subscription not appearing**
→ Check business_subscriptions table
→ Check subscription status is 'active'

---

## **Key Files to Know**

```
Core Logic:
- services/SubscriptionService.php (300+ lines)
- services/SMSService.php (11KB)
- services/PaymentGatewayService.php (430+ lines)

Database:
- database/subscription_schema.sql (main)
- database/subscription_updates.sql (Nagad + SMS)
- database/sms_config.sql (SMS tables)

Admin:
- super_admin/subscription_dashboard.php (React component)

Automation:
- cron_send_subscriptions.php (hourly)
- cron_expire_subscriptions.php (daily)

React:
- frontend/src/services/api.ts (API integration)
- frontend/src/components/SubscriptionDashboard.tsx
```

---

## **Production Tips**

1. **Backup regularly** - Use mysqldump
2. **Monitor cron jobs** - Check logs daily
3. **Test payments** - Never go live without testing
4. **Monitor SMS costs** - Track usage with sms_logs table
5. **Scale database** - Add indexes on frequently queried fields
6. **Use SSL** - All payment pages must be HTTPS
7. **Rate limiting** - Implement on API endpoints
8. **Logging** - All transactions logged automatically
9. **Monitoring** - Setup alerts for failed payments
10. **Backups** - Daily automated backups recommended

---

## **Support Quick Links**

- **Database Help**: Check database/subscription_schema.sql
- **SMS Help**: Check services/SMSService.php comments
- **React Help**: Check REACT_SMS_INTEGRATION.md
- **Payment Help**: Check NAGAD_DISCOUNT_SETUP.md
- **Complete Guide**: Check MASTER_IMPLEMENTATION_GUIDE.md

---

**Your SaaS System is READY! 🚀**

Start with: **MASTER_IMPLEMENTATION_GUIDE.md**

*Version: 3.0 Final*
