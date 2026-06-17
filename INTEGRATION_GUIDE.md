# 🔧 Quick Integration Checklist

## ✅ Step-by-Step Setup

### 1️⃣ Database Setup
```bash
# SSH/Terminal এ run করুন:
mysql -u root -p your_database < database/subscription_schema.sql
```

---

### 2️⃣ Update Registration Flow
**File:** `/api/auth.php` (Already Updated ✓)

The file already includes auto-trial creation. Just verify this section exists:
```php
require_once __DIR__ . '/../services/SubscriptionService.php';
// ...
$subscriptionService = new SubscriptionService($conn);
$trial_result = $subscriptionService->createTrialSubscription($business_id);
```

---

### 3️⃣ Add Feature Access Check
Add this to any feature page (e.g., POS, E-commerce, etc.):

```php
<?php
require_once 'config/auth.php';
require_once 'config/database.php';
require_once 'services/SubscriptionMiddleware.php';

$middleware = new SubscriptionMiddleware($conn);
$business_id = $_SESSION['business_id'];

// Check subscription is active
$check = $middleware->checkSubscription($business_id);
if (!$check['has_subscription']) {
    http_response_code(403);
    die('No active subscription. Please upgrade.');
}

// Check specific feature access
$feature_access = $middleware->canAccessFeature($business_id, 'ecommerce');
if (!$feature_access['can_access']) {
    http_response_code(403);
    die($middleware->getRestrictedFeatureMessage($business_id, 'ecommerce'));
}

// Feature is accessible - proceed normally
?>
```

---

### 4️⃣ Setup Cron Job (Auto-Expire)

**Option A: Linux Cron**
```bash
# crontab -e
0 0 * * * curl -s "https://yoursite.com/cron_expire_subscriptions.php?token=your_secure_token" >> /var/log/subscription_cron.log 2>&1
```

**Option B: CPanel Cron**
```
Command: php /home/user/public_html/cron_expire_subscriptions.php?token=your_secure_token
Time: Daily at 12:00 AM
```

---

### 5️⃣ Update Navigation
Link user to subscription dashboard:
```html
<!-- In your main navigation -->
<a href="modules/subscription.php">My Subscription</a>

<!-- Or for admin -->
<a href="super_admin/subscription_payments.php">Payment Review</a>
```

---

## 🔒 Secure Your Cron Token

**File:** `/cron_expire_subscriptions.php`

```php
// Change this to a strong token!
$secret_token = 'your_secure_cron_token_here';
```

Generate a secure token:
```bash
# Linux
openssl rand -base64 32

# Or use: a6f9b2d8e1c4k7x9m2n5p8r1t4w7z9a2
```

---

## 📊 Database Verification

After setup, verify tables exist:
```sql
SHOW TABLES LIKE 'subscription%';
SHOW TABLES LIKE 'business_subscriptions';
SHOW TABLES LIKE 'plan_pricing';
SHOW TABLES LIKE 'feature_permissions';

-- Should see these 6 tables:
-- 1. subscription_plans
-- 2. plan_pricing
-- 3. business_subscriptions
-- 4. subscription_payments
-- 5. feature_permissions
-- 6. subscription_history
```

---

## 🧪 Testing the System

### Test 1: Trial Creation
```
1. Register new user
2. Check database:
   SELECT * FROM business_subscriptions WHERE business_id = {business_id};
3. Should show: plan_id=1, status='active', expiry_date=today+3days
```

### Test 2: Feature Access
```php
// In PHP console or test file
$middleware = new SubscriptionMiddleware($conn);
$access = $middleware->canAccessFeature(1, 'ecommerce');
var_dump($access); // Should return ['can_access' => true, ...]
```

### Test 3: Payment Flow
```
1. User upgrades plan at /modules/upgrade.php
2. Fills: Payment Method, Payment Number, Transaction ID
3. Check database:
   SELECT * FROM subscription_payments WHERE payment_status = 'pending';
4. Should show pending payment
5. Admin approves at /super_admin/subscription_payments.php
6. Check business_subscriptions.status = 'active'
```

### Test 4: Trial Expiry
```
1. Manually update trial expiry:
   UPDATE business_subscriptions SET expiry_date = '2024-01-01' WHERE plan_type = 'trial';
2. Run cron job: curl "site.com/cron_expire_subscriptions.php?token=..."
3. Check status changed to 'expired'
```

---

## 📝 API Integration Examples

### Example 1: Check Subscription Status in Frontend
```javascript
// JavaScript/AJAX
fetch('/api/subscription.php?action=get_status')
    .then(r => r.json())
    .then(data => {
        console.log('Plan:', data.data.plan);
        console.log('Days Remaining:', data.data.days_remaining);
    });
```

### Example 2: Check Feature Before Showing UI
```php
<?php
// Get available features
$features = $middleware->getAvailableFeatures($business_id);
$feature_keys = array_column($features, 'feature_key');

if (in_array('ecommerce', $feature_keys)) {
    // Show e-commerce menu
    echo '<li><a href="ecommerce.php">E-commerce</a></li>';
}
?>
```

### Example 3: Auto-Upgrade on Payment
```php
// After payment processing
$result = $subscriptionService->processPayment(
    $subscription_id,
    'PAYNUMBER123',
    'TXN-ABC123',
    199,
    'bkash'
);

if ($result['success']) {
    // Redirect to admin dashboard
    header('Location: /super_admin/subscription_payments.php');
}
```

---

## ⚠️ Important Notes

1. **Payment Processing**: Payments are 'pending' until admin approves
2. **Auto Activation**: Once admin approves, subscription status becomes 'active' IMMEDIATELY
3. **Trial Lock**: Trial automatically expires after 3 days (checked by cron)
4. **Feature Access**: Based on current active subscription
5. **Transaction IDs**: Must be unique (prevents duplicate payments)

---

## 🆘 Common Issues

### Issue: Users can't see subscription page
**Solution:** Add to their navigation
```php
if (isset($_SESSION['business_id'])) {
    echo '<a href="modules/subscription.php">Subscription</a>';
}
```

### Issue: Payment not auto-activating
**Solution:** Check admin review status
```sql
SELECT * FROM subscription_payments WHERE id = {payment_id};
-- Verify admin clicked "Approve" button
```

### Issue: Features still locked after upgrade
**Solution:** Check feature_permissions
```sql
SELECT * FROM feature_permissions WHERE plan_type = 'advanced';
-- Add missing features if needed
```

### Issue: Cron job not running
**Solution:** Test manually
```bash
curl -v "https://yoursite.com/cron_expire_subscriptions.php?token=your_token"
# Should return JSON success message
```

---

## 📈 Next Steps (Optional Enhancements)

- [ ] Email notifications on payment/activation
- [ ] Recurring billing automation
- [ ] Stripe/Razorpay integration
- [ ] Subscription analytics dashboard
- [ ] Automatic invoice generation
- [ ] Subscription cancellation workflow

---

## 📞 Support Files

Files created for you:
```
✓ /database/subscription_schema.sql
✓ /services/SubscriptionService.php
✓ /services/SubscriptionMiddleware.php
✓ /api/subscription.php
✓ /modules/subscription.php
✓ /modules/upgrade.php
✓ /super_admin/subscription_payments.php
✓ /cron_expire_subscriptions.php
✓ /api/auth.php (UPDATED)
✓ SUBSCRIPTION_DOCS.md
✓ INTEGRATION_GUIDE.md (this file)
```

---

## 🎉 You're All Set!

Your SaaS Subscription System is ready to use. Main features:

✅ **Auto Trial** - 3-day free trial on registration
✅ **Payment Tracking** - Payment number + Transaction ID
✅ **Admin Review** - Admin approves before activation
✅ **Auto Activation** - Subscription status becomes active immediately
✅ **Feature Access** - Middleware checks plan features
✅ **Auto Expire** - Cron job expires old subscriptions

---

Questions? Check SUBSCRIPTION_DOCS.md for complete API reference.
