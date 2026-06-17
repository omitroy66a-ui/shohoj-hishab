# 📦 SaaS Subscription System Documentation

## Overview
A complete subscription management system with trial periods, multiple pricing tiers, payment tracking, and automatic activation.

---

## 🏗️ Architecture

### Database Schema

#### 1. **subscription_plans**
Main subscription plans table
```sql
CREATE TABLE subscription_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    plan_type ENUM('trial', 'standard', 'advanced') NOT NULL UNIQUE,
    price DECIMAL(10,2),
    duration_days INT,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE
);
```

#### 2. **plan_pricing**
Pricing for different durations (monthly, 6-months, yearly)
```sql
CREATE TABLE plan_pricing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_id INT NOT NULL,
    duration_type ENUM('monthly', 'six_months', 'yearly'),
    price DECIMAL(10,2),
    discount_percentage DECIMAL(5,2) DEFAULT 0,
    UNIQUE (plan_id, duration_type)
);
```

#### 3. **business_subscriptions**
Active subscriptions for businesses
```sql
CREATE TABLE business_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_id INT NOT NULL,
    plan_id INT NOT NULL,
    start_date DATE,
    expiry_date DATE,
    status ENUM('active', 'expired', 'cancelled', 'pending'),
    FOREIGN KEY (plan_id) REFERENCES subscription_plans(id)
);
```

#### 4. **subscription_payments** ⭐ KEY TABLE
Payment tracking with transaction details
```sql
CREATE TABLE subscription_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_subscription_id INT NOT NULL,
    business_id INT NOT NULL,
    payment_number VARCHAR(100) UNIQUE,      -- User's payment reference
    transaction_id VARCHAR(100) UNIQUE,       -- Gateway transaction ID
    amount DECIMAL(10,2),
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'completed', 'failed', 'cancelled'),
    reviewed_by INT,                          -- Admin who reviewed
    approved_at TIMESTAMP NULL,               -- When approved
    rejection_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 5. **feature_permissions**
Which features are available in each plan
```sql
CREATE TABLE feature_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_type VARCHAR(30),
    feature_key VARCHAR(100),
    feature_name VARCHAR(150),
    UNIQUE (plan_type, feature_key)
);
```

#### 6. **subscription_history**
Audit log for subscription changes
```sql
CREATE TABLE subscription_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_id INT,
    old_plan_id INT,
    new_plan_id INT,
    action VARCHAR(100),
    changed_by INT,
    reason TEXT,
    created_at TIMESTAMP
);
```

---

## 🔄 Payment Flow

### 1. User Upgrades Plan
```
1. User clicks "Upgrade" → upgrade.php
2. Selects plan + duration
3. Enters payment details (payment_number, transaction_id)
4. Form submits to upgrade.php
```

### 2. Backend Processing
```
File: /modules/upgrade.php
1. SubscriptionService::upgradeSubscription()
   - Creates business_subscriptions record (status: 'pending')
   
2. SubscriptionService::processPayment()
   - Creates subscription_payments record (payment_status: 'pending')
   - Stores: payment_number, transaction_id, amount, business_id
```

### 3. Admin Review
```
File: /super_admin/subscription_payments.php
- Admin sees all pending payments in dashboard
- Reviews: payment_number, transaction_id, business_id
- Clicks: "Approve" or "Reject"
```

### 4. Auto-Activation (KEY FEATURE)
```
Endpoint: /api/subscription.php?action=approve_payment (POST)

SubscriptionService::approvePaymentAndActivate($payment_id):
1. Marks subscription_payments.payment_status = 'completed'
2. Sets subscription_payments.approved_at = NOW()
3. Updates business_subscriptions.status = 'active'
4. Logs subscription_history
5. Plan is now ACTIVE!
```

---

## 📋 File Structure

```
/config
  ├── database.php              - DB connection
  └── auth.php                  - Session check

/services
  ├── SubscriptionService.php   - Core subscription logic
  └── SubscriptionMiddleware.php - Feature access control

/database
  └── subscription_schema.sql    - Database setup

/api
  └── subscription.php           - REST API endpoints

/modules
  ├── subscription.php           - User dashboard
  ├── upgrade.php               - Payment form
  └── ...

/super_admin
  └── subscription_payments.php  - Admin payment panel

/cron_expire_subscriptions.php   - Auto-expire cron job
```

---

## 🚀 Implementation Guide

### Step 1: Setup Database
```bash
# Import the schema
mysql -u user -p database < database/subscription_schema.sql
```

### Step 2: Auto-Trial on Registration
User registration automatically creates 3-day trial:
```php
// /api/auth.php
$subscriptionService->createTrialSubscription($business_id);
```

### Step 3: Setup Cron Job
Auto-expire subscriptions daily:
```bash
# crontab -e
0 0 * * * curl "https://yoursite.com/cron_expire_subscriptions.php?token=your_secure_token"
```

### Step 4: Feature Access Control
In your feature pages:
```php
require_once 'services/SubscriptionMiddleware.php';
$middleware = new SubscriptionMiddleware($conn);

// Check if user can access feature
$access = $middleware->canAccessFeature($business_id, 'ecommerce');
if (!$access['can_access']) {
    die($middleware->getRestrictedFeatureMessage($business_id, 'ecommerce'));
}
```

---

## 📱 API Endpoints

### User Endpoints

#### Get Subscription Status
```
GET /api/subscription.php?action=get_status
Response: {
    "status": "active",
    "type": "trial",
    "plan": "Free Trial",
    "days_remaining": 2,
    "features": [...]
}
```

#### Get All Plans
```
GET /api/subscription.php?action=get_plans
Response: {
    "data": [
        {
            "id": 1,
            "name": "Free Trial",
            "plan_type": "trial",
            "price": 0,
            "pricing_options": {}
        },
        ...
    ]
}
```

#### Upgrade Plan
```
POST /api/subscription.php?action=upgrade
Body: {
    "plan_id": 3,
    "duration_type": "monthly"
}
Response: {
    "success": true,
    "subscription_id": 42,
    "amount": 199
}
```

#### Process Payment
```
POST /api/subscription.php?action=pay
Body: {
    "subscription_id": 42,
    "payment_number": "12345678",
    "transaction_id": "TXN-20231215-ABC123",
    "amount": 199,
    "payment_method": "bkash"
}
Response: {
    "success": true,
    "payment_id": 15,
    "status": "pending"
}
```

#### Check Feature Access
```
GET /api/subscription.php?action=check_feature&feature=ecommerce
Response: {
    "can_access": true,
    "plan": "advanced"
}
```

### Admin Endpoints

#### Get Pending Payments
```
GET /api/subscription.php?action=pending_payments&limit=50
(Requires: admin/super_admin role)
```

#### Approve Payment (AUTO-ACTIVATE)
```
POST /api/subscription.php?action=approve_payment
Body: {
    "payment_id": 15
}
Response: {
    "success": true,
    "message": "Payment approved and subscription activated successfully"
}
```

#### Reject Payment
```
POST /api/subscription.php?action=reject_payment
Body: {
    "payment_id": 15,
    "reason": "Invalid transaction ID"
}
```

---

## 💰 Plan Structure

| Feature | Trial | Standard | Advanced |
|---------|-------|----------|----------|
| **Duration** | 3 Days | Monthly | Monthly |
| **Price** | Free | ৳60 | ৳199 |
| **6-Month Price** | - | ৳219 | ৳999 |
| **Yearly Price** | - | ৳699 | ৳1999 |
| **POS** | ✅ | ✅ | ✅ |
| **Inventory** | ✅ | ❌ | ✅ |
| **Multi-Branch** | ✅ | ❌ | ✅ |
| **E-commerce** | ✅ | ❌ | ✅ |
| **API Access** | ✅ | ❌ | ✅ |
| **Advanced Reports** | ✅ | ❌ | ✅ |

---

## 🔐 Security Features

1. **Payment Validation**
   - Duplicate payment_number prevention (UNIQUE constraint)
   - Duplicate transaction_id prevention (UNIQUE constraint)
   - Admin review before activation

2. **Access Control**
   - Feature access tied to subscription status
   - Expired subscriptions auto-locked
   - Role-based admin access

3. **Audit Trail**
   - All changes logged in subscription_history
   - Admin tracked with reviewed_by
   - Rejection reasons recorded

---

## 🐛 Troubleshooting

### Payment Not Activating?
1. Check subscription_payments table - status should be 'pending'
2. Admin must click "Approve" in /super_admin/subscription_payments.php
3. Verify approvePaymentAndActivate() is called

### Trial Not Creating?
1. Ensure SubscriptionService.php is included in /api/auth.php
2. Check database/subscription_schema.sql is imported
3. Verify businesses table exists

### Features Not Accessible?
1. Check feature_permissions table has correct entries
2. Verify subscription status is 'active'
3. Check expiry_date hasn't passed

---

## 📞 Support

Files to modify when integrating:
- `/api/auth.php` - Add trial creation
- Any feature pages - Add feature middleware check
- Admin dashboard - Link to subscription_payments.php

---

## Version
**v1.0** - Initial Release
- ✅ Multi-tier subscription plans
- ✅ Payment tracking with transaction IDs
- ✅ Auto-activation after admin approval
- ✅ Feature access control
- ✅ Auto-expire functionality
- ✅ Audit logging
