# 🚀 Nagad Payment + Auto-Send + Discount System

## ✨ New Features Added

### 1️⃣ **Nagad Payment Integration**
- ✅ Nagad number: **01763206165**
- ✅ Other gateways: bKash, Rocket (configurable)
- ✅ Gateway management in database
- ✅ Payment instruction generation

### 2️⃣ **Automatic Subscription Queue System**
- ✅ Database-driven subscription automation
- ✅ Queue table for pending actions
- ✅ Auto-send subscriptions to users
- ✅ Auto-activate after approval
- ✅ Error handling and retry mechanism

### 3️⃣ **Admin Discount Management**
- ✅ Apply flat amount discount
- ✅ Apply percentage discount
- ✅ Remove/revert discounts
- ✅ Discount history tracking
- ✅ Discount audit log

---

## 📊 New Database Tables

```sql
-- Payment Gateways Configuration
payment_gateways:
  - gateway_name (Nagad, bKash, etc)
  - phone_number (01763206165)
  - account_name
  - config_data (JSON)

-- Subscription Queue
subscription_queue:
  - business_subscription_id
  - action (send, activate, notify)
  - status (pending, sent, completed, failed)
  - created_at

-- Discount History
subscription_discounts:
  - business_id
  - discount_amount
  - discount_reason
  - applied_by (admin ID)
  - applied_at
```

---

## 🔧 Setup Steps

### Step 1: Import Database Updates
```bash
mysql -u root -p your_db < database/subscription_updates.sql
```

### Step 2: Verify Nagad Configuration
```sql
SELECT * FROM payment_gateways WHERE gateway_name = 'Nagad';
-- Should show: 01763206165
```

### Step 3: Setup Cron Jobs

**For Auto-Expire (daily at midnight):**
```bash
0 0 * * * curl -s "https://yoursite.com/cron_expire_subscriptions.php?token=your_secure_token"
```

**For Auto-Send (every hour):**
```bash
0 * * * * curl -s "https://yoursite.com/cron_send_subscriptions.php?token=your_secure_token"
```

### Step 4: Link Admin Dashboard
Add to your admin navigation:
```html
<a href="super_admin/subscription_dashboard.php">Subscription Management</a>
```

---

## 💰 How It Works

### User Upgrade Flow

```
1. User selects plan + duration
   ↓
2. Selects payment gateway (Nagad, bKash, etc)
   ↓
3. System shows:
   📱 Nagad: 01763206165
   💳 Send ৳X
   ↓
4. User sends payment
   ↓
5. User enters:
   - Payment Number (transaction reference)
   - Transaction ID (unique from Nagad)
   ↓
6. Payment recorded as "pending" in database
   ↓
7. Subscription added to queue (action: 'send')
```

### Admin Approval + Auto-Send

```
1. Admin opens subscription_dashboard.php
   ↓
2. Admin sees pending payments
   ↓
3. Admin verifies:
   - Payment Number
   - Transaction ID
   - Amount matches
   ↓
4. Admin clicks "Approve"
   ↓
5. Automatic activation happens:
   - subscription_payments.status = 'completed'
   - business_subscriptions.status = 'active'
   - Added to queue for sending
   ↓
6. Cron job processes queue:
   - Sends subscription details to user
   - Updates subscription_queue.status = 'completed'
```

### Admin Discount Application

```
1. Admin opens subscription_dashboard.php
   ↓
2. Finds subscription needing discount
   ↓
3. Clicks "Apply Discount"
   ↓
4. Enters:
   - Discount Amount (৳ 100) OR Percentage (20%)
   - Reason: "Loyalty discount", "Bulk purchase", etc
   ↓
5. System calculates:
   - Original Price: ৳ 500
   - Discount: ৳ 100
   - Final Price: ৳ 400
   ↓
6. Records in subscription_discounts table
   ↓
7. Admin can view discount history anytime
```

---

## 📱 Payment Gateway Configuration

### Current Setup (in database)

| Gateway | Phone | Account |
|---------|-------|---------|
| Nagad | 01763206165 | Sohoj Hishab |
| bKash | 01700000000 | Sohoj Hishab |
| Rocket | 01700000000 | Sohoj Hishab |

### To Add New Gateway
```sql
INSERT INTO payment_gateways (gateway_name, phone_number, account_name, is_active)
VALUES ('Google Pay', '01900000000', 'Sohoj Hishab', TRUE);
```

---

## 🔌 API Integration

### Get Available Gateways
```php
$paymentGatewayService = new PaymentGatewayService($conn);
$gateways = $paymentGatewayService->getAllGateways();

// Returns: ['Nagad', 'bKash', 'Rocket', ...]
```

### Get Nagad Details
```php
$nagad = $paymentGatewayService->getNagadDetails();
// Returns: ['gateway_name' => 'Nagad', 'phone_number' => '01763206165', ...]
```

### Generate Payment Instruction
```php
$instruction = $paymentGatewayService->generatePaymentInstruction(
    amount: 500,
    gateway_name: 'Nagad',
    payment_reference: 'INV-12345'
);
// Returns: "Send ৳500 to Sohoj Hishab\n📱 Nagad: 01763206165\n📝 Reference: INV-12345"
```

### Apply Discount
```php
$discountService = new SubscriptionDiscountService($conn);
$result = $discountService->applyDiscount(
    business_subscription_id: 42,
    discount_amount: 100,
    discount_reason: 'Admin promo',
    admin_id: 1
);
```

### Apply Percentage Discount
```php
$result = $discountService->applyPercentageDiscount(
    business_subscription_id: 42,
    discount_percentage: 20,
    discount_reason: 'Launch promotion',
    admin_id: 1
);
```

---

## 📋 Files Created/Updated

```
✅ NEW:
  database/subscription_updates.sql
  services/PaymentGatewayService.php
  services/SubscriptionDiscountService.php
  services/SubscriptionQueueService.php
  super_admin/subscription_dashboard.php
  cron_send_subscriptions.php

✅ UPDATED:
  modules/upgrade.php (Nagad integration)
```

---

## 🎯 Usage Examples

### Example 1: Payment Gateway Display
```php
<?php
require_once 'services/PaymentGatewayService.php';
$paymentGatewayService = new PaymentGatewayService($conn);

$gateways = $paymentGatewayService->getAllGateways();
foreach ($gateways as $gateway) {
    echo $gateway['gateway_name'] . ': ' . $gateway['phone_number'];
}
?>
```

### Example 2: Admin Apply Discount
```php
<?php
require_once 'services/SubscriptionDiscountService.php';
$discountService = new SubscriptionDiscountService($conn);

$result = $discountService->applyDiscount(
    business_subscription_id: 42,
    discount_amount: 100,
    discount_reason: 'Customer loyalty program',
    admin_id: 1
);

if ($result['success']) {
    echo "Discount applied! Final price: ৳" . $result['final_price'];
}
?>
```

### Example 3: Queue Processing
```php
<?php
require_once 'services/SubscriptionQueueService.php';
$queueService = new SubscriptionQueueService($conn);

// This would be called via cron
$result = $queueService->processQueue(limit: 100);
echo "Processed: " . $result['processed'] . " items";
?>
```

---

## 🔒 Security Notes

1. **Cron Token**: Change in both cron files
   ```php
   $secret_token = 'your_secure_cron_token_here';
   ```

2. **Payment Verification**: 
   - Always verify transaction ID before approving
   - Check payment amount matches subscription

3. **Admin Only**: 
   - Discount operations restricted to admin role
   - All admin actions logged with user ID

4. **Audit Trail**: 
   - All discounts tracked in subscription_discounts
   - Admin who applied discount is recorded
   - Discount reason stored for audit

---

## 📊 Admin Dashboard Features

**URL:** `/super_admin/subscription_dashboard.php`

✅ View pending payments (all gateways)
✅ Approve/Reject payments with one-click
✅ View all payment gateways (Nagad number, etc)
✅ View subscriptions with active discounts
✅ See total discounts given
✅ Discount statistics

---

## ⚠️ Important Notes

1. **Payment Status Flow:**
   - pending → (admin approves) → completed → active

2. **Nagad Phone:** 01763206165
   - Users send money to this number
   - They provide transaction ID

3. **Queue Processing:**
   - Cron job runs every hour
   - Processes up to 100 items per run
   - Handles errors gracefully

4. **Discount Limits:**
   - No built-in maximum discount %
   - Admin must manage manually
   - All discounts logged for audit

---

## 🧪 Testing

### Test 1: Nagad Payment
1. User selects Nagad payment method
2. Verify: "Send ৳X to 01763206165" is shown
3. User enters payment number + transaction ID
4. Admin approves
5. Subscription activates

### Test 2: Discount Application
1. Open subscription_dashboard.php
2. Find active subscription
3. Apply ৳100 discount
4. Verify: discount shown in "Subscriptions with Discounts" table
5. Check subscription_discounts table

### Test 3: Queue Processing
1. Create subscription
2. Manually add to queue: `INSERT INTO subscription_queue (...)`
3. Run cron job
4. Verify: queue item marked as completed

---

## 🎉 You're All Set!

All features ready:
- ✅ Nagad integration (01763206165)
- ✅ Multiple payment gateways
- ✅ Automatic queue processing
- ✅ Admin discount management
- ✅ Audit trail & history

**Start accepting payments now!** 🚀
